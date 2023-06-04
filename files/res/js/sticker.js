import { click_textGeneration, click_showTextSettings, click_iterateText } from "./sticker/textGeneration.js";
import { loadTags, showTaggroupManager, addTag } from "./sticker/tagManager.js";
import ProductConnector from "./sticker/productConnector.js";
import { initSVG, moveInit, click_makeColorable } from "./sticker/imageManager.js";
import { readSizeTable } from "./sticker/sizeTable.js";

const fnNames = {};
fnNames.click_makeColorable = click_makeColorable;
fnNames.click_textGeneration = click_textGeneration;
fnNames.click_showTextSettings = click_showTextSettings;
fnNames.click_iterateText = click_iterateText;
const mainVariables = {
    productConnect: [],
    pending: false,
};

/* TODO: besseres variable management */
window.mainVariables = mainVariables;

function initSticker() {
    initSVG();
    initBindings();
    moveInit();

    var pk_dropdown = document.getElementById("preiskategorie_dropdown");
    if (pk_dropdown != null) {
        pk_dropdown.addEventListener("click", preisListenerTextil, false);
        document.getElementById("preiskategorie").addEventListener("click", preisListenerTextil, false);

        document.title = "b-schriftung - Motiv " + mainVariables.motivId.innerHTML + " " + document.getElementById("name").innerHTML;

        /* from sizeTable.js */
        readSizeTable();
    }

    var input = document.getElementById('name');
    input.addEventListener('input', resizeTitle);
    input.addEventListener('change', sendTitle);
    resizeTitle.call(input);

    document.getElementById("creationDate").addEventListener("change", changeDate, false);
}

function initBindings() {
    let bindings = document.querySelectorAll('[data-binding]');
    [].forEach.call(bindings, function(el) {
        var fun_name = "";
        if (el.dataset.fun) {
            fun_name = "click_" + el.dataset.fun;
        } else {
            fun_name = "click_" + el.id;
        }
        
        el.addEventListener("click", function(e) {
            var fun = fnNames[fun_name];
            if (typeof fun === "function") {
                fun(e);
            } else {
                console.warn("event listener may not be defined or wrong");
            }
        }.bind(fun_name), false);
    });
    let variables = document.querySelectorAll('[data-variable]');
    [].forEach.call(variables, function(v) {
        mainVariables[v.id] = v;
    });
    let autowriter = document.querySelectorAll('[data-write]');
    [].forEach.call(autowriter, function(el) {
        var fun_name = "";
        if (el.dataset.fun) {
            fun_name = "write_" + el.dataset.fun;
        } else {
            fun_name = "write_" + el.id;
        }
        
        el.addEventListener("change", function(e) {
            var fun = fnNames[fun_name];
            if (typeof fun === "function") {
                fun(e);
            } else {
                console.warn("event listener may not be defined or wrong");
            }
        }.bind(fun_name), false);
    });
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
            infoSaveSuccessfull("success");
        } else {
            console.log(response);
            infoSaveSuccessfull();
        }
    });
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
            infoSaveSuccessfull("success");
        } else {
            console.log(response);
            infoSaveSuccessfull();
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
            infoSaveSuccessfull("success");
        } else {
            console.log(r);
            infoSaveSuccessfull();
        }
    });
}

function resizeTitle() {
  this.style.width = this.value.length + "ch";
}

fnNames.click_textilClick = function() {
    const infoHandler = new StatusInfoHandler();
    const infoBox = infoHandler.addInfoBox(StatusInfoHandler.TYPE_ERRORCOPY, "Wird gespeichert");
    ajax.post({
        id: mainVariables.motivId.innerHTML,
        r: "toggleTextil"
    }).then(r => {
        if (r.status == "success") {
            infoBox.statusUpdate(StatusInfoHandler.STATUS_SUCCESS);
        }
    }).catch(r => {
        infoBox.statusUpdate(StatusInfoHandler.STATUS_FAILURE, "Fehler bei der Übertragung", r);
    });
}

fnNames.click_wandtattooClick = function() {
    const infoHandler = new StatusInfoHandler();
    const infoBox = infoHandler.addInfoBox(StatusInfoHandler.TYPE_ERRORCOPY, "Wird gespeichert");
    ajax.post({
        id: mainVariables.motivId.innerHTML,
        r: "toggleWandtattoo"
    }).then(r => {
        if (r.status == "success") {
            infoBox.statusUpdate(StatusInfoHandler.STATUS_SUCCESS);
        }
    }).catch(r => {
        infoBox.statusUpdate(StatusInfoHandler.STATUS_FAILURE, "Fehler bei der Übertragung", r);
    });
}

fnNames.click_revisedClick = function() {
    ajax.post({
        id: mainVariables.motivId.innerHTML,
        r: "toggleRevised",
    }, true).then(response => {
        if (response == "success") {
            infoSaveSuccessfull("success");
        } else {
            console.log(response);
            infoSaveSuccessfull();
        }
    });
}

fnNames.click_transferAufkleber = function() {
    if (document.getElementById("previewSizeText").innerHTML == "") {
        alert("Bitte überprüfe Breiten und Preise!");
        return;
    }
    transfer(1, "Aufkleber");
}

fnNames.click_transferWandtattoo = function() {
    transfer(2, "Wandtattoo");
}

fnNames.click_transferTextil = function() {
   transfer(3, "Textil");
}

fnNames.click_transferAll = function(e) {
    transfer(4, "Alles");
}

