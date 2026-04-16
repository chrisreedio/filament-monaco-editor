import * as monaco from 'monaco-editor/esm/vs/editor/editor.api.js'

import 'monaco-editor/esm/vs/basic-languages/html/html.contribution.js'
import 'monaco-editor/esm/vs/basic-languages/php/php.contribution.js'
import 'monaco-editor/esm/vs/basic-languages/javascript/javascript.contribution.js'
import 'monaco-editor/esm/vs/basic-languages/css/css.contribution.js'
import 'monaco-editor/esm/vs/basic-languages/sql/sql.contribution.js'
import 'monaco-editor/esm/vs/basic-languages/yaml/yaml.contribution.js'
import 'monaco-editor/esm/vs/basic-languages/xml/xml.contribution.js'
import 'monaco-editor/esm/vs/basic-languages/markdown/markdown.contribution.js'

const isBladeLanguageRegistered = () => monaco.languages.getLanguages().some((language) => language.id === 'blade')

const escapeRegex = (value) => String(value).replace(/[.*+?^${}()|[\]\\]/g, '\\$&')

const normalizeHeight = (value) => {
    if (typeof value === 'number') {
        return `${value}px`
    }

    if (value === null || value === undefined || value === '') {
        return '16rem'
    }

    return value
}

const normalizePixels = (value) => {
    if (typeof value === 'number') {
        return `${value}px`
    }

    return value
}

const getPluginData = () => {
    if (typeof window === 'undefined') {
        return {}
    }

    return (
        window.__filamentMonacoEditor
        ?? window.FilamentMonacoEditor
        ?? window.filamentMonacoEditor
        ?? (window.Filament?.assets?.scriptData?.monacoEditor ?? {})
    )
}

const buildCustomRules = (patterns) => {
    if (!Array.isArray(patterns)) {
        return []
    }

    return patterns
        .map((entry) => {
            if (!entry) {
                return null
            }

            if (typeof entry === 'string') {
                try {
                    return [new RegExp(entry), 'metatag']
                } catch (error) {
                    return null
                }
            }

            if (Array.isArray(entry) && typeof entry[0] === 'string') {
                try {
                    return [new RegExp(entry[0]), String(entry[1] ?? 'metatag')]
                } catch (error) {
                    return null
                }
            }

            if (typeof entry === 'object' && typeof entry.pattern === 'string') {
                try {
                    return [new RegExp(entry.pattern), String(entry.token ?? entry.style ?? 'metatag')]
                } catch (error) {
                    return null
                }
            }

            return null
        })
        .filter(Boolean)
}

