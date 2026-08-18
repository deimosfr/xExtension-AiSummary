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
		'model_help' => 'Predefiniti: OpenAI: gpt-5-nano, Claude: claude-haiku-4-5, Gemini: gemini-3.5-flash-lite, Ollama: llama3.2',
		'api_url' => 'URL API',
		'api_url_help' => 'Richiesto solo per Ollama. Predefinito: http://localhost:11434',
		'prompt' => 'Prompt personalizzato',
		'prompt_placeholder' => 'In base ai seguenti requisiti, analizza l\'articolo e produci un riassunto conciso, punti chiave e approfondimenti contestuali aggiuntivi. La lingua di output deve essere {language}. Segnaposto: {language}, {title}, {content}.',
		'prompt_help' => 'Lasciare vuoto per usare il prompt predefinito. Segnaposto disponibili: {language}, {title}, {content}',
		'language' => 'Lingua del riassunto',
		'language_auto' => 'Auto (usa la lingua di FreshRSS)',
		'language_help' => 'Lingua utilizzata per i riassunti generati. Auto usa la lingua dell\'interfaccia FreshRSS.',
		'timeout' => 'Timeout della richiesta (secondi)',
		'timeout_help' => 'Secondi massimi di attesa per la risposta del fornitore IA. Predefinito: 30. Intervallo: 1-300.',
		'save' => 'Salva',
	],
];
