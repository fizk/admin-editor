import React, { useEffect, useReducer, useState, useCallback } from 'react';
import { PagesView } from './PagesView';
import { Editor } from './Editor';
import type {FunctionComponent} from 'react';
import type {File} from '../types';
import useCodeMirror from "./use-codemirror";
import type { EditorState } from '@codemirror/state'
import editorStateFactory from '../functions/editorStateFactory';
type State = File[];

interface Props {}

type Action =
    | { type: 'init', tree: File[]}
    | { type: 'toggle-expand', path: string}
    | { type: 'open', path: string, content: any};

function reducer(state: State, action: Action) {
    switch (action.type) {
        case 'init':
            return action.tree;
        case 'toggle-expand':
            return toggleOpen(state, action.path);
        case 'open':
            console.log(action.type, action.path)
            return state;
        default:
            throw new Error();
    }
}

function toggleOpen(tree: File[], path: string) {
    return tree.map(file => {
        const newFile = {...file};
        if(file.url === path) {
            newFile.isOpen = !Boolean(newFile.isOpen);
        }
        if (newFile.children) {
            newFile.children = toggleOpen(newFile.children, path);
        }
        return newFile;
    })
}

const textMime = [
    'application/javascript',
    'application/typescript',
    'application/json',
    'application/xml',
    'text/markdown',
    'text/yaml',
    'text/tsx',
    'text/jsx',
    'text/css',
    'text/csv',
    'text/html',
    'text/plain',
    'image/svg+xml'
];

function extractMime(response: Response) {
    //TODO handle if not found
    return response.headers.get('content-type').split(';')[0];
}

const editorViews: { [key: string]: EditorState } = {};

export const App: FunctionComponent<Props> = () => {
    const [pagesState, pagesDispatch] = useReducer(reducer, []);
    const [themesState, themesDispatch] = useReducer(reducer, []);
    const [configState, configDispatch] = useReducer(reducer, []);
    const [view, setView] = useState('pages');

    useEffect(() => {
        fetch('/api/pages')
            .then(response => response.json())
            .then(json => pagesDispatch({type: 'init', tree: json}));

        fetch('/api/themes')
            .then(response => response.json())
            .then(json => themesDispatch({type: 'init', tree: json}));

        fetch('/api/config')
            .then(response => response.json())
            .then(json => configDispatch({type: 'init', tree: json}));
    }, []);

    const [refContainer, editorView] = useCodeMirror<HTMLDivElement>({})

    const handleOpen = async (path: string) => {
        if (!editorViews.hasOwnProperty(path)) {
            const response = await fetch(path);
            if (response.status === 200) {
                const mime = extractMime(response).toLowerCase();
                if (textMime.indexOf(mime) >= 0 ) {
                    const content = await response.text();
                    editorViews[path] = editorStateFactory(content, mime, console.log)
                }
            }
        }

        editorView.setState(editorViews[path]);
    }

    return (
        <>
            <header>header</header>
            <aside>
                <ul style={{ display: 'flex', listStyle: 'none' }}>
                    <li><span onClick={() => setView('pages')}>Pages</span></li>
                    <li><span onClick={() => setView('themes')}>Themes</span></li>
                    <li><span onClick={() => setView('config')}>Config</span></li>
                </ul>

                {view === 'pages' && <PagesView tree={pagesState}
                    onToggleExpand={(path) => pagesDispatch({ type: 'toggle-expand', path: path })}
                    onOpen={handleOpen}
                />}
                {view === 'themes' && <PagesView tree={themesState}
                    onToggleExpand={(path) => themesDispatch({ type: 'toggle-expand', path: path })}
                    onOpen={handleOpen}
                />}
                {view === 'config' && <PagesView tree={configState}
                    onToggleExpand={(path) => configDispatch({ type: 'toggle-expand', path: path })}
                    onOpen={handleOpen}
                />}
            </aside>
            <main>
                {/* <Editor initialDoc="" onChange={console.log} /> */}
                <div ref={refContainer}></div>
            </main>
            <footer>footer</footer>


        </>
    )
}