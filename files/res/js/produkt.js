import { ajax } from "./classes/ajax.js";
import { createFileUpload } from "./classes/upload.js";

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
    btnSaveConfig.addEventListener("click", () => {
        generateTable();
        sendAttributeTable();
    });

    const uploadAnchor = document.getElementById("uploadAnchor");
    createFileUpload(uploadAnchor);
    /* <form class="fileUploader mt-2" method="post" enctype="multipart/form-data" data-target="product" name="productUpload">
			Dateien hinzufügen:
			<input type="file" name="uploadedFile" multiple class="hidden">
			<input name="produkt" value="<?= $id ?>" hidden>
		</form>*/
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
    ajax.put(`/api/v1/product/${id}/type/${type}`, {
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
        attributeValueSelector.dataset.id = attributeGroupId;

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

    attributes = generateCombinations(groups);
    const addedValues = document.getElementById("addedValues");
    addedValues.innerHTML += "Tabelle wird erstellt...";
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
    const id = document.getElementById("productId").dataset.id;
    ajax.put(`/api/v1/product/${id}/combinations`, {
        combinations: JSON.stringify(attributes),
    }).then((response) => {
        infoSaveSuccessfull(response);
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
