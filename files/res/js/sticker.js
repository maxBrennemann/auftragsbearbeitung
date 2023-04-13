import click_textGeneration from "./sticker/textGeneration.js";
import {initTagManager, loadTags, showTaggroupManager, addTag} from "./sticker/tagManager.js";
import ProductConnector from "./sticker/productConnector.js";
import {initSVG, moveInit} from "./sticker/imageManager.js";

var mainVariables = {
    productConnect: [],
    pending: false,
};

if (document.readyState !== 'loading' ) {
    initSticker();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initSticker();
    });
}

function initSticker() {
    initSVG();
    initBindings();
    initTagManager();
    moveInit();

    var pk_dropdown = document.getElementById("preiskategorie_dropdown");
    if (pk_dropdown != null) {
        pk_dropdown.addEventListener("click", preisListenerTextil, false);
        document.getElementById("preiskategorie").addEventListener("click", preisListenerTextil, false);

        document.title = "b-schriftung - Motiv " + mainVariables.motivId.innerHTML + " " + document.getElementById("name").innerHTML;

        readSizeTable();
    }

    var input = document.getElementById('name');
    input.addEventListener('input', resizeTitle);
    input.addEventListener('change', sendTitle);
    resizeTitle.call(input);
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
            var fun = window[fun_name];
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
            var fun = window[fun_name];
            if (typeof fun === "function") {
                fun(e);
            } else {
                console.warn("event listener may not be defined or wrong");
            }
        }.bind(fun_name), false);
    });
}

