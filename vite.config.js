import fs from "fs";
import path from "path";

import { defineConfig } from "vite";

function jsToTsRedirectPlugin() {
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

function getJsEntries(dir) {
    const entries = {};
    const files = fs.readdirSync(dir);
    files.forEach(file => {
        if (file.endsWith('.js') || file.endsWith('.ts')) {
            const name = path.parse(file).name;
            entries[name] = path.resolve(dir, file);
        }
    });
    return entries;
}

const projectRoot = __dirname;
const httpsConfig = {};

const keyPath = path.resolve(projectRoot, ".config/certs/localhost-key.pem");
const certPath = path.resolve(projectRoot, ".config/certs/localhost.pem");

if (fs.existsSync(keyPath) && fs.existsSync(certPath)) {
    httpsConfig.key = fs.readFileSync(keyPath);
    httpsConfig.cert = fs.readFileSync(certPath);
}

export default defineConfig({
    plugins: [jsToTsRedirectPlugin()],

    root: path.resolve(__dirname, "files/res/js"),

    server: {
        origin: "https://localhost:5173",
        https: httpsConfig,
        hmr: {
            overlay: true,
        },
    },

    esbuild: {
        target: 'esnext',
        jsx: 'automatic',
    },

    build: {
        outDir: "../assets",
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: getJsEntries(path.resolve(__dirname, 'files/res/js')),
            output: {
                entryFileNames: `[name].[hash].js`,
                chunkFileNames: `common.[hash].js`,
                assetFileNames: `[name].[hash].[ext]`,
            },
        },
    },
});
