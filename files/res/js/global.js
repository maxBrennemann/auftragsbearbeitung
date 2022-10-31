document.addEventListener("click", function(event) {
	if (!event.target.matches('.showLog,.showLog *')) {
		if(document.getElementById("login")) {
			document.getElementById("login").style.display = "none";
		}
		if(document.getElementById("register")) {
			document.getElementById("register").style.display = "none";
		}
	}
})

var lastActivity = null;
document.addEventListener("click", registerLastActivity, false);

function registerLastActivity() {
	if (lastActivity == null) {
		lastActivity = new Date();
	} else {
		var diff = ((new Date()).getTime() - lastActivity.getTime()) / 1000;
		if (diff > 1440) {
			location.reload();
		}
	}
}

function toggleHamList() {
	var hamlist = document.getElementById("hamlist"),
		hammen = document.getElementById("hammen");
	if(hammen.className.includes("hideHamlist")) {
		hammen.className = "showHamlist";
		hamlist.style.display = "inline";
	} else {
		hammen.className = "hideHamlist";
		hamlist.style.display = "none";
	}
}

function toggleVisibility(id) {
	if (document.getElementById(id).style.display == "none") {
		document.getElementById(id).style.display = "inline";
	} else {
		document.getElementById(id).style.display = "none";
	}
}

function logout() {
	ac = new AjaxCall("info=logout");
	ac.setUrl(window.location);
	ac.makeAjaxCall(function() {
		location.reload();
	});
}

function goToProfile() {
	document.getElementById("goToProfile").click();
}

if (document.readyState !== 'loading' ) {
    startFunc();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        startFunc();
    });
}

function startFunc() {
	var el = document.querySelector("input[type=email");
	if (el != null) {
		el.addEventListener("input", function() {
			if (validateEmail(el.value)) {
				el.style.color = "green";
			} else {
				el.style.color = "red";
			}
		}, false);
	}

	autosubmit();

	/* auto sizes textareas on page load */
	var textareas = document.querySelectorAll("textarea");
	for (t of textareas) {
		if (t.scrollHeight != 0) {
			t.style.height = '';
			t.style.height = t.scrollHeight + 'px';
		}
	}

	listener_logout();
	listener_bellAndSearch();
	initializeFileUpload();
	initializeInfoBtn();
}

function listener_logout() {
	var logout = document.getElementById("logoutBtn");
	logout.addEventListener("click", function() {
		var logout = new AjaxCall(`logout_session=logout`, "POST", window.location.href);
		logout.makeAjaxCall(function (response) {
			location.reload();
		});
	}, false);
}

function listener_bellAndSearch() {
	var bellAndSearch = document.getElementsByClassName("settingsContainer")[0];
	bellAndSearch.addEventListener("click", function(event) {
		if (event.target.id == "settings") {
			/* link is hardcoded, maybe change later */
			window.location.href = (document.getElementById("home_link").href) + "einstellungen";
		} else if (document.getElementById("showNotifications") == null) {
			let div = document.createElement("div");
			div.id = "showNotifications";
			document.body.appendChild(div);

			var getHTMLContent = new AjaxCall(`getReason=notification`, "POST", window.location.href);
			getHTMLContent.makeAjaxCall(function (response, args) {
				var responseDiv = document.createElement("div");
				responseDiv.innerHTML = response;
				args[0].appendChild(responseDiv);
				responseDiv.classList.add("notificationWrapper");
				addActionButtonForDiv(args[0], "hide");
				centerAbsoluteElement(args[0]);
			}, div);
		} else {
			document.getElementById("showNotifications").style.display = "inline";
		}
	}, false);
}

function validateEmail(email) {
	var re = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
	return re.test(String(email).toLowerCase());
}

function getElement(id) {
	return document.getElementById(id);
}

/* 
* can be used to center an absolute or fixed div
*/
function centerAbsoluteElement(div) {
	var divWidth = div.offsetWidth;
	var divHeight = div.offsetHeight;

	var pageWidth = window.innerWidth;
	var pageHeight = window.innerHeight;

	div.style.left = ((pageWidth - divWidth) / 2) + "px";
	div.style.top = ((pageHeight - divHeight) / 2) + "px";
}

