import { click_textGeneration, click_showTextSettings, click_iterateText } from "./sticker/textGeneration.js";
import ProductConnector from "./sticker/productConnector.js";
import { click_makeColorable, deleteImage, updateImageDescription, updateImageOverwrite } from "./sticker/imageManager.js";
import { } from "./sticker/statsManager.js";
import { initSizeTable } from "./sticker/sizeTable.js";
import { initBindings } from "./classes/bindings.js";
import "./sticker/imageMove.js";
import { ajax } from "./classes/ajax.js";
import initTagManager from "./sticker/tagManager.js";
import { notificatinReplace, notification, notificationLoader } from "./classes/notifications.js";

const fnNames = {
    click_makeColorable: click_makeColorable,
    click_textGeneration: click_textGeneration,
    click_showTextSettings: click_showTextSettings,
    click_iterateText: click_iterateText,
    click_deleteImage: deleteImage,
    write_updateImageDescription: updateImageDescription,
};

const mainVariables = {
    productConnect: [],
    pending: false,
    overwriteImages: {
        aufkleber: false,
        wandtattoo: false,
        textil: false,
    },
};

/* TODO: besseres variable management */
window.mainVariables = mainVariables;
window.updateImageOverwrite = updateImageOverwrite

function initSticker() {
    initBindings(fnNames);
    checkProductErrorStatus();

    if (mainVariables.motivId == null) {
        return;
    }

    document.title = "b-schriftung - Motiv " + mainVariables.motivId.innerHTML + " " + document.getElementById("name").value;

    var input = document.getElementById('name');
    input.addEventListener('input', manageTitle);
    input.addEventListener('change', sendTitle);
    manageTitle.call(input);

    document.getElementById("creationDate").addEventListener("change", changeDate, false);

    initTextiles();
    initSizeTable();
    initTagManager();
}

fnNames.click_toggleCheckbox = async function(e) {
    e.preventDefault();
    var inputNode = e.target.parentNode.children[0];
    inputNode.checked = !inputNode.checked;

    var checked = inputNode.checked == true ? 1 : 0;
    var name = inputNode.id;

    if (name == "plotted") {
        aufkleberPlottClick(e);
    }

    ajax.post({
        json: JSON.stringify({
            [name]: checked,
            name: name,
        }),
        id: mainVariables.motivId.innerHTML,
        r: "setAufkleberParameter",
    }, true).then(response => {
        if (response == "success") {
            notification("", "success");
        } else {
            console.log(response);
            notification("", "failure");;
        }
    });
}

