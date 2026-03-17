<?php

declare(strict_types=1);

return [
	'ai_summary' => [
		'summarize' => 'AI Podsumuj',
		'provider' => 'Dostawca AI',
		'api_key' => 'Klucz API',
		'api_key_help' => 'Nie wymagany dla Ollama.',
		'model' => 'Model',
		'model_placeholder' => 'Pozostaw puste dla domyślnego',
		'model_help' => 'Domyślne: OpenAI: gpt-4o-mini, Claude: claude-sonnet-4-6, Gemini: gemini-2.5-flash, Ollama: llama3.2',
		'api_url' => 'Adres URL API',
		'api_url_help' => 'Wymagany tylko dla Ollama. Domyślny: http://localhost:11434',
		'prompt' => 'Niestandardowy prompt',
		'prompt_placeholder' => 'Na podstawie poniższych wymagań przeanalizuj artykuł i utwórz zwięzłe podsumowanie, kluczowe wnioski i dodatkowe informacje kontekstowe. Język wyjściowy powinien być {language}. Zmienne: {language}, {title}, {content}.',
		'prompt_help' => 'Pozostaw puste, aby użyć domyślnego promptu. Dostępne zmienne: {language}, {title}, {content}',
		'language' => 'Język podsumowania',
		'language_auto' => 'Auto (użyj języka FreshRSS)',
		'language_help' => 'Język generowanych podsumowań. Auto używa języka interfejsu FreshRSS.',
		'save' => 'Zapisz',
	],
];
