import { getTemplate, setInpupts, clearInputs, createPopup } from "../global.js";
import { ajax } from "./ajax.js";
import { addBindings } from "./bindings.js";
import { notification } from "./notifications.js";
import { addRow, renderTable } from "./table.js";

const config = {
    "type": "order",
    "itemType": "time",
    "surcharge": 0,
    "table": null,
    "tableOptions": {
        "primaryKey": "id",
        "hide": ["id"],
        "hideOptions": ["addRow", "check", "add"],
        "styles": {
            "table": {
                "className": ["w-full"],
            },
        },
        "autoSort": true,
        "sum": [
            {
                "key": "price",
                "format": "EUR",
            },
        ],
    },
    "tableHeader": [
        {
            "key": "id",
            "label": "Id",
        },
        {
            "key": "position",
            "label": "Position",
        },
        {
            "key": "name",
            "label": "Bezeichnung",
        },
        {
            "key": "description",
            "label": "Beschreibung",
        },
        {
            "key": "quantity",
            "label": "Menge",
        },
        {
            "key": "unit",
            "label": "MEH",
        },
        {
            "key": "price",
            "label": "Preis [€]",
        },
        {
            "key": "totalPrice",
            "label": "Gesamt [€]",
        },
        {
            "key": "purchasePrice",
            "label": "EK [€]",
        },
    ],
    "extendedTimes": [],
}

const functionNames = {};

export const getItems = async (id, type = "order") => {
    let query = ``;

    switch (type) {
        case "order":
            query = `/api/v1/order-items/${id}/all`;
            break;
        case "invoice":
            query = `/api/v1/order-items/invoice/${id}/all`;
            break;
        case "offer":
            query = `/api/v1/order-items/offer/${id}/all`;
            break;
    }

    const data = await ajax.get(query);
    return data;
}

export const getItemsTable = async (tableName, id, type = "order") => {
    const data = await getItems(id, type);
    const table = renderTable(tableName, config.tableHeader, data, config.tableOptions);

    table.addEventListener("rowDelete", deleteItem);
    table.addEventListener("rowEdit", editItem);
    table.addEventListener("rowMove", moveItem)

    addExtraData(data, table);

    config.table = table;
    return table;
}

const addExtraData = (data, table) => {
    const extraDataEls = table.querySelectorAll(`button.info-button`);
    extraDataEls.forEach(el => {
        const id = el.dataset.id;
        el.addEventListener("click", () => {
            const div = document.createElement("div");
            const extraData = data.find(entry => entry.id == id);
            div.innerHTML = extraData.extraData;
            createPopup(div);
        });
    })
}

const initItems = () => {
    const tabButtons = document.querySelectorAll(".tab-button");
    const tabContent = document.querySelectorAll(".tab-content");

    Array.from(tabButtons).forEach((button) => {
        button.addEventListener("click", e => {
            const target = e.target.dataset.target;
            tabButtons.forEach(btn => btn.classList.remove("tab-active"));
            button.classList.add("tab-active");

            tabContent.forEach(content => {
                if (content.id === target) {
                    content.classList.remove("hidden");
                } else {
                    content.classList.add("hidden");
                }
            });
            config.itemType = target;
        });
    });
}

const deleteItem = e => {
    const data = e.detail;

    ajax.delete(`/api/v1/order-items/${data.type}/${data.id}`).then(() => {
        data.row.remove();
    });
}

const moveItem = e => {
    console.log(e.detail);
}

const editItem = e => {
    const data = e.detail;
    const type = data.type;

    const itemsMenu = document.querySelector("#showPostenAdd");
    const itemsMenuButton = document.querySelector("#showItemsMenu");

    itemsMenu.classList.remove("hidden");
    itemsMenuButton.classList.add("hidden");

    const tab = document.querySelector(`.tab-button[data-target="${type}"]`);
    tab.click();

    switch (type) {
        case "time":
            editTime();
            break;
        case "service":
            editService();
            break;
        case "product":
            break;
    }
}

