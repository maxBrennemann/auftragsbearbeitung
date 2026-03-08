import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings"
import { notification } from "js-classes/notifications";

import { createHeader, createTable, addRow, fetchAndRenderTable } from "../classes/table";
import { tableConfig } from "../classes/tableconfig";
import { initFileUploader } from "../classes/upload";
import { clearInputs } from "../global";
import { FunctionMap, TableOptions } from "../types/types";
import { loader } from "../classes/helpers";
import { createIcons, Download } from 'lucide';

const fnNames = {} as FunctionMap;
type RowData = Record<string, string>;

function initEventListeners() {
    addBindings(fnNames);
    timeTracking();

    initFileUploader({
        "companyLogo": {
            "location": `/api/v1/settings/add-logo`,
        },
    });
    const fileUpload = document.querySelector(`input[data-type="companyLogo"]`) as HTMLInputElement;
    fileUpload.addEventListener("fileUploaded", (r: any) => {
        const companyLogo = document.getElementById("companyLogo") as HTMLElement;
        companyLogo.classList.remove("hidden");
        const img = companyLogo.querySelector("img") as HTMLImageElement;
        img.src = r.detail.file;
    });

    const addCategoryBtn = document.getElementById("addCategory") as HTMLButtonElement;
    addCategoryBtn.addEventListener("click", addCategory);

    getCategories();
    showTree();

    createOrderTypeTable();
    createWholesalerTable();
    createUserTable();
    createPDFTextTable();
    showFilesInfo();

    createIcons({
        icons: {
            Download
        }
    });
}

const timeTracking = () => {
    const switchTimeTracking = document.getElementById("showTimeTracking");
    if (switchTimeTracking == null) {
        return;
    }

    switchTimeTracking.addEventListener("change", () => {
        ajax.put("/api/v1/settings/global-timetracking").then(r => {
            if (r.data.status == "success") {
                notification("", "success");
                const el = document.getElementById("timeTrackingContainer") as HTMLElement;
                if (r.data.display == "false") {
                    el.classList.add("hidden");
                    el.classList.remove("inline-flex");
                } else {
                    el.classList.remove("hidden");
                    el.classList.add("inline-flex");
                }
            } else {
                notification("", "failure");
            }
        });
    });
}

function getCategories() {
    ajax.get("/api/v1/category").then(response => {
        const categories = response.data;
        const select = document.getElementById("parentCategory") as HTMLElement;
        Object.keys(categories).forEach(key => {
            const category = categories[key];
            const option = document.createElement("option");
            option.value = category.id;
            option.text = category.name;
            select.appendChild(option);
        });
    });
}

function addCategory() {
    const newCategory = document.getElementById("newCategory") as HTMLInputElement;
    const parentCategory = document.getElementById("parentCategory") as HTMLSelectElement;

    if (newCategory.value === "") {
        notification("", "failure");
        return;
    }

    ajax.post(`/api/v1/category`, {
        name: newCategory.value,
        parent: parentCategory.value || null,
    }).then(r => {
        clearInputs({ "id": "newCategory" });

        if (r.data.status !== "success") {
            notification("", "failure");
            return;
        }

        notification("", "success");
    });
}

function showTree() {
    ajax.get("/api/v1/category/tree").then(response => {
        const tree = document.getElementById("categoryTree") as HTMLElement;
        tree.innerHTML = "";

        createCategoryTree(tree, response.data);
    });
}

function createCategoryTree(anchor: HTMLElement, categories: any[]) {
    const ul = document.createElement("ul");
    ul.classList.add("pl-2", "list-disc");
    anchor.appendChild(ul);

    categories.forEach(category => {
        const li = document.createElement("li");
        li.innerHTML = `
            <span class="cursor-pointer" data-id="${category.id}">${category.name}</span>
            <button class="btn-primary">Bearbeiten</button>`;

        li.classList.add("cursor-pointer");
        li.dataset.id = category.id;
        ul.appendChild(li);

        createCategoryTree(ul, category.children);
    });
}

fnNames.click_toggleCache = () => {
    const cacheStatusSwitch = document.getElementById("cacheStatusSwitch") as HTMLInputElement;

    ajax.put(`/api/v1/settings/cache`, {
        "status": cacheStatusSwitch.checked,
    }).then(r => {
        if (r.data.status == "success") {
            notification("", "success");
        }
    });
}

fnNames.click_deleteCache = () => {
    ajax.delete(`/api/v1/settings/cache`).then(r => {
        if (r.data.status == "success") {
            notification("", "success");
        }
    });
}

fnNames.write_changeSetting = e => {
    const target = e.currentTarget;
    const value = target.value;
    const setting = target.dataset.setting;

    ajax.put(`/api/v1/settings/config/${setting}`, {
        "value": value,
    }).then(r => {
        if (r.data.status == "success") {
            notification("", "success");
        } else {
            notification("", "failure");
        }
    });
}

fnNames.click_downloadDatabase = () => {
    const downloadLink = document.getElementById("download_db") as HTMLAnchorElement;

    ajax.post(`/api/v1/settings/backup`).then(r => {
        downloadLink.download = r.data.filename;
        downloadLink.href = r.data.url;
        downloadLink.click();

        if (r.data.status == "success") {
            notification("", "success");
        }
    });
}

