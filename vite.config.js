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

const httpsConfig = {};

if (fs.existsSync('.config/certs/localhost-key.pem') && fs.existsSync('.config/certs/localhost-cert.pem')) {
    httpsConfig.key = fs.readFileSync('.config/certs/localhost-key.pem');
    httpsConfig.cert = fs.readFileSync('.config/certs/localhost-cert.pem');
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
        rollupOptions: {
            input: getJsEntries(path.resolve(__dirname, 'files/res/js')),
            output: {
                entryFileNames: `[name].js`,
                chunkFileNames: `common-[hash].js`,
                assetFileNames: `[name].[ext]`,
            },
        },
    },
});
