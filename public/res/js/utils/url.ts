export type NavigateOptions = { replace?: boolean; state?: unknown };

export const Url = {
    path(): string {
        let p = window.location.pathname.replace(/\/+$/, "");
        return p === "" ? "/" : p;
    },

    segments(): string[] {
        return this.path().split("/").filter(Boolean);
    },

    query(): Record<string, string> {
        return Object.fromEntries(new URLSearchParams(window.location.search));
    },

    queryGet(key: string, def: string | null = null): string | null {
        const v = new URLSearchParams(window.location.search).get(key);
        return v ?? def;
    },

    build(path: string, query: Record<string, string | null | undefined> = {}): string {
        const url = new URL(path, window.location.origin);
        for (const [k, v] of Object.entries(query)) {
            if (v !== null && v !== undefined && v !== "") url.searchParams.set(k, v);
        }
        return url.pathname + url.search;
    },

    updateQuery(
        set: Record<string, string | null | undefined> = {},
        unset: string[] = [],
        opts: NavigateOptions = {}
    ): void {
        const url = new URL(window.location.href);

        for (const k of unset) url.searchParams.delete(k);

        for (const [k, v] of Object.entries(set)) {
            if (v === null || v === undefined || v === "") url.searchParams.delete(k);
            else url.searchParams.set(k, v);
        }

        if (opts.replace) history.replaceState(opts.state ?? {}, "", url);
        else history.pushState(opts.state ?? {}, "", url);
    },

    navigate(path: string, query: Record<string, string | null | undefined> = {}, opts: NavigateOptions = {}): void {
        const url = new URL(path, window.location.origin);
        for (const [k, v] of Object.entries(query)) {
            if (v !== null && v !== undefined && v !== "") url.searchParams.set(k, v);
        }

        if (opts.replace) history.replaceState(opts.state ?? {}, "", url);
        else history.pushState(opts.state ?? {}, "", url);
    }
} as const;
