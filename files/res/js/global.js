document.addEventListener("click", function(event) {
	if (!event.target.matches('.showLog,.showLog *')) {
		if (document.getElementById("login")) {
			document.getElementById("login").style.display = "none";
		}
		if (document.getElementById("register")) {
			document.getElementById("register").style.display = "none";
		}
	}
})

var currentTableSorter;

/* https://stackoverflow.com/questions/14267781/sorting-html-table-with-javascript */
class TableSorter {

	constructor() {
		this.url = window.location.href;
		this.settings = this.getSortSettings();
	}

	saveSortSettings(sortDirection, sortedColumn, tableNumber) {
		if (this.settings == null) {
			this.settings = {
	
			}
		}
	
		this.settings[tableNumber] = {
			sortDirection: sortDirection,
			sortedColumn: sortedColumn,
		}
	
		localStorage.setItem(this.url, JSON.stringify(this.settings));
	}

	get(tableIndex) {
		if (this.settings[tableIndex]) {
			return this.settings[tableIndex];
		} else {
			this.saveSortSettings("asc", 0, tableIndex);
			return this.getSortSettings();
		}
	}

	getSortSettings() {
		this.settings = JSON.parse(localStorage.getItem(this.url));
		if (this.settings == null) {
			this.settings = {};
		}
		return this.settings;
	}

	readTableSorted() {
		const tables = document.querySelectorAll("table");
	
		if (this.settings == null)
			return;
	
		for (const [key, value] of Object.entries(this.settings)) {
			const table = tables[key];
	
			if (table != undefined) {
				const ths = table.querySelectorAll("th");
				const th = ths[value.sortedColumn];
				const sort = value.sortDirection == "asc";

				this.sortColumn(table, th, sort);
			}
		}
	}

	sortColumn(table, th, sort) {
		Array.from(table.querySelectorAll('tr:nth-child(n+2)'))
		.sort(this.comparer(Array.from(th.parentNode.children).indexOf(th), sort))
		.forEach(tr => table.appendChild(tr));
	
		let tr = th.closest('tr');
		Array.from(tr.children).forEach(element => {
			if (element != th) {
				element.style.backgroundColor = "";
			} else {
				element.style.backgroundColor = "#005999";
				let sortIcon = element.querySelector("span");
				
				if (sort) {
					sortIcon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="inline" viewBox="0 0 24 24" style="width: 12px; height 12px"><title>Absteigend sortieren</title><path d="M19 7H22L18 3L14 7H17V21H19M2 17H12V19H2M6 5V7H2V5M2 11H9V13H2V11Z" fill="white" /></svg>`;
				} else {
					sortIcon.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" class="inline" viewBox="0 0 24 24" style="width: 12px; height 12px"><title>Aufsteigend sortieren</title><path d="M19 17H22L18 21L14 17H17V3H19M2 17H12V19H2M6 5V7H2V5M2 11H9V13H2V11Z" fill="white" /></svg>`;
				}
			}
		});
	
		const sortedColumn = Array.from(tr.children).indexOf(th);
		const tableNumber = Array.from(document.querySelectorAll("table")).indexOf(table);
		/* turn sorting direction on click */
		const sortDirection = sort ? "asc" : "desc";
		this.saveSortSettings(sortDirection, sortedColumn, tableNumber);
	}

	sort(e) {
		const th = e.target;
		const table = th.closest('table');

		const tableIndex = Array.from(document.querySelectorAll("table")).indexOf(table);
		const sort = this.get(tableIndex).sortDirection != "asc";

		this.sortColumn(table, th, sort);
	}

	getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;

	comparer = (idx, asc) => (a, b) => ((v1, v2) =>
        v1 !== '' && v2 !== '' && !isNaN(v1) && !isNaN(v2) ? v1 - v2 : v1.toString().localeCompare(v2))(this.getCellValue(asc ? a : b, idx), this.getCellValue(asc ? b : a, idx));
}

function sortTableNew(e) {
	currentTableSorter.sort(e);
} 

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
	//initializeFileUpload();
	initializeInfoBtn();
	currentTableSorter = new TableSorter();
	currentTableSorter.readTableSorted();
	timeGlobalListener();
}

