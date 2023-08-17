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
    .then(() => {
        handleAfterDeleteSizeRow(reference);
    });
}

function handleAfterDeleteSizeRow(reference) {
    var row = reference.parentNode.parentNode;

    /* delete row from SizeTable */
    sizeTable.delete(reference);
    row.parentNode.removeChild(row);
}

function deleteSizeRow(id) {
    ajax.post({
        id: id,
        r: "deleteSizeRow",
    }, true).then(response => {
        handleAfterDeleteSizeRow(response);
    });
}

/**
 * resets the price of a size
 * @param {*} key
 * @param {*} event
 * @returns
 */
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
        handleAfterPerformAction(newPrice, refRow);
    });
}

function handleAfterPerformAction(newPrice, refRow) {
    sizeTable.sizeTableRows.forEach(row => {
        if (row.row == refRow) {
            row.priceCent = newPrice / 100;
            row.inputPrice.value = SizeTableRow.formatEuro(row.priceCent);
            row.isDefaultPrice();
        }
    });
}

function resetSizeRow(refRow, id) {
    ajax.post({
        id: id,
        r: "resetSizeRow",
    }, true).then(newPrice => {
        handleAfterPerformAction(newPrice, refRow);
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
        this.ratio = width / height;
    }

    async addNewLine(width, price, isDefaultPrice) {
        const newRow = this.table.insertRow(-1);
        for (let i = 0; i < 6; i++) {
            let cell = newRow.insertCell(-1);
            cell.classList.add("h-10");
        }

        const height = Math.round(width * this.ratio);
        const data = await ajax.post({
            width: width * 10,
            height: height * 10,
            price: price * 100,
            id: mainVariables.motivId.innerHTML,
            isDefaultPrice: isDefaultPrice,
            r: "addSize",
        });
        const id = data.id;

        newRow.children[0].innerHTML = id;
        newRow.children[1].innerHTML = width + "cm";
        newRow.children[2].innerHTML = height + "cm";
        newRow.children[3].innerHTML = SizeTableRow.formatEuro(price);
        newRow.children[4].innerHTML = SizeTableRow.formatEuro((width * height) / 1000);

        this.#copyRowActions(newRow, id);
        this.sizeTableRows.push(new SizeTableRow(newRow, this));
    }

    #copyRowActions(row, id) {
        const refRow = this.sizeTableRows[0].row;
        const refRowActions = refRow.children[5];
        const newRowActions = row.children[5];

        newRowActions.innerHTML = refRowActions.innerHTML;

        newRowActions.children[0].removeAttribute("onclick");
        newRowActions.children[0].addEventListener("click", () => {
            deleteSizeRow(id);
        }, false);

        newRowActions.children[1].removeAttribute("onclick");
        newRowActions.children[1].addEventListener("click", () => {
            resetSizeRow(row, id);
        }, false);
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

    /**
     * iterates over all rows and updates the values,
     * except for the row that is passed as noNewHeight
     * @param {*} ratio 
     * @param {*} noNewHeight 
     */
    iterateAll(ratio, noNewHeight) {
        this.ratio = ratio;
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
    }

    delete(ref) {
        var index = this.sizeTableRows.indexOf(ref);
        if (index > -1) {
            arr.splice(index, 1);
        }
    }

    sendToServer() {
        const data = {};
        data.sizes = {};
        let count = 0;
        this.sizeTableRows.forEach(row => {
            const rowData = {
                width: row.width,
                height: row.height,
                price: row.priceCent,
            };
            data.sizes[count] = rowData;
            count++;
        });

        ajax.post({
            sizes: JSON.stringify(data),
            id: mainVariables.motivId.innerHTML,
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
        this.priceCent = SizeTableRow.parsePrice(row.children[3]);
        this.priceEuro = parseInt(row.children[3].innerHTML);
        this.purchasePrice = SizeTableRow.parsePrice(row.children[4]);

        this.parent = parent;

        this.addListeners();
        this.isDefaultPrice();
    }

    isDefaultPrice() {
        if (this.priceCent == SizeTableRow.calcNewPriceCent(this.width, this.height, this.parent.difficulty)) {
            this.inputPrice.classList.add("text-green-600", "italic");
        } else {
            this.inputPrice.classList.remove("text-green-600", "italic");
        }
    }

    static formatCentimeters(value) {
        let cm = value.toFixed(1);
        cm = cm.toString(cm);
        cm = cm.replace(".", ",");
        return cm + "cm";
    }

    /**
     * takes a euro value and formats it to a string
     * @param {*} value 
     * @returns 
     */
    static formatEuro(value) {
        value = parseFloat(value);
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

    /**
     * calculates the new price for a given size in euro
     * @param {*} width 
     * @param {*} height 
     * @param {*} difficulty 
     * @returns 
     */
    static calcNewPriceCent(width, height, difficulty) {
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
        
        return base;
    }

    static calcNewPriceEuro(width, height, difficulty) {
        const price = SizeTableRow.calcNewPriceCent(width, height, difficulty);
        return price / 100;
    }

    /**
     * this function so called from the iterator,
     * it calculates the new price for a given size only if the price is set to be default
     */
    setNewPrice() {
        this.priceCent = SizeTableRow.calcNewPriceCent(this.width, this.height, this.parent.difficulty);
        this.priceEuro = SizeTableRow.calcNewPriceEuro(this.width, this.height, this.parent.difficulty);
        this.inputPrice.value = SizeTableRow.formatEuro(this.priceEuro);
        this.isDefaultPrice();
    }

    setNewPurchasePrice() {
        this.purchasePrice = (this.width * this.height) / 100000;
        this.row.children[4].innerHTML = SizeTableRow.formatEuro(this.purchasePrice);
    }

    static parsePrice(el) {
        const value = el.innerHTML || el;
        const euro = value.split(",")[0];
        let cent = value.split(",")[1] || 0;

        /**
         * important: when the user enters a value with a comma, like: 1,7
         * the 7 is interpreted as 7 cents, but it should be 70 cents, therefore this fix
         */
        if (cent.length == 1) {
            cent = cent * 10;
        }

        return parseInt(euro) * 100 + parseInt(cent);
    }

    addListeners() {
        const height = this.row.children[2];
        const price = this.row.children[3];

        this.inputHeight = this.#createInput(height);
        this.inputPrice = this.#createInput(price);

        this.inputHeight.addEventListener("input", this.updateHeight.bind(this));
        this.inputHeight.addEventListener("change", this.setHeight.bind(this));
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

    static stringToMM(length) {
        length = length.toString();
        length = length.replace(",", ".");
        length = parseFloat(length);
        return length * 10;
    }

    /**
     * updates the height of the row when a new width is set
     * @param {*} e 
     */
    updateHeight(e) {
        let height = e.target.value;
        this.height = SizeTableRow.stringToMM(height);

        const ratio = this.height / this.width;
        this.parent.iterateAll(ratio, this);
    }

    /**
     * sets the height of the row when the input is changed and foramts the value
     * then sends the new size table to the server
     * @param {*} e 
     */
    setHeight(e) {
        let height = e.target.value;
        height = height.replace(",", ".");
        height = parseFloat(height);

        const cm = SizeTableRow.formatCentimeters(height);
        this.height = SizeTableRow.stringToMM(height);
        this.inputHeight.value = cm;
        this.parent.sendToServer();
    }

    setPrice(e) {
        const price = e.currentTarget.value;
        const euro = price.split(",")[0];
        let cent = price.split(",")[1] || 0;

        /**
         * important: when the user enters a value with a comma, like: 1,7
         * the 7 is interpreted as 7 cents, but it should be 70 cents, therefore this fix
         */
        if (cent.length == 1) {
            cent = cent * 10;
        }

        const parsedPrice = parseInt(euro) * 100 + parseInt(cent);
        ajax.post({
            id: this.id,
            price: parsedPrice,
            r: "setSizePrice",
        }, true);
        
        this.priceCent = parsedPrice;
        this.priceEuro = parsedPrice / 100;
        this.inputPrice.value = SizeTableRow.formatEuro(this.priceCent / 100);
        this.isDefaultPrice();
    }

}

export function click_addNewWidth() {
    const newWidth = document.getElementById("newWidth").value;
    let newPrice = document.getElementById("newPrice").value;
    let isDefaultPrice = false;

    if (newWidth == "") {
        return;
    }

    if (newPrice == "") {
        isDefaultPrice = true;
        let tempWidth = SizeTableRow.stringToMM(newWidth);
        let tempHeight = tempWidth * sizeTable.ratio;
        newPrice = SizeTableRow.calcNewPriceEuro(tempWidth, tempHeight, sizeTable.difficulty);
    } else {
        newPrice = SizeTableRow.parsePrice(newPrice);
        newPrice = newPrice / 100;
    }

    sizeTable.addNewLine(newWidth, newPrice, isDefaultPrice);
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
