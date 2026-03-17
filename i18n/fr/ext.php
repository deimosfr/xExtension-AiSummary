<?php

declare(strict_types=1);

return [
	'ai_summary' => [
		'summarize' => 'AI Résumer',
		'provider' => 'Fournisseur IA',
		'api_key' => 'Clé API',
		'api_key_help' => 'Non requis pour Ollama.',
		'model' => 'Modèle',
		'model_placeholder' => 'Laisser vide pour le défaut',
		'model_help' => 'Défauts : OpenAI : gpt-4o-mini, Claude : claude-sonnet-4-6, Gemini : gemini-2.5-flash, Ollama : llama3.2',
		'api_url' => 'URL de l\'API',
		'api_url_help' => 'Requis uniquement pour Ollama. Défaut : http://localhost:11434',
		'prompt' => 'Prompt personnalisé',
		'prompt_placeholder' => 'Selon les exigences suivantes, analysez l\'article et produisez un résumé concis, les points clés et des informations contextuelles supplémentaires. La langue de sortie doit être {language}. Variables : {language}, {title}, {content}.',
		'prompt_help' => 'Laisser vide pour utiliser le prompt par défaut. Variables disponibles : {language}, {title}, {content}',
		'language' => 'Langue du résumé',
		'language_auto' => 'Auto (utiliser la langue de FreshRSS)',
		'language_help' => 'Langue utilisée pour les résumés générés. Auto utilise la langue de votre interface FreshRSS.',
		'save' => 'Enregistrer',
	],
];
