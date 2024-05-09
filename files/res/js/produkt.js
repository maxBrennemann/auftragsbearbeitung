import { ajax } from "./classes/ajax.js";

function init() {
    const urlString = window.location.href;
    const url = new URL(urlString);
    if (!url.searchParams.get("id")) {
        return;
    }

    const productInfo = document.querySelectorAll(".productInfo");
    Array.from(productInfo).forEach((element) => {
        element.addEventListener("change", function(event) {
            const type = event.target.dataset.type;
            const content = event.target.value;
            updateProduct(type, content);
        });
    });
}

function updateProduct(type, content) {
    if (content.length > 64) {
        return;
    }

    const id = document.getElementById("productId").dataset.id;
    ajax.put(`/api/v1/product/${id}/${type}`, {
        content: content,
    }).then((response) => {
        infoSaveSuccessfull(response);
    }).catch((error) => {
        console.error(error);
    });
}

/* js for attributes */
function getHTMLForAttributes() {
    var getHTML = new AjaxCall(`getReason=getAttributeMatcher`, "POST", window.location.href);
    getHTML.makeAjaxCall(function (responseHTML) {
        var div = document.createElement("div");
        div.innerHTML = responseHTML;
        div.id = "htmlForAddingAttributes";
        div.classList.add("ajaxBox");
        document.body.appendChild(div);
        centerAbsoluteElement(div);
    });
}

function removeHTMLForAttributes() {
    var child = document.getElementById("htmlForAddingAttributes");
    child.parentNode.removeChild(child);
    loadNewSelect();
}

/* attribute matcher functions */

/* this function adds the attribute to the attribute value selector */
function addToSelector() {
    var attributeSelector = document.getElementById("attributeSelector");
    var title = attributeSelector.options[attributeSelector.selectedIndex].innerHTML;
    attributeSelector = attributeSelector.options[attributeSelector.selectedIndex].value;

    var attValues = document.getElementById("showAttributeValues");
    var heading = document.createElement("h3");
    heading.innerHTML = title;

    attValues.appendChild(heading);
    loadAttributes(attributeSelector);
}

/* only used by addToSelector to load the attribute values */
function loadAttributes(attributeGroupId) {
    var getAttributes = new AjaxCall(`getReason=getAttributes&attGroupId=${attributeGroupId}`, "POST", window.location.href);
    getAttributes.makeAjaxCall(function (responseHTML) {
        var attValues = document.getElementById("showAttributeValues");
        attValues.innerHTML += responseHTML;
    });
}

/* global variables for attribute selection */
var attributes = {};
var tableAnchor = null;

/* adds the attribute value to the product */
function addAttributeToProduct(attributeGroupId, attributeId, bez) {
    var anchor = document.getElementById("addedValues");
    var div = document.getElementById( attributeGroupId + "addedValues");
    if (div == null) {
        div = document.createElement("div");
        div.id = attributeGroupId + "addedValues";
        div.classList.add("selectedAttList");

        attributes[attributeGroupId] = {};
        attributes[attributeGroupId][attributeId] = bez;
    }

    if (!attributes[attributeGroupId].hasOwnProperty(bez)) {
        var span = document.createElement("span");
        var remove = document.createElement("span");

        span.innerHTML = bez;

        remove.innerHTML = "⊖";
        remove.style.cursor = "default";
        remove.addEventListener("click", function(event) {
            var child = event.target.parentNode;
            var parent = event.target.parentNode.parentNode;
            parent.removeChild(child);

            if (attributes[attributeGroupId].hasOwnProperty(attributeId)) {
                delete attributes[attributeGroupId].attributeId;
            }
        }.bind(attributeGroupId), false);

        span.appendChild(remove);
        span.appendChild(document.createElement("br"));
        div.appendChild(span);
        anchor.appendChild(div);

        attributes[attributeGroupId][attributeId] = bez;
    }
}

/*
* Entfernt den Attribute-Matcher, berechnet die Anzahl der Zeilen und mit Hilfe der for Schleife wird der Array generiert;
* Die Tabelle wird mit der passenden Funktion erstellt;
*/
function takeConfiguration() {
    removeElement("htmlForAddingAttributes");

    var y = 1,
        x = Object.keys(attributes).length,
        data = [];
    for (const [key, value] of Object.entries(attributes)) {
        y *= Object.keys(value).length;
    }

    var d = [];
    for (let i = 0; i < x; i++) {
        d[i] = "Test";
    }
    data = matchAttributeArray(objectToArrays(attributes));
    data.unshift(d);

    var table = createTable(y, x, data, true);
    document.getElementById("addAttributeTable").appendChild(table);
    tableAnchor = table;
}

function objectToArrays(attributeObject, toAttributeKeys = false) {
    var attributeArray = [];
    for (const [key, value] of Object.entries(attributeObject)) {
        var tempArray =  [];
        for (const [innerKey, innerValue] of Object.entries(value)) {
            if (toAttributeKeys) {
                tempArray.push(innerKey)
            } else {
                tempArray.push(innerValue);
            }
        }
        attributeArray.push(tempArray);
    }
    return attributeArray;
}

function matchAttributeArray(attributeArray) {
    /* inner function permute, not needed outside of function scope */
    function permute(element, partialArray) {
        /* edge case szenarios */
        if (partialArray.length == 0) {
            return [[]];
        }

        var result =  [];
        for (let i = 0; i < partialArray[0].length; i++) {
            var temp = permute(partialArray[0][i], partialArray.slice(1));
            for (let n = 0; n < temp.length; n++) {
                temp[n].push(partialArray[0][i]);
                result.push(temp[n]);
            }
        }
        return result;
    }

    /* edge case szenarios */
    if (attributeArray.length == 0) {
        return [];
    }

    if (attributeArray.length == 1) {
        return attributeArray;
    }

    var result =  [];
    for (let i = 0; i < attributeArray[0].length; i++) {
        var temp = permute(attributeArray[0][i], attributeArray.slice(1));
        for (let n = 0; n < temp.length; n++) {
            temp[n].push(attributeArray[0][i]);
            result.push(temp[n]);
        }
    }

    return result;
}

function sendAttributeTable() {
    var attribute_string = JSON.stringify(matchAttributeArray(objectToArrays(attributes, true)));
    
    let params = {
        getReason: "insertAttributeTable",
        attributes: attribute_string,
        productId: document.getElementById("productId").dataset.id
    };
    
    var ajax = new AjaxCall(params, "POST", window.location.href);
    ajax.makeAjaxCall(function (response) {
        if (response == "ok")
            infoSaveSuccessfull("success");
    });
}

if (document.readyState !== 'loading' ) {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
