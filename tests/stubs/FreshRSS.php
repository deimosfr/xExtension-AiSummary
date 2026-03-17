<?php

declare(strict_types=1);

/**
 * Minimal stubs for FreshRSS framework classes used by the extension.
 * These allow unit tests to run without the full FreshRSS installation.
 */

// --- Minz_Extension ---
class Minz_Extension {
	public function init(): void {}

	public function handleConfigureAction(): void {}

	public function getFileUrl(string $file, string $type): string {
		return '/ext/' . $file;
	}

	public function getName(): string {
		return 'AiSummary';
	}

	public function registerTranslates(): void {}

	public function registerHook(string $name, callable $callback): void {}

	public function registerController(string $name): void {}
}

// --- Minz_View ---
class Minz_View {
	private ?string $layout = '';

	public static function appendStyle(string $url): void {}

	public static function appendScript(string $url): void {}

	public function _layout(?string $layout): void {
		$this->layout = $layout;
	}
}

// --- Minz_ActionController ---
class Minz_ActionController {
	public Minz_View $view;

	public function __construct() {
		$this->view = new Minz_View();
	}
}

// --- Minz_Request ---
class Minz_Request {
	/** @var array<string, string> */
	private static array $params = [];

	public static function setParam(string $key, string $value): void {
		self::$params[$key] = $value;
	}

	public static function param(string $key, string $default = ''): string {
		return self::$params[$key] ?? $default;
	}

	public static function isPost(): bool {
		return (self::$params['_method'] ?? '') === 'POST';
	}

	public static function reset(): void {
		self::$params = [];
	}
}

// --- FreshRSS_UserConfiguration (stub) ---
class FreshRSS_UserConfiguration {
	/** @var array<string, mixed> */
	private array $data = [];

	public function __get(string $name): mixed {
		return $this->data[$name] ?? null;
	}

	public function __set(string $name, mixed $value): void {
		$this->data[$name] = $value;
	}

	public function save(): bool {
		return true;
	}
}

// --- FreshRSS_Context ---
class FreshRSS_Context {
	public static FreshRSS_UserConfiguration $user_conf;

	public static function init(): void {
		self::$user_conf = new FreshRSS_UserConfiguration();
	}
}

// --- FreshRSS_Auth ---
class FreshRSS_Auth {
	private static bool $hasAccess = true;

	public static function setAccess(bool $access): void {
		self::$hasAccess = $access;
	}

	public static function hasAccess(): bool {
		return self::$hasAccess;
	}

	public static function csrfToken(): string {
		return 'test-csrf-token';
	}
}

// --- FreshRSS_Entry ---
class FreshRSS_Entry {
	private string $id;
	private string $title;
	private string $content;
	private string $link;

	public function __construct(string $id = '', string $title = '', string $content = '', string $link = '') {
		$this->id = $id;
		$this->title = $title;
		$this->content = $content;
		$this->link = $link;
	}

	public function id(): string {
		return $this->id;
	}

	public function title(): string {
		return $this->title;
	}

	public function content(): string {
		return $this->content;
	}

	public function link(): string {
		return $this->link;
	}

	public function _content(string $content): void {
		$this->content = $content;
	}
}

// --- FreshRSS_EntryDAO ---
class FreshRSS_EntryDAO {
	/** @var array<string, FreshRSS_Entry> */
	private static array $entries = [];

	public static function addEntry(string $id, FreshRSS_Entry $entry): void {
		self::$entries[$id] = $entry;
	}

	public static function clearEntries(): void {
		self::$entries = [];
	}

	public function searchById(string $id): ?FreshRSS_Entry {
		return self::$entries[$id] ?? null;
	}
}

// --- FreshRSS_Factory ---
class FreshRSS_Factory {
	public static function createEntryDao(): FreshRSS_EntryDAO {
		return new FreshRSS_EntryDAO();
	}
}

// --- Translation function stub ---
function _t(string $key, mixed ...$args): string {
	return $key;
}

// --- URL helper stub ---
function _url(string ...$args): string {
	return '/?c=' . ($args[0] ?? '') . '&a=' . ($args[1] ?? '');
}
