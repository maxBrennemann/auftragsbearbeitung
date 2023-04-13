/* drag and drop handler */
export function itemDropHandler(e, imageCategory) {
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

export function getIcon(type = "icon-file") {
    const template = document.getElementById(type);
    return template.content.cloneNode(true);
}

export function generatePreviewContainer() {
    const div = document.createElement("div");
    div.classList.add("imageMovable");
    div.draggable = true;
    return div;
}

/* https://stackoverflow.com/questions/71822008/how-to-tell-if-an-image-is-drag-drop-from-the-same-page-or-drag-drop-uploaded */
export function preventCopy(e) {
    e.dataTransfer.setData("text/plain", "not_uploaded");
}

export function imageClickListener(event) {
    if (!event.target.matches(".imgAbsoluteCenter,.imgPreview")) {
        let elements = document.getElementsByClassName("imgAbsoluteCenter");
        Array.prototype.forEach.call(elements, function(element) {
            element.remove();
        });
    }
}

export function imagePreview(e) {
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

var currentMoveImage;
var moveImages;
export function moveImagesInDiv(event) {
    if (event.target.classList.contains("imgPreview")) {
        event.preventDefault();

        if (moveImages.indexOf(event.target.parentNode.parentNode) > moveImages.indexOf(currentMoveImage))
            event.target.parentNode.after(currentMoveImage.parentNode);
        else
            event.target.parentNode.before(currentMoveImage.parentNode);
    }
}

export function moveImageStart(event) {
    currentMoveImage = event.target;
}

export function moveImageEnd(event) {
    console.log("ended moving");
}

export function moveInit() {
    moveImages = Array.from(document.getElementsByClassName("imageMovable"));

    moveImages.forEach(div => {
        div.addEventListener("dragstart", moveImageStart, false);
        div.addEventListener("dragover", moveImagesInDiv, false);
        div.addEventListener("dragend", moveImageEnd, false);
    })
}

export function uploadFileForSticker(files, imageCategory) {
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
    formData.set("motivNumber", mainVariables.motivId.innerHTML);
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
				bytesUploaded = e.loaded;
				bytesTotal = e.total;
		
				percentage = Math.round(bytesUploaded * 100 / bytesTotal);
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
export function handleUploadedImages(imageData) {
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

// TODO: write file uploader
// TODO: prevent false copying of text
document.addEventListener("click", imageClickListener);

export function checkSVGCount() {

}

var svg_elem;
export function initSVG() {
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
export function loadSVGEvent() {
    var a = document.getElementById("svgContainer");
    var svgDoc = a.contentDocument;
    svg_elem = svgDoc.getElementById("svg_elem");
    adjustSVG();
}

/**
 * adjust the svg into the svg container, so that the element is not too small
 */
export function adjustSVG() {
    if (svg_elem != null) {
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
}

export async function click_makeColorable() {
    ajax.post({
        id: mainVariables.motivId.innerHTML,
        r: "makeSVGColorable"
    }).then(r => {
        const svgContainer = document.getElementById("svgContainer");
        svgContainer.data = r.url;
    });
}

if (document.readyState !== 'loading' ) {
    initImageManager();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initImageManager();
    });
}

export function initImageManager() {
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
}

/**
 * deletes the currently selected image
 */
export function deleteImage(imageId) {
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

export function insertNewlyUploadedImages(json) {
    let imageContainer = document.getElementsByClassName("imageContainer")[0];

    for (let key in json.imageData) {
        let image = json.imageData[key];
        console.log(image.id + " " + image.url);

        let imageEl = document.createElement("img");
        imageEl.setAttribute("src", image.url);
        imageEl.title = image.original;
        imageEl.classList.add("imagePrev");
        imageEl.dataset.imageId = image.id;
        imageEl.dataset.isAufkleber = 0;
        imageEl.dataset.insWandtattoo = 0;
        imageEl.dataset.isTextil = 0;
        imageEl.setAttribute("onclick", "change(event)");

        imageContainer.appendChild(imageEl);

        console.log("test");
    }
}