function addActionButtonForDiv(div, action) {
	div.classList.add("centeredContainer");

	var firstNode = div.firstChild;
	var btn = document.createElement("button");
	btn.innerHTML = "×";
	btn.classList.add("closeButton");
	btn.dataset.close = "1";
	btn.addEventListener("click", function(event) {
		var child = event.target.parentNode;
		var parent = event.target.parentNode.parentNode;
		switch (action) {
			case 'hide':
				child.style.display = "none";
				break;
			case 'remove':
				parent.removeChild(child);
				break;
			default:
				break;
		}
	}.bind(action), false);

	if (firstNode.dataset == null || firstNode.dataset.close == null) {
		div.insertBefore(btn, firstNode);
	}
}

function removeElement(element) {
	if (typeof element === 'string' || element instanceof String) {
		var child = document.getElementById(element);
		var parent = child.parentNode;
		parent.removeChild(child);
	} else if (element instanceof HTMLElement) {
		var parent = element.parentNode;
		parent.removeChild(element);
	}
}

function getDate(offset = 0) {
	var today = new Date(Date.now() + offset);
	var dd = String(today.getDate()).padStart(2, '0');
	var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
	var yyyy = today.getFullYear();

	today = yyyy + "-" + mm + "-" + dd;
	return today;
}

/* https://stackoverflow.com/questions/34910042/get-index-of-class/34910134 */
function indexInClass(node) {
	var collection = document.getElementsByClassName(node.className);
	for (var i = 0; i < collection.length; i++) {
		if (collection[i] === node)
			return i;
	}
	return -1;
}

/*
* data should be an array containing arrays the size of a row, the data[0] array should contain the heading
* of the table, so the size of data is rows + 1;
*/
function createTable(rows, columns, data, emptyFields) {
	var table = document.createElement("table");
	var tbody = document.createElement("tobdy");

	if (emptyFields == null || emptyFields == undefined) {
		emptyFields = false;
	}

	var tr, td, th;
	for (var i = 0; i <= rows; i++) {
		tr = document.createElement("tr");
		for (var n = 0; n < columns; n++) {
			if (i == 0) {
				th = document.createElement("th");
				th.innerText = data[i][n] == undefined ? "" : data[i][n];
				tr.appendChild(th);
			} else {
				td = document.createElement("td");
				if (emptyFields == true && data[i][n] == undefined) {
					td.contentEditable = "true";
				}
				td.innerText = data[i][n] == undefined ? "" : data[i][n];
				tr.appendChild(td);
			}
		}
		tbody.appendChild(tr);
	}

	table.appendChild(tbody);

	return table;
}

/*
	paramString can be a string or an object with key value pairs

	string: "r=test&value=1";
	object: {
		r : "test",
		value : "1"
	};

	added encodeURIComponent to make ajax requests safer
*/
var AjaxCall = function(param, ajaxType) {
	this.type = (ajaxType != null) ? ajaxType : "POST";

	if (typeof param === 'string') {
		this.paramString = (param != null) ? param : ""; //encodeURIComponent((param != null) ? param : "");
	} else if (typeof param === 'object') {
		let temp = "";
		for (let key in param) {
			temp += key + "=" + param[key] + "&";
		}

		this.paramString = temp.slice(0, -1);// encodeURIComponent(temp.slice(0, -1));
	}
	this.url;
}

AjaxCall.prototype.setType = function(type) {
	if(type != null) {
		this.type = type;
	} else {
		console.error("Ajax Type not defined");
	}
}

AjaxCall.prototype.setParamString = function(paramString) {
	if(paramString != null) {
		this.paramString = paramString;
	} else {
		console.warn("AjaxCall: no parameters given");
	}
}

AjaxCall.prototype.setUrl = function(url) {
	this.url = url;
}

AjaxCall.prototype.makeAjaxCall = function(dataCallback, ...args) {
	if (this.paramString == null) {
		console.warn("AjaxCall: no parameters given");
	}
	
	if (this.type == "POST") {
		var ajaxCall = new XMLHttpRequest();
		ajaxCall.onreadystatechange = function() {
			if(this.readyState == 4 && this.status == 200) {
				dataCallback(this.responseText, args);
			}
		}
		ajaxCall.open(this.type, this.url, true);
		ajaxCall.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		ajaxCall.send(this.paramString);
	} else if (this.type == "GET") {
		var ajaxCall = new XMLHttpRequest();
		ajaxCall.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				dataCallback(this.responseText, args);
			}
		}
		ajaxCall.open("GET", this.url + this.paramString, true);
		ajaxCall.send();
	} else {
		console.error("AjaxCall: Ajax Type not defined");
	}
}

