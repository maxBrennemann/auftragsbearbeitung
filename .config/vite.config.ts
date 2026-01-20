import fs from "fs";
import path from "path";
import { fileURLToPath } from 'url';
import { dirname } from 'path';
import type { ProcessOptions } from 'postcss';
import type { ServerOptions } from 'vite';
import type { Plugin } from 'vite';

import { defineConfig } from "vite";
import postcssConfig from "./postcss.config.ts";
import tailwindcss from "@tailwindcss/vite";

function jsToTsRedirectPlugin(): Plugin {
    return {
        name: 'js-to-ts-redirect',
        enforce: 'pre',
        configureServer(server) {
            server.middlewares.use((req, res, next) => {
                if (!req.url?.endsWith('.js')) return next();

                const tsUrl = req.url.replace(/\.js$/, '.ts');
                const cleanPath = tsUrl.startsWith('/') ? tsUrl.slice(1) : tsUrl;
                const fullPath = path.resolve(server.config.root, cleanPath);

                if (fs.existsSync(fullPath)) {
                    req.url = tsUrl;
                }

                next();
            })
        }
    }
}

function getJsEntries(dir: string): Record<string, string> {
    const entries: Record<string, string> = {};
    const files = fs.readdirSync(dir);
    files.forEach(file => {
        if (file.endsWith('.js') || file.endsWith('.ts')) {
            const name = path.parse(file).name;
            entries[name] = path.resolve(dir, file);
        }
    });
    return entries;
}

const fullReloadAlways: Plugin = {
    name: 'full-reload-always',
    apply: 'serve',
    handleHotUpdate({ server }) {
        server.ws.send({
            type: 'full-reload',
        });
        return [];
    }
}

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

const projectRoot = __dirname;
const httpsConfig: ServerOptions['https'] = {};

const keyPath = path.resolve(projectRoot, "./certs/localhost-key.pem");
const certPath = path.resolve(projectRoot, "./certs/localhost.pem");

if (fs.existsSync(keyPath) && fs.existsSync(certPath)) {
    httpsConfig.key = fs.readFileSync(keyPath);
    httpsConfig.cert = fs.readFileSync(certPath);
}

export default defineConfig({
    plugins: [jsToTsRedirectPlugin(), fullReloadAlways, tailwindcss()],

    root: path.resolve(__dirname, "../public/res/js"),

    css: {
        postcss: postcssConfig as ProcessOptions,
    },

    server: {
        origin: "https://localhost:5173",
        https: httpsConfig,
        hmr: {
            overlay: true,
        },
        fs: {
            allow: [
                path.resolve(__dirname, "../"),
            ],
        },
    },

    esbuild: {
        target: 'esnext',
        jsx: 'automatic',
        pure: ['console.log'],
    },

    build: {
        outDir: "../assets",
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: {
                ...getJsEntries(path.resolve(__dirname, '../public/res/js')),
                ...getJsEntries(path.resolve(__dirname, '../public/res/js/pages')),
            },
            output: {
                entryFileNames: `[name].[hash].js`,
                chunkFileNames: `common.[hash].js`,
                assetFileNames: `[name].[hash].[ext]`,
            },
        },
    },
});
