/**
 * toggles the is colorable flag of the svg,
 * if the svg is colorable, the svg is displayed in the colorable svg container
 * and all colors will be removed
 * @file imageManager.js
 */
export function click_makeColorable() {
    ajax.post({
        id: motivId,
        r: "makeSVGColorable"
    }).then(r => {
        const svgContainer = document.getElementById("svgContainer");
        svgContainer.data = r.url;
    });
}

function dropMiscHandler(e, imageCategory) {
    e.preventDefault();
    let uploadableFiles = [];

    /* https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API/File_drag_and_drop */
    if (!e.dataTransfer.items || e.dataTransfer.getData("text/plain") == "not_uploaded") {
        return;
    }

    const div = generatePreviewContainer();
    [...e.dataTransfer.items].forEach((item, i) => {
        const file = item.getAsFile();

        if (item.kind !== 'file' || !item.type.match('image.*')) {
            uploadableFiles.push(file);
            const icon = getIcon();
            div.appendChild(icon);
        }
    });
    
    e.target.appendChild(div);
    uploadFileForSticker(uploadableFiles, imageCategory, handleUploadedImages);
}

/* drag and drop handler */
function itemDropHandler(e, imageCategory) {
	e.preventDefault();
    let uploadableFiles = [];

    /* https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API/File_drag_and_drop */
    if (!e.dataTransfer.items || e.dataTransfer.getData("text/plain") == "not_uploaded") {
        return;
    }

    [...e.dataTransfer.items].forEach((item, i) => {
        const file = item.getAsFile();

        if (item.kind === 'file' && item.type.match('image.*')) {
            uploadableFiles.push(file);
        }
    });
    
    uploadFileForSticker(uploadableFiles, imageCategory, handleImagesPreview);
}

function handleImagesPreview(filesInfo, imageCategory) {
    filesInfo = filesInfo.imageData;

    filesInfo.forEach(f => {
        const src = f.url;
        const imageFileId = f.id;
        addTableRow(src, imageCategory, imageFileId);
    });
}

function addTableRow(imageSrc, imageCategory, imageFileId) {
    const parent = document.querySelector(`[data-image-type="${imageCategory}"]`);
    const template = document.getElementById("templateImageRow");
	parent.appendChild(template.content.cloneNode(true));

    const image = document.querySelector("img");
    image.src = imageSrc;
    image.alt = "New uploaded image";
    image.classList.add("imgPreview");
    image.addEventListener("click", imagePreview, false);

    const fileIds = document.querySelectorAll(`[data-file-id]`);
    Array.from(fileIds).forEach(f => {
        f.dataset.fileId = imageFileId;
    });

    addBindings(fileIds);
}

function getIcon(type = "icon-file") {
    const template = document.getElementById(type);
    return template.content.cloneNode(true);
}

/* generates the div container for the image in the grey boxes */
function generatePreviewContainer() {
    const div = document.createElement("div");
    div.classList.add("imageMovable");
    div.draggable = true;
    return div;
}

/* https://stackoverflow.com/questions/71822008/how-to-tell-if-an-image-is-drag-drop-from-the-same-page-or-drag-drop-uploaded */
function preventCopy(e) {
    e.dataTransfer.setData("text/plain", "not_uploaded");
}

function imageClickListener(event) {
    if (!event.target.matches(".imgAbsoluteCenter,.imgPreview")) {
        let elements = document.getElementsByClassName("imgAbsoluteCenter");
        Array.prototype.forEach.call(elements, function(element) {
            element.remove();
        });
    }
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
    
    document.body.appendChild(copy);
    copy.classList.add("imgAbsoluteCenter");
    centerAbsoluteElement(copy);
}

function itemDragOverHandler(e) {
	e.preventDefault();
}

async function uploadFileForSticker(files, imageCategory, callback = null) {
    if (files.length == 0) {
        return;
    }

    const data = {
        motivname: "",
        motivNumber: motivId,
        imageCategory: imageCategory
    };

    const response = await ajax.uploadFiles(files, "motiv", data);

    if (callback != null) {
        callback(response, imageCategory);
    }
}

