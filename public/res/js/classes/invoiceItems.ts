import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings";
import { notification } from "js-classes/notifications";

import { createPopup } from "../classes/helpers";
import { getTemplate, setInpupts, clearInputs } from "../global";
import type { FunctionMap, TableHeader, TableOptions } from "../types/types";

import { DragSortManager } from "./DragSortManager";
import { renderTable } from "./table";
import { initFileUploader } from "./upload";


interface ItemConfig {
    orderId: number;
    editItemId: number;
    editItemRow: HTMLTableRowElement | null;
}

interface Config {
    tableName: string;
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
    orderId: 0,
    editItemId: 0,
    editItemRow: null,
}

const config: Config = {
    tableName: "",
    type: "order",
    itemType: "time",
    surcharge: 0,
    table: null,
    tableOptions: {
        primaryKey: "id",
        hide: ["id"],
        hideOptions: ["addRow", "check"],
        styles: {
            table: {
                className: ["w-full"],
            },
            key: {
                quantity: ["font-mono"],
                price: ["font-mono", "whitespace-pre"],
                totalPrice: ["font-mono", "whitespace-pre"],
                purchasePrice: ["font-mono"],
            },
            sum: {
                totalPrice: ["font-bold", "font-mono", "whitespace-pre"],
            },
        },
        autoSort: true,
        sum: [
            {
                key: "totalPrice",
                format: "EUR",
            },
        ],
    },
    tableHeader: [
        {
            key: "id",
            label: "Id",
        },
        {
            key: "position",
            label: "Position",
        },
        {
            key: "name",
            label: "Bezeichnung",
        },
        {
            key: "description",
            label: "Beschreibung",
        },
        {
            key: "quantity",
            label: "Menge",
        },
        {
            key: "unit",
            label: "MEH",
        },
        {
            key: "price",
            label: "Preis [€]",
        },
        {
            key: "totalPrice",
            label: "Gesamt [€]",
        },
        {
            key: "purchasePrice",
            label: "EK [€]",
        },
    ],
    extendedTimes: [],
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

    /* the table is rendered again after every change, so an earlier one has to go first */
    document.getElementById(tableName)?.querySelectorAll("table").forEach(el => el.remove());

    const table = renderTable(
        tableName,
        config.tableHeader,
        data,
        config.tableOptions
    ) as HTMLTableElement;

    table.addEventListener("rowDelete", deleteItem as EventListener);
    table.addEventListener("rowEdit", editItem as EventListener);
    table.addEventListener("rowUpload", (e: Event) => uploadItem(e as CustomEvent));

    const tbody = table.tBodies[0];
    new DragSortManager(tbody, {
        itemSelector: "tr",
        handleSelector: ".drag-handle",
        ignoreSelector: ".empty-placeholder, .editable-row, .add-row",
        onOrderChange: (positions) => saveItemsOrder(positions.map(position => Number(position.id))),
    });

    addExtraData(data, table);
    config.table = table;
    config.tableName = tableName;
    return table;
}

/* positions, sums and the padded number columns depend on all rows, so the table is reloaded as a whole */
const reloadTable = async (): Promise<void> => {
    if (config.tableName === "") {
        return;
    }

    await getItemsTable(config.tableName, itemsConf.orderId, config.type);
}

const addExtraData = (data: any[], table: HTMLTableElement): void => {
    const extraDataEls = table.querySelectorAll<HTMLButtonElement>(`button.additional-data-btn`);
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
        ajax.delete(`/api/v1/order-items/${data.type}/${data.id}`).then((r: any) => {
            if (!r.success || r.data?.status !== "success") {
                notification("", "failure", r.error ?? r.data?.message ?? "Der Posten konnte nicht gelöscht werden");
                return;
            }

            notification("", "success");
            updatePrice(r.data.price);

            if (itemsConf.editItemId == data.id) {
                closeItemsMenu();
            }

            reloadTable();
        });
    });

    btnDelete.innerHTML = "Ja";
    btnDelete.classList.add("btn-delete");
    settingsContainer.appendChild(btnDelete);
}

/* stores the order the rows were dragged into; the table is reloaded to show the new position numbers */
const saveItemsOrder = (ids: number[]): void => {
    const url = config.type === "offer"
        ? `/api/v1/order-items/offer/${itemsConf.orderId}/positions`
        : `/api/v1/order-items/${itemsConf.orderId}/positions`;

    ajax.put(url, {
        "positions": JSON.stringify(ids),
    }).then((r: any) => {
        if (!r.success || r.data?.status !== "success") {
            notification("", "failure", r.error ?? r.data?.message ?? "Die Reihenfolge konnte nicht gespeichert werden");
        } else {
            notification("", "success");
        }

        reloadTable();
    });
}

