<?php

declare(strict_types=1);

return [
	'ai_summary' => [
		'summarize' => 'AI Resumir',
		'provider' => 'Provedor de IA',
		'api_key' => 'Chave da API',
		'api_key_help' => 'Não necessária para Ollama.',
		'model' => 'Modelo',
		'model_placeholder' => 'Deixe vazio para o padrão',
		'model_help' => 'Padrões: OpenAI: gpt-4o-mini, Claude: claude-sonnet-4-6, Gemini: gemini-2.5-flash, Ollama: llama3.2',
		'api_url' => 'URL da API',
		'api_url_help' => 'Necessária apenas para Ollama. Padrão: http://localhost:11434',
		'prompt' => 'Prompt personalizado',
		'prompt_placeholder' => 'Com base nos seguintes requisitos, analise o artigo e produza um resumo conciso, pontos-chave e informações contextuais adicionais. O idioma de saída deve ser {language}. Variáveis: {language}, {title}, {content}.',
		'prompt_help' => 'Deixe vazio para usar o prompt padrão. Variáveis disponíveis: {language}, {title}, {content}',
		'language' => 'Idioma do resumo',
		'language_auto' => 'Auto (usar idioma do FreshRSS)',
		'language_help' => 'Idioma usado para os resumos gerados. Auto usa o idioma da interface do FreshRSS.',
		'save' => 'Salvar',
	],
];
