<?php

declare(strict_types=1);

return [
	'ai_summary' => [
		'summarize' => 'AI Resumir',
		'provider' => 'Proveedor de IA',
		'api_key' => 'Clave API',
		'api_key_help' => 'No requerida para Ollama.',
		'model' => 'Modelo',
		'model_placeholder' => 'Dejar vacío para el predeterminado',
		'model_help' => 'Predeterminados: OpenAI: gpt-4o-mini, Claude: claude-sonnet-4-6, Gemini: gemini-2.5-flash, Ollama: llama3.2',
		'api_url' => 'URL de la API',
		'api_url_help' => 'Solo requerida para Ollama. Predeterminado: http://localhost:11434',
		'prompt' => 'Prompt personalizado',
		'prompt_placeholder' => 'Según los siguientes requisitos, analice el artículo y produzca un resumen conciso, puntos clave e información contextual adicional. El idioma de salida debe ser {language}. Variables: {language}, {title}, {content}.',
		'prompt_help' => 'Dejar vacío para usar el prompt predeterminado. Variables disponibles: {language}, {title}, {content}',
		'language' => 'Idioma del resumen',
		'language_auto' => 'Auto (usar idioma de FreshRSS)',
		'language_help' => 'Idioma utilizado para los resúmenes generados. Auto usa el idioma de su interfaz FreshRSS.',
		'save' => 'Guardar',
	],
];
