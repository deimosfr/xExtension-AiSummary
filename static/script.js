'use strict';

(function () {
	var nextSummaryId = 0;

	function initButtons() {
		var wrappers = document.querySelectorAll('.ai-summary-wrapper:not(.ai-summary-initialized)');
		wrappers.forEach(function (wrapper) {
			var label = wrapper.dataset.label || 'Summarize';
			var button = document.createElement('button');
			button.type = 'button';
			button.className = 'ai-summary-btn';
			button.setAttribute('aria-expanded', 'false');
			button.title = label;

			var icon = document.createElement('span');
			icon.className = 'ai-summary-icon';
			icon.setAttribute('aria-hidden', 'true');
			icon.textContent = '✦';
			button.appendChild(icon);

			var labelSpan = document.createElement('span');
			labelSpan.className = 'ai-summary-label';
			labelSpan.textContent = label;
			button.appendChild(labelSpan);

			var contentDiv = document.createElement('div');
			contentDiv.className = 'ai-summary-content';
			contentDiv.id = 'ai-summary-content-' + (++nextSummaryId);
			contentDiv.setAttribute('role', 'region');
			contentDiv.setAttribute('aria-live', 'polite');
			contentDiv.setAttribute('aria-busy', 'false');
			contentDiv.setAttribute('aria-label', label + ' summary');
			button.setAttribute('aria-controls', contentDiv.id);
			wrapper.replaceChildren(button, contentDiv);
			wrapper.classList.add('ai-summary-initialized');
		});
	}

	// Initialize on load
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', initButtons);
	} else {
		initButtons();
	}

	// More robust dynamic content discovery
	var observer = new MutationObserver(function () {
		initButtons();
	});
	if (document.body) {
		observer.observe(document.body, { childList: true, subtree: true });
	} else {
		document.addEventListener('DOMContentLoaded', function () {
			observer.observe(document.body, { childList: true, subtree: true });
		});
	}

	// Periodic poll for late-loading dynamic content (fallback)
	// Some themes or views (like Reader) might inject content in ways that are tricky to observe
	setInterval(initButtons, 1000);

	// Handle summarize button clicks (event delegation)
	document.addEventListener('click', function (e) {
		var btn = e.target.closest('.ai-summary-btn');
		if (!btn) {
			return;
		}

		e.preventDefault();
		e.stopPropagation();

		var wrapper = btn.closest('.ai-summary-wrapper');
		if (!wrapper) {
			return;
		}

		var entryId = wrapper.dataset.entryId;
		var contentDiv = wrapper.querySelector('.ai-summary-content');

		function syncExpandedState() {
			btn.setAttribute(
				'aria-expanded',
				contentDiv.classList.contains('ai-summary-visible') ? 'true' : 'false',
			);
		}

		function showError(message) {
			var error = document.createElement('p');
			error.className = 'ai-summary-error';
			error.textContent = '⚠ ' + message;
			contentDiv.replaceChildren(error);
			contentDiv.classList.add('ai-summary-visible');
			contentDiv.dataset.loaded = '';
			contentDiv.setAttribute('aria-busy', 'false');
			syncExpandedState();
		}

		// Toggle: if summary is visible, hide it
		if (contentDiv.classList.contains('ai-summary-visible') && contentDiv.dataset.loaded) {
			contentDiv.classList.remove('ai-summary-visible');
			btn.setAttribute('aria-expanded', 'false');
			return;
		}

		// If already loaded, just show it
		if (contentDiv.dataset.loaded) {
			contentDiv.classList.add('ai-summary-visible');
			btn.setAttribute('aria-expanded', 'true');
			return;
		}

		// Fetch summary (streaming)
		btn.disabled = true;
		btn.classList.add('ai-summary-loading');
		var originalHTML = btn.innerHTML;
		var labelText = btn.textContent.trim();
		btn.innerHTML = '⏳ ' + escapeHtml(labelText);
		// Pick up the theme's accent color from .flux.current border-left
		var flux = btn.closest('.flux');
		if (flux) {
			var fluxBorder = getComputedStyle(flux).borderLeftColor;
			if (fluxBorder && fluxBorder !== 'rgba(0, 0, 0, 0)' && fluxBorder !== 'transparent') {
				contentDiv.style.setProperty('--ai-summary-accent', fluxBorder);
			}
		}
		// Resolve parent background for the gradient border effect
		var el = wrapper.parentElement;
		while (el) {
			var bg = getComputedStyle(el).backgroundColor;
			if (bg && bg !== 'rgba(0, 0, 0, 0)' && bg !== 'transparent') {
				contentDiv.style.setProperty('--ai-summary-bg', bg);
				break;
			}
			el = el.parentElement;
		}
		contentDiv.classList.add('ai-summary-visible');
		contentDiv.setAttribute('aria-busy', 'true');
		syncExpandedState();
		contentDiv.innerHTML = '<p class="ai-summary-placeholder">Initializing…</p>';

		var formData = new URLSearchParams();
		formData.append('_csrf', context.csrf);
		formData.append('id', entryId);

		fetch('./?c=AiSummary&a=summarize', {
			method: 'POST',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
			body: formData.toString(),
		}).then(function (response) {
			var contentType = response.headers.get('Content-Type') || '';

			// JSON error response (validation errors)
			if (contentType.indexOf('application/json') !== -1) {
				return response.json().then(function (data) {
					if (data.error) {
						showError(data.error);
					}
				});
			}

			// SSE streaming response
			var reader = response.body.getReader();
			var decoder = new TextDecoder();
			var sseBuffer = '';
			var currentEvent = '';
			var fullText = '';
			var summaryDiv = null;

			function processLine(line) {
				if (line.indexOf('event: ') === 0) {
					currentEvent = line.substring(7);
				} else if (line.indexOf('data: ') === 0) {
					var data;
					try { data = JSON.parse(line.substring(6)); } catch (ex) { return; }

					if (currentEvent === 'status') {
						contentDiv.innerHTML = '<p class="ai-summary-placeholder">' + escapeHtml(data.message) + '</p>';
						summaryDiv = null;
					} else if (currentEvent === 'chunk') {
						if (!summaryDiv) {
							summaryDiv = document.createElement('div');
							summaryDiv.className = 'ai-summary-text ai-summary-streaming';
							contentDiv.innerHTML = '';
							contentDiv.appendChild(summaryDiv);
						}
						fullText += data.text;
						summaryDiv.innerHTML = formatSummary(fullText);
					} else if (currentEvent === 'error') {
						showError(data.message);
					} else if (currentEvent === 'done') {
						if (summaryDiv) {
							summaryDiv.classList.remove('ai-summary-streaming');
						}
						contentDiv.dataset.loaded = '1';
						contentDiv.setAttribute('aria-busy', 'false');
						syncExpandedState();
					}
					currentEvent = '';
				}
			}

			function read() {
				return reader.read().then(function (result) {
					if (result.done) {
						if (sseBuffer.trim()) {
							processLine(sseBuffer.trim());
						}
						if (fullText && !contentDiv.dataset.loaded) {
							if (summaryDiv) {
								summaryDiv.classList.remove('ai-summary-streaming');
							}
							contentDiv.dataset.loaded = '1';
							contentDiv.setAttribute('aria-busy', 'false');
							syncExpandedState();
						}
						return;
					}
					sseBuffer += decoder.decode(result.value, { stream: true });
					var lines = sseBuffer.split('\n');
					sseBuffer = lines.pop();
					lines.forEach(function (line) {
						line = line.trim();
						if (line) {
							processLine(line);
						}
					});
					return read();
				});
			}

			return read();
		}).catch(function (err) {
			showError(err.message);
		}).finally(function () {
			btn.disabled = false;
			btn.classList.remove('ai-summary-loading');
			btn.innerHTML = originalHTML;
			contentDiv.setAttribute('aria-busy', 'false');
			syncExpandedState();
		});
	});

	function escapeHtml(text) {
		var div = document.createElement('div');
		div.appendChild(document.createTextNode(text));
		return div.innerHTML;
	}

	function formatSummary(text) {
		var lines = text.split('\n');
		var html = '';
		var inUl = false;
		var inOl = false;

		for (var i = 0; i < lines.length; i++) {
			var line = lines[i];
			var trimmed = line.trim();

			// Empty line — close any open list
			if (trimmed === '') {
				if (inUl) { html += '</ul>'; inUl = false; }
				if (inOl) { html += '</ol>'; inOl = false; }
				continue;
			}

			// Headers: ### text, ## text, # text
			var headerMatch = trimmed.match(/^(#{1,4})\s+(.+)$/);
			if (headerMatch) {
				if (inUl) { html += '</ul>'; inUl = false; }
				if (inOl) { html += '</ol>'; inOl = false; }
				var level = headerMatch[1].length + 1; // ### → h4, ## → h3, # → h2
				if (level > 4) level = 4;
				html += '<h' + level + '>' + formatInline(escapeHtml(headerMatch[2])) + '</h' + level + '>';
				continue;
			}

			// Standalone bold line as header: **Title** or **Title (note)**
			var boldHeaderMatch = trimmed.match(/^\*\*(.+)\*\*$/);
			if (boldHeaderMatch) {
				if (inUl) { html += '</ul>'; inUl = false; }
				if (inOl) { html += '</ol>'; inOl = false; }
				html += '<h4>' + escapeHtml(boldHeaderMatch[1]) + '</h4>';
				continue;
			}

			// Horizontal rule: --- or ***
			if (/^[-*_]{3,}\s*$/.test(trimmed)) {
				if (inUl) { html += '</ul>'; inUl = false; }
				if (inOl) { html += '</ol>'; inOl = false; }
				html += '<hr>';
				continue;
			}

			// Unordered list: - item or * item
			var ulMatch = trimmed.match(/^[\-\*]\s+(.+)$/);
			if (ulMatch) {
				if (inOl) { html += '</ol>'; inOl = false; }
				if (!inUl) { html += '<ul>'; inUl = true; }
				html += '<li>' + formatInline(escapeHtml(ulMatch[1])) + '</li>';
				continue;
			}

			// Ordered list: 1. item
			var olMatch = trimmed.match(/^\d+[\.\)]\s+(.+)$/);
			if (olMatch) {
				if (inUl) { html += '</ul>'; inUl = false; }
				if (!inOl) { html += '<ol>'; inOl = true; }
				html += '<li>' + formatInline(escapeHtml(olMatch[1])) + '</li>';
				continue;
			}

			// Regular paragraph
			if (inUl) { html += '</ul>'; inUl = false; }
			if (inOl) { html += '</ol>'; inOl = false; }
			html += '<p>' + formatInline(escapeHtml(trimmed)) + '</p>';
		}

		if (inUl) html += '</ul>';
		if (inOl) html += '</ol>';

		return html;
	}

	function formatInline(escaped) {
		// Bold + italic: ***text*** or ___text___
		escaped = escaped.replace(/\*\*\*(.+?)\*\*\*/g, '<strong><em>$1</em></strong>');
		// Bold: **text**
		escaped = escaped.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
		// Italic: *text* or _text_
		escaped = escaped.replace(/\*(.+?)\*/g, '<em>$1</em>');
		escaped = escaped.replace(/(?<!\w)_(.+?)_(?!\w)/g, '<em>$1</em>');
		// Inline code: `text`
		escaped = escaped.replace(/`(.+?)`/g, '<code>$1</code>');
		return escaped;
	}
})();