function initTextiles() {
    const textiles = document.getElementsByClassName("textiles-switches");
    for (let i = 0; i < textiles.length; i++) {
        textiles[i].addEventListener("click", function(e) {
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
        prices[i].addEventListener("change", function(e) {
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

function disableInputSlide(input) {
    input.checked = false;
    input.disabled = true;
    input.parentNode.children[1].classList.add("pointer-none");
}

function enableInputSlide(input) {
    input.disabled = false;
    input.parentNode.children[1].classList.remove("pointer-none");
}

/**
 * called from the checkbox "Aufkleber Plott" to enable or disable the input fields for the sticker
 */
function aufkleberPlottClick(e) {
    if (mainVariables.plotted.checked == true) {
        enableInputSlide(mainVariables.short);
        enableInputSlide(mainVariables.long);
        enableInputSlide(mainVariables.multi);
        document.getElementById("transferAufkleber").disabled = false;
    } else {
        disableInputSlide(mainVariables.short);
        disableInputSlide(mainVariables.long);
        disableInputSlide(mainVariables.multi);

        document.getElementById("transferAufkleber").disabled = true;
    }
}

function changeDate(e) {
    ajax.post({
        date: e.target.value,
        id: mainVariables.motivId.innerHTML,
        r: "changeMotivDate",
    }, true).then(response => {
        if (response == "success") {
            notification("", "success");
        } else {
            console.log(response);
            notification("", "failure");;
        }
    });
}

function sendTitle(e) {
    const title = e.target.value;
    ajax.post({
        title: title,
        id: mainVariables.motivId.innerHTML,
        r: "setAufkleberTitle",
    }, true).then(r => {
        if (r == "success") {
            notification("", "success");
        } else {
            console.log(r);
            notification("", "failure");;
        }
    });
}

function manageTitle() {
    var title = this.value;
    this.style.width = title.length + "ch";

    if (title.length > 50) {
        this.classList.remove("border-b-gray-600");
        this.classList.add("text-red-500", "border-b-red-500");
    } else {
        this.classList.remove("text-red-500", "border-b-red-500");
        this.classList.add("border-b-gray-600");
    }
}

fnNames.click_textilClick = function() {
    notificationLoader("textile-click", "Wird gespeichert");

    ajax.post({
        id: mainVariables.motivId.innerHTML,
        r: "toggleTextil"
    }).then(r => {
        if (r.status == "success") {
            notificatinReplace("textile-click", "", "success");

            /* not the best solution, but it works */
            const status = document.getElementById("textil").checked;
            const btn = document.getElementById("transferTextil");

            if (status == true) {
                btn.disabled = false;
            } else {
                btn.disabled = true;
            }
        }
    }).catch(r => {
        notificatinReplace("textile-click", "Fehler bei der Übertragung", "failure", r);
    });
}

fnNames.click_wandtattooClick = function() {
    notificationLoader("wandtatoo-click", "Wird gespeichert");
    ajax.post({
        id: mainVariables.motivId.innerHTML,
        r: "toggleWandtattoo"
    }).then(r => {
        if (r.status == "success") {
            notificatinReplace("wandtatoo-click", "", "success");

            /* not the best solution, but it works */
            const status = document.getElementById("wandtattoo").checked;
            const btn = document.getElementById("transferWandtattoo");

            if (status == true) {
                btn.disabled = false;
            } else {
                btn.disabled = true;
            }
        }
    }).catch(r => {
        notificatinReplace("wandtatoo-click", "Fehler bei der Übertragung", "failure", r);
    });
}

fnNames.click_revisedClick = function() {
    ajax.post({
        id: mainVariables.motivId.innerHTML,
        r: "toggleRevised",
    }, true).then(response => {
        if (response == "success") {
            notification("", "success");
        } else {
            console.log(response);
            notification("", "failure");;
        }
    });
}

fnNames.click_transferAufkleber = function() {
    transfer(1, "Aufkleber");
}

fnNames.click_transferWandtattoo = function() {
    transfer(2, "Wandtattoo");
}

fnNames.click_transferTextil = function() {
   transfer(3, "Textil");
}

fnNames.click_transferAll = function(e) {
    transfer(4, "Allen Produkten");
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
    
    if (mainVariables.overwriteImages.aufkleber == true || mainVariables.overwriteImages.wandtattoo || mainVariables.overwriteImages.textil) {
        if (!confirm("Möchtest du die Bilder überschreiben?")) {
            mainVariables.overwriteImages.aufkleber = false;
            mainVariables.overwriteImages.wandtattoo = false;
            mainVariables.overwriteImages.textil = false;
        }
    }

    ajax.post(`/api/v1/sticker/${mainVariables.motivId.innerHTML}/export`, {
        stickerType: type,
        overwrite: JSON.stringify(mainVariables.overwriteImages),
    }).then(r => {
        if (r.status == "success") {
            mainVariables.pending = false;
            notificatinReplace("various-click", `Übertragung von ${text} erfolgreich`, "success");
        } else {
            mainVariables.pending = false;
            notificatinReplace("various-click", `Übertragung von ${text} erfolgreich`, "success");
        }
    }).catch(error => {
        notificatinReplace("various-click", `Übertragung von ${text} fehlgeschlagen`, "failure", error);
    });
}

fnNames.write_productDescription = function(e) {
    var target = e.target.dataset.target;
    var content = e.target.value;
    var type = e.target.dataset.type;

    ajax.post({
        id: mainVariables.motivId.innerHTML,
        target: target,
        type: type,
        content: content,
        r: "writeProductDescription",
    }, true).then(response => {
        if (response == "success") {
            notification("", "success");
        } else {
            console.log(response);
            notification("", "failure");;
        }
    });
}

fnNames.write_speicherort = function(e) {
    ajax.post({
        id: mainVariables.motivId.innerHTML,
        content: encodeURIComponent(e.target.value),
        r: "writeSpeicherort",
    }, true).then(r => {
        if (r == "success") {
            notification("", "success");
        } else {
            console.log(r);
            notification("", "failure");;
        }
    });
}

fnNames.write_additionalInfo = function(e) {
    var content = e.target.value;
    ajax.post({
        id: mainVariables.motivId.innerHTML,
        content: content,
        r: "writeAdditionalInfo",
    }, true).then(r => {
        if (r == "success") {
            notification("", "success");
        } else {
            console.log(r);
            notification("", "failure");;
        }
    });
}

fnNames.click_bookmark = async function(e) {
    const target = e.currentTarget;
    const type = target.dataset.status;

    let iconNew = "";

    switch (type) {
        case "unmarked":
            iconNew = await ajax.post({
                r: "getIcon",
                custom: true,
                icon: "iconUnbookmark",
                width: 18,
                height: 18,
                classes: "inline,bookmarked",
            });
            break;
        case "marked":
            iconNew = await ajax.post({
                r: "getIcon",
                custom: true,
                icon: "iconBookmark",
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

fnNames.click_changeColor = function(e) {
    var color = e.target.dataset.color;

    if (svg_elem != null) {
        svg_elem.setAttribute("fill", color);
    }
}

fnNames.click_copyToClipboard = function() {
    var input = document.getElementById("dirInput");
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value); 
}

/* must be redone; TODO: nachlesen, wie man event listener richtig bindet */
fnNames.click_addAltTitle = function(e) {
    const node = e.currentTarget.parentNode;
    var input = node.children[0];
    input.classList.toggle("hidden");
}

fnNames.write_changeAltTitle = function(e) {
    ajax.post({
        newTitle: e.target.value,
        type: e.target.dataset.type,
        id: mainVariables.motivId.innerHTML,
        r: "setAltTitle",
    }).then(r => {
        if (r.status == "success") {
            notification("", "success");
        } else {
            notification("", "failure");;
        }
    });
}

/**
 * toggles the visibility of the product in the store
 * @param {} e 
 */
fnNames.click_toggleProductVisibility = function(e) {
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

fnNames.click_shortcutProduct = async function(e) {
    const target = e.currentTarget;
    const type = target.dataset.type;

    if (mainVariables.productConnect[type]) {
        mainVariables.productconnect[type].show();
    } else {
        const productConnector = new ProductConnector(type);
        mainVariables.productConnect[type] = await productConnector.showSearchContainer();
    }
}

var selectedCategories = [];
fnNames.click_chooseCategory = async function() {
    const div = document.createElement("div");
    div.classList.add("z-20", "paddingDefault");
    document.body.appendChild(div);

    const innerDiv = document.createElement("div");
    innerDiv.classList.add("my-6", "mr-4", "overflow-y-auto", "h-96");
    div.appendChild(innerDiv);

    addActionButtonForDiv(div, "remove");
    div.classList.add("centeredDiv");
    centerAbsoluteElement(div);

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
        centerAbsoluteElement(div);
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

fnNames.click_exportToggle = function(e) {
    let exportType = e.target.dataset.value;

    ajax.post({
        id: mainVariables.motivId.innerHTML, 
        export: exportType,
        r: "setExportStatus",
    }).then(() => {
        notification("", isSuccessfull);
    });
}

fnNames.click_makeCustomizable = function(e) {
    ajax.post({
        id: mainVariables.motivId.innerHTML,
        r: "makeCustomizable"
    }).then(r => {
        const svgContainer = document.getElementById("svgContainer");
        svgContainer.data = r.url;
    });
}

fnNames.click_makeForConfig = function(e) {
    ajax.post({
        id: mainVariables.motivId.innerHTML,
        r: "makeForConfig"
    }).then(r => {
        const svgContainer = document.getElementById("svgContainer");
        svgContainer.data = r.url;
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
    return mainVariables.motivId.innerHTML;
}

if (document.readyState !== 'loading' ) {
    initSticker();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initSticker();
    });
}
