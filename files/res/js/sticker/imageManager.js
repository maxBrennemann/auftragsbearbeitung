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

/* drag and drop handler */
function itemDropHandler(e, imageCategory) {
	e.preventDefault();
    let uploadableFiles = [];

    /* https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API/File_drag_and_drop */
    if (e.dataTransfer.items && e.dataTransfer.getData("text/plain") !== "not_uploaded") {
        [...e.dataTransfer.items].forEach((item, i) => {
            const file = item.getAsFile();
            const div = generatePreviewContainer();

            /* generate image preview */
            if (item.kind === 'file' && item.type.match('image.*')) {
                const fileReader = new FileReader();
                fileReader.readAsDataURL(file);
                fileReader.onloadend = function() {
                    let img = document.createElement("img");

                    img.src = fileReader.result;
                    img.alt = "Uploaded File";
                    img.classList.add("imgPreview");
                    img.addEventListener("click", imagePreview, false);

                    div.appendChild(img); 
                }
            } else {
                const icon = getIcon();
                div.appendChild(icon);
            }

            e.target.appendChild(div);
            uploadableFiles.push(file);
        });
    }

    uploadFileForSticker(uploadableFiles, imageCategory);
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

function moveImagesInDiv(event) {
    if (event.target.classList.contains("imgPreview")) {
        event.preventDefault();

        if (moveImages.indexOf(event.target.parentNode.parentNode) > moveImages.indexOf(currentMoveImage)) {
            event.target.parentNode.after(currentMoveImage.parentNode);
        } else {
            event.target.parentNode.before(currentMoveImage.parentNode);
        }
        // TODO: reihenfolge ausgeben
    }
}

function moveImageStart(event) {
    currentMoveImage = event.target;
}

function moveImageEnd(event) {
    console.log("ended moving");
}

function moveInit() {
    moveImages = Array.from(document.getElementsByClassName("imageMovable"));

    moveImages.forEach(div => {
        div.addEventListener("dragstart", moveImageStart, false);
        div.addEventListener("dragover", moveImagesInDiv, false);
        div.addEventListener("dragend", moveImageEnd, false);
    })
}

function uploadFileForSticker(files, imageCategory) {
    if (files.length == 0) {
        return;
    }

    let formData = new FormData();
    files.forEach(file => {
        formData.append("files[]", file);
    });

    /* set upload variable to be recognized by the backend */
    formData.set("upload", "motiv");
    formData.set("motivname", "");
    formData.set("motivNumber", motivId);
    formData.set("imageCategory", imageCategory);

	const uploader = new Promise((resolve, reject) => {
		var ajax = new XMLHttpRequest();
        ajax.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
                resolve(this.responseText);
			}
		}

		ajax.onerror = reject;
		ajax.open('POST', '');
		ajax.upload.addEventListener("progress", function(e) {
			if (e.lengthComputable) {
				const bytesUploaded = e.loaded;
				const bytesTotal = e.total;
		
				const percentage = Math.round(bytesUploaded * 100 / bytesTotal);
                let uploadNode = document.getElementById("showUploadProgress");
				uploadNode.value = percentage;
			}
		}, false);
		ajax.send(formData);
	});

    uploader.then((response) => handleUploadedImages(response));
}

/**
 * TODO: es muss für mehrere Dateien gleichzeitig gehen, später eine Klasse aus dieser Datei machen
 * @param {*} imageData 
 */
function handleUploadedImages(imageData) {
    const data = JSON.parse(imageData);
    const image = data.imageData[0];
    var icon;

    if (image.type.toLowerCase() == "cdr") {
        icon = getIcon("icon-corel");
    } else if (image.type.toLowerCase() == "ltp") {
        icon = getIcon("icon-letterplot");
    }

    if (icon != null) {
        const container = document.querySelector(".imageMovableContainer");
        const el = container.getElementsByClassName("imageMovable");
        el[el.length - 1].innerHTML = "";
        el[el.length - 1].appendChild(icon);
    }
}

/**
 * deletes the currently selected image
 */
function deleteImage() {
    ajax.post({
        imageId: mainVariables.currentDelete,
        r: "deleteImage",
    }).then(r => {
        if (r.status == "success") {
            infoSaveSuccessfull("success");
            const image = document.querySelector(`[data-file-id="${mainVariables.currentDelete}"]`);
            image.parentNode.removeChild(image);
        }
    });
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

    const contextMenu = document.getElementById("delete-menu");
    const scope = document.querySelector("body");

    scope.addEventListener("contextmenu", (event) => {
        const isDeletable = event.target.dataset.deletable != null || event.target.parentNode.dataset.deletable != null;
        if (isDeletable) {
            mainVariables.currentDelete = event.target.dataset.fileId;
            event.preventDefault();

            const {
                clientX: mouseX, 
                clientY: mouseY 
            } = event;
    
            contextMenu.style.top = `${mouseY}px`;
            contextMenu.style.left = `${mouseX}px`;
    
            contextMenu.classList.add("visible");
        }
    });

    scope.addEventListener("click", (e) => {
        if (e.target.offsetParent != contextMenu) {
            contextMenu.classList.remove("visible");
        }
    });

    const imgMovableContainers = document.querySelectorAll(".imageMovableContainer");
    Array.from(imgMovableContainers).forEach(container => {
        const dropType = container.dataset.dropType;
        container.addEventListener("drop", e => itemDropHandler(e, dropType), false);
        container.addEventListener("dragover", itemDragOverHandler, false);
    });

    const imgPrev = document.querySelectorAll(".imgPreview");
    Array.from(imgPrev).forEach(img => {
        img.addEventListener("click", imagePreview, false);
        img.addEventListener("dragstart", preventCopy, false);
    });

    const delItems = document.querySelectorAll("#delete-menu.item");
    Array.from(delItems).forEach(item => {
        item.addEventListener("click", deleteImage, false);
    });

    const svgContainer = document.getElementById("svgContainer");
    svgContainer.addEventListener("dragover", itemDragOverHandler, false);
    svgContainer.addEventListener("drop", e => itemDropHandler(e, "textilsvg"), false);
}

const motivId = document.getElementById("motivId").innerHTML;
var currentMoveImage;
var moveImages;
var svg_elem;

/* init */
if (document.readyState !== 'loading' ) {
    initImageManager();
    initSVG();
    moveInit()
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initImageManager();
        initSVG();
        moveInit();
    });
}
