//@ts-nocheck

import { initTextGeneration } from "../sticker/textGeneration.ts";
import { productConnector } from "../sticker/productConnector.js";

import { } from "../sticker/statsManager.js";
import { addBindings, getVariable } from "js-classes/bindings.js";
import "../sticker/imageMove.js";
import { ajax } from "js-classes/ajax.js";
import { notificatinReplace, notification, notificationLoader } from "js-classes/notifications.js";

import { createPopup } from "../global.js";
import { initImageManager } from "../sticker/imageManager.ts";
import { initSizeTable } from "../sticker/sizeTable.js";
import { initTagManager } from "../sticker/tagManager.ts";

const fnNames = {};

const mainVariables = {
    productConnect: [],
    pending: false,
    overwriteImages: {
        sticker: false,
        walldecal: false,
        textile: false,
    },
    stickerName: "",
};

function initSticker() {
    addBindings(fnNames);
    checkProductErrorStatus();

    mainVariables.motivId = getVariable("motivId");
    mainVariables.stickerName = document.getElementById("stickerName").value;

    initTextiles();
    initSizeTable();
    initTagManager();
    initImageManager();
    initTextGeneration(getStickerId(), getStickerName());
}

fnNames.write_stickerName = e => {
    const title = e.target.value;
    document.title = "b-schriftung - Motiv " + mainVariables.motivId + " " + title;
    mainVariables.stickerName = title;
    ajax.put(`/api/v1/sticker/${mainVariables.motivId}/title`, {
        "title": title,
    }).then(r => {
        if (r.message == "OK") {
            notification("", "success");
        } else {
            notification("", "failure", JSON.stringify(r));
        }
    });
}

fnNames.input_stickerName = e => {
    const title = e.target.value;

    if (title.length > 50) {
        e.target.classList.remove("border-b-gray-600");
        e.target.classList.add("text-red-500", "border-b-red-500");
    } else {
        e.target.classList.remove("text-red-500", "border-b-red-500");
        e.target.classList.add("border-b-gray-600");
    }
}

fnNames.write_creationDate = e => {
    ajax.put(`/api/v1/sticker/${mainVariables.motivId}/creation-date`, {
        "date": e.target.value,
    }).then(r => {
        if (r.message == "OK") {
            notification("", "success");
        } else {
            notification("", "failure", JSON.stringify(r));
        }
    });
}

function initTextiles() {
    const textiles = document.getElementsByClassName("textiles-switches");
    for (let i = 0; i < textiles.length; i++) {
        textiles[i].addEventListener("click", function (e) {
            const target = e.target;
            const id = target.dataset.id;
            const idSticker = mainVariables.motivId.innerHTML;

            ajax.post(`/api/v1/sticker/${idSticker}/textile/${id}/toggle`, {
                status: target.checked,
            }).then(r => {
                notification("", r.status);
            });
        });
    }

    const prices = document.getElementsByClassName("textiles-prices");
    for (let i = 0; i < prices.length; i++) {
        prices[i].addEventListener("change", function (e) {
            const target = e.target;
            const id = target.dataset.id;
            const idSticker = mainVariables.motivId.innerHTML;

            let price = target.value;
            price = price.replace(",", ".");
            price = parseFloat(price);
            price = price * 100;
            price = parseInt(price);

            ajax.post(`/api/v1/sticker/${idSticker}/textile/${id}/price`, {
                price: price,
            }).then(r => {
                notification("", r.status);
            });
        });
    }
}

fnNames.click_transferAufkleber = function () {
    transfer("sticker", "Aufkleber");
}

fnNames.click_transferWandtattoo = function () {
    transfer("walldecal", "Wandtattoo");
}

fnNames.click_transferTextil = function () {
    transfer("textile", "Textil");
}

fnNames.click_transferAll = function (e) {
    transfer("all", "allen Produkten");
}

/**
 * Sends an ajax request to transfer the product to the shop
 * 
 * @param {String} type 
 * @param {String} text 
 * @returns null
 */
