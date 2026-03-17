<?php

declare(strict_types=1);

return [
	'ai_summary' => [
		'summarize' => 'AI 摘要',
		'provider' => 'AI 提供商',
		'api_key' => 'API 密钥',
		'api_key_help' => 'Ollama 不需要。',
		'model' => '模型',
		'model_placeholder' => '留空使用默认值',
		'model_help' => '默认值：OpenAI: gpt-4o-mini, Claude: claude-sonnet-4-6, Gemini: gemini-2.5-flash, Ollama: llama3.2',
		'api_url' => 'API 地址',
		'api_url_help' => '仅 Ollama 需要。默认：http://localhost:11434',
		'prompt' => '自定义提示词',
		'prompt_placeholder' => '根据以下要求，分析文章并生成简洁摘要、关键要点和额外的背景信息。输出语言应为 {language}。占位符：{language}、{title}、{content}。',
		'prompt_help' => '留空使用默认提示词。可用占位符：{language}、{title}、{content}',
		'language' => '摘要语言',
		'language_auto' => '自动（使用 FreshRSS 语言）',
		'language_help' => '生成摘要使用的语言。自动使用您的 FreshRSS 界面语言。',
		'save' => '保存',
	],
];
