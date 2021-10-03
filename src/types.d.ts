
export interface File {
    category: string
    created: number
    extension: string
    group: number
    modified: number
    name: string
    owner: number
    permissions: string
    size: string
    type: string
    url: string
    isOpen: boolean
    children: File[]
}