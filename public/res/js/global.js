//@ts-nocheck
import "../css/input.css";

if (import.meta.env.VITE_DEBUG_CSS === 'true') {
	import('../css/debug.css');
}

if (import.meta.env.DEV) {
	const pageScript = document.body.dataset.page;
	if (pageScript) {
		if (window.__PAGE_SCRIPT_LOADED__ === pageScript) {
			console.warn("[DEV] Page script already loaded, skipping");
		} else {
			window.__PAGE_SCRIPT_LOADED__ = pageScript;
			import(/* @vite-ignore */ `./pages/${pageScript}`)
				.then((mod) => {
					if (typeof mod.default === "function") {
						mod.default();
					}
					console.log(`[DEV] Loaded page script: ${pageScript}`);
				})
				.catch(() => {
					console.log(`[DEV] No page script found for: ${pageScript}`);
				});
		}
	}
}

import { ajax } from "js-classes/ajax.js";
import { addBindings } from "js-classes/bindings.js"
import { DeviceDetector } from "js-classes/deviceDetector";

import { initNotificationService } from "./classes/notificationUpdater.js";
import { timeGlobalListener } from "./classes/timetracking.js";

const fnNames = {};
const imagePreviewListeners = new WeakSet();

document.addEventListener("click", function (event) {
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

function init() {
	addBindings(fnNames);
	timeGlobalListener();

	autoValidateEmails();
	autoSizeTextareas();
	autosubmit();

	initializeInfoBtn();
	initImagePreviewListener();
	initSearch();
	initNotificationService();
}

const autoSizeTextareas = () => {
	const textareas = document.querySelectorAll("textarea");
	textareas.forEach(t => {
		if (t.scrollHeight != 0) {
			t.style.height = '';
			t.style.height = t.scrollHeight + 'px';
		}
	});
}

const autoValidateEmails = () => {
	const emailInputs = document.querySelectorAll("input[type=email]");
	emailInputs.forEach(el => {
		el.addEventListener("input", function () {
			if (validateEmail(el.value)) {
				el.style.color = "green";
			} else {
				el.style.color = "red";
			}
		}, false);
	});
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
		if ((event.key === "k" && event.ctrlKey) ||
			(event.key === "k" && event.metaKey)) {
			event.stopPropagation();
			event.preventDefault();

			const searchInput = document.querySelector(".searchContainer input");
			searchInput.focus();
		}
	});
}

export const initImagePreviewListener = () => {
	const images = document.querySelectorAll("img");
	images.forEach(image => {
		if (imagePreviewListeners.has(image)) {
			return;
		}

		image.classList.add("cursor-pointer");
		image.addEventListener("click", () => {
			const imageCopy = document.createElement("img");
			imageCopy.src = image.src;
			imageCopy.title = image.title;
			imageCopy.width = image.naturalWidth < 500 ? image.naturalWidth : 500;
			createPopup(imageCopy);
		});
		imagePreviewListeners.add(image);
	});
}

function initSearchIcons() {
	const searchInput = document.querySelector(".searchContainer input");
	if (searchInput != null) {
		const device = DeviceDetector.getOS();
		if (device == "Mac OS") {
			searchInput.placeholder = "⌘ K";
		} else if (device == "Windows" || device == "Linux") {
			searchInput.placeholder = "Ctrl K";
		}
	}
}

export const createPopup = (content) => {
	const container = document.createElement("div");
	container.classList.add("overlay-container");
	const contentContainer = document.createElement("div");
	contentContainer.classList.add("overlay-container__content");
	const optionsContainer = document.createElement("div");
	optionsContainer.classList.add("overlay-container__content__options");

	const button = document.createElement("button");
	button.classList.add("btn-cancel", "ml-2");
	button.innerHTML = "Abbrechen";
	button.addEventListener("click", () => {
		container.parentNode.removeChild(container);
		const event = new CustomEvent("closePopup", {
			bubbles: true,
		});
		optionsContainer.dispatchEvent(event);
	});
	optionsContainer.appendChild(button);

	content.classList.add("p-3");
	contentContainer.appendChild(content);
	contentContainer.appendChild(optionsContainer);
	container.appendChild(contentContainer);
	document.body.appendChild(container);

	container.addEventListener("click", e => {
		const target = e.target;
		if (target === container) {
			container.parentNode.removeChild(container);
			const event = new CustomEvent("closePopup", {
				bubbles: true,
			});
			optionsContainer.dispatchEvent(event);
		}
	});

	return optionsContainer;
}

fnNames.click_logout = () => {
	const cookies = checkCookies();
	let loginkey = "false";
	if ("loginkey" in cookies) {
		loginkey = cookies["loginkey"];
	}
	ajax.post(`/api/v1/auth/logout`, {
		"loginkey": loginkey,
	}).then(() => location.reload());
}

fnNames.click_showNotifications = async () => {
	const div = document.createElement("div");
	div.classList.add("w-2/3", "z-10", "h-96");

	const response = await ajax.get(`/api/v1/notification/template`);
	const innerDiv = document.createElement("div");
	innerDiv.innerHTML = response.data.html;

	div.appendChild(innerDiv);
	innerDiv.classList.add("notificationWrapper");

	const options = createPopup(innerDiv);
	const goToNotifications = document.createElement("a");
	goToNotifications.innerHTML = "Zum Benachrichtigungszentrum";
	goToNotifications.href = "/notifications";
	goToNotifications.classList.add("btn-primary");
	options.appendChild(goToNotifications);
}

function validateEmail(email) {
	const re = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
	return re.test(String(email).toLowerCase());
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

/** 
 * Info Buttons
 * - adds an event listener to each btn of that class
 * - a click loads the info text from the server
 * -  displays its content in an extra div next to the "i"
 */
function initializeInfoBtn() {
	let btns = document.getElementsByClassName("info-button");
	Array.from(btns).forEach(btn => {
		btn.addEventListener("click", async function () {
			const id = btn.dataset.info;
			const response = await ajax.get(`/api/v1/manual/text/${id}`);

			let infoBox = document.getElementById("infoBox" + id);
			if (infoBox == undefined) {
				infoBox = document.createElement("div");
				infoBox.classList.add("infoBox");
				infoBox.classList.add("infoBoxShow");
				infoBox.id = "infoBox" + id;

				let text = document.createTextNode(response.data["info"]);
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

window.addEventListener("click", function (event) {
	if (!event.target.matches('info-button')) {
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
 * @param {Object} inputs JSON object with this pattern: 
 * {
 * 		"id":"clearthisid", 
 * 		"class":"clearthisclass"
 * }
 */
export const clearInputs = (inputs) => {
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
fnNames.click_toggleNav = function () {
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

async function performGlobalSearch(e) {
	const query = e.target.value;
	const results = await ajax.get(`/api/v1/search/all?query=${query}`);

	const div = document.createElement("div");
	div.innerHTML = results.data.html;
	div.classList.add("h-96", "overflow-y-scroll");

	createPopup(div);
}

export function getCookie(name) {
	var allCookies = checkCookies();
	if (name in allCookies) {
		const val = allCookies[name];
		return val;
	}

	return null;
}

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

export const setCookie = (cname, cvalue, exdays) => {
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

if (document.readyState !== 'loading') {
	init();
} else {
	document.addEventListener('DOMContentLoaded', init);
}

if (import.meta.hot) {
	import.meta.hot.accept(() => {
		init();
	})
}
