import { defineConfig } from "vite";
import fs from "fs";
import path from "path";
import tailwindcss from "tailwindcss";

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

    plugins: [tailwindcss()],

    css: {
        postcss: {
            plugins: [tailwindcss()],
        },
    },

});
