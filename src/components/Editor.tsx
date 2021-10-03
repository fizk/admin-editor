import React, { useRef, useEffect, useCallback } from "react";
import useCodeMirror from "./use-codemirror";
import type {FunctionComponent} from "react";

interface Props {
    initialDoc: string,
    onChange: (doc: string) => void
}

export const Editor: FunctionComponent<Props> = ({ onChange, initialDoc }) => {
    const handleChange = useCallback(
        state => onChange(state.doc.toString()),
        [onChange]
    )
    const [refContainer, editorView] = useCodeMirror<HTMLDivElement>({
        initialDoc: initialDoc,
        onChange: handleChange
    })

    useEffect(() => {
        if (editorView) {
            // Do nothing for now
        }
    }, [editorView])

    return <div className='editor-wrapper' ref={refContainer}></div>
}