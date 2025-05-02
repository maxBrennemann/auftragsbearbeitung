import { notification } from "../classes/notifications.js";
import { ajax } from "../classes/ajax.js";
import { createPopup } from "../global.js";
import { initFileUploader } from "../classes/upload.js";
import { addBindings } from "../classes/bindings.js";

const svgContainer = document.getElementById("svgContainer");
const motivId = document.getElementById("motivId")?.innerHTML;
var svg_elem;

const fnNames = {};

/**
 * toggles the is colorable flag of the svg,
 * if the svg is colorable, the svg is displayed in the colorable svg container
 * and all colors will be removed
 * @file imageManager.js
 */
fnNames.click_makeColorable = () => {
    ajax.post({
        id: motivId,
        r: "makeSVGColorable"
    }).then(r => {
        svgContainer.data = r.url;
    });
}

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

function initSVG() {
    if (svgContainer == null || svgContainer == undefined) {
        return;
    }

    svgContainer.addEventListener("load", loadSVGEvent, false);

    if (svgContainer.contentDocument == null) {
        return;
    }

    svg_elem = svgContainer.contentDocument.querySelector("svg");
    adjustSVG();
}

/* sets the svg_elem element when the content is loaded */
function loadSVGEvent() {
    svg_elem = svgContainer.contentDocument.querySelector("svg");
    adjustSVG();
}

/**
 * adjust the svg into the svg container, so that the element is not too small
 */
function adjustSVG() {
    if (svg_elem == null) {
        return;
    }

    let children = svg_elem.children;
    let positions = {
        furthestX: 0,
        nearestX: 0,
        furthestY: 0,
        nearestY: 0,

        edited: false,
    }

    for (let i = 0; i < children.length; i++) {
        let child = children[i];

        if (child.getBBox() && child.nodeName != "defs") {
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

    svg_elem.setAttribute("viewBox", `${positions.nearestX} ${positions.nearestY} ${width} ${height}`);
}

export const initImageManager = () => {
    document.addEventListener("click", imageClickListener);

    addBindings(fnNames);
    initSVG();
    initFileUploader({
        "aufkleber": {
            "location": `/api/v1/sticker/${motivId}/aufkleber/add-files`,
        },
        "wandtattoo": {
            "location": `/api/v1/sticker/${motivId}/wandtattoo/add-files`,
        },
        "textil": {
            "location": `/api/v1/sticker/${motivId}/textil/add-files`,
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
    window.mainVariables.overwriteImages[type] = !window.mainVariables.overwriteImages[type];
}
