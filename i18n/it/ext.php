<?php

declare(strict_types=1);

return [
	'ai_summary' => [
		'summarize' => 'AI Riassumi',
		'provider' => 'Fornitore IA',
		'api_key' => 'Chiave API',
		'api_key_help' => 'Non richiesta per Ollama.',
		'model' => 'Modello',
		'model_placeholder' => 'Lasciare vuoto per il predefinito',
		'model_help' => 'Predefiniti: OpenAI: gpt-4o-mini, Claude: claude-sonnet-4-6, Gemini: gemini-2.5-flash, Ollama: llama3.2',
		'api_url' => 'URL API',
		'api_url_help' => 'Richiesto solo per Ollama. Predefinito: http://localhost:11434',
		'prompt' => 'Prompt personalizzato',
		'prompt_placeholder' => 'In base ai seguenti requisiti, analizza l\'articolo e produci un riassunto conciso, punti chiave e approfondimenti contestuali aggiuntivi. La lingua di output deve essere {language}. Segnaposto: {language}, {title}, {content}.',
		'prompt_help' => 'Lasciare vuoto per usare il prompt predefinito. Segnaposto disponibili: {language}, {title}, {content}',
		'language' => 'Lingua del riassunto',
		'language_auto' => 'Auto (usa la lingua di FreshRSS)',
		'language_help' => 'Lingua utilizzata per i riassunti generati. Auto usa la lingua dell\'interfaccia FreshRSS.',
		'save' => 'Salva',
	],
];
