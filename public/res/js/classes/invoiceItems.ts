import { ajax } from "js-classes/ajax.js";
import { addBindings } from "js-classes/bindings.js";
import { notification } from "js-classes/notifications.js";

import { getTemplate, setInpupts, clearInputs } from "../global.js";
import { createPopup } from "../classes/helpers";
import { addRow, updateRow, renderTable } from "./table.ts";
import type { FunctionMap, TableHeader, TableOptions } from "../types/types.ts";

interface ItemConfig {
    orderId: number;
    editItemId: number;
    editItemRow: HTMLTableRowElement | null;
}

interface Config {
    type: string;
    itemType: string;
    surcharge: number;
    table: HTMLTableElement | null;
    tableOptions: TableOptions;
    tableHeader: TableHeader[];
    extendedTimes: ExtendedTime[];
}

interface ExtendedTime {
    start: string;
    end: string;
    date: string;
}

const itemsConf: ItemConfig = {
    "orderId": 0,
    "editItemId": 0,
    "editItemRow": null,
}

const config: Config = {
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

const functionNames: FunctionMap = {};

export const getItems = async (id: number, type: string = "order") => {
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
        default:
            throw new Error(`Unknown type: ${type}`);
    }

    const data = await ajax.get(query);
    return data.data;
}

export const getItemsTable = async (
    tableName: string,
    id: number,
    type: string = "order"
) => {
    const data = await getItems(id, type);
    const table = renderTable(
        tableName,
        config.tableHeader,
        data,
        config.tableOptions
    ) as HTMLTableElement;

    table.addEventListener("rowDelete", deleteItem as EventListener);
    table.addEventListener("rowEdit", editItem as EventListener);
    table.addEventListener("rowMove", moveItem as EventListener)

    addExtraData(data, table);
    config.table = table;
    return table;
}

