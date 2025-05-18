import { notification } from "../classes/notifications.js";
import { ajax } from "../classes/ajax.js";
import { initFileUploader } from "../classes/upload.js";
import { addBindings } from "../classes/bindings.js";
import { initSVG } from "./svgManager.js";
import { getStickerId } from "../sticker.js";
import { tableConfig } from "../classes/tableconfig.js";
import { fetchAndRenderTable } from "../classes/table.js";
import { createPopup, initImagePreviewListener } from "../global.js";

const fnNames = {};
const imageTables = {};

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

const createImageTable = async (imageType, anchorId, config) => {
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

    const table = await fetchAndRenderTable(anchorId, "module_sticker_image", options);
    table.addEventListener("deleteRow", e => {});
    imageTables.imageType = table;
}

fnNames.click_showImageOptions = e => {
    const target = e.target.dataset.target;
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

fnNames.write_updateImageDescription = (e) => {
    const imageId = e.currentTarget.dataset.fileId;
    const description = e.currentTarget.value;
    ajax.post({
        imageId: imageId,
        description: description,
        r: "updateImageDescription",
    }).then(r => {
        if (r.status == "success") {
            notification("", result.status);
        }
    });
}

/**
 * called from stickerImageView.php to update the overwrite images
 * 
 * @param {*} type Image type that should be updated
 */
fnNames.write_updateImageOverwrite = (e) => {
    const type = e.target.dataset.type;
    //window.mainVariables.overwriteImages[type] = !window.mainVariables.overwriteImages[type];
}