function listener_logout() {
	var logout = document.getElementById("logoutBtn");
	if (logout == null) return null;
	logout.addEventListener("click", function() {
		var cookies = checkCookies();
		var loginkey = "false";
		if ("loginkey" in cookies) {
			loginkey = cookies["loginkey"];
		}
		var logout = new AjaxCall(`logout_session=logout&loginkey=${loginkey}`, "POST", window.location.href);
		logout.makeAjaxCall(function (response) {
			location.reload();
		});
	}, false);
}

function listener_bellAndSearch() {
	var bellAndSearch = document.getElementsByClassName("notificationContainer")[0];
	if (bellAndSearch == null) return null;
	bellAndSearch.addEventListener("click", function(event) {
		if (document.getElementById("showNotifications") == null) {
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

var globalTimerInterval;
function timeGlobalListener() {
	const displayTime = document.getElementById("timeGlobal");
	if (displayTime != null) {
		const start = localStorage.getItem("startTime");
		if (start != null) {
			globalTimerInterval = setInterval(countTimeGlobal, 1000);
		}
	}
}

function countTimeGlobal() {
    let curr = new Date().getTime().toString();
    let startTime = parseInt(localStorage.getItem("startTime"));

    let diff = curr - startTime;

    let sec = Math.floor(diff / 1000);
    let hou = Math.floor(sec / 60 / 60);
    sec = sec - hou * 60 * 60;
    let min = Math.floor(sec / 60);
    sec = sec - min * 60;

	const displayTime = document.getElementById("timeGlobal");
    displayTime.innerHTML = `${pad(hou)}:${pad(min)}:${pad(sec)}`;
}

function pad(num) {
    return ('00' + num).slice(-2);
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

	var scrollTop = 0; //document.documentElement.scrollTop || document.body.scrollTop;

	div.style.left = ((pageWidth - divWidth) / 2) + "px";
	div.style.top = (((pageHeight - divHeight) / 2) + scrollTop) + "px";
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

// TODO: unhandled promise rejection behandeln
/*
	paramString can be a string or an object with key value pairs

	string: "r=test&value=1";
	object: {
		r : "test",
		value : "1"
	};

	added encodeURIComponent to make ajax requests safer
*/
var AjaxCall = function(param, ajaxType, url) {
	this.type = (ajaxType != null) ? ajaxType : "POST";

	if (typeof param === 'string') {
		this.paramString = (param != null) ? param : ""; //encodeURIComponent((param != null) ? param : "");
	} else if (typeof param === 'object') {
		let temp = "";
		for (let key in param) {
			let parameterEncoded = encodeURIComponent(param[key]);
			temp += key + "=" + parameterEncoded + "&";
		}

		this.paramString = temp.slice(0, -1);// encodeURIComponent(temp.slice(0, -1));
	}
	this.url = url;
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

const ajax = {
    async post(data, noJSON = false) {
        data.getReason = data.r;
        const param = Object.keys(data).map(key => {
            return `${key}=${encodeURIComponent(data[key])}`;
        });
        let response = await makeAsyncCall("POST", param.join("&"), "").then(result => {
            return result;
        });
    
        if (noJSON) {
            return response;
        }

        let json = {};
        try {
            json = JSON.parse(response);
        } catch (e) {
            return {};
        }

        return json;
    },

    async get() {

    },
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
					reject(this.responseText);
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

/* https://www.geekstrick.com/snippets/how-to-parse-cookies-in-javascript/ */
function checkCookies() {
	var cookies = document.cookie.split(";");
	var cookieObj = {};
	for (let i = 0; i < cookies.length; i++) {
		var parts = cookies[i].split("=");
		parts[0] = parts[0].substring(1);
		cookieObj[parts[0]] = parts[1];
	}

	return cookieObj;
}

/**
 * template for adding new elements to DOM
 * @param {*} elementType 
 * @param {*} elemntId 
 * @param {*} elementClass 
 * @param  {...any} args 
 */
function createNewElement(elementType, elementId, elementClass, ...args) {
	let element = document.createElement(elementType);
	element.id = elementId;
	element.classList.add(elementClass);
	return element;
}
