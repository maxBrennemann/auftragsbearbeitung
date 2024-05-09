import { ajax } from "./classes/ajax.js";

/* global variables for attribute selection */
var attributes = {};

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

    const btnAddAttribute = document.getElementById("btnAddAttribute");
    btnAddAttribute.addEventListener("click", () => {
        getHTMLForAttributes();
        const el = document.getElementById("addAttributes");
        el.classList.toggle("hidden");
    });

    const btnToggle = document.getElementById("btnToggle");
    btnToggle.addEventListener("click", () => {
        const el = document.getElementById("addAttributes");
        el.classList.toggle("hidden");
    });

    const btnAttributeGroupSelector = document.getElementById("btnAttributeGroupSelector");
    btnAttributeGroupSelector.addEventListener("click", addToSelector);

    const btnAttributeSelector = document.getElementById("btnAttributeSelector");
    btnAttributeSelector.addEventListener("click", matchAttributeGroups);

    const btnSaveConfig = document.getElementById("btnSaveConfig");
    btnSaveConfig.addEventListener("click", takeConfiguration);

    console.log(generateCombinations({1: [1, 2], 2: [3, 4]}));
}

/**
 * Updates the current product with the given type and content
 * 
 * @param {*} type The field to update
 * @param {*} content The new content
 */
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

/**
 * Loads the attribute groups into the select element
 */
async function getHTMLForAttributes() {
    const attributeGroups = await ajax.get(`/api/v1/attribute/groups`);
    const attributeSelector = document.getElementById("attributeSelector");
    
    attributeGroups.forEach((group) => {
        const option = document.createElement("option");
        option.value = group.id;
        option.innerText = group.attribute_group;
        attributeSelector.appendChild(option);
    });
}

/**
 * Adds the selected attribute(s) to the product
 * by adding new select elements to the DOM
 */
function addToSelector() {
    const attributeSelector = document.getElementById("attributeSelector");
    const selectedAttributeGroups = Array.from(attributeSelector.selectedOptions).map(option => option.value);
    
    attributeSelector.selectedIndex = -1;

    selectedAttributeGroups.forEach(async (attributeGroupId) => {
        const attributeValues = await loadAttributes(attributeGroupId);
        const attributeValueSelector = document.createElement("select");
        attributeValueSelector.multiple = true;
        attributeValueSelector.classList.add("w-28");

        attributeValues.forEach((attribute) => {
            const option = document.createElement("option");
            option.value = attribute.id;
            option.innerText = attribute.value;
            attributeValueSelector.appendChild(option);
        });

        const showAttributeValues = document.getElementById("showAttributeValues");
        showAttributeValues.appendChild(attributeValueSelector);
    });
}

/**
 * Loads the attributes for the given attribute group
 * 
 * @param {*} attributeGroupId 
 * @returns 
 */
async function loadAttributes(attributeGroupId) {
    return await ajax.get(`/api/v1/attribute/group/${attributeGroupId}`);
}

/* adds the attribute value to the product */
function matchAttributeGroups() {
    const anchor = document.getElementById("showAttributeValues");
    const selects = anchor.querySelectorAll("select");
    const groups = {};

    /* select all selected values and generate new array out of it */
    selects.forEach((select) => {
        const selectedValues = Array.from(select.selectedOptions).map(option => option.value);
        const attributeGroupId = select.dataset.id;

        selectedValues.forEach((attributeValueId) => {
            if (!groups[attributeGroupId]) {
                groups[attributeGroupId] = [];
            }

            groups[attributeGroupId].push(attributeValueId);
        });
    });

    const combinations = generateCombinations(groups);
}

function generateCombinations(groups) {
    const combinations = [];

    for (const value of Object.entries(groups)) {

    }

    return combinations;
}

function combineArrays(arr1, arr2) {
    const combinations = [];

    for (let i = 0; i < arr1.length; i++) {
        for (let n = 0; n < arr2.length; n++) {
            combinations.push(arr1[i].concat(arr2[n]));
        }
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

/**
 * 
 */
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
