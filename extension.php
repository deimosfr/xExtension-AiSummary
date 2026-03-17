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
		$aiIcon = '<svg class="ai-summary-icon" viewBox="0 0 512 512" width="16" height="16" fill="currentColor">'
			. '<path d="M208 0c13 0 22 9 25 21l26 105 105 26c12 3 21 12 21 25s-9 22-21 25l-105 26-26 105c-3 12-12 21-25 21s-22-9-25-21l-26-105L52 202c-12-3-21-12-21-25s9-22 21-25l105-26L183 21c3-12 12-21 25-21z"/>'
			. '<path d="M384 288c8 0 14 5 16 13l14 55 55 14c8 2 13 8 13 16s-5 14-13 16l-55 14-14 55c-2 8-8 13-16 13s-14-5-16-13l-14-55-55-14c-8-2-13-8-13-16s5-14 13-16l55-14 14-55c2-8 8-13 16-13z" opacity="0.7"/>'
			. '</svg>';
		$html = '<div class="ai-summary-wrapper" data-entry-id="' . $entryId . '">'
			. '<button type="button" class="ai-summary-btn btn btn-important">' . $aiIcon . ' ' . $label . '</button>'
			. '<div class="ai-summary-content"></div>'
			. '</div>';
		$entry->_content($html . $entry->content());
		return $entry;
	}
}
