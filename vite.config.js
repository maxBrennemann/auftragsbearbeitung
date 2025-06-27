import { defineConfig } from "vite";
import fs from "fs";
import path from "path";

const projectRoot = __dirname;

export default defineConfig({
    root: path.resolve(__dirname, "files/res/js"),

    server: {
        origin: "https://localhost:5173",
        https: {
            key: fs.readFileSync(path.resolve(projectRoot, ".config/certs/localhost-key.pem")),
            cert: fs.readFileSync(path.resolve(projectRoot, ".config/certs/localhost.pem")),
        },
    },

    css: {
        postcss: path.resolve(projectRoot, "tailwind.config.js"),
    },
});
