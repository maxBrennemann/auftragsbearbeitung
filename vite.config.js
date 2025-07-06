import { defineConfig } from "vite";
import fs from "fs";
import path from "path";

function getJsEntries(dir) {
    const entries = {};
    const files = fs.readdirSync(dir);
    files.forEach(file => {
        if (file.endsWith('.js')) {
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
    root: path.resolve(__dirname, "files/res/js"),

    server: {
        origin: "https://localhost:5173",
        https: httpsConfig,
        hmr: {
            overlay: true,
        },
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
