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

/**
 * delets from size table
 * @param {*} key the table key
 * @param {*} table the table name, used to get the table key
 * @param {*} reference the target
 */
async function deleteRow(key, table, reference) {
    var tableKey = document.querySelector(`[data-type="${table}"]`).dataset.key;
    var data = {
        id: mainVariables.motivId.innerHTML,
        key: key,
        table: tableKey,
    };
    console.log(await send(data, "deleteSize"));
    var row = reference.parentNode.parentNode;
    row.parentNode.removeChild(row);
}

function parseNumber(number) {
    var parts = number.split(",");
    if (parts.length == 2) {
        return parseInt(parts[0]) + 0.1 * parseInt(parts[1]);
    } else {
        return parseInt(parts[0]);
    }
}

async function sendRows(data, text) {
    data.id = mainVariables.motivId.innerHTML;
    data.text = text;

    var response = await send(data, "setAufkleberGroessen", true);
    console.log(response);
}

/** calculates material prices */
const calcMaterial = (width, height) => {
    return (width / 1000) * (height / 1000) * 7;
}

/** calculates height based on ratio */
const calcHeight = (width, ratio) => {
    return width * ratio;
}

/** calculates ratio based on one width and height pair */
const calcRatio = (width, height) => {
    return height / width;
}

class SizeRow {
    constructor(row) {
        this.row = row.children;

        this.id = parseInt(this.row[0].innerHTML);
        this.width = this.cmTomm(this.row[1].innerHTML);
        this.height = this.cmTomm(this.row[2].children[0].value);
        this.price = this.getPriceInCent(this.row[3].innerHTML);
        this.material = calcMaterial(this.height, this.width);
    }

    /* recalculates the height of a row based on ratio */
    recalcHeight(ratio) {
        this.height = this.width * ratio;
    }

    updateMaterial() {
        this.material = calcMaterial(this.height, this.width);
        var materialFormatted = this.formatEuro(this.material);
        this.row[4].innerHTML = materialFormatted;
    }

    updateHoehe() {
        this.row[2].children[0].value = this.formatCM(this.height);
    }

    /*
     * es wird nur die erste Nachkommastelle berücksichtigt
     */
    cmTomm(cm) {
        var parts = cm.split(",");
        if (parts.length == 2) {
            let first = parseInt(parts[0]) * 10;
            let second = parseInt(parts[1][0]);
            if (isNaN(second)) {
                return first;
            } else {
                return first + second;
            }
        } else if (parts.length == 1) {
            return parseInt(parts[0]) * 10;
        }
        return 0;
    }

    getRatio() {
        return calcRatio(this.width, this.height);
    }

    getPriceInCent(price) {
        price = price.replace(",", ".");
        price = parseFloat(price) * 100;
        return parseInt(price);
    }

    formatEuro(param) {
        let temp = ((param * 100) / 100).toFixed(2);
        temp = temp.replace(".", ",");
        return temp + "€";
    }

    formatCM(param) {
        let temp = (param / 10).toFixed(1);
        temp = temp.toString(temp);
        temp = temp.replace(".", ",");
        return temp + "cm";
    }
}

var sizes = [];

/**
 * changes the price class for Aufkleber and Wandtattoo, adds 1€ in price
 * @param {*} e 
 */
async function changePriceclass(e) {
    var newPrice = "";
    if (e.target.id == "price1") {
        newPrice = 0;
    } else if (e.target.id == "price2") {
        newPrice = 1;
    }

    if (newPrice !== "") {
        var response = await send({priceclass: newPrice, id: mainVariables.motivId.innerHTML}, "setPriceclass");
        if (response == "ok") {
            infoSaveSuccessfull("success");
        } else {
            infoSaveSuccessfull();
        }
    }
}

function readSizeTable() {
    var table = document.querySelector("[data-type='module_sticker_sizes']").children[0].children;

    for (let i = 1; i < table.length; i++) {
        var inputSize = createInput(table[i].children[2], i - 1);
        inputSize.addEventListener("input", changeHeight, false);

        let sr = new SizeRow(table[i]);
        sizes.push(sr);

        var inputPrice = createInput(table[i].children[3], i - 1);
        inputPrice.addEventListener("input", changePrice, false);
    }
}

function createInput(tableField, id) {
    var input = document.createElement("input");
    input.classList.add("inputHeight");
    input.dataset.id = id;
    input.value = tableField.innerHTML;
    tableField.innerHTML = "";
    tableField.appendChild(input);

    return input;
}

/**
 * changes the height and material costs of all sizes
 * @param {*} e event
 */
function changeHeight(e) {
    var targetId = parseInt(e.target.dataset.id);
    var size = sizes[targetId];
    size.height = size.cmTomm(e.target.value);
    var ratio = size.getRatio();

    var text = "<br><p>Folie konturgeschnitten, ohne Hintergrund</p>";
    var data = {};
    data.sizes = {};
    var c = 0;

    sizes.forEach((s) => {
        var innerData =  {};
        if (s != size) {
            s.recalcHeight(ratio);
            s.updateHoehe();
        }

        /* adds sizes to data object */
        innerData.width = s.width;
        innerData.height = s.height;
        innerData.price = s.price;
        data.sizes[c] = innerData;
        c++;

        s.updateMaterial();

        text += "<p class=\"breiten\">" + s.formatCM(s.width) + " <span>x " + s.formatCM(s.height) + "</span></p>";
    });

    document.getElementById("previewSizeText").innerHTML = text;
    sendRows(data, text);
}