const addExtraData = (data: any[], table: HTMLTableElement): void => {
    const extraDataEls = table.querySelectorAll<HTMLButtonElement>(`button.info-button`);
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

const initItems = (): void => {
    const tabButtons = document.querySelectorAll<HTMLButtonElement>(".tab-button");
    const tabContent = document.querySelectorAll<HTMLElement>(".tab-content");

    Array.from(tabButtons).forEach((button) => {
        button.addEventListener("click", (e: Event) => {
            const eventTarget = e.target as HTMLElement;
            const target = eventTarget.dataset.target;

            if (!target) return;

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

const deleteItem = (e: CustomEvent): void => {
    const data = e.detail;

    const div = document.createElement("div");
    const p = document.createElement("p");
    p.innerHTML = "Möchtest Du den Posten sicher löschen?";
    div.appendChild(p);

    const settingsContainer = createPopup(div);
    const btnDelete = document.createElement("button");
    const btnCancel = settingsContainer.querySelector("button.btn-cancel");

    btnDelete.addEventListener("click", () => {
        (btnCancel as HTMLButtonElement).click();
        ajax.delete(`/api/v1/order-items/${data.type}/${data.id}`).then(() => {
            data.row.remove();
        });
    });

    btnDelete.innerHTML = "Ja";
    btnDelete.classList.add("btn-delete");
    settingsContainer.appendChild(btnDelete);
}

const moveItem = (e: CustomEvent): void => {
    console.log(e.detail);
}

const editItem = (e: CustomEvent): void => {
    const data = e.detail;
    const type = data.type;

    const itemsMenu = document.querySelector("#showPostenAdd") as HTMLElement;
    const itemsMenuButton = document.querySelector("#showItemsMenu") as HTMLElement;

    itemsMenu.classList.remove("hidden");
    itemsMenuButton.classList.add("hidden");

    const tab = document.querySelector(`.tab-button[data-target="${type}"]`) as HTMLButtonElement;
    tab.click();

    const addItem = document.querySelector("#addItem") as HTMLElement;
    const saveItem = document.querySelector("#saveItem") as HTMLElement;

    addItem.classList.add("hidden");
    saveItem.classList.remove("hidden");

    itemsConf.editItemId = data.id;
    itemsConf.editItemRow = data.row;

    switch (type) {
        case "time":
            editTime(data.id);
            break;
        case "service":
            editService(data.id);
            break;
        case "product":
            break;
    }
}

const editTime = async (id: number) => {
    const response = await ajax.get(`/api/v1/order-items/times/${id}`);
    const data = response.data;
    setInpupts({
        "ids": {
            "timeInput": data.time,
            "wage": data.wage,
            "timeDescription": data.description,
            "isFree": data.notcharged,
            "addToInvoice": data.isinvoice,
            "getDiscount": data.discount,
        },
    });
}

const editService = async (id: number) => {
    const response = await ajax.get(`/api/v1/order-items/services/${id}`);
    const data = response.data;
    setInpupts({
        "ids": {
            "selectLeistung": data.type,
            "anz": data.quantity,
            "bes": data.description,
            "ekp": data.buyingprice,
            "pre": data.price,
            "meh": data.unit,
            "isFree": data.notcharged,
            "addToInvoice": data.isinvoice,
            "getDiscount": data.discount,
        },
    });
}

const saveEditTime = (): void => {
    const wage = getWage();
    if (!wage) {
        return;
    }

    const data = getTimeData(wage);
    ajax.put(`/api/v1/order-items/${itemsConf.orderId}/times/${itemsConf.editItemId}`, data).then((r: any) => {
        resetTimeInputs(r);
    });
}

const saveEditService = (): void => {
    ajax.put(`/api/v1/order-items/${itemsConf.orderId}/services/${itemsConf.editItemId}`, getServiceData()).then((r: any) => resetServiceInputs(r));
}

const getTimeData = (wage: number) => {
    return {
        "time": (document.querySelector("#timeInput") as HTMLInputElement).value,
        "wage": wage,
        "description": (document.querySelector("#timeDescription") as HTMLInputElement).value,
        "noPayment": getIsFree(),
        "addToInvoice": getAddToInvoice(),
        "discount": (document.querySelector("#getDiscount") as HTMLInputElement).value,
        "times": JSON.stringify(config.extendedTimes),
    }
}

const getServiceData = () => {
    return {
        "lei": (document.querySelector("#selectLeistung") as HTMLInputElement).value,
        "bes": (document.querySelector("#bes") as HTMLInputElement).value,
        "ekp": (document.querySelector("#ekp") as HTMLInputElement).value,
        "pre": (document.querySelector("#pre") as HTMLInputElement).value,
        "meh": (document.querySelector("#meh") as HTMLInputElement).value,
        "anz": (document.querySelector("#anz") as HTMLInputElement).value,
        "ohneBerechnung": getIsFree(),
        "addToInvoice": getAddToInvoice(),
        "discount": (document.querySelector("#getDiscount") as HTMLInputElement).value,
    };
}

const getWage = (): false | number => {
    const wageEl = document.querySelector<HTMLInputElement>("#wage");
    if (!wageEl || wageEl.value === "") {
        alert("Stundenlohn kann nicht leer sein.");
        return false;
    }

    const wage = Number(wageEl.value);
    return wage;
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

functionNames.click_saveEdit = async () => {
    switch (config.itemType) {
        case "time":
            saveEditTime();
            break;
        case "service":
            saveEditService();
            break;
        case "product":
            break;
    }
}

const resetTimeInputs = (r: any) => {
    if (r.data.status !== "success") {
        notification("", "failure", r.message);
        return;
    }

    notification("", "success");
    updatePrice(r.data.price);
    updateTable(r.data.data);

    config.extendedTimes = [];
    (document.getElementById("extendedTimeInput") as HTMLElement).innerHTML = "";

    clearInputs({
        "ids": ["timeInput", "timeDescription"],
        "classes": ["timeInput", "dateInput"]
    });

    (document.querySelector("#isFree") as HTMLInputElement).checked = false;
    (document.querySelector("#addToInvoice") as HTMLInputElement).checked = false;

    resetItemsMenu();
}

const resetServiceInputs = (r: any) => {
    if (r.data.status !== "success") {
        notification("", "failure", r.message);
        return;
    }

    notification("", "success");
    updatePrice(r.data.price);
    updateTable(r.data.data);
    clearInputs({ "ids": ["bes", "ekp", "pre", "meh", "anz"] });
    (document.getElementById("selectLeistung") as HTMLSelectElement).value = "0";

    resetItemsMenu();
}

const addTime = (): void => {
    const wage = getWage();
    if (!wage) {
        return;
    }

    const data = getTimeData(wage);

    ajax.post(`/api/v1/order-items/${itemsConf.orderId}/times`, data).then((r: any) => resetTimeInputs(r));
}

const addService = () => {
    ajax.post(`/api/v1/order-items/${itemsConf.orderId}/services`, getServiceData()).then((r: any) => resetServiceInputs(r));
}

functionNames.click_showItemsMenu = () => {
   resetItemsMenu();
}

const resetItemsMenu = () => {
    const itemsMenu = document.querySelector("#showPostenAdd") as HTMLElement;
    const itemsMenuButton = document.querySelector("#showItemsMenu") as HTMLElement;

    itemsMenu.classList.toggle("hidden");
    itemsMenuButton.classList.toggle("hidden");

    const addItem = document.querySelector("#addItem") as HTMLElement;
    const saveItem = document.querySelector("#saveItem") as HTMLElement;

    addItem.classList.remove("hidden");
    saveItem.classList.add("hidden");

    itemsConf.editItemRow = null;
    itemsConf.editItemId = 0;

    // toggle edit button
}

functionNames.click_selectLeistung = (e: Event): void => {
    const el = e.target as HTMLSelectElement;
    config.surcharge = Number(el.options[el.selectedIndex].dataset.surcharge || 0);
    (document.querySelector("#surcharge") as HTMLInputElement).value = String(config.surcharge);
}

functionNames.click_calculatePrice = (): void => {
    const ekpEl = document.querySelector<HTMLInputElement>("#ekp");
    if (!ekpEl) return;
    const price = parseFloat(ekpEl.value);
    if (isNaN(price)) return;
    const newPrice = price * (1 + (config.surcharge / 100));
    (document.querySelector("#pre") as HTMLInputElement).value = String(newPrice);
}

functionNames.write_changeMeh = (): void => {
    const meh = (document.getElementById("meh") as HTMLInputElement).value;
    (document.getElementById("showMeh") as HTMLElement).innerHTML = meh;
}

/**
* this function gets executed when the "+" button is pressed to add a new timeframe or on init
* @param {*} event this is the passed event
*/
functionNames.click_createTimeInputRow = (): void => {
    const div = document.createElement("div");
    div.appendChild(getTemplate("templateTimeInput"));

    const extendedTimeInput = document.getElementById("extendedTimeInput")!;
    extendedTimeInput.appendChild(div);

    const dateInput = div.querySelector<HTMLInputElement>(".dateInput")!;
    dateInput.dataset.index = config.extendedTimes.length.toString();
    dateInput.addEventListener("change", (e) => adjustTime(e, "date"));

    const [start, end] = div.querySelectorAll<HTMLInputElement>(".timeInput");

    start.addEventListener("change", (e) => adjustTime(e, "start"), false);
    end.addEventListener("change", (e) => adjustTime(e, "end"), false);

    start.dataset.index = config.extendedTimes.length.toString();
    start.dataset.type = "start";
    end.dataset.index = config.extendedTimes.length.toString();
    end.dataset.type = "end";

    /* lazy solution */
    const removeBtn = div.querySelector<HTMLButtonElement>(".btn-delete")!;
    removeBtn.dataset.index = config.extendedTimes.length.toString();
    removeBtn.addEventListener("click", (e) => {
        div.classList.add("hidden");
        const index = Number((e.target as HTMLElement).dataset.index);
        config.extendedTimes[index] = { start: "00:00", end: "00:00", date: "" };
        calculateTime();
    }, false);

    config.extendedTimes.push({ start: "00:00", end: "00:00", date: "" });
    start.focus();
}

const adjustTime = (e: Event, type: keyof ExtendedTime): void => {
    const target = e.target as HTMLInputElement;
    const index = Number(target.dataset.index) || 0;

    config.extendedTimes[index][type] = target.value;

    if (type === "date") {
        return;
    }

    const startEl = document.querySelector<HTMLInputElement>(`.timeInput[data-index="${index}"][data-type="start"]`)!;
    const endEl = document.querySelector<HTMLInputElement>(`.timeInput[data-index="${index}"][data-type="end"]`)!;

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

const calculateTime = (): void => {
    let minutes = 0;

    for (const time of config.extendedTimes) {
        const diff = getTime(time.start, time.end);
        const min = Math.floor(diff / 1000 / 60);;

        if (min >= 0) minutes += min;
    }

    (document.getElementById("timeInput") as HTMLInputElement).value = String(minutes);
}

const getTime = (startValue: string, endValue: string): number => {
    const [sh, sm] = startValue.split(":").map(Number);
    const [eh, em] = endValue.split(":").map(Number);

    const start = new Date();
    const end = new Date();

    start.setHours(sh, sm, 0, 0);
    end.setHours(eh, em, 0, 0);

    return (end.getTime() - start.getTime());
}

const getIsFree = (): number => {
    const isFree = document.querySelector("#isFree") as HTMLInputElement;
    const isFreeValue = isFree.checked ? 1 : 0;
    return isFreeValue;
}

const getAddToInvoice = (): number => {
    const addToInvoice = document.querySelector("#addToInvoice") as HTMLInputElement;
    const addToInvoiceValue = addToInvoice.checked ? 1 : 0;
    return addToInvoiceValue;
}

const updatePrice = (price: number): void => {
    const el = document.getElementById("totalPrice")!;
    el.innerText = new Intl.NumberFormat("de-DE", {
        "style": "currency",
        "currency": "EUR"
    }).format(price);
}

const updateTable = (data: any): void => {
    if (itemsConf.editItemId == 0) {
        addRow(data, config.table, config.tableOptions, config.tableHeader);
    } else {
        updateRow(data, config.table, itemsConf.editItemRow, config.tableOptions, config.tableHeader);
    }
}

export const initInvoiceItems = (orderId = 0): void => {
    itemsConf.orderId = orderId;
    addBindings(functionNames);
    initItems();
}
