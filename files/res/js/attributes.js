import { ajax } from "./classes/ajax.js";
import { notification } from "./notifications.js";

let currentDraggedGroup = null;
let currentDraggedElement = null;
let currentIndex = null;

function init() {
    const btnAddAttribute = document.getElementById("btnAddAttribute");
    btnAddAttribute.addEventListener("click", addNewAttribute);

    const btnAbortAttribute = document.getElementById("btnAbortAttribute");
    btnAbortAttribute.addEventListener("click", function() {
        clearInputs({"ids": ["newName", "descr"]});
    });

    const btnAddAttributeValue = document.getElementById("btnAddValue");
    btnAddAttributeValue.addEventListener("click", addNewAttributeValue);

    initSortAttributes();
    initSortAttributeValues();
}

function initSortAttributes() {

}

function initSortAttributeValues() {
    const attributeValueGroups = document.getElementsByClassName("attributeValueGroups");
    Array.from(attributeValueGroups).forEach((group) => {
        const groupElements = group.getElementsByTagName("li");
        Array.from(groupElements).forEach((element) => {
            element.addEventListener("dragstart", function(event) {
                currentDraggedGroup = group;
                currentDraggedElement = event.target;
                currentIndex = Array.from(group.children).indexOf(event.target);
            });
    
            element.addEventListener("dragover", function(event) {
                event.preventDefault();
            });
    
            element.addEventListener("drop", function(event) {
                event.preventDefault();
                let indexDrop = Array.from(group.children).indexOf(event.target);
                const targetElement = event.target;
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
            });
        });
    });
}

function updatePositions() {
    const listItems = currentDraggedGroup.getElementsByTagName("li");
    let positions = [];
    Array.from(listItems).forEach((el, idx) => {
        const id = el.dataset.id;
        const newPosition = idx + 1;
        positions.push({id: id, position: newPosition});
    });

    ajax.put(`/api/v1/attribute/${currentDraggedGroup.dataset.id}/positions`, {
        positions: JSON.stringify(positions),
    }).then(() => {
        notification("", "success");
    }).catch((error) => {
        console.error(error);
    });
}

function addNewAttribute() {
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

function addNewAttributeValue() {
    var value = document.getElementById("newVal").value;
    var attribute = document.getElementById("selectAttribute").value;

    ajax.post(`/api/v1/attribute/${attribute}/value`, {
        value: value,
    }).then((response) => {
        var li = document.createElement("li");
            li.innerText = value;
            li.classList.add("bg-white", "rounded-md", "p-1", "pl-2", "hover:bg-blue-300");
            var ul = document.getElementById("attributeValues_" + attribute);
            ul.appendChild(li);

            document.getElementById("newVal").value = "";
    }).catch((error) => {
        console.error(error);
    });
}

if (document.readyState !== 'loading' ) {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
