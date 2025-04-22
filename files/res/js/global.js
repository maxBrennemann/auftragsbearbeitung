import { DeviceDetector } from "./classes/deviceDetector.js";
import { TableSorter, currentTableSorter, setTableSorter, sortTableNew } from "./classes/tableSorter.js";
import { StatusInfoHandler } from "./classes/statusInfo.js";
import { FileUploader } from "./classes/fileUploader.js";
import { AjaxCall, ajax, makeAsyncCall } from "./classes/ajax.js";
import { timeGlobalListener } from "./classes/timetracking.js";
import { notification } from "./notifications.js";

/**
 * function is called when the page is loaded,
 * workaround for new modules in js
 */
function exportToWindow() {
	window.DeviceDetector = DeviceDetector;
	window.TableSorter = TableSorter;
	window.currentTableSorter = currentTableSorter;
	window.setTableSorter = setTableSorter;
	window.sortTableNew = sortTableNew;
	window.StatusInfoHandler = StatusInfoHandler;
	window.FileUploader = FileUploader;
	window.AjaxCall = AjaxCall;
	window.ajax = ajax;
	window.makeAsyncCall = makeAsyncCall;

	window.centerAbsoluteElement = centerAbsoluteElement;
	window.addActionButtonForDiv = addActionButtonForDiv;
	window.removeElement = removeElement;
	window.indexInClass = indexInClass;
	window.createTable = createTable;
	window.sortTable = sortTable;
	window.clearInputs = clearInputs;
	window.toggleNav = toggleNav;
	window.setRead = setRead;
	window.updateNotifications = updateNotifications;
	window.performGlobalSearch = performGlobalSearch;
	window.getCookie = getCookie;
	window.checkCookies = checkCookies;
	window.setCookie = setCookie;
}

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

function startFunc() {
	timeGlobalListener();

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
	for (let t of textareas) {
		if (t.scrollHeight != 0) {
			t.style.height = '';
			t.style.height = t.scrollHeight + 'px';
		}
	}

	listener_logout();
	listener_bellAndSearch();
	initializeInfoBtn();
	setTableSorter(new TableSorter());
	currentTableSorter.readTableSorted();
	initSearch();
}

const initSearch = () => {
	const globalSearch = document.querySelector(".searchContainer input");
	globalSearch.addEventListener("change", performGlobalSearch);

	/* initSearch */
	initSearchIcons();
	initSearchListener();
}

function initSearchListener() {
	document.addEventListener("keydown", function (event) {
		if (event.key === "k" && event.ctrlKey) {
			event.stopPropagation();
			event.preventDefault();

			const searchInput = document.querySelector(".searchContainer input");
			searchInput.focus();
		}
	});
}

/**
 * sets the placeholder of the search input according to the device operating system
 */
function initSearchIcons() {
	const searchInputs = document.querySelectorAll(".searchContainer input");
	Array.from(searchInputs).forEach(searchInput => {
		if (searchInput != null) {
			const device = DeviceDetector.getOS();
			if (device == "Mac OS") {
				searchInput.placeholder = "⌘ K";
			} else if (device == "Windows" || device == "Linux") {
				searchInput.placeholder = "Ctrl K";
			}
		}
	});
}

function listener_logout() {
	const logout = document.getElementById("logoutBtn");
	if (logout == null) {
		return;
	}

	logout.addEventListener("click", () => {
		const cookies = checkCookies();
		let loginkey = "false";
		if ("loginkey" in cookies) {
			loginkey = cookies["loginkey"];
		}
		ajax.post(`/api/v1/auth/logout`, {
			"loginkey": loginkey,
		}).then(() => location.reload());
	}, false);
}

/**
 * shows the notification container when the bell is clicked
 * @returns {null} if the bellAndSearch element is not found
 */
function listener_bellAndSearch() {
	var bellAndSearch = document.getElementsByClassName("notificationContainer")[0];
	if (bellAndSearch == null) {
		return null;
	}

	bellAndSearch.addEventListener("click", async function() {
		if (document.getElementById("showNotifications") == null) {
			const div = document.createElement("div");
			div.id = "showNotifications";
			div.classList.add("w-7/12", "z-10", "h-96");
			document.body.appendChild(div);

			const htmlContent = await ajax.post({
				r: "notification",
			}, true);

			const innerDiv = document.createElement("div");
			innerDiv.innerHTML = htmlContent;

			div.appendChild(innerDiv);
			innerDiv.classList.add("notificationWrapper");
			addActionButtonForDiv(div, "hide");
			centerAbsoluteElement(div);
		} else {
			document.getElementById("showNotifications").style.display = "inline";
		}
	}, false);
}