async function click_toggleCheckbox(e) {
    e.preventDefault();
    var inputNode = e.target.parentNode.children[0];
    inputNode.checked = !inputNode.checked;

    var checked = inputNode.checked == true ? 1 : 0;
    var name = inputNode.id;
    var data =  {
        json: JSON.stringify({
            [name]: checked,
            name: name,
        }),
        id: mainVariables.motivId.innerHTML,
    };

    if (name == "plotted") {
        aufkleberPlottClick(e);
    }

    var response = await send(data, "setAufkleberParameter");
    if (response == "success") {
        infoSaveSuccessfull("success");
    } else {
        console.log(response);
        infoSaveSuccessfull();
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

async function changeDate(e) {
    var data = {
        date: e.target.value,
        id: mainVariables.motivId.innerHTML,
    }
    var response = await send(data, "changeMotivDate");
    if (response == "success") {
        infoSaveSuccessfull("success");
    } else {
        console.log(response);
        infoSaveSuccessfull();
    }
}

async function sendTitle() {
    title = this.value;
    var data = {
        title: title,
        id: mainVariables.motivId.innerHTML,
    };

    var response = await send(data, "setAufkleberTitle");
    if (response == "success") {
        infoSaveSuccessfull("success");
    } else {
        console.log(response);
        infoSaveSuccessfull();
    }
}

function resizeTitle() {
  this.style.width = this.value.length + "ch";
}

async function click_textilClick() {
    const statusInfo = new StatusInfo("", "");
    ajax.post({
        id: mainVariables.motivId.innerHTML,
        r: "toggleTextil"
    }).then(r => {
        if (r.status == "success") {
            infoSaveSuccessfull("success");
        }
    }).catch(r => {
        statusInfo.setText(r);
        statusInfo.showError();
    });
}

async function click_wandtattooClick() {
    const statusInfo = new StatusInfo("", "");
    ajax.post({
        id: mainVariables.motivId.innerHTML,
        r: "toggleWandtattoo"
    }).then(r => {
        if (r.status == "success") {
            infoSaveSuccessfull("success");
        }
    }).catch(r => {
        statusInfo.setText(r);
        statusInfo.showError();
    });
}

async function click_revisedClick() {
    var data =  {
        id: mainVariables.motivId.innerHTML,
    };
    var response = await send(data, "toggleRevised");
    if (response == "success") {
        infoSaveSuccessfull("success");
    } else {
        console.log(response);
        infoSaveSuccessfull();
    }
}

function click_transferAufkleber() {
    if (document.getElementById("previewSizeText").innerHTML == "") {
        alert("Bitte überprüfe Breiten und Preise!");
        return;
    }
    transfer(1, "Aufkleber");
}

function click_transferWandtattoo() {
    transfer(2, "Wandtattoo");
}

function click_transferTextil() {
   transfer(3, "Textil");
}

function click_transferAll(e) {
    transfer(4, "Alles");
}

function transfer(type, text) {
    if (mainVariables.pending == true) {
        return;
    }

    let statusInfo = new StatusInfo("", `${text} wird übertragen`);
    statusInfo.show();
    mainVariables.pending = true;

    console.log(mainVariables.pending);

    ajax.post({
        id: mainVariables.motivId.innerHTML,
        type: type,
        r: "transferProduct",
    }, true).then(r => {
        mainVariables.pending = false;
        console.log(r);

        statusInfo.statusUpdate(`${text} ist übertragen`);
        statusInfo.hide();
    });
}

function send(data, intent = "", json = false) {
    if (intent == null) {
        data.getReason = data.r;
    } else {
        data.getReason = intent;
    }

    if (json) {
        paramString = "getReason=" + intent + "&json=" + JSON.stringify(data);
    } else {
        /* temporarily copied here */
        let temp = "";
        for (let key in data) {
            temp += key + "=" + data[key] + "&";
        }

        paramString = temp.slice(0, -1);
    }

    var response = makeAsyncCall("POST", paramString, "").then(result => {
        return result;
    });

    if (intent == null) {
        return JSON.parse(response);
    }

    return response;
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

async function click_changePreiskategorie(e) {
    var element = document.getElementById("preiskategorie");
    element.value = e.target.innerHTML;
    var kategorieId = e.target.dataset.kategorieId;
    document.getElementById("showPrice").innerHTML = e.target.dataset.defaultPrice + "€";

    var data = {
        categoryId: kategorieId,
        id: mainVariables.motivId.innerHTML,
    };
    var response = await send(data, "changePreiskategorie");
    if (response == "success") {
        infoSaveSuccessfull("success");
    } else {
        console.log(response);
        infoSaveSuccessfull();
    }
}

async function write_productDescription(e) {
    var target = e.target.dataset.target;
    var content = e.target.value;
    var type = e.target.dataset.type;

    var data = {
        id: mainVariables.motivId.innerHTML,
        target: target,
        type: type,
        content: content
    };
    var response = await send(data, "writeProductDescription");
    if (response == "success") {
        infoSaveSuccessfull("success");
    } else {
        console.log(response);
        infoSaveSuccessfull();
    }
}

function write_speicherort(e) {
    ajax.post({
        id: mainVariables.motivId.innerHTML,
        content: encodeURIComponent(e.target.value),
        r: "writeSpeicherort",
    }, true).then(r => {
        if (r == "success") {
            infoSaveSuccessfull("success");
        } else {
            console.log(response);
            infoSaveSuccessfull();
        }
    });
}

async function write_additionalInfo(e) {
    var content = e.target.value;
    var data = {
        id: mainVariables.motivId.innerHTML,
        content: content
    };
    var response = await send(data, "writeAdditionalInfo");
    if (response == "success") {
        infoSaveSuccessfull("success");
    } else {
        console.log(response);
        infoSaveSuccessfull();
    }
}

function click_bookmark(e) {
    var star = e.target;

    if (star.nodeName == "path") {
        star = star.parentNode;
    }
    var newStar = `<svg onclick="unbookmark(event)" class="bookmarked" viewBox="0 0 24 24"><path fill="currentColor" d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z" /></svg>`;
    star.parentNode.innerHTML = newStar;
    toggleBookmark();
}

function click_unbookmark(e) {
    var star = e.target;

    if (star.nodeName == "path") {
        star = star.parentNode;
    }
    var newStar = `<svg onclick="bookmark(event)" style="width:24px;height:24px; vertical-align:middle;" viewBox="0 0 24 24"><path fill="currentColor" d="M12,15.39L8.24,17.66L9.23,13.38L5.91,10.5L10.29,10.13L12,6.09L13.71,10.13L18.09,10.5L14.77,13.38L15.76,17.66M22,9.24L14.81,8.63L12,2L9.19,8.63L2,9.24L7.45,13.97L5.82,21L12,17.27L18.18,21L16.54,13.97L22,9.24Z" /></svg>`;
    star.parentNode.innerHTML = newStar;
    toggleBookmark();
}

async function toggleBookmark() {
    var data =  {
        id: mainVariables.motivId.innerHTML,
    };
    var response = await send(data, "toggleBookmark");

    if (response == "success") {
        infoSaveSuccessfull("success");
    } else {
        console.log(response);
        infoSaveSuccessfull();
    }
}

function click_changeColor(e) {
    var color = e.target.dataset.color;

    if (svg_elem != null) {
        svg_elem.setAttribute("fill", color);
    }
}

function click_copyToClipboard() {
    var input = document.getElementById("dirInput");
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value); 
}

async function performAction(key, event) {
    let tableKey = document.querySelector('[data-type="module_sticker_sizes"]').dataset.key;
    let data = {
        row: key,
        table: tableKey,
        id:  mainVariables.motivId.innerHTML,
    };

    let newPrice = await send(data, "resetStickerPrice");
    let priceRow = event.target.parentNode.parentNode;
    let priceField = priceRow.children[3].chilren[0];

    priceField.value = newPrice;
    /* TODO: über sizes variable ändern */
}

/* must be redone; TODO: nachlesen, wie man event listener richtig bindet */
function click_addAltTitle() {
    var node = this.event.currentTarget;
    var parent = node.parentNode;
    var input = parent.children[0];
    input.classList.toggle("visible");
}

function write_changeAltTitle(e) {
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

async function click_exportFacebook() {
    var response = await send({
        "id": mainVariables.motivId.innerHTML,
    }, "exportFacebook");
    console.log(response);
}

/**
 * toggles the visibility of the product in the store
 * @param {} e 
 */
async function click_toggleProductVisibility(e) {
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

async function click_shortcutProduct(e) {
    const target = e.currentTarget;
    const type = target.dataset.type;

    if (mainVariables.productConnect[type]) {
        mainVariables.productconnect[type].show();
    } else {
        const productConnector = new ProductConnector(type);
        mainVariables.productConnect[type] = await productConnector.showSearchContainer();
    }
}

async function searchShop() {
    let searchQuery = document.getElementById("searchShopQuery").value;
    let results = await send({query: searchQuery}, "searchShop");

    let appendTo = document.getElementById("showSearchResults");

    results = JSON.parse(results);
    results.forEach((value) => {
        let link = document.createElement("a");
        link.href = value.link;
        link.innerHTML = value.name;

        let span = document.createElement("span");
        span.appendChild(document.createTextNode(`Artikel ${value.id}: `));
        span.appendChild(link);

        let label = document.createElement("label");
        label.style.display = "block";
        let checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        label.appendChild(checkbox);
        label.appendChild(span);

        appendTo.appendChild(label);
    });
}

async function connectResults() {

}

var selectedCategories = [];

async function click_chooseCategory() {
    var categoryData = await send({categoryId: 13}, "getCategoryTree");
    categoryData = JSON.parse(categoryData);

    var ul = document.createElement("ul");
    ul.appendChild(createUlCategoryList(categoryData)); 

    let div = document.createElement("div");
    div.appendChild(ul);
    div.classList.add("paddingDefault");
    document.body.appendChild(div);
    addActionButtonForDiv(div, "remove");
    div.classList.add("centeredDiv");
    centerAbsoluteElement(div);
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

async function click_exportToggle(e) {
    let exportType = e.target.dataset.value;
    let isSuccessfull = await send({
        id: mainVariables.motivId.innerHTML, 
        export: exportType
    }, "setExportStatus");
    infoSaveSuccessfull(isSuccessfull);
}