fnNames.click_downloadAllFiles = () => {
    const downloadLink = document.getElementById("download_files") as HTMLAnchorElement;

    ajax.post(`/api/v1/settings/file-backup`).then(r => {
        downloadLink.download = r.data.filename;
        downloadLink.href = r.data.url;
        downloadLink.click();

        if (r.data.status == "success") {
            notification("", "success");
        }
    });
}

fnNames.click_clearFiles = () => {
    ajax.post(`/api/v1/upload/clear-files`).then(r => {
        if (r.data.status == "success") {
            notification(`${r.data.deleted_count} Dateien entfernt.`, "success",);
        } else {
            notification("", "failure", JSON.stringify(r.data));
        }
    });
}

fnNames.click_adjustFiles = () => {
    ajax.post(`/api/v1/upload/adjust-files`).then(r => {
        if (r.data.message == "OK") {
            notification("", "success");
        } else {
            notification("", "failure", JSON.stringify(r.data));
        }
    });
}

const createOrderTypeTable = async () => {
    const config = tableConfig["auftragstyp"];
    const options = {
        "hideOptions": ["delete", "check", "move", "add"],
        "primaryKey": config.primaryKey,
        "autoSort": true,
        "styles": {
            "table": {
                "className": ["w-full"],
            },
        },
    } as TableOptions;

    const table = await fetchAndRenderTable("orderTypes", "auftragstyp", options) as HTMLTableElement;
    table.addEventListener("rowInsert", () => addOrderType(table, options));
}

const createWholesalerTable = async () => {
    const table = createTable("wholesalerTypes", {
        "styles": {
            "table": {
                "className": ["w-full"],
            },
        },
    }) as HTMLTableElement;

    const config = tableConfig["einkauf"];
    createHeader(config.columns, table);

    const data = await ajax.get(`/api/v1/tables/einkauf`);

    data.data.forEach((row: any) => {
        addRow(row, table, {
            "hideOptions": ["delete", "check", "move", "add"],
        });
    });
}

const createUserTable = async () => {
    const options = {
        "styles": {
            "table": {
                "className": ["w-full"],
            },
        },
        "primaryKey": "id",
        "hideOptions": ["all"],
        "link": "/mitarbeiter?id=",
    };

    const table = createTable("userTable", options);
    const config = tableConfig["user"];
    createHeader(config.columns, table, options);

    const data = await ajax.get(`/api/v1/tables/user`);
    data.data.forEach((row: any) => {
        addRow(row, table, options);
    });
}

const createPDFTextTable = async () => {
    const options = {
        "styles": {
            "table": {
                "className": ["w-full"],
            },
        },
        "primaryKey": "id",
        "hideOptions": ["check", "move", "add"],
    } as TableOptions;

    const table = createTable("pdfTextsCont", options) as HTMLTableElement;
    const config = tableConfig["pdf_texts"];
    createHeader(config.columns, table, options);

    const data = await ajax.get(`/api/v1/tables/pdf_texts`);
    data.data.forEach((row: any) => {
        addRow(row, table, options);
    });

    table.addEventListener("rowInsert", () => addPDFText(table, options));
    table.addEventListener("rowDelete", (event: any) => {
        const data = event.detail;
        const id = data.id;

        const conditions = JSON.stringify({
            "id": id,
        });
        ajax.delete(`/api/v1/tables/pdf_texts`, {
            "conditions": conditions,
            //"customerId": customerData.id,
        });
    });
}

const addPDFText = async (table: HTMLTableElement, options: TableOptions) => {
    const lastRow = table.querySelector("tbody")?.lastElementChild?.children;

    if (!lastRow) return;

    const data: RowData = {};
    Array.from(lastRow).forEach(el => {
        const cell = el as HTMLElement;
        const key = cell.dataset.key;

        if (!key) return;

        const value = cell.innerHTML;
        data[key] = value;
    });

    const response = await ajax.post<{ data: Record<string, string> }>(`/api/v1/tables/pdf_texts`, {
        "conditions": JSON.stringify(data),
    });

    Object.assign(data, response.data);

    addRow(data, table, options);
}

const addOrderType = async (table: HTMLTableElement, options: TableOptions) => {
    const lastRow = table?.querySelector("tbody")?.lastElementChild?.children;

    if (!lastRow) return;

    const data: RowData = {};

    Array.from(lastRow).forEach(el => {
        const cell = el as HTMLElement;
        const key = cell.dataset.key;

        if (!key) return;

        const value = cell.innerHTML;
        data[key] = value;
    });

    const response = await ajax.post<{ data: Record<string, string> }>(`/api/v1/tables/auftragstyp`, {
        "conditions": JSON.stringify(data),
    });

    Object.assign(data, response.data);

    addRow(data, table, options);
}

const showFilesInfo = () => {
    ajax.get(`/api/v1/settings/files/info`).then(r => {
        const el = document.getElementById("showFilesInfo") as HTMLElement;
        el.innerHTML = `Es sind ${r.data.count} Dateien mit einer Gesamtgröße von ${r.data.size}MB hochgeladen/ generiert.`;
    });
}

fnNames.click_sendNewInvoiceNumber = () => {
    if (!confirm("Setzt die aktuelle Rechnungsnummer fest. Bitte nicht unnötig überschreiben.")) {
        return;
    }

    const invoiceNumber = document.getElementById("newInvoiceNumber") as HTMLInputElement;
    ajax.post(`/api/v1/invoice/init-invoice-number`, {
        "invoiceNumber": invoiceNumber.value,
    }).then(r => {
        if (r.data.message == "OK") {
            notification("", "success");
        }
    });
}

loader(initEventListeners);
