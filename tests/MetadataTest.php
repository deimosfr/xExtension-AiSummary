<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class MetadataTest extends TestCase {

	/** @var array<string, mixed> */
	private static array $metadata;

	public static function setUpBeforeClass(): void {
		$file = dirname(__DIR__) . '/metadata.json';
		$content = file_get_contents($file);
		self::assertNotFalse($content);
		$data = json_decode($content, true);
		self::assertIsArray($data);
		self::$metadata = $data;
	}

	public function testHasRequiredFields(): void {
		foreach (['name', 'author', 'description', 'version', 'entrypoint', 'type'] as $field) {
			self::assertArrayHasKey($field, self::$metadata, "Missing field: {$field}");
		}
	}

	public function testEntrypointMatchesExtensionClass(): void {
		self::assertSame('AiSummary', self::$metadata['entrypoint']);
	}

	public function testTypeIsUser(): void {
		self::assertSame('user', self::$metadata['type']);
	}

	public function testVersionIsSemver(): void {
		self::assertMatchesRegularExpression('/^\d+\.\d+\.\d+$/', self::$metadata['version']);
	}

	public function testExtensionClassExists(): void {
		$className = self::$metadata['entrypoint'] . 'Extension';
		self::assertTrue(class_exists($className), "Class {$className} does not exist");
	}

	public function testControllerClassExists(): void {
		$className = 'FreshExtension_' . self::$metadata['entrypoint'] . '_Controller';
		self::assertTrue(class_exists($className), "Class {$className} does not exist");
	}

	public function testJsonIsValid(): void {
		$file = dirname(__DIR__) . '/metadata.json';
		$content = file_get_contents($file);
		json_decode($content, true, 512, JSON_THROW_ON_ERROR);
		// No exception = valid JSON
		self::assertTrue(true);
	}
}
