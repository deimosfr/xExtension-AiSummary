<?php

declare(strict_types=1);

return [
	'ai_summary' => [
		'summarize' => 'AI 요약',
		'provider' => 'AI 제공자',
		'api_key' => 'API 키',
		'api_key_help' => 'Ollama에는 필요하지 않습니다.',
		'model' => '모델',
		'model_placeholder' => '기본값을 사용하려면 비워두세요',
		'model_help' => '기본값: OpenAI: gpt-4o-mini, Claude: claude-sonnet-4-6, Gemini: gemini-2.5-flash, Ollama: llama3.2',
		'api_url' => 'API URL',
		'api_url_help' => 'Ollama에만 필요합니다. 기본값: http://localhost:11434',
		'prompt' => '사용자 정의 프롬프트',
		'prompt_placeholder' => '다음 요구사항에 따라 기사를 분석하고 간결한 요약, 핵심 포인트 및 추가 배경 정보를 생성하세요. 출력 언어는 {language}이어야 합니다. 변수: {language}, {title}, {content}.',
		'prompt_help' => '기본 프롬프트를 사용하려면 비워두세요. 사용 가능한 변수: {language}, {title}, {content}',
		'language' => '요약 언어',
		'language_auto' => '자동 (FreshRSS 언어 사용)',
		'language_help' => '생성된 요약에 사용되는 언어. 자동은 FreshRSS 인터페이스 언어를 사용합니다.',
		'save' => '저장',
	],
];