const registerBladeLanguage = (bladeConfig = {}) => {
    if (isBladeLanguageRegistered()) {
        return
    }

    const delimiters = {
        output: bladeConfig?.delimiters?.output ?? ['{{', '}}'],
        raw: bladeConfig?.delimiters?.raw ?? ['{!!', '!!}'],
        comment: bladeConfig?.delimiters?.comment ?? ['{{--', '--}}'],
    }

    const keywords = (bladeConfig?.keywords ?? bladeConfig?.directives ?? ['if', 'elseif', 'else', 'endif', 'foreach', 'endforeach']).map((token) => escapeRegex(String(token).replace(/^@/, '')))
    const customRules = buildCustomRules(bladeConfig?.patterns)

    const directiveExpression = keywords.length > 0
        ? new RegExp(`@(?:${keywords.join('|')})(?=\\b|\\()`, 'g')
        : /@\w+/

    const outputOpen = escapeRegex(delimiters.output?.[0] ?? '{{')
    const outputClose = escapeRegex(delimiters.output?.[1] ?? '}}')
    const rawOpen = escapeRegex(delimiters.raw?.[0] ?? '{!!')
    const rawClose = escapeRegex(delimiters.raw?.[1] ?? '!!}')
    const commentOpen = escapeRegex(delimiters.comment?.[0] ?? '{{--')
    const commentClose = escapeRegex(delimiters.comment?.[1] ?? '--}}')

    monaco.languages.register({
        id: 'blade',
        aliases: ['Blade', 'blade'],
        extensions: ['.blade.php'],
        mimetypes: ['text/x-blade', 'text/blade'],
    })

    monaco.languages.setMonarchTokensProvider('blade', {
        defaultToken: '',
        tokenPostfix: '.blade',
        ignoreCase: false,
        tokenizer: {
            root: [
                [new RegExp(`${commentOpen}[\\s\\S]*?${commentClose}`), 'comment'],
                [new RegExp(`${rawOpen}[\\s\\S]*?${rawClose}`), 'metatag'],
                [new RegExp(`${outputOpen}[\\s\\S]*?${outputClose}`), 'metatag'],
                [directiveExpression, 'keyword'],
                [/@[a-zA-Z_][\w-]*/, 'keyword'],
                [/<\/?[A-Za-z][^\s/>]*/, { token: 'tag', next: '@htmlTag' }],
                ...customRules,
                { include: '@whitespace' },
            ],
            htmlTag: [
                [/'([^'\\]|\\.)*'/, 'string'],
                [/"([^"\\]|\\.)*"/, 'string'],
                [/[@#]?[\w-]+(?=\s*=)/, 'attribute.name'],
                [/=\s*/, 'delimiter'],
                [/>/, 'tag', '@pop'],
                [/\//, 'delimiter'],
                [/[\w-]+/, 'identifier'],
            ],
            whitespace: [
                [/\s+/, 'white'],
            ],
        },
    })

    monaco.languages.setLanguageConfiguration('blade', {
        comments: {
            blockComment: delimiters.comment,
        },
        brackets: [
            ['{', '}'],
            ['[', ']'],
            ['(', ')'],
        ],
        autoClosingPairs: [
            { open: delimiters.output?.[0] ?? '{{', close: delimiters.output?.[1] ?? '}}' },
            { open: delimiters.raw?.[0] ?? '{!!', close: delimiters.raw?.[1] ?? '!!}' },
            { open: delimiters.comment?.[0] ?? '{{--', close: delimiters.comment?.[1] ?? '--}}' },
            { open: '"', close: '"' },
            { open: "'", close: "'" },
            { open: '(', close: ')' },
            { open: '[', close: ']' },
            { open: '{', close: '}' },
            { open: '<', close: '>' },
        ],
        surroundingPairs: [
            { open: delimiters.output?.[0] ?? '{{', close: delimiters.output?.[1] ?? '}}' },
            { open: delimiters.raw?.[0] ?? '{!!', close: delimiters.raw?.[1] ?? '!!}' },
            { open: delimiters.comment?.[0] ?? '{{--', close: delimiters.comment?.[1] ?? '--}}' },
            { open: '"', close: '"' },
            { open: "'", close: "'" },
            { open: '(', close: ')' },
            { open: '[', close: ']' },
            { open: '{', close: '}' },
            { open: '<', close: '>' },
        ],
    })
}

const getConfig = (componentConfig = {}) => {
    const pluginData = getPluginData() ?? {}
    const pluginConfig = pluginData?.monacoEditor ?? pluginData
    const pluginDefaults = pluginConfig?.blade ?? {}

    const merged = {
        language: componentConfig.language ?? pluginConfig?.defaultLanguage ?? 'blade',
        theme: componentConfig.theme ?? pluginConfig?.defaultTheme ?? 'vs-dark',
        height: normalizeHeight(componentConfig.height ?? pluginConfig?.defaultMinHeight ?? '16rem'),
        readonly: componentConfig.readonly ?? false,
        options: componentConfig.options ?? {},
    }

    return {
        ...merged,
        readOnly: merged.readonly,
        options: {
            ...(pluginConfig?.defaults ?? {}),
            ...(merged.options ?? {}),
            readOnly: merged.readonly,
        },
        pluginDefaults: {
            ...pluginDefaults,
            directives: pluginDefaults?.directives ?? [],
            keywords: pluginDefaults?.keywords ?? pluginDefaults?.directives ?? [],
            patterns: pluginDefaults?.patterns ?? [],
            delimiters: pluginDefaults?.delimiters ?? {
                output: ['{{', '}}'],
                raw: ['{!!', '!!}'],
                comment: ['{{--', '--}}'],
            },
        },
    }
}

const registerAlpineComponent = () => {
    window.Alpine.data('monacoCodeEditor', (payload = {}) => ({
        editor: null,
        state: payload.state ?? '',
        updateState: payload.updateState ?? null,
        monacoConfig: getConfig(payload.config),

        init() {
            registerBladeLanguage(this.monacoConfig.pluginDefaults)

            this.editor = monaco.editor.create(this.$refs.editor, {
                value: this.state ?? '',
                language: this.monacoConfig.language || 'blade',
                theme: this.monacoConfig.theme || 'vs-dark',
                readOnly: this.monacoConfig.readOnly || false,
                minimap: {
                    enabled: false,
                },
                automaticLayout: true,
                fontFamily: 'monospace',
                ...this.monacoConfig.options,
            })

            this.$refs.editor.style.minHeight = normalizePixels(this.monacoConfig.height)

            this.editor.onDidChangeModelContent(() => {
                const value = this.editor.getValue()

                if (this.state !== value) {
                    this.state = value

                    if (typeof this.updateState === 'function') {
                        this.updateState(value)
                    }
                }
            })

            this.$watch('state', (value) => {
                if (!this.editor || value === this.editor.getValue()) {
                    return
                }

                this.editor.setValue(value ?? '')
            })
        },

        destroy() {
            if (this.editor) {
                this.editor.dispose()
                this.editor = null
            }
        },
    }))
}

if (window.Alpine && window.Alpine.__isStarted) {
    registerAlpineComponent()
} else {
    document.addEventListener('alpine:init', registerAlpineComponent)
}
