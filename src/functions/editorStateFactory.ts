import { EditorState } from '@codemirror/state'
import { EditorView, keymap, highlightActiveLine } from '@codemirror/view'
import { defaultKeymap } from '@codemirror/commands'
import { history, historyKeymap } from '@codemirror/history'
import { indentOnInput } from '@codemirror/language'
// import { bracketMatching } from '@codemirror/matchbrackets'
import { lineNumbers, highlightActiveLineGutter } from '@codemirror/gutter'
import { defaultHighlightStyle, HighlightStyle, tags } from '@codemirror/highlight'
import { markdown, markdownLanguage } from '@codemirror/lang-markdown'
import { javascript, javascriptLanguage,  } from '@codemirror/lang-javascript'
import { css, cssLanguage  } from '@codemirror/lang-css'
import { html, htmlLanguage } from '@codemirror/lang-html'
import { json, jsonLanguage } from '@codemirror/lang-json'
import { languages } from '@codemirror/language-data'
import type { LanguageSupport } from '@codemirror/language';

export default (content: string, mime: string = '', onChange: (state: EditorState) => void) => {

    const languageSupport: {[key: string]: LanguageSupport} = {
        'text/markdown': markdown({
            base: markdownLanguage,
            codeLanguages: languages,
            addKeymap: true
        }),
        'application/javascript': javascript({jsx: true, typescript: false}),
        'text/typescript': javascript({jsx: true, typescript: true}),
        'text/css': css(),
        'text/html': html({autoCloseTags: true}),
        'application/json': json(),
    };

    return EditorState.create({
        doc: content,
        extensions: [
            keymap.of([...defaultKeymap, ...historyKeymap]),
            lineNumbers(),
            highlightActiveLineGutter(),
            history(),
            // indentOnInput(),
            // bracketMatching(),
            defaultHighlightStyle.fallback,
            highlightActiveLine(),
            languageSupport[mime],
            // oneDark,
            // transparentTheme,
            // syntaxHighlighting,
            EditorView.lineWrapping,
            EditorView.updateListener.of(update => {
                if (update.changes) {
                    onChange && onChange(update.state)
                }
            })
        ]
    });
}