function validateEmail(email) {
	var re = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
	return re.test(String(email).toLowerCase());
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

/* submit button onenter */
function autosubmit() {
	const elements = document.getElementsByClassName("autosubmit");
	for (let element of elements) {
		const id = element.dataset.btnid;
		const btn = document.getElementById("autosubmit_" + id);

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
	Array.from(btns).forEach(btn => {
		btn.addEventListener("click", async function() {
			const id = btn.dataset.info;
			const response = await ajax.post({
				r: "getInfoText",
				info : id,
			}, true);

			let infoBox = document.getElementById("infoBox" + id);
			if (infoBox == undefined) {
				infoBox = document.createElement("div");
				infoBox.classList.add("infoBox");
				infoBox.classList.add("infoBoxShow");
				infoBox.id = "infoBox" + id;

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
	for (let key in inputs) {
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
					for (let c of classes) {
						c.value = "";
					}
				}
				break;
		}
	}
}

export const setInpupts = inputs => {
	for (let key in inputs) {
		switch (key) {
			case "ids":
				for (let item in inputs[key]) {
					const el = document.getElementById(item);
					el.value = inputs[key][item];
				}
				break;
			case "classes":
				for (let item in inputs[key]) {
					const els = document.getElementsByClassName(item);
					els.forEach(el => {
						if (el.type == "checkbox") {
							el.checked = inputs[key][item];
							return;
						}

						el.value = inputs[key][item];
					});
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
	ajax.post([
		"r", "setNotificationsRead",
		"notificationIds", "all",
	]);
}

async function updateNotifications() {
	const containerDiv = document.getElementById("showNotifications");
	const replacementDiv = document.createElement("div");
	replacementDiv.innerHTML = "test"; // TODO: replace with ajax call

	replacementDiv.classList.add("notificationWrapper");

	const toReplace = containerDiv.children[1];
	containerDiv.replaceChild(replacementDiv, toReplace); 
}

async function performGlobalSearch(e) {
	const query = e.target.value;
	const search = await ajax.post({
		r: "globalSearch",
		query: query,
	}, true);

	const div = document.createElement("div");
	div.innerHTML = search;
	div.classList.add("w-7/12", "z-10", "h-96");

	div.style.height = "500px";
	if (innerHeight < 550) {
		div.style.height = "200px";
	}
	div.style.overflowY = "scroll";

	document.body.appendChild(div);
	addActionButtonForDiv(div, "remove");
	centerAbsoluteElement(div);
}

function getCookie(name) {
    var allCookies = checkCookies();
    if (name in allCookies) {
        const val = allCookies[name];
        return val;
    }

    return null;
}

/* https://www.geekstrick.com/snippets/how-to-parse-cookies-in-javascript/ */
function checkCookies() {
    var cookies = document.cookie.split(";");
    var cookieObj = {};
    for (let i = 0; i < cookies.length; i++) {
        var parts = cookies[i].split("=");

		if (parts[0].charAt(0) == " ") {
			parts[0] = parts[0].substring(1);
		}
        
        cookieObj[parts[0]] = parts[1];
    }

    return cookieObj;
}

/* https://www.w3schools.com/js/js_cookies.asp */
function setCookie(cname, cvalue, exdays) {
    const d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    let expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
}

export const loadFromLocalStorage = (key) => {
	const item = localStorage.getItem(key);
	try {
		const json = JSON.parse(item);
		return json;
	} catch (e) {
		return item;
	}
}

export const saveToLocalStorage = (key, value) => {
	value = JSON.stringify(value);
	localStorage.setItem(key, value);
}

export const getTemplate = (id) => {
	const template = document.getElementById(id);
	if (template == null) {
		return null;
	}

	const clone = template.content.cloneNode(true);
	return clone;
}

if (document.readyState !== 'loading' ) {
	exportToWindow();
    startFunc();
} else {
    document.addEventListener('DOMContentLoaded', function () {
		exportToWindow();
        startFunc();
    });
}
