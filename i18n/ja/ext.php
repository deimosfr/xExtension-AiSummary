<?php

declare(strict_types=1);

return [
	'ai_summary' => [
		'summarize' => 'AI 要約',
		'provider' => 'AIプロバイダー',
		'api_key' => 'APIキー',
		'api_key_help' => 'Ollamaでは不要です。',
		'model' => 'モデル',
		'model_placeholder' => '空欄でデフォルトを使用',
		'model_help' => 'デフォルト：OpenAI: gpt-4o-mini, Claude: claude-sonnet-4-6, Gemini: gemini-2.5-flash, Ollama: llama3.2',
		'api_url' => 'API URL',
		'api_url_help' => 'Ollamaのみ必要です。デフォルト：http://localhost:11434',
		'prompt' => 'カスタムプロンプト',
		'prompt_placeholder' => '以下の要件に基づいて、記事を分析し、簡潔な要約、重要なポイント、追加の背景情報を生成してください。出力言語は {language} にしてください。プレースホルダー：{language}、{title}、{content}。',
		'prompt_help' => '空欄でデフォルトのプロンプトを使用します。利用可能なプレースホルダー：{language}、{title}、{content}',
		'language' => '要約の言語',
		'language_auto' => '自動（FreshRSSの言語を使用）',
		'language_help' => '生成される要約の言語。自動の場合、FreshRSSのインターフェース言語を使用します。',
		'save' => '保存',
	],
];
