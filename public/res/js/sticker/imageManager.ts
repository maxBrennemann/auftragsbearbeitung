import { ajax } from "js-classes/ajax.js";
import { addBindings } from "js-classes/bindings.js";
import { notification } from "js-classes/notifications.js";

import { fetchAndRenderTable } from "../classes/table.js";
import { tableConfig } from "../classes/tableconfig.js";
import { initFileUploader } from "../classes/upload.js";
import { createPopup, initImagePreviewListener } from "../global.js";
import { getStickerId } from "../pages/sticker.js";

import { initSVG } from "./svgManager";

const fnNames: { [key: string]: (...args: any[]) => void } = {};
const imageTables: {
    imageType?: HTMLTableElement,
} = {};

export const initImageManager = () => {
    addBindings(fnNames);
    initSVG();
    initFileUploader({
        "aufkleber": {
            "location": `/api/v1/sticker/${getStickerId()}/aufkleber/add-files`,
        },
        "wandtattoo": {
            "location": `/api/v1/sticker/${getStickerId()}/wandtattoo/add-files`,
        },
        "textil": {
            "location": `/api/v1/sticker/${getStickerId()}/textil/add-files`,
        },
    });
    initImageTables();
    document.body.addEventListener("fileUploaded", e => console.log(e));
}

const initImageTables = async () => {
    const config = tableConfig["module_sticker_image"];
    config.columns.push({
        "key": "image",
        "label": "Bild",
    });

    await createImageTable("aufkleber", "aufkleberTable", config);
    await createImageTable("wandtattoo", "wandtattooTable", config);
    await createImageTable("textil", "textilTable", config);

    initImagePreviewListener();
}

const createImageTable = async (imageType: string, anchorId: string, config: { primaryKey: string, }) => {
    const options = {
        "hideOptions": ["check", "addRow", "add"],
        "primaryKey": config.primaryKey,
        "hide": ["id_datei", "id_motiv", "image_sort", "id_product", "id_image_shop"],
        "autoSort": true,
        "styles": {
            "table": {
                "className": ["w-full"],
            },
        },
        "conditions": {
            "image_sort": imageType,
            "id_motiv": getStickerId(),
        },
        "joins": {
            "files": 0,
        }
    };

    const table = await fetchAndRenderTable(anchorId, "module_sticker_image", options) as HTMLTableElement;
    table.addEventListener("deleteRow", e => {});
    imageTables.imageType = table;
}

fnNames.click_showImageOptions = (e: Event) => {
    //const target = e.target.dataset.target;
    const div = document.createElement("div");
    div.innerHTML = `
        <p class="font-semibold">Vorsicht: Diese Option überschreibt die aktuellen Bilder des Artikels!</p>
        <p class="text-sm italic">Die Einstellung bleibt nur für diese Sitzung erhalten.</p>
        <label class="mt-2">
            <input type="checkbox" data-binding="true" data-fun="updateImageOverwrite">
            <span>Bilder erneut hochladen</span>
        </label>`;
    createPopup(div);
}

fnNames.write_updateImageDescription = (e: Event) => {
    const target = e.currentTarget as HTMLInputElement;
    const imageId = target.dataset.fileId;
    const description = target.value;

    ajax.put(`/api/v1/sticker/image/${imageId}`, {
        "description": description,
    }).then((r: any) => {
        if (r.data.status == "success") {
            notification("", r.data.status);
        }
    });
}

/**
 * called from stickerImageView.php to update the overwrite images
 * 
 * @param {*} type Image type that should be updated
 */
fnNames.write_updateImageOverwrite = (e: Event) => {
    const target = e.target as HTMLElement;
    const type = target.dataset.type;
    //window.mainVariables.overwriteImages[type] = !window.mainVariables.overwriteImages[type];
}
