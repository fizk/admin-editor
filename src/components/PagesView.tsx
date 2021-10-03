import React from "react";
import { FileTree } from './FileTree';
import type {FunctionComponent} from "react";
import type { File } from '../types';

interface Props {
    tree: File[],
    onToggleExpand: (path: string) => void
    onOpen: (path: string) => void
}

export const PagesView: FunctionComponent<Props> = ({ tree, onToggleExpand, onOpen }) => {
    return (
        <div>
            <h2>PagesView</h2>
            <FileTree files={tree} onToggleExpand={onToggleExpand} onOpen={onOpen} />
        </div>
    )
}