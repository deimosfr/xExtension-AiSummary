<?php

declare(strict_types=1);

/**
 * Enhanced stubs for FreshRSS framework classes.
 */

class Minz_Extension {
	public function install(): bool|string { return true; }
	public function uninstall(): bool|string { return true; }
	public function init(): void {}
	public function handleConfigureAction(): void {}

	public function getName(): string { return ''; }
	public function getEntrypoint(): string { return ''; }
	public function getPath(): string { return ''; }
	public function getAuthor(): string { return ''; }
	public function getDescription(): string { return ''; }
	public function getVersion(): string { return ''; }
	public function getType(): string { return 'user'; }

	/** @return array<string, mixed> */
	public function getSystemConfiguration(): array { return []; }
	/** @param array<string, mixed> $config */
	public function setSystemConfiguration(array $config): void {}
	public function removeSystemConfiguration(): void {}

	/** @return array<string, mixed> */
	public function getUserConfiguration(): array { return []; }
	/** @param array<string, mixed> $config */
	public function setUserConfiguration(array $config): void {}
	public function removeUserConfiguration(): void {}

	public function getFileUrl(string $filename, string $type): string {
		return '/ext/' . $filename;
	}

	public function registerController(string $base_name): void {}
	public function registerViews(): void {}
	public function registerTranslates(): void {}
	public function registerHook(string $hook_name, callable $hook_function): void {}
}

class Minz_View {
	public Minz_ActionController $controller;
	public static function appendStyle(string $url): void {}
	public static function appendScript(string $url): void {}
	public function _layout(?string $layout): void {}
}

class Minz_ActionController {
	public Minz_View $view;
	public function __construct() {
		$this->view = new Minz_View();
		$this->view->controller = $this;
	}
}

class Minz_Request {
	/** @var array<string, string> */
	private static array $params = [];
	public static function setParam(string $key, string $value): void { self::$params[$key] = $value; }
	/** @deprecated use paramString, paramInt, etc. */
	public static function param(string $key, string $default = ''): string { return self::$params[$key] ?? $default; }
	public static function paramString(string $name, bool $plaintext = true): string { return self::$params[$name] ?? ''; }
	public static function paramStringNull(string $name, bool $plaintext = true): ?string { return self::$params[$name] ?? null; }
	public static function paramInt(string $name, int $default = 0): int { return (int)(self::$params[$name] ?? $default); }
	public static function paramBoolean(string $name): bool { return (bool)(self::$params[$name] ?? false); }
	
	public static function isPost(): bool { return (self::$params['_method'] ?? '') === 'POST'; }
	public static function actionName(): string { return 'index'; }
	public static function controllerName(): string { return 'index'; }
	
	public static function good(string $url, string $msg = ''): void {}
	public static function bad(string $url, string $msg = ''): void {}
	public static function reset(): void { self::$params = []; }
}

/**
 * @property string $ai_summary_provider
 * @property string $ai_summary_api_key
 * @property string $ai_summary_model
 * @property string $ai_summary_openai_api_url
 * @property string $ai_summary_api_url
 * @property string $ai_summary_prompt
 * @property string $ai_summary_language
 * @property int $ai_summary_timeout
 * @property string $language
 */
class FreshRSS_UserConfiguration {
	/** @var array<string, mixed> */
	private array $data = [];
	public function __get(string $name): mixed { return $this->data[$name] ?? null; }
	public function __set(string $name, mixed $value): void { $this->data[$name] = $value; }
	public function save(): bool { return true; }
	
	public function attributeInt(string $name): int { return 0; }
	public function attributeBool(string $name): bool { return false; }
	public function _attribute(string $name, mixed $value): void { $this->data[$name] = $value; }
}

class FreshRSS_Context {
	public static FreshRSS_UserConfiguration $user_conf;
	public static function hasUserConf(): bool { return true; }
	public static function hasSystemConf(): bool { return true; }
	public static function userConf(): FreshRSS_UserConfiguration { return self::$user_conf; }
	public static function init(): void {
		self::$user_conf = new FreshRSS_UserConfiguration();
	}
}

class FreshRSS_Auth {
	private static bool $hasAccess = true;
	public static function setAccess(bool $access): void { self::$hasAccess = $access; }
	public static function hasAccess(): bool { return self::$hasAccess; }
	public static function csrfToken(): string { return 'test-csrf-token'; }
}

class Minz_Error {
	public static function error(int $code): void {
		throw new \RuntimeException('Minz Error ' . $code, $code);
	}
}

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
	public function id(): string { return $this->id; }
	public function title(): string { return $this->title; }
	public function content(): string { return $this->content; }
	public function link(): string { return $this->link; }
	public function _content(string $content): void { $this->content = $content; }
}

class FreshRSS_Feed {
	public function name(bool $takeAlias = false): string { return ''; }
	public function favicon(): string { return ''; }
	public function website(): string { return ''; }
}

class FreshRSS_FeedDAO {
	public function searchById(int $id): ?FreshRSS_Feed { return null; }
}

class FreshRSS_EntryDAO {
	/** @var array<string, FreshRSS_Entry> */
	private static array $entries = [];
	public static function addEntry(string $id, FreshRSS_Entry $entry): void { self::$entries[$id] = $entry; }
	public static function clearEntries(): void { self::$entries = []; }
	public function searchById(string $id): ?FreshRSS_Entry { return self::$entries[$id] ?? null; }
}

class FreshRSS_Factory {
	public static function createEntryDao(): FreshRSS_EntryDAO { return new FreshRSS_EntryDAO(); }
	public static function createFeedDao(): FreshRSS_FeedDAO { return new FreshRSS_FeedDAO(); }
}

class Minz_Exception extends Exception {}
class FreshRSS_Feed_Exception extends Exception {}
class FreshRSS_Context_Exception extends Exception {}

function _t(string $key, mixed ...$args): string { return $key; }
function _i(string $icon): string { return ''; }
/**
 * @param string|int|array<string, mixed> $params
 */
function _url(string $c = '', string $a = '', string|int|array $params = [], mixed ...$args): string { return ''; }
