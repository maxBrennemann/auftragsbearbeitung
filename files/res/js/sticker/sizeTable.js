/**
 * delets from size table
 * @param {*} key the table key
 * @param {*} table the table name, used to get the table key
 * @param {*} reference the target
 */
function deleteRow(key, table, reference) {
    var tableKey = document.querySelector(`[data-type="${table}"]`).dataset.key;

    ajax.post({
        id: mainVariables.motivId.innerHTML,
        key: key,
        table: tableKey,
        r: "deleteSize",
    }, true).then(response => {
        console.log(response);
        var row = reference.parentNode.parentNode;

        /* delete row from SizeTable */
        sizeTable.delete(reference);

        row.parentNode.removeChild(row);
    });
}

function performAction(key, event) {
    let tableKey = document.querySelector('[data-type="module_sticker_sizes"]').dataset.key;
    ajax.post({
        row: key,
        table: tableKey,
        id:  mainVariables.motivId.innerHTML,
        r: "resetStickerPrice",
    }, true).then(newPrice => {
        let priceRow = event.target.parentNode.parentNode;
        let priceField = priceRow.children[3].chilren[0];

        priceField.value = newPrice;
    });
    /* TODO: über sizes variable ändern */
}

/* TODO: überlegen, ob das so okay ist */
window["deleteRow"] = deleteRow;
window["performAction"] = performAction;

function parseNumber(number) {
    var parts = number.split(",");
    if (parts.length == 2) {
        return parseInt(parts[0]) + 0.1 * parseInt(parts[1]);
    } else {
        return parseInt(parts[0]);
    }
}

function sendRows(data, text) {
    ajax.post({
        sizes: JSON.stringify(data),
        id: mainVariables.motivId.innerHTML,
        text: text,
        r: "setAufkleberGroessen",
    }, true).then(response => {
        console.log(response);
    });
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

    ajax.post({
        priceclass: newPrice,
        id: mainVariables.motivId.innerHTML,
        r: "setPriceclass",
    }, true).then(response => {
        if (response == "ok") {
            infoSaveSuccessfull("success");
        } else {
            infoSaveSuccessfull();
        }
    });

    sizeTable.setDifficulty(newPrice);
}

