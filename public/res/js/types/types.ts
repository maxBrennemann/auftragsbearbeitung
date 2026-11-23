export type FunctionMap = Record<string, (...args: any[]) => void>;

export interface TableHeader {
    key: string;
    label: string;
    /** Spaltenausrichtung; "right" für Beträge und Mengen (Kopf und Zellen rechtsbündig) */
    align?: "left" | "right";
}

export interface TableOptions {
    primaryKey: string;
    hide: string[];
    hideOptions: string[];
    styles: {
        table?: {
            className: string[];
        };
        key?: {
            [key: string]: string[];
        };
        sum?: {
            [key: string]: string[];
        };
    };
    conditions?: Record<string, string | number>;
    joins?: Record<string, string | number>;
    autoSort: boolean;
    sum?: { key: string; format: string }[];
}
