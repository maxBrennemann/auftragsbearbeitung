import { notification } from "../classes/notifications.js";
import { ajax } from "../classes/ajax.js";
import { createPopup } from "../global.js";
import { initFileUploader } from "../classes/upload.js";
import { addBindings } from "../classes/bindings.js";
import { initSVG } from "./svgManager.js";
import { getStickerId } from "../sticker.js";

const fnNames = {};

const imageClickListener = (e) => {
    if (e.target.matches(".imgAbsoluteCenter,.imgPreview")) {
        return;
    }

    const elements = document.querySelectorAll(".imgAbsoluteCenter");
    elements.forEach(element => {
        element.remove();
    })
}

/* displays a div with the image preview */
function imagePreview(e) {
    let target = e.target;
    let copy = target.cloneNode();
    let imageSize = 500;

    if (target.naturalWidth < 500) {
        imageSize = target.naturalWidth;
    }

    copy.style.width = imageSize + "px";

    copy.classList.add("imgAbsoluteCenter");
    createPopup(copy);
}

export const initImageManager = () => {
    document.addEventListener("click", imageClickListener);

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
}

fnNames.click_deleteImage = (e) => {
    const imageId = e.currentTarget.dataset.fileId;
    ajax.post({
        imageId: imageId,
        r: "deleteImage",
    }).then(r => {
        if (r.status == "success") {
            notification("", result.status);
            const image = document.querySelector(`[data-file-id="${imageId}"]`);
            const imageRow = image.parentNode.parentNode;
            imageRow.parentNode.removeChild(imageRow);
        }
    });
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
