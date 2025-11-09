//@ts-nocheck

import { ajax } from "js-classes/ajax.js";
import { addBindings } from "js-classes/bindings.js"
import { notification } from "js-classes/notifications.js";

import { fetchAndRenderTable } from "../classes/table.js";
import { tableConfig } from "../classes/tableconfig.js";
import { initFileUploader } from "../classes/upload.js";
import { createPopup } from "../classes/helpers";

let attributes = {};
const productData = {};
const fnNames = {};

function init() {
    if (!getProductId()) {
        createProductOverviewTable();
        return;
    }

    addBindings(fnNames);

    const productInfo = document.querySelectorAll(".productInfo");
    Array.from(productInfo).forEach((element) => {
        element.addEventListener("change", function (event) {
            const type = event.target.dataset.type;
            const content = event.target.value;
            updateProduct(type, content);
        });
    });

    initFileUploader({
        "product": {
            "location": `/api/v1/product/${productData.productId}/add-files`,
        },
    });
}

const getProductId = () => {
    const urlString = window.location.href;
    const url = new URL(urlString);
    if (!url.searchParams.get("id")) {
        return false;
    }

    productData.productId = url.searchParams.get("id");
    return true;
}

const createProductOverviewTable = () => {
    const config = tableConfig["produkt"];
    const options = {
        "hideOptions": ["all"],
        "primaryKey": config.primaryKey,
        "autoSort": true,
        "styles": {
            "table": {
                "className": ["w-full"],
            },
        },
        "link": "/produkt?id=",
    };

    fetchAndRenderTable("tableContainer", "produkt", options);
}

fnNames.click_addAttributes = () => {
    const template = document.getElementById("addAttributes");
	const div = document.createElement("div");
	div.appendChild(template.content.cloneNode(true));
    const settingsContainer = createPopup(div);
    
    const btnSaveConfig = document.createElement("button");
    btnSaveConfig.classList.add("btn-primary");
    btnSaveConfig.innerHTML = "Speichern";
    btnSaveConfig.addEventListener("click", () => {
        generateTable();
        sendAttributeTable();
    });

    settingsContainer.appendChild(btnSaveConfig);
    getHTMLForAttributes();
    addBindings(fnNames);
}

fnNames.click_btnAttributeGroupSelector = () => {
    const attributeSelector = document.getElementById("attributeSelector");
    const selectedAttributeGroups = Array.from(attributeSelector.selectedOptions).map(option => option.value);

    attributeSelector.selectedIndex = -1;

    selectedAttributeGroups.forEach(async (attributeGroupId) => {
        const attributeValues = await loadAttributes(attributeGroupId);
        const attributeValueSelector = document.createElement("select");
        attributeValueSelector.multiple = true;
        attributeValueSelector.classList.add("w-28");
        attributeValueSelector.dataset.id = attributeGroupId;

        attributeValues.forEach((attribute) => {
            const option = document.createElement("option");
            option.value = attribute.id;
            option.innerText = attribute.value;
            attributeValueSelector.appendChild(option);
        });

        if (attributeValues.length != 0) {
            const showAttributeValues = document.getElementById("showAttributeValues");
            showAttributeValues.appendChild(attributeValueSelector);
        }
    });
}

fnNames.click_btnAttributeSelector = () => {
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

    attributes = generateCombinations(groups);
    const addedValues = document.getElementById("addedValues");
    addedValues.innerHTML += "Tabelle wird erstellt...";
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

    ajax.put(`/api/v1/product/${productData.productId}/type/${type}`, {
        content: content,
    }).then((response) => {
        notification("", response.data.message);
    }).catch((error) => {
        notification("", "failure", JSON.stringify(error));
    });
}

/**
 * Loads the attribute groups into the select element
 */
async function getHTMLForAttributes() {
    const attributeGroups = await ajax.get(`/api/v1/attribute/groups`);
    const attributeSelector = document.getElementById("attributeSelector");

    attributeGroups.data.forEach((group) => {
        const option = document.createElement("option");
        option.value = group.id;
        option.innerText = group.attribute_group;
        attributeSelector.appendChild(option);
    });
}

/**
 * Loads the attributes for the given attribute group
 * 
 * @param {*} attributeGroupId 
 * @returns 
 */
async function loadAttributes(attributeGroupId) {
    const response = await ajax.get(`/api/v1/attribute/group/${attributeGroupId}`);
    return response.data;
}

function generateTable() {
    const table = document.createElement("table");

    for (let i = 0; i < attributes.length; i++) {
        const row = document.createElement("tr");

        for (let n = 0; n < attributes[i].length; n++) {
            const cell = document.createElement("td");
            cell.innerText = attributes[i][n];
            row.appendChild(cell);
        }

        table.appendChild(row);
    }

    const tableContainer = document.getElementById("addAttributeTable");
    tableContainer.innerHTML = "";
    tableContainer.appendChild(table);
}

function generateCombinations(groups) {
    let combinations = [];

    for (const [key, value] of Object.entries(groups)) {
        if (combinations.length === 0) {
            for (let i = 0; i < value.length; i++) {
                combinations.push([value[i]]);
            }
        } else {
            combinations = combineArrays(combinations, value);
        }
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

    return combinations;
}

function sendAttributeTable() {
    ajax.put(`/api/v1/product/${productData.productId}/combinations`, {
        combinations: JSON.stringify(attributes),
    }).then((response) => {
        notification("", response.data);
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