async function makeAsyncCall(type, params, location) {
	return new Promise((resolve, reject) => {
		if (params == null) {
			console.warn("AjaxCall: no parameters given");
		}
		
		if (type == "POST") {
			var ajaxCall = new XMLHttpRequest();
			ajaxCall.onload  = function() {
				if (this.readyState == 4 && this.status == 200) {
					resolve(this.responseText);
				} else {
					reject();
				}
			}
			ajaxCall.open("POST",  location, true);
			ajaxCall.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			ajaxCall.send(params);
		} else if (type == "GET") {
			var ajaxCall = new XMLHttpRequest();
			ajaxCall.onload = function() {
				if (this.readyState == 4 && this.status == 200) {
					resolve(this.responseText);
				} else {
					reject();
				}
			}
			ajaxCall.open("GET", location + params, true);
			ajaxCall.send();
		} else {
			console.error("AjaxCall: Ajax Type not defined");
		}
	});
}

/* submit button onenter */
function autosubmit() {
	var elements = document.getElementsByClassName("autosubmit");
	var id = "";
	var btn;
	for (let element of elements) {
		id = element.dataset.btnid;
		btn = document.getElementById("autosubmit_" + id);

		element.addEventListener("keyup", function (event) {
			if (event.key === "Enter") {
				btn.click();
			}
		}.bind(btn));
	}
}

function sortTable(element, id, direction) {
	var table = element.parentNode.parentNode.parentNode;
	var t = new TableClass(table);
	t.sortByRow(id, direction);
}

class TableClass {
    constructor(html_table) {
        this.html_table = html_table;
        this.rows = html_table.rows;
    }

    sortByRow(rowId, direction) {
        var switching = true, x, y, shouldSwitch;
		while (switching) {
			switching = false;
			
			for (var i = 1; i < (this.rows.length - 1); i++) {
				shouldSwitch = false;
				x = this.rows[i].getElementsByTagName("TD")[rowId];
				y = this.rows[i + 1].getElementsByTagName("TD")[rowId];
				
				if (direction) {
					if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
						shouldSwitch = true;
						break;
					}
				} else {
					if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
						shouldSwitch = true;
						break;
					}
				}
			}
			if (shouldSwitch) {
			
			this.rows[i].parentNode.insertBefore(this.rows[i + 1], this.rows[i]);
			switching = true;
			}
		}
    }
}

/* adds file upload class to every form with that class */
var fileUploader;
function initializeFileUpload() {
	let forms = document.querySelectorAll("form.fileUploader");
	fileUploaders = [];
	for (let i = 0, f; f = forms[i]; i++) {
		let u = new FileUploader(f);
		fileUploaders.push(u);
	}
}

