<p align="center">
  <img src="https://freshrss.org/images/icon.svg" alt="FreshRSS" width="60" />
</p>

<h1 align="center">AI Summary for FreshRSS</h1>

<p align="center">
  <strong>Summarize any article with one click using your favorite AI provider.</strong>
</p>

<p align="center">
  <a href="https://github.com/deimosfr/xExtension-AiSummary/actions/workflows/ci.yml"><img src="https://github.com/deimosfr/xExtension-AiSummary/actions/workflows/ci.yml/badge.svg" alt="CI" /></a>
  <a href="https://github.com/deimosfr/xExtension-AiSummary/blob/main/LICENSE"><img src="https://img.shields.io/github/license/deimosfr/xExtension-AiSummary?color=blue" alt="License" /></a>
  <img src="https://img.shields.io/badge/PHP-%3E%3D8.1-8892BF?logo=php&logoColor=white" alt="PHP >= 8.1" />
  <img src="https://img.shields.io/badge/FreshRSS-Extension-green?logo=rss&logoColor=white" alt="FreshRSS Extension" />
</p>

---

## Features

- **One-click summaries** — A "Summarize" button appears in every article. Click it to get an AI-generated summary streamed in real time below the title.
- **Real-time streaming** — Summaries are delivered via Server-Sent Events (SSE), so you see text appear as the AI generates it, with a live typing cursor.
- **Auto-fetch full articles** — When the RSS feed only contains a short excerpt, the extension automatically fetches and parses the full article from the original URL.
- **4 AI providers** — Choose the one that fits your setup:

  | Provider | Default Model | API Key Required |
  |----------|--------------|:----------------:|
  | OpenAI (ChatGPT) | `gpt-4o-mini` | Yes |
  | Anthropic (Claude) | `claude-sonnet-4-6` | Yes |
  | Google (Gemini) | `gemini-2.5-flash` | Yes |
  | Ollama | `llama3.2` | No |

- **Custom prompts** — Override the default summarization prompt. Use `{content}`, `{title}`, and `{language}` placeholders in your template.
- **Language override** — Choose the summary output language independently of your FreshRSS UI language.
- **Toggle summaries** — Click again to show/hide a cached summary without re-fetching.
- **Markdown formatting** — AI responses are rendered with support for headers, bold, italic, inline code, lists, and horizontal rules.
- **Theme-aware styling** — Adapts to your FreshRSS theme's colors with a gradient border accent.
- **14 languages** — cs, de, en, es, fr, it, ja, ko, nl, pl, pt-br, ru, tr, zh-cn.
- **Secure** — API keys stay server-side. All requests are proxied through PHP.

## Installation

### From Git

```bash
cd /path/to/FreshRSS/extensions
git clone https://github.com/deimosfr/xExtension-AiSummary.git
```

### Manual

1. Download the [latest release](https://github.com/deimosfr/xExtension-AiSummary/releases) or the repository ZIP.
2. Extract it into your FreshRSS `extensions/` directory.
3. Rename the folder to `xExtension-AiSummary` if needed.

### Enable

1. In FreshRSS, go to **Settings > Extensions**.
2. Enable **AI Summary**.
3. Click the gear icon to configure your provider, API key, and model.

## Configuration

| Setting | Description |
|---------|-------------|
| **AI Provider** | Select OpenAI, Anthropic, Gemini, or Ollama. |
| **API Key** | Your provider's API key. Not required for Ollama. |
| **Model** | Leave empty to use the provider's default (see table above), or specify any model your provider supports. |
| **API URL** | Only for Ollama. Defaults to `http://localhost:11434`. |
| **Custom Prompt** | Override the system prompt sent to the AI. Supports `{content}`, `{title}`, and `{language}` placeholders. Leave empty for the built-in default. |
| **Language** | Override the summary output language. Defaults to your FreshRSS UI language. |

## How It Works

```
User clicks "Summarize"
        |
        v
  JS sends AJAX POST ──> AiSummaryController (PHP)
                              |
                              ├── Fetches article from database
                              ├── If content is too short, fetches full article from URL
                              ├── Strips HTML, truncates to 12k chars
                              ├── Builds prompt (system + article content)
                              └── Calls AI provider API via cURL
                                      |
                                      v
                              Streams response via SSE
                              (status → chunks → done)
                                      |
                                      v
                          JS renders formatted summary
                          in real time below the article title
```

## Development

No build step required. PHP 8.1+ with the `curl` and `mbstring` extensions.

```bash
# Install test dependencies
composer install

# Run tests
vendor/bin/phpunit

# Run tests with verbose output
vendor/bin/phpunit --testdox

# Run a single test
vendor/bin/phpunit --filter testMethodName

# Lint PHP files
php -l extension.php
```

### Project Structure

```
xExtension-AiSummary/
├── extension.php              # Extension entrypoint
├── configure.phtml            # Settings form
├── metadata.json              # Extension metadata
├── Controllers/
│   └── AiSummaryController.php  # AI provider API proxy
├── static/
│   ├── script.js              # Frontend logic
│   └── style.css              # Styling (light + dark)
├── i18n/                      # Translations (14 languages)
│   ├── en/ext.php
│   ├── fr/ext.php
│   └── ...
├── tests/                     # PHPUnit test suite
│   ├── stubs/                 # FreshRSS framework stubs
│   ├── AiSummaryControllerTest.php
│   ├── AiSummaryExtensionTest.php
│   ├── I18nTest.php
│   └── MetadataTest.php
└── .github/workflows/ci.yml  # CI pipeline
```

## Contributing

Contributions are welcome! Please:

1. Fork the repository.
2. Create a feature branch.
3. Ensure tests pass: `vendor/bin/phpunit`
4. Submit a pull request.

### Adding a Translation

Create `i18n/{language-code}/ext.php` with all keys from [`i18n/en/ext.php`](i18n/en/ext.php). The test suite will automatically validate it.

## License

This project is licensed under the [GNU General Public License v3.0](LICENSE).