/**
 * TODO: es muss für mehrere Dateien gleichzeitig gehen, später eine Klasse aus dieser Datei machen
 * @param {*} imageData 
 */
function handleUploadedImages(imageData, imageCategory) {
    const data = JSON.parse(imageData);
    const image = data.imageData[0];
    var icon;

    if (image.type.toLowerCase() == "cdr") {
        icon = getIcon("icon-corel");
    } else if (image.type.toLowerCase() == "ltp") {
        icon = getIcon("icon-letterplot");
    }

    if (icon != null) {
        const container = document.querySelector(".imageUpload");
        const el = container.getElementsByClassName("imageMovable");
        el[el.length - 1].innerHTML = "";
        el[el.length - 1].appendChild(icon);
    }
}

function initSVG() {
    var a = document.getElementById("svgContainer");
    if (a != null || a != undefined) {
        a.addEventListener("load", loadSVGEvent, false);

        if (a.contentDocument != null) {
            var svgDoc = a.contentDocument;
            svg_elem = svgDoc.getElementById("svg_elem");
            adjustSVG();
        }
    
        if (a.attributes.data.value == "") {
            a.style.height = "100px";
            a.style.backgroundColor = "#b1b1b1";
        }
    }
}

/* sets the svg_elem element when the content is loaded */
function loadSVGEvent() {
    var a = document.getElementById("svgContainer");
    var svgDoc = a.contentDocument;
    svg_elem = svgDoc.getElementById("svg_elem");
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

    for (let i = 0; i< children.length; i++) {
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

function initImageManager() {
    document.addEventListener("click", imageClickListener);

    const imgMovableContainers = document.querySelectorAll(".imageUpload");
    Array.from(imgMovableContainers).forEach(container => {
        const dropType = container.dataset.dropType;
        container.addEventListener("drop", e => itemDropHandler(e, dropType), false);
        container.addEventListener("dragover", itemDragOverHandler, false);
        container.addEventListener("click", openFileDialog);
    });

    const imgMovable = document.querySelector(".imageMovableContainer");
    const dropType = imgMovable.dataset.dropType;
    imgMovable.addEventListener("drop", e => dropMiscHandler(e, dropType), false);
    imgMovable.addEventListener("dragover", itemDragOverHandler, false);

    const imgPrev = document.querySelectorAll(".imgPreview");
    Array.from(imgPrev).forEach(img => {
        img.addEventListener("click", imagePreview, false);
        img.addEventListener("dragstart", preventCopy, false);
    });

    const svgContainer = document.getElementById("svgContainer");
    svgContainer.addEventListener("dragover", itemDragOverHandler, false);
    svgContainer.addEventListener("drop", e => itemDropHandler(e, "textilsvg"), false);
}

const motivId = document.getElementById("motivId").innerHTML;
var svg_elem;

if (document.readyState !== 'loading' ) {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}

function init() {
    initImageManager();
    initSVG();
}

export function deleteImage(e) {
    const imageId = e.currentTarget.dataset.fileId;
    ajax.post({
        imageId: imageId,
        r: "deleteImage",
    }).then(r => {
        if (r.status == "success") {
            infoSaveSuccessfull("success");
            const image = document.querySelector(`[data-file-id="${imageId}"]`);
            const imageRow = image.parentNode.parentNode;
            imageRow.parentNode.removeChild(imageRow);
        }
    });
}

export function updateImageDescription(e) {
    const imageId = e.currentTarget.dataset.fileId;
    const description = e.currentTarget.value;
    ajax.post({
        imageId: imageId,
        description: description,
        r: "updateImageDescription",
    }).then(r => {
        if (r.status == "success") {
            infoSaveSuccessfull("success");
        }
    });
}

/**
 * chatGPT generated
 * @param {*} event 
 */
function openFileDialog(event) {
    // Create a hidden file input element
    var fileInput = document.createElement("input");
    fileInput.type = "file";
    fileInput.accept = "image/*";
  
    // Trigger click event on the file input
    fileInput.click();
  
    // Handle selected files
    fileInput.addEventListener("change", function(event) {
      var files = event.target.files;
      uploadFileForSticker(files, e.target.dataset.dropType, handleImagesPreview);
    });
}
