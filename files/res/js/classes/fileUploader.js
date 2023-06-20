/* adds file upload class to every form with that class */
var fileUploaders;
export function initializeFileUpload() {
	let forms = document.querySelectorAll("form.fileUploader");
	fileUploaders = [];
	for (let i = 0, f; f = forms[i]; i++) {
		let u = new FileUploader(f);
		fileUploaders.push(u);
	}
}

/* https://stackoverflow.com/questions/30008114/how-do-i-promisify-native-xhr */
export var FileUploader = function(target) {
	if (target.nodeName == "FORM") {
		this.target = target;
		this.files = document.querySelector(`input[type="file"][form="${this.target.id}"]`);

		if (this.files != null) {
			this.files.addEventListener("change", this.preview.bind(this), false);
			this.fileArrayDragDrop = [];
	
			this.initHTML();
			this.initializeDragAndDrop();
		}
	} else {
		return null;
	}
}

FileUploader.prototype.resetUploader = function() {
	this.target.reset();
	var filesList = document.querySelector(".filesList");
	filesList.innerHTML = "";
	filesList.style.height = "100px";
	this.uploadNode.value = 0;
}

FileUploader.prototype.initHTML = function() {
	let uploadNode = document.createElement("input");
	uploadNode.type = "range";
	uploadNode.min = 0;
	uploadNode.max = 100;
	uploadNode.value = 0;
	uploadNode.disabled = true;

	let uploadButton = document.createElement("input");
	uploadButton.type = "button";
	uploadButton.value = "Hochladen";
	uploadButton.classList.add("custom-file-upload");
	uploadButton.addEventListener("click", function() {
		const upload = this.upload();
		upload.then(result => {
				this.resetUploader();
				infoSaveSuccessfull("success");
			}).catch((e) => {
				console.log(e);
			});
	}.bind(this), false);

	this.target.appendChild(uploadButton);
	this.target.appendChild(uploadNode);

	this.uploadNode = uploadNode;

	/* adds form element to set a post parameter to determine on the server that it is an file upload */
	let hidden = document.createElement("input");
	hidden.name = "upload";
	hidden.hidden = true;
	hidden.type = "text";
	hidden.value = this.target.dataset.target;
	this.target.appendChild(hidden);
}

FileUploader.prototype.upload = function() {
	let target = document.forms.namedItem(this.target.name);
	let uploadNode = this.uploadNode;

	let files = document.querySelector(`input[type="file"][form="${target.id}"]`);

	if (this.fileArrayDragDrop.length == 0 && files.files.length == 0) {
		return Promise.reject("no files selected");
	}

	return new Promise(function(resolve, reject) {
		var formData = new FormData(target);
		var ajax = new XMLHttpRequest();

		/* resolves the promise and then function with the form reset is called */
		ajax.onreadystatechange = function() {
			if(this.readyState == 4 && this.status == 200) {
				try {
					//let uploadJson = JSON.parse(this.responseText);
					//insertNewlyUploadedImages(uploadJson); /* TODO: callback function als Parameter übergeben, akutell hardcoded, ruft function in sticker.js auf */
				} catch(e) {
					document.getElementById("showFilePrev").innerHTML = this.responseText;
					console.log(e);
					console.log("no json found, continue with regular procedure...");
				}

				/* remove upload preview images */
				document.querySelector(".filesList").parentNode.removeChild(document.querySelector(".filesList").previousSibling);
				resolve();
			}
		}

		for (let i = 0; i < this.fileArrayDragDrop.length; i++) {
			formData.append("files[]", this.fileArrayDragDrop[i]);
		}
		for (let i = 0; i < files.files.length; i++) {
			formData.append("files[]", files.files[i]);
		}

		console.log(formData.getAll("files[]"));

		ajax.onerror = function() {
			reject();
		}

		ajax.open('POST', '');
		ajax.upload.addEventListener("progress", function(e) {
			if (e.lengthComputable) {
				bytesUploaded = e.loaded;
				bytesTotal = e.total;
		
				percentage = Math.round(bytesUploaded * 100 / bytesTotal);
				uploadNode.value = percentage;
			}
		}, false);
		ajax.send(formData);
	}.bind(this)); 
}

/* https://www.mediaevent.de/javascript/ajax-2-xmlhttprequest.html */
FileUploader.prototype.preview = function() {
	let files = this.files.files;
	for (let i = 0, f; f = files[i]; i++) {
		if (!f.type.match('image.*'))
			continue;
		let reader = new FileReader();
		reader.onload = (function(_file) {
			return function(e) {
				let span = document.createElement("span");
				span.innerHTML = ['<img class="upload_prev" src="', e.target.result, '" title="', escape(_file.name), '"/>'].join('');
				document.querySelector(".filesList").insertBefore(span, null);
			}
		})(f);
		reader.readAsDataURL(f);
		document.querySelector(".filesList").style.height = "auto";
	}
}

FileUploader.prototype.initializeDragAndDrop = function() {
	document.getElementsByClassName("filesList")[0].addEventListener("drop", function(e) {
		this.ondropHandler(e);
	}.bind(this), false);
	document.getElementsByClassName("filesList")[0].addEventListener("dragover", function(e) {
		this.ondragHandler(e);
	}.bind(this), false);
}

FileUploader.prototype.ondropHandler = function(e) {
	e.preventDefault();

	if (e.dataTransfer.files) {
		[...e.dataTransfer.files].forEach((item, i) => {
			if (item.type.match('image.*')) {
				let reader = new FileReader();
				reader.onload = (function(_file) {
					return function(e) {
						let span = document.createElement("span");
						span.innerHTML = ['<img class="upload_prev" src="', e.target.result, '" title="', escape(_file.name), '"/>'].join('');
						document.querySelector(".filesList").insertBefore(span, null);
					}
				})(item);
				reader.readAsDataURL(item);
			} else {
				let span = document.createElement("span");
				span.innerHTML = `<i>${item.name}</i>`;
				document.querySelector(".filesList").insertBefore(span, null);
			}

			this.fileArrayDragDrop.push(item);
		})
	}

	e.target.style.height = "auto";
}

FileUploader.prototype.ondragHandler = function(e) {
	e.preventDefault();
}

if (document.readyState !== 'loading' ) {
    initializeFileUpload();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initializeFileUpload();
    });
}