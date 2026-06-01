function decodeHtml(value) {
    const textarea = document.createElement('textarea');
    textarea.innerHTML = value;
    return textarea.value;
}

function escapeHtml(value) {
    return value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;');
}

function normalizeWhitespace(value) {
    const lines = value.replace(/\r\n?/g, '\n').split('\n');

    while (lines.length > 0 && lines[0].trim() === '') {
        lines.shift();
    }

    while (lines.length > 0 && lines[lines.length - 1].trim() === '') {
        lines.pop();
    }

    const compactLines = lines.filter((line) => line.trim() !== '');
    const indents = compactLines
        .map((line) => (line.match(/^ */) ?? [''])[0].length);
    const minIndent = indents.length > 0 ? Math.min(...indents) : 0;

    return compactLines
        .map((line) => line.slice(minIndent).replace(/\s+$/g, ''))
        .join('\n');
}

function highlightTag(tagSource) {
    const isClosingTag = /^<\//.test(tagSource);
    const isSelfClosingTag = /\/>$/.test(tagSource);
    const tagMatch = tagSource.match(/^<\/?([a-zA-Z][\w:-]*)\s*([^>]*)\/?>$/s);
    if (!tagMatch) {
        return `<span class="token punctuation">${escapeHtml(tagSource)}</span>`;
    }

    const tagName = tagMatch[1];
    const attributesSource = tagMatch[2] ?? '';
    let highlighted = '<span class="token punctuation">&lt;';
    if (isClosingTag) {
        highlighted += '/';
    }
    highlighted += `</span><span class="token tag">${tagName}</span>`;

    const attributePattern = /([^\s=]+)(?:\s*=\s*(?:"([^"]*)"|'([^']*)'))?/g;
    let match;

    while ((match = attributePattern.exec(attributesSource)) !== null) {
        const attributeName = match[1];
        const attributeValue = match[2] ?? match[3] ?? null;
        highlighted += ` <span class="token attr-name">${escapeHtml(attributeName)}</span>`;
        if (attributeValue !== null) {
            highlighted += '<span class="token punctuation">=</span>';
            highlighted += `<span class="token attr-value">&quot;${escapeHtml(attributeValue)}&quot;</span>`;
        }
    }

    highlighted += isSelfClosingTag && !isClosingTag
        ? '<span class="token punctuation">/&gt;</span>'
        : '<span class="token punctuation">&gt;</span>';

    return highlighted;
}

function highlightMarkup(source) {
    let result = '';
    let cursor = 0;
    const tagPattern = /<\/?[^>]+>/g;
    let match;

    while ((match = tagPattern.exec(source)) !== null) {
        result += escapeHtml(source.slice(cursor, match.index));
        result += highlightTag(match[0]);
        cursor = match.index + match[0].length;
    }

    result += escapeHtml(source.slice(cursor));
    return result;
}

export function initMarkupCodePreview(options = {}) {
    const root = options.root instanceof HTMLElement ? options.root : document.body;

    root.querySelectorAll('code.language-markup').forEach((codeBlock) => {
        if (!(codeBlock instanceof HTMLElement)) {
            return;
        }

        const source = normalizeWhitespace(
            decodeHtml(
                codeBlock.innerHTML
                    .replace(/<br\s*\/?>/gi, '\n')
                    .replace(/&nbsp;/gi, ' ')
            )
        );

        codeBlock.classList.add('migration-ui-code-ready');
        codeBlock.textContent = '';
        codeBlock.innerHTML = highlightMarkup(source);

        const pre = codeBlock.parentElement;
        if (pre instanceof HTMLElement && pre.tagName === 'PRE') {
            Array.from(pre.childNodes).forEach((node) => {
                if (node === codeBlock) {
                    return;
                }

                if (node.nodeType === Node.TEXT_NODE && (node.textContent ?? '').trim() === '') {
                    node.parentNode?.removeChild(node);
                }
            });
        }
    });
}