const uploadItem = async (e: CustomEvent) => {
    const data = e.detail;
    const uploadFile = await ajax.get(`/api/v1/template/uploadFile`, {
        "params": JSON.stringify({
            "target": "item",
        }),
    });

    const div = document.createElement("div");
    div.innerHTML = uploadFile.data.content;
    const optionsContainer = createPopup(div);
    const btnCancel = optionsContainer.querySelector("button.btn-cancel");
    initFileUploader({
        "item": {
            "location": `/api/v1/order-items/${data.type}/${data.id}/add-files`,
        },
    });

    div.addEventListener("fileUploaded", () => (btnCancel as HTMLButtonElement).click());
}

const editItem = (e: CustomEvent): void => {
    const data = e.detail;
    const type = data.type;

    /* the row's edit button turns into a save button while the item is being edited */
    if (itemsConf.editItemId != 0 && itemsConf.editItemId == data.id) {
        saveEdit();
        return;
    }

    if (type !== "time" && type !== "service") {
        notification("Produktposten können hier nicht bearbeitet werden.", "failure");
        reloadTable();
        return;
    }

    const wasEditing = itemsConf.editItemId != 0;
    openItemsMenu();

    const tab = document.querySelector(`.tab-button[data-target="${type}"]`) as HTMLButtonElement;
    tab.click();

    const addItem = document.querySelector("#addItem") as HTMLElement;
    const saveItem = document.querySelector("#saveItem") as HTMLElement;

    addItem.classList.add("hidden");
    saveItem.classList.remove("hidden");

    itemsConf.editItemId = data.id;
    itemsConf.editItemRow = data.row;

    /* switching from another item: its row still shows the save button */
    if (wasEditing) {
        reloadTable();
    }

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

    /* load the stored time tracking entries, saving sends the complete list back */
    resetExtendedTimes();
    (data.timetable ?? []).forEach((entry: { from_time: number, to_time: number, date: string | null }) => {
        createTimeInputRow({
            start: minutesToTimeString(Number(entry.from_time)),
            end: minutesToTimeString(Number(entry.to_time)),
            date: entry.date ?? "",
        });
    });

    setInpupts({
        "ids": {
            "timeInput": data.time,
            "wage": data.wage,
            "timeDescription": data.description,
            "isFree": Number(data.notcharged) === 1,
            "addToInvoice": Number(data.isinvoice) === 1,
            "getDiscount": data.discount,
        },
    });
}

const minutesToTimeString = (minutes: number): string => {
    const hours = Math.floor(minutes / 60).toString().padStart(2, "0");
    const rest = (minutes % 60).toString().padStart(2, "0");
    return `${hours}:${rest}`;
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
            "isFree": Number(data.notcharged) === 1,
            "addToInvoice": Number(data.isinvoice) === 1,
            "getDiscount": data.discount,
        },
    });

    const select = document.getElementById("selectLeistung") as HTMLSelectElement;
    config.surcharge = Number(select.options[select.selectedIndex]?.dataset.surcharge || 0);
    (document.querySelector("#surcharge") as HTMLInputElement).value = String(config.surcharge);
    (document.getElementById("showMeh") as HTMLElement).innerHTML = data.unit ?? "";
}

const saveEditTime = (): void => {
    const wage = getWage();
    if (!wage) {
        return;
    }

    const data = getTimeData(wage);
    const url = config.type === "offer"
        ? `/api/v1/order-items/offer/${itemsConf.orderId}/times/${itemsConf.editItemId}`
        : `/api/v1/order-items/${itemsConf.orderId}/times/${itemsConf.editItemId}`;

    ajax.put(url, data).then((r: any) => {
        resetTimeInputs(r);
    });
}

const saveEditService = (): void => {
    const url = config.type === "offer"
        ? `/api/v1/order-items/offer/${itemsConf.orderId}/services/${itemsConf.editItemId}`
        : `/api/v1/order-items/${itemsConf.orderId}/services/${itemsConf.editItemId}`;

    ajax.put(url, getServiceData()).then((r: any) => resetServiceInputs(r));
}

const getTimeData = (wage: number) => {
    return {
        time: (document.querySelector("#timeInput") as HTMLInputElement).value,
        wage: wage,
        description: (document.querySelector("#timeDescription") as HTMLInputElement).value,
        noPayment: getIsFree(),
        addToInvoice: getAddToInvoice(),
        discount: (document.querySelector("#getDiscount") as HTMLInputElement).value,
        /* removed rows stay in the list as empty placeholders and must not be stored */
        times: JSON.stringify(config.extendedTimes.filter(time => time.start !== time.end)),
    }
}