async function changePrice(e) {
    let targetId = parseInt(e.target.dataset.id);
    let size = sizes[targetId];
    size.price = size.getPriceInCent(e.target.value);

    /* send price data to server */
    let data = {
        id: mainVariables.motivId.innerHTML,
        size: size,
        price: size.price,
        width: size.width,
        height: size.height,
    };
    let success = await send(data, "updateSpecificPrice");
    infoSaveSuccessfull(success);
}

/**
 * this function is called when the table is updated via
 * the addNewLine functionality,
 * the server responds with a new generated table
 */
async function tableUpdateCallback() {
    var data = {
        id: mainVariables.motivId.innerHTML,
    };
    var response = await send(data, "getSizeTable");
    document.getElementById("sizeTableWrapper").innerHTML = response;
    sizes = [];
    readSizeTable();
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

async function changePreiskategorie(e) {
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

async function write_speicherort(e) {
    var content = e.target.value;
    var data = {
        id: mainVariables.motivId.innerHTML,
        content: encodeURIComponent(content)
    };
    var response = await send(data, "writeSpeicherort");
    if (response == "success") {
        infoSaveSuccessfull("success");
    } else {
        console.log(response);
        infoSaveSuccessfull();
    }
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

function bookmark(e) {
    var star = e.target;

    if (star.nodeName == "path") {
        star = star.parentNode;
    }
    var newStar = `<svg onclick="unbookmark(event)" class="bookmarked" viewBox="0 0 24 24"><path fill="currentColor" d="M12,17.27L18.18,21L16.54,13.97L22,9.24L14.81,8.62L12,2L9.19,8.62L2,9.24L7.45,13.97L5.82,21L12,17.27Z" /></svg>`;
    star.parentNode.innerHTML = newStar;
    toggleBookmark();
}

function unbookmark(e) {
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

function changeColor(e) {
    var color = e.target.dataset.color;

    if (svg_elem != null) {
        svg_elem.setAttribute("fill", color);
    }
}

function copyToClipboard(inputname) {
    var input = document.getElementById(inputname);
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

async function write_changeAltTitle(e) {
    let val = e.target.value;
    let type = e.target.dataset.type;
    var response = await send({
        "newTitle": val,
        "type": type,
        "id": mainVariables.motivId.innerHTML,
    }, "setAltTitle");
    if (response == "success") {
        infoSaveSuccessfull("success");
    } else {
        infoSaveSuccessfull();
    }
}

async function exportFacebook() {
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
    let target = e.target;
    let type = target.dataset.type;
    let status = target.dataset.status;

    let result = await send({
        id: mainVariables.motivId.innerHTML,
        type: type,
        status: status,
    }, "productVisibility");
    result = JSON.parse(result);
    infoSaveSuccessfull(result["status"]);

    if (result["icon" == "enabled"]) {
        target.innerHTML = `<svg style="width: 10px; height: 10px;" viewBox="0 0 24 24"><path fill="currentColor" d="M12,9A3,3 0 0,0 9,12A3,3 0 0,0 12,15A3,3 0 0,0 15,12A3,3 0 0,0 12,9M12,17A5,5 0 0,1 7,12A5,5 0 0,1 12,7A5,5 0 0,1 17,12A5,5 0 0,1 12,17M12,4.5C7,4.5 2.73,7.61 1,12C2.73,16.39 7,19.5 12,19.5C17,19.5 21.27,16.39 23,12C21.27,7.61 17,4.5 12,4.5Z" /></svg>`;
    } else {
        target.innerHTML = `<svg style="width:24px;height:24px" viewBox="0 0 24 24"><path fill="currentColor" d="M11.83,9L15,12.16C15,12.11 15,12.05 15,12A3,3 0 0,0 12,9C11.94,9 11.89,9 11.83,9M7.53,9.8L9.08,11.35C9.03,11.56 9,11.77 9,12A3,3 0 0,0 12,15C12.22,15 12.44,14.97 12.65,14.92L14.2,16.47C13.53,16.8 12.79,17 12,17A5,5 0 0,1 7,12C7,11.21 7.2,10.47 7.53,9.8M2,4.27L4.28,6.55L4.73,7C3.08,8.3 1.78,10 1,12C2.73,16.39 7,19.5 12,19.5C13.55,19.5 15.03,19.2 16.38,18.66L16.81,19.08L19.73,22L21,20.73L3.27,3M12,7A5,5 0 0,1 17,12C17,12.64 16.87,13.26 16.64,13.82L19.57,16.75C21.07,15.5 22.27,13.86 23,12C21.27,7.61 17,4.5 12,4.5C10.6,4.5 9.26,4.75 8,5.2L10.17,7.35C10.74,7.13 11.35,7 12,7Z" /></svg>`;
    }
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
