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

if (fs.readFileSync(path.resolve(projectRoot, ".config/certs/localhost-key.pem")) && fs.readFileSync(path.resolve(projectRoot, ".config/certs/localhost.pem"))) {
    httpsConfig.key = fs.readFileSync(path.resolve(projectRoot, ".config/certs/localhost-key.pem"));
    httpsConfig.cert = fs.readFileSync(path.resolve(projectRoot, ".config/certs/localhost.pem"));
}

export default defineConfig({
    root: path.resolve(__dirname, "files/res/js"),

    server: {
        origin: "https://localhost:5173",
        https: httpsConfig,
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
