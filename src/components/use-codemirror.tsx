import type React from 'react'
import { useEffect, useState, useRef } from 'react'
import { EditorView, keymap, highlightActiveLine } from '@codemirror/view'

interface Props {
}

const useCodeMirror = <T extends Element>(
    props: Props
): [React.MutableRefObject<T | null>, EditorView?] => {
    const refContainer = useRef<T>(null)
    const [editorView, setEditorView] = useState<EditorView>()

    useEffect(() => {
        if (!refContainer.current) return
        const view = new EditorView({
            parent: refContainer.current
        })
        setEditorView(view)
    }, [refContainer])

    return [refContainer, editorView]
}

export default useCodeMirror