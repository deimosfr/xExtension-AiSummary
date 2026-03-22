<?php

declare(strict_types=1);

final class FreshExtension_AiSummary_Controller extends Minz_ActionController {

	private const DEFAULT_MODELS = [
		'openai' => 'gpt-4o-mini',
		'anthropic' => 'claude-sonnet-4-6',
		'gemini' => 'gemini-2.5-flash',
		'ollama' => 'llama3.2',
	];

	private const DEFAULT_OLLAMA_URL = 'http://localhost:11434';

	private const MAX_CONTENT_LENGTH = 12000;

	private const MIN_CONTENT_LENGTH = 200;

	private const DEFAULT_PROMPT = <<<'PROMPT'
Based on the following requirements, please analyze the article and produce the output that includes a concise summary, key takeaways, and additional contextual insights. The output language should be {language}. Use clear, accessible, and natural phrasing suitable for general readers.

1. Provide a 50 words long concise and engaging summary that captures the article's core viewpoints and main idea.
2. List the most important insights or facts using clear and easy-to-understand language.
3. If the article contains chronological information or significant events, include a short timeline summarizing the key moments in order.
4. Give a brief background to help readers understand the article's context — such as why the topic matters or what situation it addresses.

Article Title: {title}
Article Content: {content}

Provide the content directly, without any additional explanatory text.
PROMPT;

	/** @var array<string, string> */
	private const LANGUAGE_NAMES = [
		'cs' => 'Czech',
		'de' => 'German',
		'en' => 'English',
		'es' => 'Spanish',
		'fr' => 'French',
		'it' => 'Italian',
		'ja' => 'Japanese',
		'ko' => 'Korean',
		'nl' => 'Dutch',
		'pl' => 'Polish',
		'pt-br' => 'Portuguese',
		'ru' => 'Russian',
		'tr' => 'Turkish',
		'zh-cn' => 'Chinese',
	];

	public function summarizeAction(): void {
		$this->view->_layout(null);

		// --- Validation phase (JSON errors) ---
		if (!FreshRSS_Auth::hasAccess()) {
			header('Content-Type: application/json; charset=UTF-8');
			$this->sendJsonError('Unauthorized', 403);
			return;
		}

		// CSRF protection
		if (Minz_Request::param('_csrf') !== FreshRSS_Auth::csrfToken()) {
			Minz_Error::error(403);
		}

		$id = Minz_Request::param('id', '');
		if ($id === '') {
			header('Content-Type: application/json; charset=UTF-8');
			$this->sendJsonError('Missing entry ID', 400);
			return;
		}

		$entryDao = FreshRSS_Factory::createEntryDao();
		$entry = $entryDao->searchById($id);
		if ($entry === null) {
			header('Content-Type: application/json; charset=UTF-8');
			$this->sendJsonError('Entry not found', 404);
			return;
		}

		$provider = FreshRSS_Context::$user_conf->ai_summary_provider ?? 'openai';
		$apiKey = FreshRSS_Context::$user_conf->ai_summary_api_key ?? '';
		$model = FreshRSS_Context::$user_conf->ai_summary_model ?? '';
		$apiUrl = FreshRSS_Context::$user_conf->ai_summary_api_url ?? '';
		$customPrompt = FreshRSS_Context::$user_conf->ai_summary_prompt ?? '';

		if ($provider !== 'ollama' && $apiKey === '') {
			header('Content-Type: application/json; charset=UTF-8');
			$this->sendJsonError('API key not configured. Please configure the extension settings.', 400);
			return;
		}

		// --- Streaming phase (SSE) ---
		$this->beginStreaming();

		$content = $this->extractText($entry->content());

		// If RSS content is too short, fetch the full article from the original URL
		if (mb_strlen($content) < self::MIN_CONTENT_LENGTH) {
			$link = $entry->link();
			if ($link !== '') {
				$this->sendEvent('status', json_encode(['message' => 'Fetching full article…']));
				$fetched = $this->fetchArticleContent($link);
				if ($fetched !== '') {
					$content = $fetched;
				}
			}
		}

		$content = mb_substr($content, 0, self::MAX_CONTENT_LENGTH);

		$promptTemplate = $customPrompt !== '' ? $customPrompt : self::DEFAULT_PROMPT;
		$langCode = FreshRSS_Context::$user_conf->ai_summary_language ?? '';
		if ($langCode === '') {
			$langCode = FreshRSS_Context::$user_conf->language ?? 'en';
		}
		$language = self::LANGUAGE_NAMES[$langCode] ?? $langCode;

		// Template prompt: placeholders are substituted and sent as user message
		// Legacy prompt (no {content}): used as system message with article as user message
		if (str_contains($promptTemplate, '{content}')) {
			$systemPrompt = '';
			$userPrompt = str_replace(
				['{language}', '{title}', '{content}'],
				[$language, $entry->title(), $content],
				$promptTemplate,
			);
		} else {
			$systemPrompt = $promptTemplate;
			$userPrompt = 'Title: ' . $entry->title() . "\n\n" . $content;
		}

		$this->sendEvent('status', json_encode(['message' => 'Generating summary…']));

		try {
			match ($provider) {
				'openai' => $this->callOpenAI($apiKey, $model ?: self::DEFAULT_MODELS['openai'], $systemPrompt, $userPrompt),
				'anthropic' => $this->callAnthropic($apiKey, $model ?: self::DEFAULT_MODELS['anthropic'], $systemPrompt, $userPrompt),
				'gemini' => $this->callGemini($apiKey, $model ?: self::DEFAULT_MODELS['gemini'], $systemPrompt, $userPrompt),
				'ollama' => $this->callOllama(
					$apiUrl !== '' ? $apiUrl : self::DEFAULT_OLLAMA_URL,
					$model ?: self::DEFAULT_MODELS['ollama'],
					$systemPrompt,
					$userPrompt,
				),
				default => throw new \RuntimeException('Unknown provider: ' . $provider),
			};
			$this->sendEvent('done', '{}');
		} catch (\Exception $e) {
			$this->sendEvent('error', json_encode(['message' => $e->getMessage()]));
		}
	}