function transfer(type, text) {
    if (mainVariables.pending == true) {
        return;
    }

    const infoHandler = new StatusInfoHandler();
    const infoBox = infoHandler.addInfoBox(StatusInfoHandler.TYPE_LOADER, "Wird gespeichert");

    mainVariables.pending = true;
    console.log(mainVariables.pending);

    ajax.post({
        id: mainVariables.motivId.innerHTML,
        type: type,
        r: "transferProduct",
    }, true).then(r => {
        mainVariables.pending = false;

        infoBox.statusUpdate(StatusInfoHandler.STATUS_SUCCESS, "Übertragung erfolgreich");
    }).catch(error => {
        infoBox.setType(StatusInfoHandler.TYPE_ERRORCOPY);
        infoBox.statusUpdate(StatusInfoHandler.STATUS_FAILURE, "Übertragung fehlgeschlagen", error);
    });
}

/* todo: größe der neuen daten ergänzen und preise updatebar machen */

function preisListenerTextil() {
    document.getElementById("selectReplacerPreiskategorie").classList.add("selectReplacerShow");
}

/* https://www.w3schools.com/howto/tryit.asp?filename=tryhow_css_js_dropdown */
window.addEventListener("click", function(event) {
    if (!event.target.matches('.selectReplacer') && !event.target.matches('#preiskategorie_dropdown') && !event.target.matches('#preiskategorie')) {
        var dropdowns = document.getElementsByClassName("selectReplacer");
        var i;
        for (i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('selectReplacerShow')) {
                openDropdown.classList.remove('selectReplacerShow');
            }
        }
    }
}, false);

fnNames.click_changePreiskategorie = function(e) {
    var element = document.getElementById("preiskategorie");
    element.value = e.target.innerHTML;
    var kategorieId = e.target.dataset.kategorieId;
    document.getElementById("showPrice").innerHTML = e.target.dataset.defaultPrice + "€";

    ajax.post({
        categoryId: kategorieId,
        id: mainVariables.motivId.innerHTML,
        r: "changePreiskategorie",
    }, true).then(response => {
        if (response == "success") {
            infoSaveSuccessfull("success");
        } else {
            console.log(response);
            infoSaveSuccessfull();
        }
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
            infoSaveSuccessfull("success");
        } else {
            console.log(response);
            infoSaveSuccessfull();
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
            infoSaveSuccessfull("success");
        } else {
            console.log(r);
            infoSaveSuccessfull();
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
            infoSaveSuccessfull("success");
        } else {
            console.log(r);
            infoSaveSuccessfull();
        }
    });
}

fnNames.click_bookmark = function(e) {
    var star = e.target;

    if (star.nodeName == "path") {
        star = star.parentNode;
    }
    var newStar = `<svg onclick="unbookmark(event)" class="bookmarked" viewBox="0 0 24 24"><path fill="currentColor" d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z" /></svg>`;
    star.parentNode.innerHTML = newStar;
    toggleBookmark();
}

fnNames.click_unbookmark = function(e) {
    var star = e.target;

    if (star.nodeName == "path") {
        star = star.parentNode;
    }
    var newStar = `<svg onclick="bookmark(event)" style="width:24px;height:24px; vertical-align:middle;" viewBox="0 0 24 24"><path fill="currentColor" d="M12,15.39L8.24,17.66L9.23,13.38L5.91,10.5L10.29,10.13L12,6.09L13.71,10.13L18.09,10.5L14.77,13.38L15.76,17.66M22,9.24L14.81,8.63L12,2L9.19,8.63L2,9.24L7.45,13.97L5.82,21L12,17.27L18.18,21L16.54,13.97L22,9.24Z" /></svg>`;
    star.parentNode.innerHTML = newStar;
    toggleBookmark();
}

function toggleBookmark() {
    ajax.post({
        id: mainVariables.motivId.innerHTML,
        r: "toggleBookmark",
    }, true).then(r => {
        if (r == "success") {
            infoSaveSuccessfull("success");
        } else {
            console.log(r);
            infoSaveSuccessfull();
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
            infoSaveSuccessfull("success");
        } else {
            infoSaveSuccessfull();
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
        infoSaveSuccessfull(r.status);

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
fnNames.click_chooseCategory = function() {
    ajax.post({
        categoryId: 13,
        r: "getCategoryTree",
    }).then(categoryData => {
        var ul = document.createElement("ul");
        ul.appendChild(createUlCategoryList(categoryData)); 
    
        let div = document.createElement("div");
        div.appendChild(ul);
        div.classList.add("paddingDefault");
        document.body.appendChild(div);
        addActionButtonForDiv(div, "remove");
        div.classList.add("centeredDiv");
        centerAbsoluteElement(div);
    });
}

function createUlCategoryList(element) {
    var ul = document.createElement("ul");
    var li = document.createElement("li");
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
    if (!selectedCategories.includes(id)) {
        selectedCategories.push(id);
    } else {
        var index = selectedCategories.indexOf(id);
        if (index !== -1) {
            selectedCategories.splice(index, 1);
        }
    }
}

fnNames.click_exportToggle = function(e) {
    let exportType = e.target.dataset.value;

    ajax.post({
        id: mainVariables.motivId.innerHTML, 
        export: exportType,
        r: "setExportStatus",
    }).then(() => {
        infoSaveSuccessfull(isSuccessfull);
    });
}

if (document.readyState !== 'loading' ) {
    initSticker();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initSticker();
    });
}
