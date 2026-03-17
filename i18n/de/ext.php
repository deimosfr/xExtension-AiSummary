<?php

declare(strict_types=1);

return [
	'ai_summary' => [
		'summarize' => 'AI Zusammenfassen',
		'provider' => 'KI-Anbieter',
		'api_key' => 'API-Schlüssel',
		'api_key_help' => 'Nicht erforderlich für Ollama.',
		'model' => 'Modell',
		'model_placeholder' => 'Leer lassen für Standard',
		'model_help' => 'Standards: OpenAI: gpt-4o-mini, Claude: claude-sonnet-4-6, Gemini: gemini-2.5-flash, Ollama: llama3.2',
		'api_url' => 'API-URL',
		'api_url_help' => 'Nur für Ollama erforderlich. Standard: http://localhost:11434',
		'prompt' => 'Benutzerdefinierter Prompt',
		'prompt_placeholder' => 'Basierend auf den folgenden Anforderungen, analysieren Sie den Artikel und erstellen Sie eine prägnante Zusammenfassung, wichtige Erkenntnisse und zusätzliche kontextuelle Einblicke. Die Ausgabesprache soll {language} sein. Platzhalter: {language}, {title}, {content}.',
		'prompt_help' => 'Leer lassen, um den Standard-Prompt zu verwenden. Verfügbare Platzhalter: {language}, {title}, {content}',
		'language' => 'Zusammenfassungssprache',
		'language_auto' => 'Auto (FreshRSS-Sprache verwenden)',
		'language_help' => 'Sprache für die generierten Zusammenfassungen. Auto verwendet Ihre FreshRSS-Oberflächensprache.',
		'save' => 'Speichern',
	],
];