	private function beginStreaming(): void {
		if (defined('AI_SUMMARY_TESTING')) {
			return;
		}

		header('Content-Type: text/event-stream; charset=UTF-8');
		header('Cache-Control: no-cache');
		header('X-Accel-Buffering: no');
		@ini_set('output_buffering', 'off');
		@ini_set('zlib.output_compression', false);
		if (PHP_SAPI !== 'cli') {
			while (ob_get_level() > 0) {
				ob_end_flush();
			}
			ob_implicit_flush(true);
		}
	}

	private function sendEvent(string $event, string $data): void {
		echo "event: {$event}\ndata: {$data}\n\n";
	}

	private function callOpenAI(string $apiKey, string $model, string $systemPrompt, string $userPrompt): void {
		$messages = [];
		if ($systemPrompt !== '') {
			$messages[] = ['role' => 'system', 'content' => $systemPrompt];
		}
		$messages[] = ['role' => 'user', 'content' => $userPrompt];

		$this->curlStreamRequest(
			'https://api.openai.com/v1/chat/completions',
			['model' => $model, 'messages' => $messages, 'max_tokens' => 1024, 'stream' => true],
			['Authorization: Bearer ' . $apiKey, 'Content-Type: application/json'],
			function (string $line): void {
				if (!str_starts_with($line, 'data: ')) {
					return;
				}
				$json = substr($line, 6);
				if (trim($json) === '[DONE]') {
					return;
				}
				$data = json_decode($json, true);
				$text = $data['choices'][0]['delta']['content'] ?? '';
				if ($text !== '') {
					$this->sendEvent('chunk', json_encode(['text' => $text], JSON_UNESCAPED_UNICODE));
				}
			},
		);
	}

	private function callAnthropic(string $apiKey, string $model, string $systemPrompt, string $userPrompt): void {
		$payload = [
			'model' => $model,
			'max_tokens' => 1024,
			'stream' => true,
			'messages' => [
				['role' => 'user', 'content' => $userPrompt],
			],
		];
		if ($systemPrompt !== '') {
			$payload['system'] = $systemPrompt;
		}

		$this->curlStreamRequest(
			'https://api.anthropic.com/v1/messages',
			$payload,
			['x-api-key: ' . $apiKey, 'anthropic-version: 2023-06-01', 'Content-Type: application/json'],
			function (string $line): void {
				if (!str_starts_with($line, 'data: ')) {
					return;
				}
				$data = json_decode(substr($line, 6), true);
				if (($data['type'] ?? '') === 'content_block_delta') {
					$text = $data['delta']['text'] ?? '';
					if ($text !== '') {
						$this->sendEvent('chunk', json_encode(['text' => $text], JSON_UNESCAPED_UNICODE));
					}
				}
			},
		);
	}

	private function callGemini(string $apiKey, string $model, string $systemPrompt, string $userPrompt): void {
		$url = 'https://generativelanguage.googleapis.com/v1beta/models/'
			. urlencode($model)
			. ':streamGenerateContent?alt=sse&key=' . urlencode($apiKey);

		$payload = [
			'contents' => [
				['parts' => [['text' => $userPrompt]]],
			],
		];
		if ($systemPrompt !== '') {
			$payload['system_instruction'] = [
				'parts' => [['text' => $systemPrompt]],
			];
		}

		$this->curlStreamRequest(
			$url,
			$payload,
			['Content-Type: application/json'],
			function (string $line): void {
				if (!str_starts_with($line, 'data: ')) {
					return;
				}
				$data = json_decode(substr($line, 6), true);
				$text = $data['candidates'][0]['content']['parts'][0]['text'] ?? '';
				if ($text !== '') {
					$this->sendEvent('chunk', json_encode(['text' => $text], JSON_UNESCAPED_UNICODE));
				}
			},
		);
	}

