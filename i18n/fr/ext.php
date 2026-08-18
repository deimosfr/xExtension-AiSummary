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
		'model_help' => 'Défauts : OpenAI : gpt-5-nano, Claude : claude-haiku-4-5, Gemini : gemini-3.5-flash-lite, Ollama : llama3.2',
		'api_url' => 'URL de l\'API',
		'api_url_help' => 'Requis uniquement pour Ollama. Défaut : http://localhost:11434',
		'prompt' => 'Prompt personnalisé',
		'prompt_placeholder' => 'Selon les exigences suivantes, analysez l\'article et produisez un résumé concis, les points clés et des informations contextuelles supplémentaires. La langue de sortie doit être {language}. Variables : {language}, {title}, {content}.',
		'prompt_help' => 'Laisser vide pour utiliser le prompt par défaut. Variables disponibles : {language}, {title}, {content}',
		'language' => 'Langue du résumé',
		'language_auto' => 'Auto (utiliser la langue de FreshRSS)',
		'language_help' => 'Langue utilisée pour les résumés générés. Auto utilise la langue de votre interface FreshRSS.',
		'timeout' => 'Délai de requête (secondes)',
		'timeout_help' => 'Nombre maximal de secondes pour attendre la réponse du fournisseur d\'IA. Défaut : 30. Plage : 1-300.',
		'save' => 'Enregistrer',
	],
];
