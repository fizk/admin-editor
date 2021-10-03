import React from "react";
import { File as FileIcon } from '../elements/icons/File';
import { Folder as FolderIcon } from '../elements/icons/Folder';
import type {FunctionComponent } from "react";
import type { File } from "../types";

interface Props {
    files: File[]
    onToggleExpand: (path: string) => void
    onOpen: (path: string) => void
}

export const FileTree: FunctionComponent<Props> = ({ files, onToggleExpand, onOpen}) => {
    return (
        <ul>
            {files.map(file => (
                <li key={file.url}>
                    {file.type === 'dir' && (
                        <>
                        <span onClick={() => onToggleExpand(file.url)}><FolderIcon /> {file.name}</span>
                        {file.children && file.isOpen === true && (
                            <FileTree files={file.children} onToggleExpand={onToggleExpand} onOpen={onOpen} />
                        )}
                        </>
                    )}
                    {file.type !== 'dir' && (
                        <span onClick={() => onOpen(file.url)}><FileIcon /> {file.name}</span>
                    )}
                </li>
            ))}
        </ul>
    )
}