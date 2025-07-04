import { defineConfig } from "vite";
import fs from "fs";
import path from "path";

const projectRoot = __dirname;

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

export default defineConfig({
    root: path.resolve(__dirname, "files/res/js"),

    server: {
        origin: "https://localhost:5173",
        https: {
            key: fs.readFileSync(path.resolve(projectRoot, ".config/certs/localhost-key.pem")),
            cert: fs.readFileSync(path.resolve(projectRoot, ".config/certs/localhost.pem")),
        },
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