export function readSizeTable() {
    /*var table = document.querySelector("[data-type='module_sticker_sizes']").children[0].children;

    for (let i = 1; i < table.length; i++) {
        var inputSize = createInput(table[i].children[2], i - 1);
        inputSize.addEventListener("input", changeHeight, false);

        let sr = new SizeRow(table[i]);
        sizes.push(sr);

        var inputPrice = createInput(table[i].children[3], i - 1);
        inputPrice.addEventListener("input", changePrice, false);
    }*/
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

function changePrice(e) {
    let targetId = parseInt(e.target.dataset.id);
    let size = sizes[targetId];
    size.price = size.getPriceInCent(e.target.value);

    /* send price data to server */
    ajax.post({
        id: mainVariables.motivId.innerHTML,
        size: size,
        price: size.price,
        width: size.width,
        height: size.height,
        r: "updateSpecificPrice",
    }, true).then(response => {
        infoSaveSuccessfull(response);
    });
}

/**
 * this function is called when the table is updated via
 * the addNewLine functionality,
 * the server responds with a new generated table
 */
function tableUpdateCallback() {
    ajax.post({
        id: mainVariables.motivId.innerHTML,
        r: "getSizeTable",
    }, true).then(response => {
        document.getElementById("sizeTableWrapper").innerHTML = response;
        initSizeTable();
    });
}

window["tableUpdateCallback"] = tableUpdateCallback;
var sizeTable;

function initSizeTable() {
    const tbl = document.querySelector("[data-type='module_sticker_sizes']");
    sizeTable = new SizeTable(tbl);

    const price1 = document.getElementById("price1");
    price1.addEventListener("click", changePriceclass, false);
    const price2 = document.getElementById("price2");
    price2.addEventListener("click", changePriceclass, false);
}

class SizeTable {

    constructor(tbl) {
        this.table = tbl;
        this.sizeTableRows = [];
        this.ratio = 0;

        this.difficulty = this.getInitDifficulty();
        this.parseTable();
        this.addListeners()
    }

    addListeners() {
        const addNewLineBtn = document.getElementById("sizeTableWrapper").querySelector(".addToTable");
        addNewLineBtn.addEventListener("click", this.add.bind(this));
    }

    parseTable() {
        let rows = this.table.rows;
        rows = Array.from(rows);
        rows.shift();
        rows.forEach(row => {
            const parsedRow = new SizeTableRow(row, this);
            this.sizeTableRows.push(parsedRow)
        }, this);
    }

    iterateAll(ratio, noNewHeight) {
        this.ratio = ratio;
        console.log(ratio);
        this.sizeTableRows.forEach(row => {
            if (row != noNewHeight) {
                row.setNewHeight(ratio);
            }
            row.setNewPrice();
            row.setNewPurchasePrice();
        });
    }

    getInitDifficulty() {
        const el = document.getElementById("price1");
        if (el.checked) {
            return 0;
        }
        return 1;
    }

    setDifficulty(diff) {
        this.difficulty = diff;
    }

    add() {
        const lastChild = this.table.lastChild;
        lastChild.children[2].contenteditable = false;
        lastChild.children[4].contenteditable = false;
        // TODO: implement 
    }

    delete(ref) {
        var index = this.sizeTableRows.indexOf(ref);
        if (index > -1) {
            arr.splice(index, 1);
        }
    }

    generatePreview() {
        this.text = "<br><p>Folie konturgeschnitten, ohne Hintergrund</p>";
        this.sizeTableRows.forEach(row => {
            this.text += "<p class=\"breiten\">" + row.formatCentimeters(row.width / 10) + " <span>x " + row.formatCentimeters(row.height / 10) + "</span></p>";
        });
        document.getElementById("previewSizeText").innerHTML = this.text;
    }

    reset() {
        
    }

    sendToServer() {
        const data = {};
        data.sizes = {};
        let count = 0;
        this.sizeTableRows.forEach(row => {
            const rowData = {
                width: row.width,
                height: row.height,
                price: row.price
            };
            data.sizes[count] = rowData;
            count++;
        });

        ajax.post({
            sizes: JSON.stringify(data),
            id: mainVariables.motivId.innerHTML,
            text: this.text,
            r: "setAufkleberGroessen",
        }, true).then(response => {
            console.log(response);
        });
    }

}

/**
 * extracts the data of each row,
 * converts the values into cents and millimeters,
 * update functions to change the values when a value is changed
 */
class SizeTableRow {

    constructor(row, parent) {
        this.row = row;
        this.id = parseInt(row.children[0].innerHTML);
        this.width = parseFloat(row.children[1].innerHTML) * 10;
        this.height = parseFloat(row.children[2].innerHTML) * 10;
        this.price = this.parsePrice(row.children[3]);
        this.purchasePrice = this.parsePrice(row.children[4]);

        this.parent = parent;

        this.addListeners();
    }

    formatCentimeters(value) {
        let cm = value.toFixed(1);
        cm = cm.toString(cm);
        cm = cm.replace(".", ",");
        return cm + "cm";
    }

    formatEuro(value) {
        let euro = value.toFixed(2);
        euro = euro.toString(euro);
        euro = euro.replace(".", ",");
        return euro + " €";
    }

    setNewHeight(ratio) {
        const newHeight = this.width * ratio;
        this.height = parseInt(newHeight);
        const cm = this.formatCentimeters(this.height / 10);
        this.inputHeight.value = cm;
    }

    setNewPrice() {
        let base = 0;

        if (this.width >= 1200) {
            base = 2100;
        } else if (this.width >= 900) {
            base = 1950;
        } else if (this.width >= 600) {
            base = 1700;
        } else if (this.width >= 300) {
            base = 1500;
        } else {
            base = 1200;
        }
        
        base = base + 200 * this.parent.difficulty;
        if (this.height >= 0.5 * this.width) {
            base += 100;
        }
        
        this.price = base / 100;
        this.row.children[3].innerHTML = this.formatEuro(this.price);
    }

    setNewPurchasePrice() {
        this.purchasePrice = (this.width * this.height) / 100000;
        this.row.children[4].innerHTML = this.formatEuro(this.purchasePrice);
    }

    parsePrice(el) {
        const value = el.innerHTML;
        const euro = value.split(",")[0];
        const cent = value.split(",")[1];

        return parseInt(euro) * 100 + parseInt(cent);
    }

    addListeners() {
        const height = this.row.children[2];
        const price = this.row.children[3];

        this.inputHeight = this.#createInput(height);
        this.inputPrice = this.#createInput(price);

        this.inputHeight.addEventListener("input", this.updateHeight.bind(this));
        this.inputHeight.addEventListener("change", this.setHeight.bind(this));

        this.inputPrice.addEventListener("change", this.updatePrice.bind(this));
        this.inputPrice.addEventListener("input", this.setPrice.bind(this));
    }

    #createInput(node) {
        const input = document.createElement("input");
        input.value = node.innerHTML;
        input.classList.add("inputHeight");
        input.dataset.id = this.id;

        node.innerHTML = "";
        node.appendChild(input);

        return input;
    }

    updateHeight(e) {
        let height = e.target.value;
        height = height.replace(",", ".");
        this.height = parseFloat(height) * 10;

        const ratio = this.height / this.width;
        this.parent.iterateAll(ratio, this);
    }

    setHeight(e) {
        let height = e.target.value;
        height = height.replace(",", ".");

        this.height = parseFloat(height);
        const cm = this.formatCentimeters(this.height);
        this.inputHeight.value = cm;

        this.parent.generatePreview();
        this.parent.sendToServer();
    }

    updatePrice(e) {
        // TODO: format
    }

    setPrice(e) {
        // TODO: send to server
    }

}

if (document.readyState !== 'loading' ) {
    initSizeTable();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initSizeTable();
    });
}