	private function callOllama(string $apiUrl, string $model, string $systemPrompt, string $userPrompt): void {
		$url = rtrim($apiUrl, '/') . '/api/chat';

		$messages = [];
		if ($systemPrompt !== '') {
			$messages[] = ['role' => 'system', 'content' => $systemPrompt];
		}
		$messages[] = ['role' => 'user', 'content' => $userPrompt];

		$this->curlStreamRequest(
			$url,
			['model' => $model, 'messages' => $messages],
			['Content-Type: application/json'],
			function (string $line): void {
				$data = json_decode($line, true);
				if (!is_array($data)) {
					return;
				}
				if (isset($data['error'])) {
					$msg = is_string($data['error']) ? $data['error'] : ($data['error']['message'] ?? 'Unknown error');
					throw new \RuntimeException('Ollama: ' . $msg);
				}
				$text = $data['message']['content'] ?? '';
				if ($text !== '') {
					$this->sendEvent('chunk', json_encode(['text' => $text], JSON_UNESCAPED_UNICODE));
				}
			},
		);
	}

	private function extractText(string $html): string {
		$text = strip_tags($html);
		$text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		$text = (string) preg_replace('/\s+/', ' ', trim($text));
		return $text;
	}

	private function fetchArticleContent(string $url): string {
		$scheme = parse_url($url, PHP_URL_SCHEME);
		if (!in_array($scheme, ['http', 'https'], true)) {
			return '';
		}

		$ch = curl_init($url);
		if ($ch === false) {
			return '';
		}

		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 5,
			CURLOPT_TIMEOUT => 15,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_USERAGENT => 'FreshRSS/AiSummary',
			CURLOPT_HTTPHEADER => ['Accept: text/html'],
		]);

		$html = curl_exec($ch);
		$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		unset($ch);

		if (!is_string($html) || $httpCode >= 400 || $html === '') {
			return '';
		}

		return $this->extractReadableText($html);
	}

	private function extractReadableText(string $html): string {
		// Remove script, style, nav, header, footer, aside tags and their content
		$html = (string) preg_replace(
			'#<\s*(script|style|nav|header|footer|aside|noscript)\b[^>]*>.*?</\s*\1\s*>#is',
			'',
			$html,
		);

		// Try to find <article> or <main> content first
		if (preg_match('#<\s*article\b[^>]*>(.*?)</\s*article\s*>#is', $html, $matches)) {
			$html = $matches[1];
		} elseif (preg_match('#<\s*main\b[^>]*>(.*?)</\s*main\s*>#is', $html, $matches)) {
			$html = $matches[1];
		} elseif (preg_match('#<\s*div\b[^>]*(?:class|id)\s*=\s*["\'][^"\']*(?:content|article|post|entry|body)[^"\']*["\'][^>]*>(.*?)</\s*div\s*>#is', $html, $matches)) {
			$html = $matches[1];
		}

		return $this->extractText($html);
	}

	/**
	 * @param array<string, mixed> $payload
	 * @param list<string> $headers
	 */
	private function curlStreamRequest(string $url, array $payload, array $headers, callable $lineHandler): void {
		$ch = curl_init($url);
		if ($ch === false) {
			throw new \RuntimeException('Failed to initialize cURL');
		}

		$buffer = '';
		$rawResponse = '';
		$error = null;

		curl_setopt_array($ch, [
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => json_encode($payload, JSON_THROW_ON_ERROR),
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_RETURNTRANSFER => false,
			CURLOPT_TIMEOUT => 120,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_WRITEFUNCTION => function ($ch, $data) use (&$buffer, &$rawResponse, $lineHandler, &$error) {
				if (strlen($rawResponse) < 4096) {
					$rawResponse .= $data;
				}
				$buffer .= $data;
				while (($pos = strpos($buffer, "\n")) !== false) {
					$line = trim(substr($buffer, 0, $pos));
					$buffer = substr($buffer, $pos + 1);
					if ($line !== '') {
						try {
							$lineHandler($line);
						} catch (\Exception $e) {
							$error = $e;
							return 0;
						}
					}
				}
				return strlen($data);
			},
		]);

		curl_exec($ch);
		$httpCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$curlError = curl_error($ch);
		unset($ch);

		if ($error !== null) {
			throw $error;
		}

		$remaining = trim($buffer);
		if ($remaining !== '') {
			$lineHandler($remaining);
		}

		if ($httpCode === 0 && $curlError !== '') {
			throw new \RuntimeException('HTTP request failed: ' . $curlError);
		}

		if ($httpCode >= 400) {
			$errorData = json_decode($rawResponse, true);
			if (is_array($errorData)) {
				$msg = $errorData['error']['message'] ?? $errorData['error'] ?? 'HTTP ' . $httpCode;
				throw new \RuntimeException('API error: ' . (is_string($msg) ? $msg : json_encode($msg)));
			}
			throw new \RuntimeException('API error: HTTP ' . $httpCode);
		}
	}

	/**
	 * @param array<string, mixed> $data
	 */
	private function sendJson(array $data, int $httpCode = 200): void {
		http_response_code($httpCode);
		echo json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE);
	}

	private function sendJsonError(string $message, int $httpCode): void {
		$this->sendJson(['error' => $message], $httpCode);
	}
}