const getServiceData = () => {
    return {
        lei: (document.querySelector("#selectLeistung") as HTMLInputElement).value,
        bes: (document.querySelector("#bes") as HTMLInputElement).value,
        ekp: (document.querySelector("#ekp") as HTMLInputElement).value,
        pre: (document.querySelector("#pre") as HTMLInputElement).value,
        meh: (document.querySelector("#meh") as HTMLInputElement).value,
        anz: (document.querySelector("#anz") as HTMLInputElement).value,
        ohneBerechnung: getIsFree(),
        addToInvoice: getAddToInvoice(),
        discount: (document.querySelector("#getDiscount") as HTMLInputElement).value,
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

const saveEdit = (): void => {
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

functionNames.click_saveEdit = saveEdit;

/* shared by add and edit, for times and services */
const handleSaveResponse = (r: any) => {
    if (!r.success || r.data?.status !== "success") {
        notification("", "failure", r.error ?? r.data?.message ?? "Der Posten konnte nicht gespeichert werden");
        return;
    }

    notification("", "success");
    updatePrice(r.data.price);
    closeItemsMenu();
    reloadTable();
}

const resetTimeInputs = handleSaveResponse;
const resetServiceInputs = handleSaveResponse;

const resetExtendedTimes = (): void => {
    config.extendedTimes = [];
    (document.getElementById("extendedTimeInput") as HTMLElement).innerHTML = "";
}

const clearItemInputs = (): void => {
    resetExtendedTimes();

    clearInputs({ "ids": ["timeInput", "timeDescription", "bes", "meh"] });
    (document.getElementById("anz") as HTMLInputElement).value = "1";
    (document.getElementById("ekp") as HTMLInputElement).value = "0";
    (document.getElementById("pre") as HTMLInputElement).value = "0";
    (document.getElementById("getDiscount") as HTMLInputElement).value = "0";
    (document.getElementById("showMeh") as HTMLElement).innerHTML = "";

    const wage = document.getElementById("wage") as HTMLInputElement;
    wage.value = wage.defaultValue;

    const select = document.getElementById("selectLeistung") as HTMLSelectElement;
    select.selectedIndex = 0;

    (document.querySelector("#isFree") as HTMLInputElement).checked = false;
    (document.querySelector("#addToInvoice") as HTMLInputElement).checked = false;
}

const addTime = (): void => {
    const wage = getWage();
    if (!wage) {
        return;
    }

    const data = getTimeData(wage);
    const url = config.type === "offer"
        ? `/api/v1/order-items/offer/${itemsConf.orderId}/times`
        : `/api/v1/order-items/${itemsConf.orderId}/times`;

    ajax.post(url, data).then((r: any) => resetTimeInputs(r));
}

const addService = () => {
    const url = config.type === "offer"
        ? `/api/v1/order-items/offer/${itemsConf.orderId}/services`
        : `/api/v1/order-items/${itemsConf.orderId}/services`;

    ajax.post(url, getServiceData()).then((r: any) => resetServiceInputs(r));
}

/* bound to both "Hinzufügen" (menu closed) and "Abbrechen" (menu open) */
functionNames.click_showItemsMenu = () => {
    const itemsMenu = document.querySelector("#showPostenAdd") as HTMLElement;

    if (itemsMenu.classList.contains("hidden")) {
        openItemsMenu();
        return;
    }

    const wasEditing = itemsConf.editItemId != 0;
    closeItemsMenu();

    /* the edited row still shows the save button */
    if (wasEditing) {
        reloadTable();
    }
}

const openItemsMenu = (): void => {
    (document.querySelector("#showPostenAdd") as HTMLElement).classList.remove("hidden");
    (document.querySelector("#showItemsMenu") as HTMLElement).classList.add("hidden");
}

/* hides the form, leaves edit mode and resets the inputs for the next posten */
const closeItemsMenu = (): void => {
    (document.querySelector("#showPostenAdd") as HTMLElement).classList.add("hidden");
    (document.querySelector("#showItemsMenu") as HTMLElement).classList.remove("hidden");

    (document.querySelector("#addItem") as HTMLElement).classList.remove("hidden");
    (document.querySelector("#saveItem") as HTMLElement).classList.add("hidden");

    itemsConf.editItemRow = null;
    itemsConf.editItemId = 0;

    clearItemInputs();
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
    createTimeInputRow(undefined, true);
}

const createTimeInputRow = (initial: ExtendedTime = { start: "00:00", end: "00:00", date: "" }, focus: boolean = false): void => {
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

    if (initial.start !== initial.end) {
        start.value = initial.start;
        end.value = initial.end;
        dateInput.value = initial.date;
    }

    config.extendedTimes.push({ ...initial });
    if (focus) {
        start.focus();
    }
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

const updatePrice = (price: number | null | undefined): void => {
    /* offers have no order total */
    const el = document.getElementById("totalPrice");
    if (!el || price === null || price === undefined) {
        return;
    }

    el.innerText = new Intl.NumberFormat("de-DE", {
        "style": "currency",
        "currency": "EUR"
    }).format(price);
}

export const initInvoiceItems = (orderId = 0, type: string = "order"): void => {
    itemsConf.orderId = orderId;
    config.type = type;
    addBindings(functionNames);
    initItems();
}