const editTime = data => {
    setInpupts({
        "ids": {
            "timeInput": 11,
            "wage": 0,
            "timeDescription": 0,
            "isFree": 0,
            "addToInvoice": 0,
        },
    });
}

const editService = data => {
    setInpupts({
        "ids": {
            "selectLeistung": 11,
            "anz": 0,
            "bes": 0,
            "ekp": 0,
            "pre": 0,
            "meh": 0,
            "isFree": 0,
            "addToInvoice": 0,
        },
    });
}

functionNames.click_addItem = async () => {
    switch (config.itemType) {
        case "time":
            addTime();
            break;
        case "service":
            addService();
            break;
        case "product":
            break;
    }
}

const addTime = () => {
    const wage = document.querySelector("#wage").value;
    if (wage === "" || wage === null) {
        alert("Stundenlohn kann nicht leer sein.");
        return;
    }

    ajax.post(`/api/v1/order-items/${globalData.auftragsId}/times`, {
        "time": document.querySelector("#timeInput").value,
        "wage": wage,
        "description": document.querySelector("#timeDescription").value,
        "noPayment": getIsFree(),
        "addToInvoice": getAddToInvoice(),
        "discount": document.querySelector("#getDiscount").value,
        "times": JSON.stringify(config.extendedTimes),
    }).then(r => {
        if (r.status !== "success") {
            notification("", "failure", r.message);
            return;
        }

        notification("", "success");

        updatePrice(r.price);
        updateTable(r.data);

        config.extendedTimes = [];
        const extendedTimeInput = document.getElementById("extendedTimeInput");
        extendedTimeInput.innerHTML = "";

        clearInputs({
            "ids": ["timeInput", "timeDescription"],
            "classes": ["timeInput", "dateInput"]
        });

        document.querySelector("#isFree").checked = 0;
        document.querySelector("#addToInvoice").checked = 0;
    });
}

const addService = () => {
    ajax.post(`/api/v1/order-items/${globalData.auftragsId}/services`, {
        "lei": document.querySelector("#selectLeistung").value,
        "bes": document.querySelector("#bes").value,
        "ekp": document.querySelector("#ekp").value,
        "pre": document.querySelector("#pre").value,
        "meh": document.querySelector("#meh").value,
        "anz": document.querySelector("#anz").value,
        "ohneBerechnung": getIsFree(),
        "addToInvoice": getAddToInvoice(),
        "discount": document.querySelector("#getDiscount").value,
    }).then(r => {
        if (r.status !== "success") {
            notification("", "failure", r.message);
            return;
        }

        notification("", "success");

        updatePrice(r.price);
        updateTable(r.data);
        clearInputs({ "ids": ["bes", "ekp", "pre", "meh", "anz"] });
        document.getElementById("selectLeistung").value = 0;
    });
}

functionNames.click_showItemsMenu = () => {
    const itemsMenu = document.querySelector("#showPostenAdd");
    const itemsMenuButton = document.querySelector("#showItemsMenu");

    itemsMenu.classList.toggle("hidden");
    itemsMenuButton.classList.toggle("hidden");
}

functionNames.click_selectLeistung = e => {
    const el = e.target;
    config.surcharge = el.options[el.selectedIndex].dataset.surcharge;
    const surchargeEl = document.querySelector("#surcharge");
    surchargeEl.value = config.surcharge;
}

functionNames.click_calculatePrice = () => {
    let price = document.querySelector("#ekp").value;
    price = parseFloat(price);
    if (isNaN(price)) {
        return;
    }
    const newPrice = price * (1 + (config.surcharge / 100));
    document.querySelector("#pre").value = newPrice;
}

functionNames.write_changeMeh = () => {
    const meh = document.getElementById("meh").value;
    const showMeh = document.getElementById("showMeh");
    showMeh.innerHTML = meh;
}

