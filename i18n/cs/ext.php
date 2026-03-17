<?php

declare(strict_types=1);

return [
	'ai_summary' => [
		'summarize' => 'AI Shrnout',
		'provider' => 'Poskytovatel AI',
		'api_key' => 'Klíč API',
		'api_key_help' => 'Není vyžadováno pro Ollama.',
		'model' => 'Model',
		'model_placeholder' => 'Ponechte prázdné pro výchozí',
		'model_help' => 'Výchozí: OpenAI: gpt-4o-mini, Claude: claude-sonnet-4-6, Gemini: gemini-2.5-flash, Ollama: llama3.2',
		'api_url' => 'URL API',
		'api_url_help' => 'Vyžadováno pouze pro Ollama. Výchozí: http://localhost:11434',
		'prompt' => 'Vlastní prompt',
		'prompt_placeholder' => 'Na základě následujících požadavků analyzujte článek a vytvořte stručné shrnutí, klíčové poznatky a doplňující kontextové informace. Výstupní jazyk by měl být {language}. Proměnné: {language}, {title}, {content}.',
		'prompt_help' => 'Ponechte prázdné pro použití výchozího promptu. Dostupné proměnné: {language}, {title}, {content}',
		'language' => 'Jazyk shrnutí',
		'language_auto' => 'Automaticky (použít jazyk FreshRSS)',
		'language_help' => 'Jazyk generovaných shrnutí. Automaticky používá jazyk rozhraní FreshRSS.',
		'save' => 'Uložit',
	],
];
