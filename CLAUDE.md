# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

FreshRSS extension that adds AI-powered article summarization. Supports OpenAI (ChatGPT), Anthropic (Claude), Google (Gemini), and Ollama as AI providers. Licensed under GPL v3.

## Architecture

This is a FreshRSS extension (PHP 8.1+ / JS). The extension class is `AiSummaryExtension` (entrypoint: `AiSummary`).

**Request flow:** User clicks "Summarize" button → JS sends AJAX POST to `AiSummaryController::summarizeAction()` → PHP fetches article from DB, strips HTML, calls configured AI provider API via curl → returns JSON summary → JS renders it below the article title.

### Key Files

- `extension.php` — Main extension class. Registers hooks (`entry_before_display` to inject the summary UI into articles), loads static assets, handles configuration save.
- `Controllers/AiSummaryController.php` — Backend endpoint that proxies AI API calls. Contains provider-specific methods (`callOpenAI`, `callAnthropic`, `callGemini`, `callOllama`). API keys never leave the server.
- `configure.phtml` — Settings form (provider, API key, model, API URL for Ollama, custom prompt).
- `static/script.js` — Click handler for summarize buttons, toolbar button injection via MutationObserver, basic markdown→HTML formatting.
- `static/style.css` — Summary container styling with dark theme support.
- `i18n/*/ext.php` — Translation strings (14 languages).

### FreshRSS Extension Conventions

- Class name must be `{entrypoint}Extension` matching `metadata.json`'s `entrypoint` field.
- User config is stored as dynamic properties on `FreshRSS_Context::$user_conf` (prefixed `ai_summary_`).
- Controller class follows `FreshExtension_{Name}_Controller` naming and is registered via `$this->registerController()`.
- CSRF token: forms use `FreshRSS_Auth::csrfToken()`, JS uses `context.csrf`.
- Controller URLs: `./?c=AiSummary&a=summarize`.

## Commands

```bash
composer install                              # install dependencies (first time)
vendor/bin/phpunit                            # run all tests
vendor/bin/phpunit --testdox                  # run tests with verbose labels
vendor/bin/phpunit --filter testMethodName    # run a single test
vendor/bin/phpunit tests/I18nTest.php         # run a single test file
php -l extension.php                          # lint a PHP file
```

No build step required. To install the extension locally, symlink or copy this directory into FreshRSS's `extensions/` folder.

## Testing

Tests use FreshRSS framework stubs in `tests/stubs/` (not the real FreshRSS). Test suites:
- `AiSummaryControllerTest` — Controller validation, error paths, JSON output, method signatures.
- `AiSummaryExtensionTest` — Hook HTML injection, XSS escaping, config save.
- `I18nTest` — All translation files have required keys, no extras, non-empty values, correct model defaults. Uses `@dataProvider` to run against all 14 languages.
- `MetadataTest` — metadata.json structure, class existence, semver format.

When adding a new i18n language, add the translation file at `i18n/{code}/ext.php` with all keys from `en/ext.php`. The `I18nTest` will automatically pick it up via `languageProvider()`.
