<?php

declare(strict_types=1);

return [
	'ai_summary' => [
		'summarize' => 'AI Summarize',
		'provider' => 'AI Provider',
		'api_key' => 'API Key',
		'api_key_help' => 'Not required for Ollama.',
		'model' => 'Model',
		'model_placeholder' => 'Leave empty for default',
		'model_help' => 'Defaults: OpenAI: gpt-4o-mini, Claude: claude-sonnet-4-6, Gemini: gemini-2.5-flash, Ollama: llama3.2',
		'api_url' => 'API URL',
		'api_url_help' => 'Only required for Ollama. Default: http://localhost:11434',
		'prompt' => 'Custom Prompt',
		'prompt_placeholder' => 'Based on the following requirements, please analyze the article and produce the output that includes a concise summary, key takeaways, and additional contextual insights. The output language should be {language}. Placeholders: {language}, {title}, {content}.',
		'prompt_help' => 'Leave empty to use the default prompt. Available placeholders: {language}, {title}, {content}',
		'language' => 'Summary Language',
		'language_auto' => 'Auto (use FreshRSS language)',
		'language_help' => 'Language used for generated summaries. Auto uses your FreshRSS interface language.',
		'save' => 'Save',
	],
];
