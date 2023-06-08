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
    }, true)
    .then(response => {
        var row = reference.parentNode.parentNode;
        console.log(response);

        /* delete row from SizeTable */
        sizeTable.delete(reference);
        row.parentNode.removeChild(row);
    });
}

function performAction(key, event) {
    const tableKey = document.querySelector('[data-type="module_sticker_sizes"]').dataset.key;
    const refRow = event.currentTarget.parentNode.parentNode;
    ajax.post({
        row: key,
        table: tableKey,
        id:  mainVariables.motivId.innerHTML,
        r: "resetStickerPrice",
    }, true)
    .then(newPrice => {
        sizeTable.sizeTableRows.forEach(row => {
            if (row.row == refRow) {
                row.price = newPrice / 100;
                row.row.children[3].innerHTML = SizeTableRow.formatEuro(row.price);
            }
        });
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

/**
 * export functions to global scope
 */
window["deleteRow"] = deleteRow;
window["performAction"] = performAction;
window["tableUpdateCallback"] = tableUpdateCallback;

/**
 * changes the price class for Aufkleber and Wandtattoo, 
 * priceclass 2 adds 1€ in price
 * @param {*} e 
 */
function changePriceclass(e) {
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

class SizeTable {

    constructor(tbl) {
        this.table = tbl;
        this.sizeTableRows = [];
        this.ratio = 0;

        this.difficulty = this.getInitDifficulty();
        this.parseTable();
        this.#initRatio();
    }

    #initRatio() {
        const width = this.sizeTableRows[0].width;
        const height = this.sizeTableRows[0].height;
        this.ratio = height / width;
    }

    async addNewLine(width, price) {
        const newRow = this.table.insertRow(-1);
        for (let i = 0; i < 6; i++) {
            let cell = newRow.insertCell(-1);
            cell.classList.add("h-10");
        }

        const height = Math.round(width * this.ratio);
        const data = await ajax.post({
            width: width * 10,
            height: height,
            price: price,
            id: mainVariables.motivId.innerHTML,
            r: "addSize",
        });
        const id = data.id;

        newRow.children[0].innerHTML = id;
        newRow.children[1].innerHTML = width + "cm";
        newRow.children[3].innerHTML = price / 100;
        
        this.sizeTableRows.push(new SizeTableRow(newRow, this));
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
            this.text += "<p class=\"breiten\">" + SizeTableRow.formatCentimeters(row.width / 10) + " <span>x " + SizeTableRow.formatCentimeters(row.height / 10) + "</span></p>";
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
                price: row.price * 100,
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
        this.price = SizeTableRow.parsePrice(row.children[3]);
        this.purchasePrice = SizeTableRow.parsePrice(row.children[4]);

        this.parent = parent;

        this.addListeners();

        if (this.price / 100 == SizeTableRow.calcNewPrice(this.width, this.height, this.parent.difficulty)) {
            let el = this.row.children[3];
            el.children[0].classList.add("text-green-600", "italic");
        }
    }

    static formatCentimeters(value) {
        let cm = value.toFixed(1);
        cm = cm.toString(cm);
        cm = cm.replace(".", ",");
        return cm + "cm";
    }

    static formatEuro(value) {
        let euro = value.toFixed(2);
        euro = euro.toString(euro);
        euro = euro.replace(".", ",");
        return euro + " €";
    }

    setNewHeight(ratio) {
        const newHeight = this.width * ratio;
        this.height = parseInt(newHeight);
        const cm = SizeTableRow.formatCentimeters(this.height / 10);
        this.inputHeight.value = cm;
    }

    static calcNewPrice(width, height, difficulty) {
        let base = 0;

        if (width >= 1200) {
            base = 2100;
        } else if (width >= 900) {
            base = 1950;
        } else if (width >= 600) {
            base = 1700;
        } else if (width >= 300) {
            base = 1500;
        } else {
            base = 1200;
        }
        
        base = base + 200 * difficulty;
        if (height >= 0.5 * width) {
            base += 100;
        }
        
        let price = base / 100;
        return price;
    }

    setNewPrice() {
        this.price = SizeTableRow.calcNewPrice(this.width, this.height, this.parent.difficulty);
        this.row.children[3].innerHTML = SizeTableRow.formatEuro(this.price);
    }

    setNewPurchasePrice() {
        this.purchasePrice = (this.width * this.height) / 100000;
        this.row.children[4].innerHTML = SizeTableRow.formatEuro(this.purchasePrice);
    }

    static parsePrice(el) {
        const value = el.innerHTML || el;
        const euro = value.split(",")[0];
        const cent = value.split(",")[1] || 0;

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
        this.inputPrice.addEventListener("change", this.setPrice.bind(this));
    }

    #createInput(node) {
        const input = document.createElement("input");
        input.value = node.innerHTML;
        input.classList.add("inputHeight");
        input.classList.add("w-24");
        input.dataset.id = this.id;

        node.innerHTML = "";
        node.appendChild(input);

        return input;
    }

    static heightToMM(height) {
        height = height.toString();
        height = height.replace(",", ".");
        height = parseFloat(height);
        return height * 10;
    }

    updateHeight(e) {
        let height = e.target.value;
        this.height = SizeTableRow.heightToMM(height);

        const ratio = this.height / this.width;
        this.parent.iterateAll(ratio, this);
    }

    setHeight(e) {
        let height = e.target.value;
        height = height.replace(",", ".");
        height = parseFloat(height);

        const cm = SizeTableRow.formatCentimeters(height);
        this.height = SizeTableRow.heightToMM(height);
        this.inputHeight.value = cm;

        this.parent.generatePreview();
        this.parent.sendToServer();
    }

    updatePrice(e) {
        // TODO: format
    }

    setPrice(e) {
        const price = e.currentTarget.value;
        const euro = price.split(",")[0];
        const cent = price.split(",")[1] || 0;

        const parsedPrice = parseInt(euro) * 100 + parseInt(cent);
        ajax.post({
            id: this.id,
            price: parsedPrice,
            r: "setSizePrice",
        });

        this.price = parsedPrice / 100;
        this.row.children[3].innerHTML = SizeTableRow.formatEuro(this.price);
    }

}

export function click_addNewWidth() {
    const newWidth = document.getElementById("newWidth").value;
    const newPrice = document.getElementById("newPrice").value;

    if (newWidth == "") {
        return;
    }

    if (newPrice == "") {
        // calculate price
    }

    sizeTable.addNewLine(newWidth, newPrice);
    document.getElementById("newWidth").value = "";
    document.getElementById("newPrice").value = "";
}

var sizeTable;

function initSizeTable() {
    const tbl = document.querySelector("[data-type='module_sticker_sizes']");
    sizeTable = new SizeTable(tbl);

    const price1 = document.getElementById("price1");
    price1.addEventListener("click", changePriceclass, false);
    const price2 = document.getElementById("price2");
    price2.addEventListener("click", changePriceclass, false);
}

if (document.readyState !== 'loading' ) {
    initSizeTable();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initSizeTable();
    });
}
