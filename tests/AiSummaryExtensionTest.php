<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AiSummaryExtensionTest extends TestCase {

	private AiSummaryExtension $extension;

	protected function setUp(): void {
		FreshRSS_Context::init();
		$this->extension = new AiSummaryExtension();
	}

	public function testEntryBeforeDisplayHookInjectsWrapper(): void {
		$entry = new FreshRSS_Entry('123', 'My Article', '<p>Original content</p>');

		$result = $this->extension->entryBeforeDisplayHook($entry);

		self::assertStringContainsString('ai-summary-wrapper', $result->content());
		self::assertStringNotContainsString('ai-summary-btn', $result->content());
		self::assertStringNotContainsString('ai-summary-content', $result->content());
	}

	public function testHookPreservesOriginalContent(): void {
		$original = '<p>My article content</p>';
		$entry = new FreshRSS_Entry('123', 'Title', $original);

		$result = $this->extension->entryBeforeDisplayHook($entry);

		self::assertStringContainsString($original, $result->content());
	}

	public function testHookInjectsBeforeOriginalContent(): void {
		$original = '<p>Original</p>';
		$entry = new FreshRSS_Entry('123', 'Title', $original);

		$result = $this->extension->entryBeforeDisplayHook($entry);

		$wrapperPos = strpos($result->content(), 'ai-summary-wrapper');
		$originalPos = strpos($result->content(), '<p>Original</p>');
		self::assertLessThan($originalPos, $wrapperPos);
	}

	public function testHookSetsCorrectEntryId(): void {
		$entry = new FreshRSS_Entry('456', 'Title', '<p>Content</p>');

		$result = $this->extension->entryBeforeDisplayHook($entry);

		self::assertStringContainsString('data-entry-id="456"', $result->content());
	}

	public function testHookEscapesEntryId(): void {
		$entry = new FreshRSS_Entry('12"3<4', 'Title', '<p>Content</p>');

		$result = $this->extension->entryBeforeDisplayHook($entry);

		// ID should be escaped - no raw quotes or angle brackets in the attribute
		self::assertStringNotContainsString('data-entry-id="12"3<4"', $result->content());
		self::assertStringContainsString('data-entry-id="12&quot;3&lt;4"', $result->content());
	}

	public function testHookReturnsSameEntryInstance(): void {
		$entry = new FreshRSS_Entry('1', 'T', '<p>C</p>');

		$result = $this->extension->entryBeforeDisplayHook($entry);

		self::assertSame($entry, $result);
	}

	public function testHookContainsSummarizeLabelInDataAttribute(): void {
		$entry = new FreshRSS_Entry('1', 'T', '<p>C</p>');

		$result = $this->extension->entryBeforeDisplayHook($entry);

		// The label comes from _t() which in our stub returns the key
		$this->assertStringContainsString('<div class="ai-summary-wrapper" data-entry-id="1" data-label="ext.ai_summary.summarize"><span></span></div>', $entry->content());
	}

	public function testHandleConfigureActionSavesConfig(): void {
		Minz_Request::setParam('_method', 'POST');
		Minz_Request::setParam('_csrf', 'test-csrf-token');
		Minz_Request::setParam('ai_summary_provider', 'anthropic');
		Minz_Request::setParam('ai_summary_api_key', 'sk-ant-test');
		Minz_Request::setParam('ai_summary_model', 'claude-sonnet-4-6');
		Minz_Request::setParam('ai_summary_api_url', '');
		Minz_Request::setParam('ai_summary_prompt', 'Custom prompt here');

		$this->extension->handleConfigureAction();

		self::assertSame('anthropic', FreshRSS_Context::$user_conf->ai_summary_provider);
		self::assertSame('sk-ant-test', FreshRSS_Context::$user_conf->ai_summary_api_key);
		self::assertSame('claude-sonnet-4-6', FreshRSS_Context::$user_conf->ai_summary_model);
		self::assertSame('Custom prompt here', FreshRSS_Context::$user_conf->ai_summary_prompt);
	}
}
