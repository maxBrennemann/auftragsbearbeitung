export type FunctionMap = Record<string, (...args: any[]) => void>;

export interface TableHeader {
    key: string;
    label: string;
}

export interface TableOptions {
    primaryKey: string;
    hide: string[];
    hideOptions: string[];
    styles: {
        table: {
            className: string[];
        };
    };
    autoSort: boolean;
    sum: { key: string; format: string }[];
}
