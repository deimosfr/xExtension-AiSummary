<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AiSummaryControllerTest extends TestCase {

	private FreshExtension_AiSummary_Controller $controller;

	protected function setUp(): void {
		$this->controller = new FreshExtension_AiSummary_Controller();
		FreshRSS_Context::init();
		FreshRSS_Auth::setAccess(true);
		Minz_Request::reset();
		Minz_Request::setParam('_csrf', 'test-csrf-token');
		FreshRSS_EntryDAO::clearEntries();
	}

	// ── Constants ──

	public function testDefaultModelsAreDefined(): void {
		$ref = new \ReflectionClassConstant(FreshExtension_AiSummary_Controller::class, 'DEFAULT_MODELS');
		$models = $ref->getValue();
		self::assertIsArray($models);
		self::assertArrayHasKey('openai', $models);
		self::assertArrayHasKey('anthropic', $models);
		self::assertArrayHasKey('gemini', $models);
		self::assertArrayHasKey('ollama', $models);
	}

	public function testDefaultOllamaUrl(): void {
		$ref = new \ReflectionClassConstant(FreshExtension_AiSummary_Controller::class, 'DEFAULT_OLLAMA_URL');
		self::assertSame('http://localhost:11434', $ref->getValue());
	}

	public function testMaxContentLengthIsPositive(): void {
		$ref = new \ReflectionClassConstant(FreshExtension_AiSummary_Controller::class, 'MAX_CONTENT_LENGTH');
		self::assertGreaterThan(0, $ref->getValue());
	}

	public function testDefaultPromptIsNotEmpty(): void {
		$ref = new \ReflectionClassConstant(FreshExtension_AiSummary_Controller::class, 'DEFAULT_PROMPT');
		self::assertNotEmpty($ref->getValue());
		self::assertIsString($ref->getValue());
	}

	// ── summarizeAction validation (JSON errors) ──

	public function testSummarizeReturnsUnauthorizedWhenNoAccess(): void {
		FreshRSS_Auth::setAccess(false);

		$output = $this->captureOutput(fn () => $this->controller->summarizeAction());

		$data = json_decode($output, true);
		self::assertSame('Unauthorized', $data['error']);
	}

	public function testSummarizeReturnsMissingIdError(): void {
		Minz_Request::setParam('_csrf', 'test-csrf-token');
		$output = $this->captureOutput(fn () => $this->controller->summarizeAction());

		$data = json_decode($output, true);
		self::assertSame('Missing entry ID', $data['error']);
	}

	public function testSummarizeReturnsEntryNotFound(): void {
		Minz_Request::setParam('id', 'nonexistent-123');
		Minz_Request::setParam('_csrf', 'test-csrf-token');

		$output = $this->captureOutput(fn () => $this->controller->summarizeAction());

		$data = json_decode($output, true);
		self::assertSame('Entry not found', $data['error']);
	}

	public function testSummarizeReturnsApiKeyError(): void {
		$entry = new FreshRSS_Entry('42', 'Test Article', '<p>Content here</p>');
		FreshRSS_EntryDAO::addEntry('42', $entry);
		Minz_Request::setParam('id', '42');
		Minz_Request::setParam('_csrf', 'test-csrf-token');
		FreshRSS_Context::$user_conf->ai_summary_provider = 'openai';
		FreshRSS_Context::$user_conf->ai_summary_api_key = '';

		$output = $this->captureOutput(fn () => $this->controller->summarizeAction());

		$data = json_decode($output, true);
		self::assertStringContainsString('API key not configured', $data['error']);
	}

	public function testSummarizeThrowsOnInvalidCsrf(): void {
		Minz_Request::setParam('id', '42');
		Minz_Request::setParam('_csrf', 'invalid-token');

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Minz Error 403');

		$this->controller->summarizeAction();
	}

	// ── summarizeAction streaming (SSE errors from curl) ──

	public function testSummarizeSkipsApiKeyCheckForOllama(): void {
		$entry = new FreshRSS_Entry('42', 'Test Article', '<p>Content here</p>');
		FreshRSS_EntryDAO::addEntry('42', $entry);
		Minz_Request::setParam('id', '42');
		FreshRSS_Context::$user_conf->ai_summary_provider = 'ollama';
		FreshRSS_Context::$user_conf->ai_summary_api_key = '';

		$output = $this->captureOutput(fn () => $this->controller->summarizeAction());

		$error = $this->getErrorFromOutput($output);
		self::assertNotNull($error);
		self::assertStringNotContainsString('API key not configured', $error);
	}

	public function testSummarizeReturnsErrorForUnknownProvider(): void {
		$entry = new FreshRSS_Entry('42', 'Test', '<p>Content</p>');
		FreshRSS_EntryDAO::addEntry('42', $entry);
		Minz_Request::setParam('id', '42');
		FreshRSS_Context::$user_conf->ai_summary_provider = 'unknown_provider';
		FreshRSS_Context::$user_conf->ai_summary_api_key = 'key-123';

		$output = $this->captureOutput(fn () => $this->controller->summarizeAction());

		$error = $this->getErrorFromOutput($output);
		self::assertNotNull($error);
		self::assertStringContainsString('Unknown provider', $error);
	}

	// ── Provider method signatures ──

	public function testCallOpenAIMethodSignature(): void {
		$ref = new \ReflectionMethod(FreshExtension_AiSummary_Controller::class, 'callOpenAI');
		self::assertSame(4, $ref->getNumberOfParameters());
		$params = array_map(fn ($p) => $p->getName(), $ref->getParameters());
		self::assertSame(['apiKey', 'model', 'systemPrompt', 'userPrompt'], $params);
	}

	public function testCallAnthropicMethodSignature(): void {
		$ref = new \ReflectionMethod(FreshExtension_AiSummary_Controller::class, 'callAnthropic');
		self::assertSame(4, $ref->getNumberOfParameters());
	}

	public function testCallGeminiMethodSignature(): void {
		$ref = new \ReflectionMethod(FreshExtension_AiSummary_Controller::class, 'callGemini');
		self::assertSame(4, $ref->getNumberOfParameters());
	}

	public function testCallOllamaMethodSignature(): void {
		$ref = new \ReflectionMethod(FreshExtension_AiSummary_Controller::class, 'callOllama');
		self::assertSame(4, $ref->getNumberOfParameters());
		$params = $ref->getParameters();
		self::assertSame('apiUrl', $params[0]->getName());
	}

	// ── Content processing ──

	public function testContentStrippingReachesApiCall(): void {
		$longContent = str_repeat('<p>Hello world. </p>', 2000);
		$entry = new FreshRSS_Entry('42', 'Title', $longContent);
		FreshRSS_EntryDAO::addEntry('42', $entry);
		Minz_Request::setParam('id', '42');
		FreshRSS_Context::$user_conf->ai_summary_provider = 'openai';
		FreshRSS_Context::$user_conf->ai_summary_api_key = 'sk-test';

		$output = $this->captureOutput(fn () => $this->controller->summarizeAction());

		$error = $this->getErrorFromOutput($output);
		self::assertNotNull($error);
		// Should reach curl (not fail on content processing)
		self::assertStringNotContainsString('Missing entry', $error);
		self::assertStringNotContainsString('Entry not found', $error);
	}

	// ── sendJson / sendJsonError ──

	public function testSendJsonOutputsValidJson(): void {
		$ref = new \ReflectionMethod(FreshExtension_AiSummary_Controller::class, 'sendJson');

		$output = $this->captureOutput(fn () => $ref->invoke($this->controller, ['key' => 'value'], 200));

		$data = json_decode($output, true);
		self::assertSame(['key' => 'value'], $data);
	}

	public function testSendJsonErrorOutputsErrorKey(): void {
		$ref = new \ReflectionMethod(FreshExtension_AiSummary_Controller::class, 'sendJsonError');

		$output = $this->captureOutput(fn () => $ref->invoke($this->controller, 'Something failed', 500));

		$data = json_decode($output, true);
		self::assertArrayHasKey('error', $data);
		self::assertSame('Something failed', $data['error']);
	}

	public function testSendJsonHandlesUnicode(): void {
		$ref = new \ReflectionMethod(FreshExtension_AiSummary_Controller::class, 'sendJson');

		$output = $this->captureOutput(
			fn () => $ref->invoke($this->controller, ['text' => 'Résumé avec des accents éàü'], 200),
		);

		self::assertStringContainsString('Résumé', $output);
		$data = json_decode($output, true);
		self::assertSame('Résumé avec des accents éàü', $data['text']);
	}

	// ── sendEvent ──

	public function testSendEventOutputsSSEFormat(): void {
		$ref = new \ReflectionMethod(FreshExtension_AiSummary_Controller::class, 'sendEvent');

		$output = $this->captureOutput(
			fn () => $ref->invoke($this->controller, 'status', '{"message":"test"}'),
		);

		self::assertSame("event: status\ndata: {\"message\":\"test\"}\n\n", $output);
	}

	// ── curlStreamRequest structure ──

	public function testCurlStreamRequestMethodSignature(): void {
		$ref = new \ReflectionMethod(FreshExtension_AiSummary_Controller::class, 'curlStreamRequest');
		$params = array_map(fn ($p) => $p->getName(), $ref->getParameters());
		self::assertSame(['url', 'payload', 'headers', 'lineHandler'], $params);
		self::assertSame('void', $ref->getReturnType()->getName());
	}

	// ── Custom prompt ──

	public function testCustomPromptPathIsReached(): void {
		$entry = new FreshRSS_Entry('42', 'Test', '<p>Content</p>');
		FreshRSS_EntryDAO::addEntry('42', $entry);
		Minz_Request::setParam('id', '42');
		FreshRSS_Context::$user_conf->ai_summary_provider = 'openai';
		FreshRSS_Context::$user_conf->ai_summary_api_key = 'sk-test';
		FreshRSS_Context::$user_conf->ai_summary_prompt = 'My custom prompt';

		$output = $this->captureOutput(fn () => $this->controller->summarizeAction());

		$error = $this->getErrorFromOutput($output);
		self::assertNotNull($error);
		// Reaching curl means prompt was resolved successfully
		self::assertStringNotContainsString('prompt', strtolower($error));
	}

	// ── Default model selection ──

	public function testEmptyModelUsesDefault(): void {
		$entry = new FreshRSS_Entry('42', 'Test', '<p>Content</p>');
		FreshRSS_EntryDAO::addEntry('42', $entry);
		Minz_Request::setParam('id', '42');
		FreshRSS_Context::$user_conf->ai_summary_provider = 'openai';
		FreshRSS_Context::$user_conf->ai_summary_api_key = 'sk-test';
		FreshRSS_Context::$user_conf->ai_summary_model = '';

		$output = $this->captureOutput(fn () => $this->controller->summarizeAction());

		// Reaches curl without model error = default was used
		$error = $this->getErrorFromOutput($output);
		self::assertNotNull($error);
		self::assertStringNotContainsString('model', strtolower($error));
	}

	// ── extractText ──

	public function testExtractTextStripsHtmlAndNormalizes(): void {
		$ref = new \ReflectionMethod(FreshExtension_AiSummary_Controller::class, 'extractText');
		$result = $ref->invoke($this->controller, '<p>Hello  <b>world</b></p>  <br>  test');
		self::assertSame('Hello world test', $result);
	}

	public function testExtractTextDecodesEntities(): void {
		$ref = new \ReflectionMethod(FreshExtension_AiSummary_Controller::class, 'extractText');
		$result = $ref->invoke($this->controller, '&amp; &lt;tag&gt; &quot;quoted&quot;');
		self::assertSame('& <tag> "quoted"', $result);
	}

	// ── extractReadableText ──

	public function testExtractReadableTextPrefersArticleTag(): void {
		$ref = new \ReflectionMethod(FreshExtension_AiSummary_Controller::class, 'extractReadableText');
		$html = '<html><body><nav>Menu</nav><article><p>Article content here</p></article><footer>Footer</footer></body></html>';
		$result = $ref->invoke($this->controller, $html);
		self::assertStringContainsString('Article content here', $result);
		self::assertStringNotContainsString('Menu', $result);
		self::assertStringNotContainsString('Footer', $result);
	}

	public function testExtractReadableTextFallsBackToMainTag(): void {
		$ref = new \ReflectionMethod(FreshExtension_AiSummary_Controller::class, 'extractReadableText');
		$html = '<html><body><nav>Nav</nav><main><p>Main content</p></main></body></html>';
		$result = $ref->invoke($this->controller, $html);
		self::assertStringContainsString('Main content', $result);
		self::assertStringNotContainsString('Nav', $result);
	}

	public function testExtractReadableTextStripsScriptAndStyle(): void {
		$ref = new \ReflectionMethod(FreshExtension_AiSummary_Controller::class, 'extractReadableText');
		$html = '<article><script>alert("xss")</script><style>.x{color:red}</style><p>Clean text</p></article>';
		$result = $ref->invoke($this->controller, $html);
		self::assertStringContainsString('Clean text', $result);
		self::assertStringNotContainsString('alert', $result);
		self::assertStringNotContainsString('color', $result);
	}

	public function testExtractReadableTextFallsBackToContentDiv(): void {
		$ref = new \ReflectionMethod(FreshExtension_AiSummary_Controller::class, 'extractReadableText');
		$html = '<html><body><div class="post-content"><p>Post body</p></div></body></html>';
		$result = $ref->invoke($this->controller, $html);
		self::assertStringContainsString('Post body', $result);
	}

	// ── Content fallback to URL ──

	public function testMinContentLengthConstant(): void {
		$ref = new \ReflectionClassConstant(FreshExtension_AiSummary_Controller::class, 'MIN_CONTENT_LENGTH');
		self::assertSame(200, $ref->getValue());
	}

	public function testShortContentTriggersUrlFetch(): void {
		// Entry with very short content and a link
		$entry = new FreshRSS_Entry('42', 'Test', '<p>Short</p>', 'https://example.com/article');
		FreshRSS_EntryDAO::addEntry('42', $entry);
		Minz_Request::setParam('id', '42');
		Minz_Request::setParam('_csrf', 'test-csrf-token');
		FreshRSS_Context::$user_conf->ai_summary_provider = 'openai';
		FreshRSS_Context::$user_conf->ai_summary_api_key = 'sk-test';

		$output = $this->captureOutput(fn () => $this->controller->summarizeAction());

		$error = $this->getErrorFromOutput($output);
		self::assertNotNull($error);
		self::assertStringNotContainsString('Entry not found', $error);
	}

	public function testFetchArticleContentValidatesScheme(): void {
		$ref = new \ReflectionMethod(FreshExtension_AiSummary_Controller::class, 'fetchArticleContent');
		
		self::assertSame('', $ref->invoke($this->controller, 'file:///etc/passwd'), 'Should reject file:// scheme');
		self::assertSame('', $ref->invoke($this->controller, 'ftp://example.com'), 'Should reject ftp:// scheme');
		// Reaches curl for http/https (returns error because we didn't mock curl)
		self::assertIsString($ref->invoke($this->controller, 'http://example.com'));
	}

	public function testShortContentSendsStatusEvents(): void {
		$entry = new FreshRSS_Entry('42', 'Test', '<p>Short</p>', 'https://example.com/article');
		FreshRSS_EntryDAO::addEntry('42', $entry);
		Minz_Request::setParam('id', '42');
		FreshRSS_Context::$user_conf->ai_summary_provider = 'openai';
		FreshRSS_Context::$user_conf->ai_summary_api_key = 'sk-test';

		$output = $this->captureOutput(fn () => $this->controller->summarizeAction());

		$events = $this->parseSSEEvents($output);
		$statusMessages = array_map(
			fn ($e) => $e['data']['message'] ?? '',
			array_filter($events, fn ($e) => $e['event'] === 'status'),
		);
		self::assertTrue(
			count(array_filter($statusMessages, fn ($m) => str_contains($m, 'Fetching'))) > 0,
			'Expected a "Fetching" status event for short content with link',
		);
	}

	// ── Helpers ──

	private function captureOutput(callable $fn): string {
		ob_start();
		try {
			$fn();
		} catch (\Throwable $e) {
			ob_end_clean();
			throw $e;
		}
		return ob_get_clean() ?: '';
	}

	/**
	 * Extract error message from output (supports both JSON and SSE formats).
	 */
	private function getErrorFromOutput(string $output): ?string {
		// Try JSON first (validation errors)
		$json = json_decode($output, true);
		if (is_array($json) && isset($json['error'])) {
			return $json['error'];
		}

		// Parse SSE for error events
		foreach ($this->parseSSEEvents($output) as $event) {
			if ($event['event'] === 'error') {
				return $event['data']['message'] ?? null;
			}
		}
		return null;
	}

	/**
	 * @return list<array{event: string, data: array<string, mixed>}>
	 */
	private function parseSSEEvents(string $output): array {
		$events = [];
		$currentEvent = '';
		foreach (explode("\n", $output) as $line) {
			$line = trim($line);
			if (str_starts_with($line, 'event: ')) {
				$currentEvent = substr($line, 7);
			} elseif (str_starts_with($line, 'data: ')) {
				$data = json_decode(substr($line, 6), true);
				$events[] = ['event' => $currentEvent, 'data' => is_array($data) ? $data : []];
				$currentEvent = '';
			}
		}
		return $events;
	}
}
