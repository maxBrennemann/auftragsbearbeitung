/* further reading:
https://stackoverflow.com/questions/24923469/modeling-product-variants
https://dba.stackexchange.com/questions/123467/schema-design-for-products-with-multiple-variants-attributes?newreg=9504cc9890d1461ea745070f28f70543
https://stackoverflow.com/questions/19144200/designing-a-sql-schema-for-a-combination-of-many-to-many-relationship-variation
*/

document.getElementById("selectSource").addEventListener("change", function(event) {
    if (event.target.value == "addNew") {
        getHTMLForAddingSource();
    }
});

function getHTMLForAddingSource() {
    var getHTML = new AjaxCall(`getReason=fileRequest&file=source.html`, "POST", window.location.href);
    getHTML.makeAjaxCall(function (responseHTML) {
        var div = document.createElement("div");
        div.innerHTML = responseHTML;
        div.id = "htmlForAddingSource";
        div.classList.add("ajaxBox");
        document.body.appendChild(div);
        centerAbsoluteElement(div);
        addActionButtonForDiv(div, 'remove');
    });
}

function removeHTMLForAddingSource() {
    var child = document.getElementById("htmlForAddingSource");
    child.parentNode.removeChild(child);
    loadNewSelect();
}

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

function loadNewSelect() {
    var getSelect = new AjaxCall(`getReason=getSelect`, "POST", window.location.href);
    getSelect.makeAjaxCall(function (responseHTML) {
        var select = document.getElementById("selectSource");
        select.innerHTML = responseHTML;
    });
}

function sendSource() {
    var name = document.getElementById("getName").value;
    var desc = document.getElementById("getDesc").value;

    var send = new AjaxCall(`getReason=sendSource&name=${name}&desc=${desc}`, "POST", window.location.href);
    send.makeAjaxCall(function (responseHTML) {
        removeHTMLForAddingSource();
    });
}

/* attribute matcher functions */

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
* Entfernt den Attribute-Matcher, berechnet die Anzahl der Zeilen und mit Hilfe der for Schleife wird der Array generiert (Farben werden mit den Größen gematcht);
* Die Tabelle wird mit der passenden Funktion erstellt;
*/
function takeConfiguration() {
    removeElement("htmlForAddingAttributes");

    var x = Object.keys(attributes[1]).length * Object.keys(attributes[2]).length;
    var data = [
        ["Größe", "Farbe", "Menge", "EK", "Preis"]
    ];

    for (var keyColor in attributes[1]) {
        if (attributes[1].hasOwnProperty(keyColor)) {
            for (var keySize in attributes[2]) {
                if (attributes[2].hasOwnProperty(keySize)) {
                    arrayEl = new Array(5);
                    arrayEl[0] = attributes[2][keySize];
                    arrayEl[1] = attributes[1][keyColor];

                    data.push(arrayEl);
                }
            }
        }
    }

    var table = createTable(x, 5, data, true);
    document.getElementById("addAttributeTable").appendChild(table);
    tableAnchor = table;
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

function saveProduct() {
    var data = JSON.stringify(getAttributeCombinationData()),
        marke = document.getElementsByName("marke")[0].value,
        source = document.getElementById("selectSource"),
        quelle =  source.options[source.selectedIndex].value,
        vkNetto = document.getElementsByName("vk_netto")[0].value,
        ekNetto = document.getElementsByName("ek_netto")[0].value,
        title = document.getElementsByName("short_description")[0].value,
        desc = document.getElementsByName("description")[0].value;

    var send = new AjaxCall(`getReason=saveProduct&attData=${data}&marke=${marke}&quelle=${quelle}&vkNetto=${vkNetto}&ekNetto=${ekNetto}&title=${title}&desc=${desc}`, "POST", window.location.href);
    send.makeAjaxCall(function (responseLink) {
        console.log(responseLink);
        //window.location.href = responseLink;
    });
}
