import { Url } from "./url.js";

export type Params = Record<string, string>;

type Handler<P extends Params> = (ctx: {
    path: string;
    params: P;
    query: URLSearchParams;
    state: unknown;
}) => void;

type Route<P extends Params> = {
    pattern: string;
    keys: string[];
    regex: RegExp;
    handler: Handler<P>;
};

export class Router {
    private routes: Array<Route<any>> = [];
    private onChangeCallbacks: Array<() => void> = [];

    constructor() {
        window.addEventListener("popstate", () => this.resolve());
        this.patchHistory();
    }

    on<P extends Params = Params>(pattern: string, handler: Handler<P>): this {
        const { regex, keys } = this.compile(pattern);
        this.routes.push({ pattern, keys, regex, handler });
        return this;
    }

    resolve(): void {
        const path = Url.path();
        const query = new URLSearchParams(window.location.search);
        const state = history.state;

        for (const r of this.routes) {
            const m = path.match(r.regex);
            if (!m) continue;

            const params: Params = {};
            r.keys.forEach((k, i) => (params[k] = decodeURIComponent(m[i + 1] ?? "")));

            r.handler({ path, params, query, state });
            this.onChangeCallbacks.forEach(cb => cb());
            return;
        }
    }

    onChange(cb: () => void): () => void {
        this.onChangeCallbacks.push(cb);
        return () => {
            this.onChangeCallbacks = this.onChangeCallbacks.filter(x => x !== cb);
        };
    }

    private compile(pattern: string): { regex: RegExp; keys: string[] } {
        const keys: string[] = [];

        let p = pattern.replace(/\/+$/, "");
        if (p === "") p = "/";

        const escaped = p.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

        const withGroups = escaped.replace(/\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\}/g, (_full, key: string) => {
            keys.push(key);
            return "([^/]+)";
        });

        const regex = new RegExp("^" + withGroups + "$");
        return { regex, keys };
    }

    private patchHistory(): void {
        const fire = () => this.resolve();

        const _pushState = history.pushState;
        history.pushState = function (...args) {
            const ret = _pushState.apply(this, args as any);
            fire();
            return ret;
        };

        const _replaceState = history.replaceState;
        history.replaceState = function (...args) {
            const ret = _replaceState.apply(this, args as any);
            fire();
            return ret;
        };
    }
}
