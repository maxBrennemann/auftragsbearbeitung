
if (document.readyState !== 'loading' ) {
    console.log( 'document is already ready, just execute code here' );
    initializeEditButtons();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        console.log( 'document was not ready, place code here' );
        initializeEditButtons();
    });
}

function initializeEditButtons() {
    var buttons = document.getElementsByTagName("button");

    buttons[0].addEventListener("click", function() {setText(this, 1)}, false);
    buttons[1].addEventListener("click", function() {setText(this, 2)}, false);
    buttons[2].addEventListener("click", function() {setText(this, 3)}, false);
}

function setText(button, id) {
    var node = getEdibleNode(button);
    node.contentEditable = node.isContentEditable ? "false" : "true";

    if (node.contentEditable == "true") {
        button.innerText = "💾";
    } else {
        button.innerText = "✎";
        sendToServer(id, node.innerText);
    }
}

function getEdibleNode(button) {
    return button.parentNode.children[0];
}

function sendToServer(type, content) {
    console.log(type + " " + content);
    if (type == 1 && content.length > 64)
        return;

    var productId = document.getElementById("product-id").innerText;
    var update = new AjaxCall(`getReason=updateProductValues&productId=${productId}&type=${type}&content=${content}`, "POST", window.location.href);
    update.makeAjaxCall(function (response) {
        infoSaveSuccessfull(response);
        console.log(response);
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

    var x = 1,
        y = Object.keys(attributes).length,
        data = [];
    for (const [key, value] of Object.entries(attributes)) {
        x *= Object.keys(value).length;
    }

    data = writeToArray(attributes, x);

    var table = createTable(x, y, data, true);
    document.getElementById("addAttributeTable").appendChild(table);
    tableAnchor = table;
}

function writeToArray(attributes, permutations) {
    data = new Array(permutations);
    var index = 0;
    var counter = 0;
    for (const [key, value] of Object.entries(attributes)) {
        var entries = Object.keys(value).length;
        for (var i = 0; i < permutations / entries; i++) {
            for (const [innerKey, innerValue] of Object.entries(value)) {
                if (index == 0) {
                    data[counter] = new Array(Object.keys(attributes).length);
                }
                data[counter][index] = innerValue;
                counter++;
            }
        }
        counter = 0;
        index++;
    }

    return data;
}

function fillArray() {
    
}

function getAttributeCombinationData() {
    var data = {},
        getSubObj = function() {
            return {
                groesse : 0,
                farbe : 0,
                menge : 0,
                ek : 0,
                price : 0,
                groesseId : 0,
                farbeId : 0
            };
        };

    if (Object.keys(attributes).length === 0 && attributes.constructor === Object) {
        return "--";
    }

    var tr, count = 1, counter = 0, subObj = getSubObj();
    for (var keyColor in attributes[1]) {
        if (attributes[1].hasOwnProperty(keyColor)) {
            tr = tableAnchor.firstChild.childNodes[count]
            for (var keySize in attributes[2]) {
                if (attributes[2].hasOwnProperty(keySize)) {
                    subObj.groesse = attributes[2][keySize];
                    subObj.farbe = attributes[1][keyColor];
                    subObj.menge = tr.childNodes[2].innerText;
                    subObj.ek = tr.childNodes[3].innerText;
                    subObj.price = tr.childNodes[4].innerText;
                    subObj.groesseId = keySize;
                    subObj.farbeId = keyColor;

                    data[counter] = subObj;
                    counter++;
                    subObj = getSubObj();
                }
            }
            count++;
        }
    }

    return data;
}
