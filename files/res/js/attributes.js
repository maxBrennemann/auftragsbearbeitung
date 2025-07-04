import { ajax } from "js-classes/ajax.js";
import { addBindings } from "js-classes/bindings.js"
import { notification } from "js-classes/notifications.js";
import { clearInputs } from "./global.js";

let currentDraggedGroup = null;
let currentDraggedElement = null;
let currentIndex = null;

const fnNames = {};

function init() {
    addBindings(fnNames);
    initSortAttributeValues();
}

fnNames.click_btnAddValue = () => {
    const value = document.getElementById("newVal").value;
    const attribute = document.getElementById("selectAttribute").value;

    ajax.post(`/api/v1/attribute/${attribute}/value`, {
        "value": value,
    }).then(r => {
        const li = document.querySelector(".attributeValueGroups li");
        const liClone = li.cloneNode(true);
        liClone.querySelector("span").innerText = value;
        liClone.dataset.id = r.id;

        const ul = document.getElementById("attributeValues_" + attribute);
        ul.appendChild(liClone);
        bindDragEvents(liClone);
    }).catch((error) => {
        console.error(error);
    });
}

fnNames.click_btnAddAttribute = () => {
    const name = document.getElementById("newName").value;
    const descr = document.getElementById("descr").value;

    ajax.post(`/api/v1/attribute`, {
        "name": name,
        "descr": descr,
    }).then(r => {
        notification("", "success");

        const div = document.querySelector("singleAttribute");
        const divClone = div.cloneNode(true);

        const dataElements = divClone.querySelectorAll(`[data-id]`)
        dataElements[0].dataset.id = r.id;
        dataElements[1].dataset.id = r.id;
        dataElements[2].dataset.id = r.id;

        dataElements[0].value = name;
        dataElements[1].value = descr;
        dataElements[2].id = "attributeValues_" + r.id;

        dataElements[2].innerHTML = "";
    }).catch((error) => {
        console.error(error);
    });
}

fnNames.click_btnAbortAttribute = () => {
    clearInputs({
        "ids": [
            "newName",
            "descr",
        ],
    });
}

function initSortAttributeValues() {
    const attributeValueGroups = document.getElementsByClassName("attributeValueGroups");

    Array.from(attributeValueGroups).forEach(group => {
        const groupElements = group.getElementsByTagName("li");

        Array.from(groupElements).forEach(element => bindDragEvents(element));
    });
}

const bindDragEvents = element => {
    element.addEventListener("dragstart", handleDragStart);
    element.addEventListener("dragover", handleDragOver);
    element.addEventListener("dragenter", handleDragEnter);
    element.addEventListener("dragleave", handleDragLeave);
    element.addEventListener("drop", handleDragDrop);
    element.addEventListener("dragend", handleDragEnd);
}

const handleDragStart = e => {
    currentDraggedGroup = e.currentTarget.closest(".attributeValueGroups");
    currentDraggedElement = e.currentTarget;
    currentIndex = Array.from(currentDraggedGroup.children).indexOf(e.currentTarget);

    e.currentTarget.classList.add("opacity-50");
}

const handleDragOver = e => {
    e.preventDefault();

    const target = e.currentTarget;
    const bounding = target.getBoundingClientRect();
    const offset = e.clientY - bounding.top;

    if (target.parentElement !== currentDraggedGroup || target === currentDraggedElement) return;

    if (offset < bounding.height / 2 && target.previousSibling === currentDraggedElement) return;
    if (offset >= bounding.height / 2 && target.nextSibling === currentDraggedElement) return;    

    if (offset < bounding.height / 2) {
        target.before(currentDraggedElement);
    } else {
        target.after(currentDraggedElement);
    }
}

const handleDragEnter = e => {
    e.preventDefault();
    e.currentTarget.classList.add("ring-2", "ring-blue-400", "bg-blue-50");
};

const handleDragLeave = e => {
    e.currentTarget.classList.remove("ring-2", "ring-blue-400", "bg-blue-50");
};

const handleDragDrop = e => {
    e.preventDefault();

    handleDragLeave(e);
    currentDraggedElement.classList.remove("opacity-50");

    updatePositions();
}

const handleDragEnd = e => {
    e.currentTarget.classList.remove("opacity-50");

    const lis = document.querySelectorAll(".attributeValueGroups li");
    lis.forEach(li => li.classList.remove("ring-2", "ring-blue-400", "bg-blue-50"));
};

function updatePositions() {
    const listItems = currentDraggedGroup.children;
    let positions = [];
    Array.from(listItems).forEach((el, idx) => {
        const id = el.dataset.id;
        const newPosition = idx + 1;
        positions.push({
            "id": id,
            "position": newPosition
        });
    });

    ajax.put(`/api/v1/attribute/${currentDraggedGroup.dataset.id}/positions`, {
        positions: JSON.stringify(positions),
    }).then(() => {
        notification("", "success");
    }).catch((error) => {
        notification("", "failure", JSON.stringify(error));
        console.error(error);
    });
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
