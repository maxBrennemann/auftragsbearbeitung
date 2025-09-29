// @ts-ignore
import { ajax } from "js-classes/ajax.js";
// @ts-ignore
import { addBindings } from "js-classes/bindings.js";

import { getStickerId } from "../sticker.js";

const fnNames: { [key: string]: (...args: any[]) => void } = {};
const config: {
    svgContainer?: HTMLObjectElement,
    svgElement?: SVGSVGElement,
} = {};

fnNames.click_makeColorable = () => {
    ajax.post(`/api/v1/sticker/${getStickerId()}/svg-colorizable`)
    .then((r: any) => {
        if (config.svgContainer) {
            config.svgContainer.data = r.url;
        }
    });
}

const loadSVGEvent = () => {
    if (config.svgContainer instanceof HTMLObjectElement) {
        const svg = config.svgContainer.contentDocument?.querySelector("svg");
        if (svg instanceof SVGSVGElement) {
            config.svgElement = svg;
        }
    }
    adjustSVG();
}

const adjustSVG = () => {
    if (config.svgElement == null) {
        return;
    }

    let children = config.svgElement.children;
    let positions = {
        furthestX: 0,
        nearestX: 0,
        furthestY: 0,
        nearestY: 0,

        edited: false,
    }

    for (let i = 0; i < children.length; i++) {
        let child = children[i];

        if (child instanceof SVGGraphicsElement && child.nodeName != "defs") {
            var coords = child.getBBox();
            if (positions.edited == false) {
                positions.furthestX = coords.x + coords.width;
                positions.furthestY = coords.y + coords.height;
                positions.nearestX = coords.x;
                positions.nearestY = coords.y;

                positions.edited = true;
            } else {
                if (coords.x < positions.nearestX) {
                    positions.nearestX = coords.x;
                }
                if (coords.y < positions.nearestY) {
                    positions.nearestY = coords.y;
                }
                if (coords.x + coords.width > positions.furthestX) {
                    positions.furthestX = coords.x + coords.width;
                }
                if (coords.y + coords.height > positions.furthestY) {
                    positions.furthestY = coords.y + coords.height;
                }
            }
        }
    }

    let width = positions.furthestX - positions.nearestX;
    let height = positions.furthestY - positions.nearestY;

    config.svgElement.setAttribute("viewBox", `${positions.nearestX} ${positions.nearestY} ${width} ${height}`);
}

export const initSVG = () => {
    addBindings(fnNames);

    config.svgContainer = document.getElementById("svgContainer") as HTMLObjectElement;
    if (config.svgContainer == null || config.svgContainer == undefined) {
        return;
    }

    config.svgContainer.addEventListener("load", loadSVGEvent, false);
    if (config.svgContainer.contentDocument == null) {
        return;
    }

    config.svgElement = config.svgContainer.contentDocument.querySelector("svg") as SVGSVGElement;

    adjustSVG();
}
