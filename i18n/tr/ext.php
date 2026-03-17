<?php

declare(strict_types=1);

return [
	'ai_summary' => [
		'summarize' => 'AI Özetle',
		'provider' => 'Yapay Zeka Sağlayıcı',
		'api_key' => 'API Anahtarı',
		'api_key_help' => 'Ollama için gerekli değildir.',
		'model' => 'Model',
		'model_placeholder' => 'Varsayılan için boş bırakın',
		'model_help' => 'Varsayılanlar: OpenAI: gpt-4o-mini, Claude: claude-sonnet-4-6, Gemini: gemini-2.5-flash, Ollama: llama3.2',
		'api_url' => 'API URL',
		'api_url_help' => 'Yalnızca Ollama için gereklidir. Varsayılan: http://localhost:11434',
		'prompt' => 'Özel İstem',
		'prompt_placeholder' => 'Aşağıdaki gereksinimlere göre makaleyi analiz edin ve özlü bir özet, önemli noktalar ve ek bağlamsal bilgiler üretin. Çıktı dili {language} olmalıdır. Değişkenler: {language}, {title}, {content}.',
		'prompt_help' => 'Varsayılan istemi kullanmak için boş bırakın. Kullanılabilir değişkenler: {language}, {title}, {content}',
		'language' => 'Özet dili',
		'language_auto' => 'Otomatik (FreshRSS dilini kullan)',
		'language_help' => 'Oluşturulan özetler için kullanılan dil. Otomatik, FreshRSS arayüz dilinizi kullanır.',
		'save' => 'Kaydet',
	],
];
