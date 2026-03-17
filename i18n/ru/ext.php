<?php

declare(strict_types=1);

return [
	'ai_summary' => [
		'summarize' => 'AI Резюме',
		'provider' => 'Провайдер ИИ',
		'api_key' => 'Ключ API',
		'api_key_help' => 'Не требуется для Ollama.',
		'model' => 'Модель',
		'model_placeholder' => 'Оставьте пустым для значения по умолчанию',
		'model_help' => 'По умолчанию: OpenAI: gpt-4o-mini, Claude: claude-sonnet-4-6, Gemini: gemini-2.5-flash, Ollama: llama3.2',
		'api_url' => 'URL API',
		'api_url_help' => 'Требуется только для Ollama. По умолчанию: http://localhost:11434',
		'prompt' => 'Пользовательский промпт',
		'prompt_placeholder' => 'На основе следующих требований проанализируйте статью и создайте краткое резюме, ключевые выводы и дополнительную контекстную информацию. Язык вывода должен быть {language}. Переменные: {language}, {title}, {content}.',
		'prompt_help' => 'Оставьте пустым для использования промпта по умолчанию. Доступные переменные: {language}, {title}, {content}',
		'language' => 'Язык резюме',
		'language_auto' => 'Авто (использовать язык FreshRSS)',
		'language_help' => 'Язык для генерируемых резюме. Авто использует язык интерфейса FreshRSS.',
		'save' => 'Сохранить',
	],
];
