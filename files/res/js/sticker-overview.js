var mainVariables = {};

if (document.readyState !== 'loading' ) {
    initStickerOverview();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initStickerOverview();
    });
}

function initStickerOverview() {
    initSVG();
    initBindings();
    addTagEventListeners();

    var pk_dropdown = document.getElementById("preiskategorie_dropdown");
    if (pk_dropdown != null) {
        pk_dropdown.addEventListener("click", preisListenerTextil, false);
        document.getElementById("preiskategorie").addEventListener("click", preisListenerTextil, false);

        document.title = "b-schriftung - Motiv " + mainVariables.motivId.innerHTML + " " + document.getElementById("name").innerHTML;

        readSizeTable();

        const contextMenu = document.getElementById("delete-menu");
        const scope = document.querySelector("body");
        scope.addEventListener("contextmenu", (event) => {
            if (event.target.dataset.deletable != null) {
                mainVariables.currentDelete = event.target.dataset.imageId;
                event.preventDefault();

                const { clientX: mouseX, clientY: mouseY } = event;
        
                contextMenu.style.top = `${mouseY}px`;
                contextMenu.style.left = `${mouseX}px`;
        
                contextMenu.classList.add("visible");
            }
        });

        scope.addEventListener("click", (e) => {
            if (e.target.offsetParent != contextMenu) {
            contextMenu.classList.remove("visible");
            }
        });
    }
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

async function click_editName(e) {
    e.target.innerHTML = e.target.innerHTML == "✔" ? "✎" : "✔";
    if (e.target.innerHTML == "✔") {
        document.getElementById("name").contentEditable = 'true';
    } else {
        document.getElementById("name").contentEditable = 'false';
    }
    document.getElementById("name").classList.toggle("contentEditable");

    var data = {
        title: document.getElementById("name").innerHTML,
        id: mainVariables.motivId.innerHTML,
    }
    var response = await send(data, "setAufkleberTitle");
    if (response == "success") {
        infoSaveSuccessfull("success");
    } else {
        console.log(response);
        infoSaveSuccessfull();
    }
}

async function click_textilClick() {
    var data =  {
        id: mainVariables.motivId.innerHTML,
    };
    var response = await send(data, "toggleTextil");
    if (response == "success") {
        infoSaveSuccessfull("success");
    } else {
        console.log(response);
        infoSaveSuccessfull();
    }
}

async function click_wandtattooClick() {
    var data =  {
        id: mainVariables.motivId.innerHTML,
    };
    var response = await send(data, "toggleWandtattoo");
    if (response == "success") {
        infoSaveSuccessfull("success");
    } else {
        console.log(response);
        infoSaveSuccessfull();
    }
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
    transfer(1);
}

function click_transferWandtattoo() {
    transfer(2);
}

function click_transferTextil() {
   transfer(3);
}

async function transfer(type) {
    document.getElementById("productLoader" + type).style.display = "inline";
    var data = {
        id: mainVariables.motivId.innerHTML,
        type: type
    };
    var response = await send(data, "transferProduct");
    console.log(response);

    document.getElementById("productLoader" + type).style.display = "none";
}

function send(data, intent, json = false) {
    data.getReason = intent;

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

/**
 * deletes the currently selected image
 */
async function deleteImage(imageId) {
    if (imageId == -1) {
       imageId = mainVariables.currentDelete;
    } else {
        imageId = document.querySelector(".imageBig");
        imageId = imageId.dataset.imageId;
    }

    var data = {
        imageId: imageId,
    };
    var response = await send(data, "deleteImage");
    infoSaveSuccessfull(response); 
}



/**
 * changes all data for main image
 * @param {Event} e 
 */
function changeImage(e) {
    var main = document.querySelector(".imageBig");
    main.src = e.target.src;
    main.dataset.imageId = e.target.dataset.imageId;

    document.getElementById("aufkleberbild").checked = e.target.dataset.isAufkleber == 0 ? false : true;
    document.getElementById("wandtattoobild").checked = e.target.dataset.isWandtattoo == 0 ? false : true;
    document.getElementById("textilbild").checked = e.target.dataset.isTextil == 0 ? false : true;
}

async function changeImageParameters(e) {
    var main = document.querySelector(".imageBig");
    var is_aufkleber = document.getElementById("aufkleberbild").checked == true ? 1 : 0;
    var is_wandtatto = document.getElementById("wandtattoobild").checked == true ? 1 : 0;
    var is_textil = document.getElementById("textilbild").checked == true ? 1 : 0;

    var data = {
        is_aufkleber: is_aufkleber,
        is_wandtatto: is_wandtatto,
        is_textil: is_textil,
        id_image: main.dataset.imageId,
    }
    var response = await send(data, "changeImageParameters");
    if (response == "success") {
        infoSaveSuccessfull("success");
    } else {
        console.log(response);
        infoSaveSuccessfull();
    }
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

function readSizeTable() {
    var table = document.querySelector("[data-type='module_sticker_sizes']").children[0].children;

    for (let i = 1; i < table.length; i++) {
        var input = document.createElement("input");
        input.classList.add("inputHeight");
        input.dataset.id = i - 1;
        input.value = table[i].children[2].innerHTML;
        table[i].children[2].innerHTML = "";
        table[i].children[2].appendChild(input);

        let sr = new SizeRow(table[i]);
        sizes.push(sr);

        input.addEventListener("input", changeHeight, false);
    }
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

var svg_elem;
function initSVG() {
    var a = document.getElementById("svgContainer");
    if (a != null || a!= undefined) {
        a.addEventListener("load", loadSVGEvent, false);

        if (a.contentDocument != null) {
            a.removeEventListener("load", loadSVGEvent);
            var svgDoc = a.contentDocument;
            svg_elem = svgDoc.getElementById("svg_elem");
            adjustSVG();
        }
    }
}

function loadSVGEvent() {
    var a = document.getElementById("svgContainer");
    var svgDoc = a.contentDocument;
    svg_elem = svgDoc.getElementById("svg_elem");
    adjustSVG();
}

function adjustSVG() {
    if (svg_elem != null) {
        let children = svg_elem.children;

        let positions = {
            furthestX: 0,
            nearestX: 0,
            furthestY: 0,
            nearestY: 0,

            edited: false,
        }

        for (let i = 0; i< children.length; i++) {
            let child = children[i];
            if (child.getBBox() && child.nodeName != "defs") {
                var coords = child.getBBox();
                if (positions.edited == false) {
                    positions.furthestX = coords.x + coords.width;
                    positions.furthestY = coords.y + coords.height;
                    positions.nearestX = coords.x;
                    positions.nearestY = coords.y;

                    positions.edited = true;
                } else {
                    if (coords.x < positions.nearestX) {
                        positions.nearestX = coords.x;
                    }
                    if (coords.y < positions.nearestY) {
                        positions.nearestY = coords.y;
                    }
                    if (coords.x + coords.width > positions.furthestX) {
                        positions.furthestX = coords.x + coords.width;
                    }
                    if (coords.y + coords.height > positions.furthestY) {
                        positions.furthestY = coords.y + coords.height;
                    }
                }
            }
        }

        let width = positions.furthestX - positions.nearestX;
        let height = positions.furthestY - positions.nearestY;

        svg_elem.setAttribute("viewBox", `${positions.nearestX} ${positions.nearestY} ${width} ${height}`);
    }
}

async function click_makeColorable() {
    var data = {
        id: mainVariables.motivId.innerHTML,
    };
    var svg_url = await send(data, "makeSVGColorable");
    console.log(svg_url);
}

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
        content: content
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

async function click_transferAll(e) {
    var id = e.target.dataset.id;
    document.getElementsByClassName("productLoader")[id].style.display = "inline";
    var data = {
        id: mainVariables.motivId.innerHTML,
        type: "all"
    };
    var response = await send(data, "transferProduct");
    console.log(response);

    document.getElementsByClassName("productLoader")[id].style.display = "none";
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

function loadTags() {
    // load more cached tags from server, if none available, print: no more suggestions found
}

function addTag(event) {
    if (event.key === ';') {
        var dt = document.createElement("dt");
        dt.innerHTML = event.target.value;
        event.target.parentNode.children[0].appendChild(dt);
        event.target.value = "";
        event.preventDefault();
    }
}

async function manageTag(event) {
    let element = event.target;
    if (element.classList.contains("remove")) {
        var parent = element.parentNode.parentNode;
        var child = element.parentNode;

        if (element.parentNode.classList.contains("suggestionTag")) {
            parent.removeChild(child);
            return;
        }

        var response = await send({id: mainVariables.motivId.innerHTML, tag: child.childNodes[0].textContent}, "removeTag");
        console.log(response);
        if (response == "") {
            parent.removeChild(child);
        }
    } else if (element.classList.contains("suggestionTag")) {
        element.classList.remove("suggestionTag");
        var response = await send({id: mainVariables.motivId.innerHTML, tag: event.target.childNodes[0].textContent}, "addTag");
        console.log(response);
    }
}

function addTagEventListeners() {
    var dts = document.querySelectorAll("dt");
    for (let i = 0; i < dts.length; i++) {
        let node = dts[i];
        node.addEventListener("click", manageTag);
    };
}

var responseLength = 0;
function crawlAll(e) {
    e.preventDefault();
    document.getElementById("crawlAll").style.display = "inline";

    var ajaxCall = new XMLHttpRequest();
    ajaxCall.onload = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("loaderCrawlAll").style.display = "none";
        }
    }
    ajaxCall.onprogress = function() {
        /* https://stackoverflow.com/questions/42838609/how-to-flush-php-output-buffer-properly */
        if (this.readyState == 3) {
            let json = this.responseText.substring(responseLength);
            responseLength = this.responseText.length;
            json = json.replace(/ /g,'');
            json = JSON.parse(json);
            if (json.products) {
                document.getElementById("productProgress").max = json.products;
                document.getElementById("maxProgress").innerHTML = json.products;
            } else if (json.shopId) {
                document.getElementById("productProgress").value = json.count;
                document.getElementById("currentProgress").innerHTML = json.count;
                if (json.existing) {
                    document.getElementById("statusProgress").innerHTML = "wurde schon gecrawlt";
                } else {
                    document.getElementById("statusProgress").innerHTML = "neu angelegt oder geupdatet";
                }
            }
        }
    }
    ajaxCall.open("POST", "", true);
    ajaxCall.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajaxCall.send("getReason=crawlAll");
}

function changeColor(e) {
    var color = e.target.dataset.color;

    if (svg_elem != null) {
        svg_elem.setAttribute("fill", color);
    }
}
