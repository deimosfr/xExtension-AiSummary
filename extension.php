<?php

declare(strict_types=1);

final class AiSummaryExtension extends Minz_Extension {

	#[\Override]
	public function init(): void {
		Minz_View::appendStyle($this->getFileUrl('style.css', 'css'));
		Minz_View::appendScript($this->getFileUrl('script.js', 'js'));
		$this->registerTranslates();
		$this->registerHook('entry_before_display', [$this, 'entryBeforeDisplayHook']);
		$this->registerController('AiSummary');
	}

	#[\Override]
	public function handleConfigureAction(): void {
		$this->registerTranslates();

		if (Minz_Request::isPost()) {
			if (Minz_Request::param('_csrf') !== FreshRSS_Auth::csrfToken()) {
				Minz_Error::error(403);
			}

			FreshRSS_Context::$user_conf->ai_summary_provider = Minz_Request::param('ai_summary_provider', 'openai');
			FreshRSS_Context::$user_conf->ai_summary_api_key = Minz_Request::param('ai_summary_api_key', '');
			FreshRSS_Context::$user_conf->ai_summary_model = Minz_Request::param('ai_summary_model', '');
			FreshRSS_Context::$user_conf->ai_summary_api_url = Minz_Request::param('ai_summary_api_url', '');
			FreshRSS_Context::$user_conf->ai_summary_prompt = Minz_Request::param('ai_summary_prompt', '');
			FreshRSS_Context::$user_conf->ai_summary_language = Minz_Request::param('ai_summary_language', '');
			FreshRSS_Context::$user_conf->save();
		}
	}

	public function entryBeforeDisplayHook(FreshRSS_Entry $entry): FreshRSS_Entry {
		$entryId = htmlspecialchars((string) $entry->id(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		$label = htmlspecialchars(_t('ext.ai_summary.summarize'), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
		$html = '<div class="ai-summary-wrapper" data-entry-id="' . $entryId . '" data-label="' . $label . '"><span></span></div>';
		$entry->_content($html . $entry->content());
		return $entry;
	}
}
