import { createElement, type IconNode } from "lucide";

import customIconData from "../icons/custom-icons.json";

/**
 * Icons im Frontend. Standard ist Lucide, eigene Icons nur, wenn Lucide nichts Passendes hat
 * (siehe public/res/js/icons/README.md). In PHP-Templates stattdessen Icon::get("name") verwenden.
 *
 *   import { Pencil } from "lucide";
 *   button.append(icon(Pencil));
 *   button.innerHTML = iconHtml(Pencil, { title: "Bearbeiten" });
 *   icon(customIcon("custom-vinyl-roll"));
 */

export interface IconOptions {
    /** Kantenlänge in px. 16 neben Text (Standard), 20 als alleinstehendes Icon. */
    size?: number;
    className?: string;
    /** Für reine Icon-Buttons: wird als <title> und aria-label gesetzt. */
    title?: string;
}

const customIcons = customIconData as Record<string, IconNode>;

export const customIcon = (name: `custom-${string}`): IconNode => {
    const node = customIcons[name];
    if (node === undefined) {
        throw new Error(`Unbekanntes Icon: ${name} (fehlt in icons/custom-icons.json)`);
    }
    return node;
};

export const icon = (node: IconNode, { size = 16, className = "", title = "" }: IconOptions = {}): SVGElement => {
    const svg = createElement(node, {
        width: size,
        height: size,
        class: `lucide ${className}`.trim(),
    });

    if (title !== "") {
        const titleEl = document.createElementNS("http://www.w3.org/2000/svg", "title");
        titleEl.textContent = title;
        svg.prepend(titleEl);
        svg.setAttribute("role", "img");
        svg.setAttribute("aria-label", title);
    } else {
        svg.setAttribute("aria-hidden", "true");
    }

    return svg;
};

export const iconHtml = (node: IconNode, options: IconOptions = {}): string => icon(node, options).outerHTML;
