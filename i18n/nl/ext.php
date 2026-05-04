<?php

declare(strict_types=1);

return [
	'ai_summary' => [
		'summarize' => 'AI Samenvatten',
		'provider' => 'AI-provider',
		'api_key' => 'API-sleutel',
		'api_key_help' => 'Niet vereist voor Ollama.',
		'model' => 'Model',
		'model_placeholder' => 'Leeg laten voor standaard',
		'model_help' => 'Standaard: OpenAI: gpt-4o-mini, Claude: claude-sonnet-4-6, Gemini: gemini-2.5-flash, Ollama: llama3.2',
		'api_url' => 'API-URL',
		'api_url_help' => 'Alleen vereist voor Ollama. Standaard: http://localhost:11434',
		'prompt' => 'Aangepaste prompt',
		'prompt_placeholder' => 'Op basis van de volgende vereisten, analyseer het artikel en produceer een beknopte samenvatting, belangrijkste inzichten en aanvullende contextuele informatie. De uitvoertaal moet {language} zijn. Variabelen: {language}, {title}, {content}.',
		'prompt_help' => 'Leeg laten om de standaard prompt te gebruiken. Beschikbare variabelen: {language}, {title}, {content}',
		'language' => 'Samenvattingstaal',
		'language_auto' => 'Auto (FreshRSS-taal gebruiken)',
		'language_help' => 'Taal voor gegenereerde samenvattingen. Auto gebruikt uw FreshRSS-interfacetaal.',
		'timeout' => 'Verzoek-time-out (seconden)',
		'timeout_help' => 'Maximaal aantal seconden om te wachten op een antwoord van de AI-provider. Standaard: 30. Bereik: 1-300.',
		'save' => 'Opslaan',
	],
];