function transfer(type, text) {
    if (mainVariables.pending == true) {
        return;
    }

    notificationLoader("various-click", "Wird gespeichert");

    mainVariables.pending = true;

    if (mainVariables.overwriteImages.sticker == true 
        || mainVariables.overwriteImages.walldecal 
        || mainVariables.overwriteImages.textile
    ) {
        if (!confirm("Möchtest Du die Bilder überschreiben?")) {
            mainVariables.overwriteImages.sticker = false;
            mainVariables.overwriteImages.walldecal = false;
            mainVariables.overwriteImages.textile = false;
        }
    }

    ajax.post(`/api/v1/sticker/${mainVariables.motivId}/export-scheduled`, {
        "stickerType": type,
        "overwrite": JSON.stringify(mainVariables.overwriteImages),
    });
}

fnNames.write_productDescription = function (e) {
    var target = e.target.dataset.target;
    var content = e.target.value;
    var textType = e.target.dataset.type;

    ajax.put(`/api/v1/sticker/${mainVariables.motivId}/${textType}/description`, {
        "target": target,
        "content": content,
    }).then(r => {
        if (r.status == "success") {
            notification("", "success");
        } else {
            console.log(r);
            notification("", "failure");;
        }
    });
}

fnNames.write_dirInput = e => {
    ajax.put(`/api/v1/sticker/${getStickerId()}/directory`, {
        "directory": encodeURIComponent(e.target.value),
    }).then(r => {
        if (r.message == "OK") {
            notification("", "success");
        } else {
            notification("", "failure", JSON.stringify(r));
        }
    });
}

fnNames.write_additionalInfo = e => {
    ajax.put(`/api/v1/sticker/${getStickerId()}/additional-info`, {
        "content": e.target.value,
    }).then(r => {
        if (r.message == "OK") {
            notification("", "success");
        } else {
            notification("", "failure", JSON.stringify(r));
        }
    });
}

fnNames.click_bookmark = async function (e) {
    const target = e.currentTarget;
    const type = target.dataset.status;

    let iconNew = "";
    let iconName = "";

    switch (type) {
        case "unmarked":
            iconName = "iconUnbookmark";
            iconNew = await ajax.get(`/api/v1/template/icon/${icon}`, {
                custom: true,
                width: 18,
                height: 18,
                classes: "inline,bookmarked",
            });
            break;
        case "marked":
            iconName = "iconBookmark";
            iconNew = await ajax.get(`/api/v1/template/icon/${icon}`, {
                custom: true,
                width: 18,
                height: 18,
                classes: "inline",
            });
            break;
    }

    target.innerHTML = iconNew.icon;
    toggleBookmark();
}

function toggleBookmark() {
    ajax.post({
        id: mainVariables.motivId.innerHTML,
        r: "toggleBookmark",
    }, true).then(r => {
        if (r == "success") {
            notification("", "success");
        } else {
            console.log(r);
            notification("", "failure");;
        }
    });
}

fnNames.click_changeColor = function (e) {
    var color = e.target.dataset.color;

    if (svg_elem != null) {
        svg_elem.setAttribute("fill", color);
    }
}

fnNames.write_changeAltTitle = function (e) {
    const target = e.target;
    ajax.put(`/api/v1/sticker/${mainVariables.motivId}/${target.dataset.type}/alt-title`, {
        "title": target.value,
    }).then(r => {
        if (r.message == "OK") {
            notification("", "success");
        } else {
            notification("", "failure", JSON.stringify(r));
        }
    });
}

/**
 * toggles the visibility of the product in the store
 * @param {} e 
 */
fnNames.click_toggleProductVisibility = function (e) {
    let target = e.currentTarget;
    let type = target.dataset.type;

    ajax.post({
        id: mainVariables.motivId.innerHTML,
        type: type,
        r: "productVisibility",
    }).then(r => {
        const icon = r.icon;
        notification("", r.status);

        var template = "";
        if (icon == 0) {
            template = document.getElementById("icon-invisible");
        } else {
            template = document.getElementById("icon-visible");
        }

        target.innerHTML = "";
        target.appendChild(template.content.cloneNode(true));
    });
}

fnNames.click_shortcutProduct = async function (e) {
    const target = e.currentTarget;
    const type = target.dataset.type;

    if (mainVariables.productConnect[type]) {
        mainVariables.productconnect[type].show();
    } else {
        const pc = productConnector(type);
        mainVariables.productConnect[type] = await pc.showSearchContainer();
    }
}

