// Erzeugt src/Classes/Project/icons.json: die Lucide-Icons, die PHP benutzt, plus eigene Icons.
// Läuft bei `npm run dev` / `npm run build`; manuell: `npm run icons`. Siehe public/res/js/icons/README.md.
import { readFile, readdir, writeFile } from "node:fs/promises";
import { existsSync } from "node:fs";
import path from "node:path";
import { fileURLToPath, pathToFileURL } from "node:url";

const root = path.resolve(path.dirname(fileURLToPath(import.meta.url)), "../..");
const scanDirs = ["src", "public/pages", "public/views", "public/layout"];
const lucideDir = path.join(root, "node_modules/lucide/dist/esm/icons");
const customFile = path.join(root, "public/res/js/icons/custom-icons.json");
const outFile = path.join(root, "src/Classes/Project/icons.json");

const patterns = [
    /Icon::\w+\(\s*["']([a-z0-9-]+)["']/g,          // Icon::get("pencil")
    /["']icon["']\s*=>\s*["']([a-z0-9-]+)["']/g,     // "icon" => "user-plus"
    /["']icon\w+["']\s*=>\s*["']([a-z0-9-]+)["']/g,  // Alias-Tabelle in Icon.php
];

async function* phpFiles(dir) {
    for (const entry of await readdir(dir, { withFileTypes: true })) {
        const full = path.join(dir, entry.name);
        if (entry.isDirectory()) yield* phpFiles(full);
        else if (entry.name.endsWith(".php")) yield full;
    }
}

const used = new Map(); // name -> erste Fundstelle
for (const dir of scanDirs) {
    for await (const file of phpFiles(path.join(root, dir))) {
        const text = await readFile(file, "utf8");
        for (const re of patterns) {
            for (const m of text.matchAll(re)) {
                if (!used.has(m[1])) used.set(m[1], path.relative(root, file));
            }
        }
    }
}

const custom = JSON.parse(await readFile(customFile, "utf8"));
for (const name of Object.keys(custom)) {
    if (!name.startsWith("custom-")) {
        console.error(`[icons] Eigenes Icon "${name}" muss mit "custom-" beginnen (${path.relative(root, customFile)}).`);
        process.exit(1);
    }
}

const icons = {};
const missing = [];
for (const name of [...used.keys()].sort()) {
    if (custom[name]) {
        icons[name] = custom[name];
        continue;
    }
    const file = path.join(lucideDir, `${name}.js`);
    if (!existsSync(file)) {
        missing.push(`${name} (${used.get(name)})`);
        continue;
    }
    const node = (await import(pathToFileURL(file).href)).default;
    icons[name] = node.map(([tag, attrs]) => [tag, Object.fromEntries(Object.entries(attrs).map(([k, v]) => [k, String(v)]))]);
}

if (missing.length) {
    console.error(`[icons] Unbekannte Icons (weder Lucide noch custom-icons.json):\n  - ${missing.join("\n  - ")}`);
    process.exit(1);
}

// Eine Zeile pro Icon: kurze, gut lesbare Diffs
const body = Object.entries(icons).map(([name, node]) => `  ${JSON.stringify(name)}: ${JSON.stringify(node)}`).join(",\n");
const json = `{\n${body}\n}\n`;
const previous = existsSync(outFile) ? await readFile(outFile, "utf8") : "";
if (previous !== json) {
    await writeFile(outFile, json);
    console.log(`[icons] ${Object.keys(icons).length} Icons nach ${path.relative(root, outFile)} geschrieben.`);
}