/* https://stackoverflow.com/questions/30008114/how-do-i-promisify-native-xhr */
var FileUploader = function(target) {
	if (target.nodeName == "FORM") {
		this.target = target;
		this.files = document.querySelector(`input[type="file"][form="${this.target.id}"]`);
		this.files.addEventListener("change", this.preview.bind(this), false);
		this.fileArrayDragDrop = [];

		this.initHTML();
		this.initializeDragAndDrop();
	} else
		return null;
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
				document.getElementById("showFilePrev").innerHTML = this.responseText;

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

/* info button code 
 * adds an event listener to each btn of that class
 * which loads the info text from the server and displays its content
 * in an extra div next to the "i"
 */
function initializeInfoBtn() {
	let btns = document.getElementsByClassName("infoButton");
	Array.from(btns).forEach(function(btn) {
		btn.addEventListener("click", function() {
			let id = btn.dataset.info;
			var getInfo = new AjaxCall(`getReason=getInfoText&info=${id}`, "POST", window.location.href);
			getInfo.makeAjaxCall(function (response, args) {
				let btn = args[0];
				
				let infoBox = document.getElementById("infoBox" + args[1]);
				if (infoBox == undefined) {
					infoBox = document.createElement("div");
					infoBox.classList.add("infoBox");
					infoBox.classList.add("infoBoxShow");
					infoBox.id = "infoBox" + args[1];

					let text = document.createTextNode(response);
					infoBox.appendChild(text);
					document.body.appendChild(infoBox);
				} else {
					if (!infoBox.classList.contains('infoBoxShow')) {
						infoBox.classList.add('infoBoxShow');
					}
				}
				
				let left = parseInt(btn.offsetWidth + btn.offsetLeft);
				let top = parseInt(- (0.75 * btn.offsetHeight) + btn.offsetTop);

				if (left + infoBox.getBoundingClientRect().width > document.body.offsetWidth) {
					left = left - infoBox.getBoundingClientRect().width - btn.offsetWidth - 15;
				}

				infoBox.style.top = top + "px";
				infoBox.style.left = left + "px";
			}, btn, id);
		}, false);
	});
}

window.addEventListener("click", function(event) {
	if (!event.target.matches('infoButton')) {
		var dropdowns = document.getElementsByClassName("infoBox");
		for (let i = 0; i < dropdowns.length; i++) {
			var openDropdown = dropdowns[i];
			if (openDropdown.classList.contains('infoBoxShow')) {
				openDropdown.classList.remove('infoBoxShow');
			}
		}
	}
}, false);

/* function shows an info text about the update status of an ajax query */
function infoSaveSuccessfull(status = "failiure") {
	var statusClass = "";
	var text = "";
	
	switch (status) {
		case "success":
			statusClass = "showSuccess";
			text = "Speichern erfolgreich!";
			break;
		case "failiure":
		default:
			statusClass = "showFailiure";
			text = "Speichern hat nicht geklappt!";
			break;
	}

	let div = document.createElement("div");
    div.innerHTML = text;
    div.classList.add(statusClass);
    document.body.appendChild(div);

    setTimeout(function () {
        div.classList.add("hidden");
    }, 1000);

    setTimeout(function () {
        div.parentNode.removeChild(div);
    }, 2000);
}

/**
 * clears all inputs, supported types: id, array of ids, classes
 * @param {Object} inputs JSON object with this pattern: {"id":"clearthisid", "class":"clearthisclass"}
 */
function clearInputs(inputs) {
	for (key in inputs) {
		switch (key) {
			case "id":
				document.getElementById(inputs[key]).value = "";
				break;
			case "ids":
				for (let i = 0; i < inputs[key].length; i++) {
					document.getElementById(inputs[key][i]).value = "";
				}
				break;
			case "class":
				var classes = document.getElementsByClassName(inputs[key]);
				for (c of classes) {
					c.value = "";
				}
				break;
			case "classes":
				for (let i = 0; i < inputs[key].length; i++) {
					var classes = document.getElementsByClassName(inputs[key][i]);
					for (c of classes) {
						c.value = "";
					}
				}
				break;
		}
	}
}

/* side nav */
function toggleNav() {
	let sidenav = document.getElementById("sidenav");
	if (sidenav.style.width == "250px") {
		sidenav.style.width = "0";
		let elements = document.getElementsByClassName("moveToSide");
		for (let i = 0; i < elements.length; i++) {
			elements[i].style.marginLeft = "0";
		}
	} else {
		sidenav.style.width = "250px";
		let elements = document.getElementsByClassName("moveToSide");
		for (let i = 0; i < elements.length; i++) {
			elements[i].style.marginLeft = "250px";
		}
	}
}

/* code for notifications */
function setRead() {
	var setNotificationsRead = new AjaxCall(`getReason=setNotificationsRead&notificationIds=all`, "POST", window.location.href);
    setNotificationsRead.makeAjaxCall(function (response) {
    });
}

async function updateNotifications() {
	var containerDiv = document.getElementById("showNotifications");
	var replacementDiv = document.createElement("div");
	replacementDiv.innerHTML = await makeAsyncCall("POST", `getReason=testDummy`, window.location.href).then(result => {
		return result;
	});

	replacementDiv.classList.add("notificationWrapper");

	var toReplace = containerDiv.children[1];
	containerDiv.replaceChild(replacementDiv, toReplace); 
}

function performGlobalSearch(e) {
	var query = e.target.value;
	var search = new AjaxCall(`getReason=globalSearch&query=${query}`, "POST", window.location.href);
    search.makeAjaxCall(function (response) {
		var div = document.createElement("div");
		div.innerHTML = response;

		div.style.height = "500px";
		if (innerHeight < 550) {
			div.style.height = "200px";
		}
		div.style.overflowY = "scroll";

		document.body.appendChild(div);
		addActionButtonForDiv(div, "remove");
		centerAbsoluteElement(div);
    });
}
