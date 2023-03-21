/* drag and drop handler */
function itemDropHandler(e, imageCategory) {
	e.preventDefault();
    let uploadableFiles = [];

    /* https://developer.mozilla.org/en-US/docs/Web/API/HTML_Drag_and_Drop_API/File_drag_and_drop */
    if (e.dataTransfer.items && e.dataTransfer.getData("text/plain") !== "not_uploaded") {
        [...e.dataTransfer.items].forEach((item, i) => {
            const file = item.getAsFile();
            if (item.kind === 'file' && item.type.match('image.*')) {
                const fileReader = new FileReader();
                fileReader.readAsDataURL(file);
                fileReader.onloadend = function() {
                    let div = document.createElement("div");
                    let img = document.createElement("img");

                    div.classList.add("imageMovable");
                    div.draggable = true;
                    div.appendChild(img);

                    img.src = fileReader.result;
                    img.alt = "Uploaded File";
                    img.classList.add("imgPreview");
                    img.addEventListener("click", imagePreview, false);

                    e.target.appendChild(div);   
                }

                uploadableFiles.push(file);
            } else if (item.kinde === 'file') {
                /* no image files here, so .ltp or .cdr files get uploaded (also other files) */
                uploadableFiles.push(file);
            }
        });
    }

    uploadFileForSticker(uploadableFiles, imageCategory);
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

var currentMoveImage;
var moveImages;
function moveImagesInDiv(event) {
    if (event.target.classList.contains("imgPreview")) {
        event.preventDefault();

        if (moveImages.indexOf(event.target.parentNode.parentNode) > moveImages.indexOf(currentMoveImage))
            event.target.parentNode.after(currentMoveImage.parentNode);
        else
            event.target.parentNode.before(currentMoveImage.parentNode);
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
    formData.set("motivNumber", mainVariables.motivId.innerHTML);
    formData.set("imageCategory", imageCategory);

	return new Promise(function(resolve, reject) {
		var ajax = new XMLHttpRequest();

		/* resolves the promise and then function with the form reset is called */
		ajax.onreadystatechange = function() {
			if(this.readyState == 4 && this.status == 200) {
				// TODO: show success message
                resolve();
			}
		}

		ajax.onerror = function() {
			reject();
		}

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
}

// TODO: write file uploader
// TODO: prevent false copying of text
// TODO: alt files like svg must be uploadable as well
document.addEventListener("click", imageClickListener);