var selectedCategories = [];
fnNames.click_chooseCategory = async function () {
    const div = document.createElement("div");
    div.classList.add("z-20", "paddingDefault");

    const innerDiv = document.createElement("div");
    innerDiv.classList.add("my-6", "mr-4", "overflow-y-auto", "h-96");
    div.appendChild(innerDiv);

    div.classList.add("centeredDiv");
    createPopup(div);

    const suggestionBtn = document.createElement("button");
    const applyBtn = document.createElement("button");

    suggestionBtn.classList.add("btn-primary", "mr-2");
    suggestionBtn.innerHTML = "Vorschlag";
    suggestionBtn.addEventListener("click", getSuggestions, false);

    applyBtn.classList.add("btn-primary");
    applyBtn.innerHTML = "Übernehmen";
    applyBtn.addEventListener("click", setCategories, false);

    innerDiv.appendChild(suggestionBtn);
    innerDiv.appendChild(applyBtn);

    await ajax.post({
        categoryId: 13,
        r: "getCategoryTree",
    }).then(categoryData => {
        const ul = document.createElement("ul");
        ul.appendChild(createUlCategoryList(categoryData));

        innerDiv.appendChild(ul);
        createPopup(div);
    });

    ajax.post({
        id: mainVariables.motivId.innerHTML,
        r: "getCategories",
    }).then(r => {
        r.forEach(id => {
            let element = div.querySelector(`[data-id="${id}"]`);
            element.classList.add("selectedCategory");
            selectedCategories.push(id);
        });
    });
}

function createUlCategoryList(element) {
    const ul = document.createElement("ul");
    const li = document.createElement("li");
    li.classList.add("cursor-pointer", "hover:underline", "hover:text-blue-500");
    li.innerHTML = `${element.name} (${element.id})`;
    li.dataset.id = element.id;
    li.addEventListener("click", selectCategory, false);
    ul.appendChild(li);
    element.children.forEach(child => {
        ul.appendChild(createUlCategoryList(child));
    });

    return ul;
}

function selectCategory(e) {
    let target = e.target;
    target.classList.toggle("selectedCategory");
    let id = target.dataset.id;
    id = parseInt(id);

    if (!selectedCategories.includes(id)) {
        selectedCategories.push(id);
    } else {
        var index = selectedCategories.indexOf(id);
        if (index !== -1) {
            selectedCategories.splice(index, 1);
        }
    }
}

function getSuggestions(e) {
    const name = document.getElementById("name").value;
    const div = e.currentTarget.parentNode;
    ajax.post({
        name: "Aufkleber " + name,
        r: "getCategoriesSuggestion",
        id: 13,
    }).then(r => {
        r.categories.forEach(id => {
            let element = div.querySelector(`[data-id="${id}"]`);
            element.classList.add("selectedCategory");
            id = parseInt(id);
            selectedCategories.push(id);
        });
    });
}

function setCategories() {
    ajax.post({
        id: mainVariables.motivId.innerHTML,
        categories: JSON.stringify(selectedCategories),
        r: "setCategories",
    }).then(r => {
        notification("", r.status);
    });
}

fnNames.click_toggleData = e => {
    const id = e.target.id;
    const type = id.replace("toggle_", "");
    ajax.put(`/api/v1/sticker/${getStickerId()}/${type}/toggle`).then(r => {
        if (r.message == "OK") {
            notification("", "success");
        } else {
            notification("", "failure", JSON.stringify(r));
        }
    });
}

fnNames.click_exportToggle = e => {
    ajax.put(`/api/v1/sticker/${getStickerId()}/export-status`, {
        "type": e.target.id,
    }).then(r => {
        if (r.message == "OK") {
            notification("", "success");
        } else {
            notification("", "failure", JSON.stringify(r));
        }
    });
}

function checkProductErrorStatus() {
    if (mainVariables.motivId == null) {
        return;
    }

    ajax.get(`/api/v1/sticker/${mainVariables.motivId.innerHTML}/status`).then(r => {
        if (r.errorStatus == "") {
            return;
        }

        const div = document.createElement("div");
        const p = document.createElement("p");
        p.classList.add("text-red-700", "p-5", "bg-red-200", "rounded-lg", "mt-2");
        p.innerText = r.errorData;

        div.appendChild(p);
        const anchor = document.querySelector(".cont1");
        anchor.parentNode.insertBefore(div, anchor);
    });
}

export const getStickerId = () => {
    return mainVariables.motivId;
}

export const getStickerName = () => {
    return mainVariables.stickerName;
}

if (document.readyState !== 'loading') {
    initSticker();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initSticker();
    });
}
