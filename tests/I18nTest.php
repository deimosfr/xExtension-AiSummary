<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class I18nTest extends TestCase {

	/** @var list<string> */
	private const REQUIRED_KEYS = [
		'summarize',
		'provider',
		'api_key',
		'api_key_help',
		'model',
		'model_placeholder',
		'model_help',
		'api_url',
		'api_url_help',
		'prompt',
		'prompt_placeholder',
		'prompt_help',
		'language',
		'language_auto',
		'language_help',
		'save',
	];

	private static string $i18nDir;

	/** @var array<string, string> */
	private static array $englishStrings;

	public static function setUpBeforeClass(): void {
		self::$i18nDir = dirname(__DIR__) . '/i18n';
		$en = require self::$i18nDir . '/en/ext.php';
		self::$englishStrings = $en['ai_summary'];
	}

	/**
	 * @return list<array{string}>
	 */
	public static function languageProvider(): array {
		$i18nDir = dirname(__DIR__) . '/i18n';
		$languages = [];
		foreach (glob($i18nDir . '/*/ext.php') as $file) {
			$lang = basename(dirname($file));
			$languages[] = [$lang];
		}
		return $languages;
	}

	/**
	 * @dataProvider languageProvider
	 */
	public function testLanguageFileIsValidPhp(string $lang): void {
		$file = self::$i18nDir . '/' . $lang . '/ext.php';
		$result = require $file;
		self::assertIsArray($result);
		self::assertArrayHasKey('ai_summary', $result);
	}

	/**
	 * @dataProvider languageProvider
	 */
	public function testLanguageHasAllRequiredKeys(string $lang): void {
		$file = self::$i18nDir . '/' . $lang . '/ext.php';
		$data = require $file;
		$strings = $data['ai_summary'];

		foreach (self::REQUIRED_KEYS as $key) {
			self::assertArrayHasKey($key, $strings, "Language '{$lang}' is missing key '{$key}'");
		}
	}

	/**
	 * @dataProvider languageProvider
	 */
	public function testLanguageHasNoExtraKeys(string $lang): void {
		$file = self::$i18nDir . '/' . $lang . '/ext.php';
		$data = require $file;
		$strings = $data['ai_summary'];

		$extraKeys = array_diff(array_keys($strings), self::REQUIRED_KEYS);
		self::assertEmpty($extraKeys, "Language '{$lang}' has extra keys: " . implode(', ', $extraKeys));
	}

	/**
	 * @dataProvider languageProvider
	 */
	public function testAllValuesAreNonEmptyStrings(string $lang): void {
		$file = self::$i18nDir . '/' . $lang . '/ext.php';
		$data = require $file;
		$strings = $data['ai_summary'];

		foreach ($strings as $key => $value) {
			self::assertIsString($value, "Language '{$lang}', key '{$key}' is not a string");
			self::assertNotEmpty($value, "Language '{$lang}', key '{$key}' is empty");
		}
	}

	/**
	 * @dataProvider languageProvider
	 */
	public function testModelHelpContainsAllProviderDefaults(string $lang): void {
		$file = self::$i18nDir . '/' . $lang . '/ext.php';
		$data = require $file;
		$help = $data['ai_summary']['model_help'];

		self::assertStringContainsString('gpt-4o-mini', $help, "Language '{$lang}' model_help missing OpenAI default");
		self::assertStringContainsString('claude-sonnet-4-6', $help, "Language '{$lang}' model_help missing Claude default");
		self::assertStringContainsString('gemini-2.5-flash', $help, "Language '{$lang}' model_help missing Gemini default");
		self::assertStringContainsString('llama3.2', $help, "Language '{$lang}' model_help missing Ollama default");
	}

	public function testEnglishStringsMatchRequiredKeys(): void {
		$expected = self::REQUIRED_KEYS;
		$actual = array_keys(self::$englishStrings);
		sort($expected);
		sort($actual);
		self::assertSame($expected, $actual);
	}

	public function testAtLeastTwoLanguagesExist(): void {
		$count = count(glob(self::$i18nDir . '/*/ext.php'));
		self::assertGreaterThanOrEqual(2, $count);
	}
}