/**
* this function gets executed when the "+" button is pressed to add a new timeframe or on init
* @param {*} event this is the passed event
*/
functionNames.click_createTimeInputRow = () => {
    const div = document.createElement("div");
    div.appendChild(getTemplate("templateTimeInput"));

    const extendedTimeInput = document.getElementById("extendedTimeInput");
    extendedTimeInput.appendChild(div);

    const dateInput = div.querySelector(".dateInput");
    dateInput.dataset.index = config.extendedTimes.length;
    dateInput.addEventListener("change", e => adjustTime(e, "date"), false);

    const timeInputs = div.querySelectorAll(".timeInput");
    const start = timeInputs[0];
    const end = timeInputs[1];

    start.addEventListener("change", e => adjustTime(e, "start"), false);
    end.addEventListener("change", e => adjustTime(e, "end"), false);

    start.dataset.index = config.extendedTimes.length;
    start.dataset.type = "start";
    end.dataset.index = config.extendedTimes.length;
    end.dataset.type = "end";

    /* lazy solution */
    const removeBtn = div.querySelector(".btn-delete");
    removeBtn.dataset.index = config.extendedTimes.length;
    removeBtn.addEventListener("click", e => {
        div.classList.add("hidden");
        const index = e.target.dataset.index;
        config.extendedTimes[index].start = "00:00";
        config.extendedTimes[index].end = "00:00";
        config.extendedTimes[index].date = "";
        calculateTime();
    }, false);

    config.extendedTimes.push({ "start": "00:00", "end": "00:00", "date": "" });

    start.focus();
}

const adjustTime = (e, type) => {
    const value = e.target.value;
    let index = e.target.dataset.index;
    index = parseInt(index);
    index = index || 0;

    config.extendedTimes[index][type] = value;

    if (type === "date") {
        return;
    }

    const startEl = document.querySelector(`.timeInput[data-index="${index}"][data-type="start"]`);
    const endEl = document.querySelector(`.timeInput[data-index="${index}"][data-type="end"]`);

    const timeDiff = getTime(startEl.value, endEl.value);
    if (timeDiff < 0) {
        startEl.classList.add("bg-red-200");
        endEl.classList.add("bg-red-200");
    } else {
        startEl.classList.remove("bg-red-200");
        endEl.classList.remove("bg-red-200");
    }

    calculateTime();
}

const calculateTime = () => {
    let minutes = 0;
    for (let i = 0; i < config.extendedTimes.length; i++) {
        const time = getTime(config.extendedTimes[i].start, config.extendedTimes[i].end);
        const timeInMinutes = Math.floor(time / 1000 / 60);

        if (timeInMinutes >= 0) {
            minutes += timeInMinutes;
        }
    }
    document.getElementById("timeInput").value = minutes;
}

const getTime = (startValue, endValue) => {
    const start = startValue.split(":");
    const end = endValue.split(":");

    const startTime = new Date();
    startTime.setHours(start[0]);
    startTime.setMinutes(start[1]);
    startTime.setSeconds(0);
    const endTime = new Date();
    endTime.setHours(end[0]);
    endTime.setMinutes(end[1]);
    endTime.setSeconds(0);

    return (endTime.getTime() - startTime.getTime());
}

const getIsFree = () => {
    const isFree = document.querySelector("#isFree");
    const isFreeValue = isFree.checked ? 1 : 0;
    return isFreeValue;
}

const getAddToInvoice = () => {
    const addToInvoice = document.querySelector("#addToInvoice");
    const addToInvoiceValue = addToInvoice.checked ? 1 : 0;
    return addToInvoiceValue;
}

const updatePrice = (price) => {
    const priceEl = document.getElementById("totalPrice");
    priceEl.innerText = new Intl.NumberFormat("de-DE", {
        "style": "currency",
        "currency": "EUR"
    }).format(price);
}

const updateTable = (data) => {
    addRow(data, config.table, config.tableOptions, config.tableHeader)
}

export const initInvoiceItems = () => {
    addBindings(functionNames);
    initItems();
}
