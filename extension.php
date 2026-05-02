<?php

declare(strict_types=1);

final class AiSummaryExtension extends Minz_Extension {

	public const TIMEOUT_MIN = 1;
	public const TIMEOUT_MAX = 300;
	public const TIMEOUT_DEFAULT = 30;

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
			if (Minz_Request::paramString('_csrf') !== FreshRSS_Auth::csrfToken()) {
				Minz_Error::error(403);
			}
			$user_conf = FreshRSS_Context::userConf();
			$user_conf->_attribute('ai_summary_provider', Minz_Request::paramString('ai_summary_provider') ?: 'openai');
			$user_conf->_attribute('ai_summary_api_key', Minz_Request::paramString('ai_summary_api_key'));
			$user_conf->_attribute('ai_summary_model', Minz_Request::paramString('ai_summary_model'));
			$user_conf->_attribute('ai_summary_api_url', Minz_Request::paramString('ai_summary_api_url'));
			$user_conf->_attribute('ai_summary_prompt', Minz_Request::paramString('ai_summary_prompt'));
			$user_conf->_attribute('ai_summary_language', Minz_Request::paramString('ai_summary_language'));
			$timeout = Minz_Request::paramInt('ai_summary_timeout');
			if ($timeout < self::TIMEOUT_MIN || $timeout > self::TIMEOUT_MAX) {
				$timeout = self::TIMEOUT_DEFAULT;
			}
			$user_conf->_attribute('ai_summary_timeout', $timeout);
			$user_conf->save();
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
