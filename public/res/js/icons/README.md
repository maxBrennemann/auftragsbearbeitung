# Icons

Standard ist [Lucide](https://lucide.dev/icons). Eigene Icons nur, wenn Lucide nichts Passendes hat
(z. B. branchenspezifische Dinge wie Folienrolle oder Plotter).

## Verwenden

- **PHP-Templates:** `Icon::get("pencil")`, optional Größe, CSS-Klassen und Titel:
  `Icon::get("trash-2", 16, ["text-danger"], "Löschen")`. Das Icon wird auf dem Server gerendert.
- **TypeScript:** `icon(Pencil)` bzw. `iconHtml(Pencil)` aus `classes/icon.ts`
  (`import { Pencil } from "lucide"`); eigene Icons über `icon(customIcon("custom-…"))`.
- Größen: 16 px neben Text (Standard), 20 px als alleinstehendes Icon. Farbe immer über `currentColor`,
  also per Textfarbe (`text-danger`, `text-ink`, …).
- Reine Icon-Buttons brauchen `title` und `aria-label`.

## PHP-Icondaten

PHP liest die Icon-Pfade aus `src/Classes/Project/icons.json`. Die Datei wird von
`.config/scripts/generate-icons.mjs` erzeugt, und zwar automatisch bei `npm run dev` und `npm run build`
(manuell: `npm run icons`). Das Skript sucht in `src/` und `public/{pages,views,layout}` nach
`Icon::…("name")` und `"icon" => "name"` und übernimmt genau diese Icons. Unbekannte Namen brechen den Build ab.

Daraus folgen zwei Regeln:

- **Icon-Namen immer wörtlich an einer dieser beiden Stellen schreiben.** Über eine Variable oder Hilfsfunktion
  (`$helper("globe")`, `["Produkte", "tag"]`) findet das Skript sie nicht, und `Icon::get()` gibt still
  einen leeren String zurück. Stattdessen `$helper(Icon::get("globe", 14))` bzw. `["icon" => "tag"]`.
- **Auch Kommentare werden durchsucht.** Ein Beispiel wie `"icon" => "name"` in einem Kommentar wird als Icon
  „name“ gelesen und bricht den Build ab.

Icons im Fließtext: `svg.lucide` ist in `input.css` auf `display: inline-block` gesetzt (Tailwinds Preflight macht
SVGs sonst zu Blockelementen und erzwingt einen Zeilenumbruch).

## Eigenes Icon hinzufügen

1. Im 24×24-Raster zeichnen, Strichstärke 2, runde Enden und Ecken, keine Füllung, nur `currentColor`
   (so wie alle Lucide-Icons).
2. In `custom-icons.json` im Lucide-Format eintragen, Name mit Präfix `custom-`:

   ```json
   {
     "custom-vinyl-roll": [
       ["circle", { "cx": "8", "cy": "9", "r": "5" }],
       ["circle", { "cx": "8", "cy": "9", "r": "1.5" }],
       ["path", { "d": "M8 14h13v6H11" }]
     ]
   }
   ```

3. Verwenden wie jedes andere Icon: `Icon::get("custom-vinyl-roll")` bzw. `icon(customIcon("custom-vinyl-roll"))`.

Nicht betroffen: Firmenlogo, Favicon und Abbildungen in PDFs.
