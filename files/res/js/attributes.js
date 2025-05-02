import { ajax } from "./classes/ajax.js";
import { addBindings } from "./classes/bindings.js";
import { notification } from "./classes/notifications.js";
import { clearInputs } from "./global.js";

let currentDraggedGroup = null;
let currentDraggedElement = null;
let currentIndex = null;

const fnNames = {};

function init() {
    addBindings(fnNames);

    initSortAttributes();
    initSortAttributeValues();
}

fnNames.click_btnAddValue = () => {
    const value = document.getElementById("newVal").value;
    const attribute = document.getElementById("selectAttribute").value;

    ajax.post(`/api/v1/attribute/${attribute}/value`, {
        value: value,
    }).then(r => {
        const li = document.querySelector(".attributeValueGroups li");
        const liClone = li.cloneNode(true);
        liClone.querySelector("span").innerText = value;
        liClone.dataset.id = r.id;

        const ul = document.getElementById("attributeValues_" + attribute);
        ul.appendChild(liClone);

        liClone.addEventListener("dragstart", handleDragStart);
        liClone.addEventListener("dragover", handleDravOver);
        liClone.addEventListener("drop", handleDragDrop);
    }).catch((error) => {
        console.error(error);
    });
}

fnNames.click_btnAddAttribute = () => {
    var name = document.getElementById("newName").value;
    var descr = document.getElementById("descr").value;

    ajax.post(`/api/v1/attribute`, {
        name: name,
        descr: descr,
    }).then(() => {
        notification("", "success");

        /* adds new attribute container to top */
        var div = document.createElement("div");
        div.classList.add("defCont");
        div.classList.add("singleAttribute");

        var h2 = document.createElement("h2");
        h2.dataset.id = response;
        h2.innerHTML = name;

        var p = document.createElement("p");

        var i = document.createElement("i");
        i.innerHTML = descr;

        var ul = document.createElement("ul");
        ul.id = "attributeValues_" + response;

        div.appendChild(h2);
        div.appendChild(p);
        p.appendChild(i);
        div.appendChild(ul);

        document.getElementsByClassName("attributesContainer")[0].appendChild(div);

        /* adds new attribte to select */
        var option = document.createElement("option");
        option.value = response;
        option.innerText = args[0];

        document.getElementById("selectAttribute").appendChild(option);
    }).catch((error) => {
        console.error(error);
    });
}

fnNames.click_btnAbortAttribute = () => {
    clearInputs({ "ids": ["newName", "descr"] });
}

function initSortAttributes() {

}

function initSortAttributeValues() {
    const attributeValueGroups = document.getElementsByClassName("attributeValueGroups");

    Array.from(attributeValueGroups).forEach(group => {
        const groupElements = group.getElementsByTagName("li");

        Array.from(groupElements).forEach(element => {
            element.addEventListener("dragstart", handleDragStart);
            element.addEventListener("dragover", handleDravOver);
            element.addEventListener("drop", handleDragDrop);
        });
    });
}

const handleDragStart = e => {
    currentDraggedGroup = group;
    currentDraggedElement = e.target;
    currentIndex = Array.from(group.children).indexOf(e.target);
}

const handleDravOver = e => {
    e.preventDefault();
}

const handleDragDrop = e => {
    e.preventDefault();
    let indexDrop = Array.from(group.children).indexOf(e.target);
    const targetElement = e.target;
    const targetGroup = targetElement.parentElement;

    if (currentDraggedGroup !== targetGroup) {
        return;
    }

    if (currentIndex > indexDrop) {
        targetElement.before(currentDraggedElement);
    } else {
        targetElement.after(currentDraggedElement);
    }

    updatePositions();
}

function updatePositions() {
    const listItems = currentDraggedGroup.getElementsByTagName("li");
    let positions = [];
    listItems.forEach((el, idx) => {
        const id = el.dataset.id;
        const newPosition = idx + 1;
        positions.push({
            id: id,
            position: newPosition
        });
    });

    ajax.put(`/api/v1/attribute/${currentDraggedGroup.dataset.id}/positions`, {
        positions: JSON.stringify(positions),
    }).then(() => {
        notification("", "success");
    }).catch((error) => {
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
