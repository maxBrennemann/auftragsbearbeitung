//@ts-nocheck

import { ajax } from "js-classes/ajax.js";
import { addBindings } from "js-classes/bindings.js"
import { notification } from "js-classes/notifications.js";

import { DragSortManager } from "../classes/DragSortManager.js";
import { clearInputs } from "../global.js";

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

        const group = ul.closest(".attributeValueGroups");
        if (group && group._dragSortInstance) {
            group._dragSortInstance.bindNewItem(liClone);
        }
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
    document.querySelectorAll(".attributeValueGroups").forEach(group => {
        const sorter = new DragSortManager(group, {
            "itemSelector": "li",
            "onOrderChange": (positions, groupEl) => {
                ajax.put(`/api/v1/attribute/${groupEl.dataset.id}/positions`, {
                    positions: JSON.stringify(positions),
                }).then(() => {
                    notification("", "success");
                }).catch((error) => {
                    notification("", "failure", JSON.stringify(error));
                    console.error(error);
                });
            }
        });

        /* quickfix -> rather store it globally or add it to typescript HTMLElement here */
        group._dragSortInstance = sorter;
    });
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
