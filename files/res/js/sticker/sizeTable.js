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

export function readSizeTable() {
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