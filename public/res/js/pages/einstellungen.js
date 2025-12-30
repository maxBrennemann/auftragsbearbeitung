//@ts-nocheck

import { ajax } from "js-classes/ajax.js";
import { addBindings } from "js-classes/bindings.js"
import { notification } from "js-classes/notifications.js";

import { createHeader, createTable, addRow, fetchAndRenderTable } from "../classes/table.ts";
import { tableConfig } from "../classes/tableconfig.ts";
import { initFileUploader } from "../classes/upload.js";
import { clearInputs } from "../global.js";

const fnNames = {};

function initEventListeners() {
    addBindings(fnNames);
    timeTracking();

    initFileUploader({
        "companyLogo": {
            "location": `/api/v1/settings/add-logo`,
        },
    });
    const fileUpload = document.querySelector(`input[data-type="companyLogo"]`);
    fileUpload.addEventListener("fileUploaded", r => {
        const companyLogo = document.getElementById("companyLogo");
        companyLogo.classList.remove("hidden");
        const img = companyLogo.querySelector("img");
        img.src = r.detail.file;
    });

    const addCategoryBtn = document.getElementById("addCategory");
    addCategoryBtn.addEventListener("click", addCategory);

    getCategories();
    showTree();

    createOrderTypeTable();
    createWholesalerTable();
    createUserTable();
    createPDFTextTable();
    showFilesInfo();
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
                const el = document.getElementById("timeTrackingContainer");
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
        const select = document.getElementById("parentCategory");
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
    const newCategory = document.getElementById("newCategory").value;
    const parentCategory = document.getElementById("parentCategory").value;

    if (newCategory === "") {
        notification("", "failure");
        return;
    }

    ajax.post(`/api/v1/category`, {
        name: newCategory,
        parent: parentCategory,
    }).then(r => {
        clearInputs({ "id": "newCategory" });

        if (r.data.status !== "success") {
            notification("", "failure");
            return;
        }

        notification("", "success");
    });
}

function editCategory() {
    const category = document.getElementById("category").value;
    const newName = document.getElementById("newName").value;
    const newParent = document.getElementById("newParent").value;

    ajax.put(`/api/v1/category/${category}`, {
        name: newName,
        parent: newParent,
    }).then(r => {
        if (r.data.message == "ok") {
            notification("", "success");
        }
    });
}

function showTree() {
    ajax.get("/api/v1/category/tree").then(response => {
        const tree = document.getElementById("categoryTree");
        tree.innerHTML = "";

        createCategoryTree(tree, response.data);
    });
}

function createCategoryTree(anchor, categories) {
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
    const value = document.getElementById("cacheStatusSwitch").value;

    ajax.put(`/api/v1/settings/cache`, {
        "status": value,
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
    ajax.post(`/api/v1/settings/backup`).then(r => {
        document.getElementById("download_db").download = r.data.filename;
        document.getElementById("download_db").href = r.data.url;
        document.getElementById("download_db").click();

        if (r.data.status == "success") {
            notification("", "success");
        }
    });
}

fnNames.click_downloadAllFiles = () => {
    ajax.post(`/api/v1/settings/file-backup`).then(r => {
        document.getElementById("download_files").download = r.data.filename;
        document.getElementById("download_files").href = r.data.url;
        document.getElementById("download_files").click();

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
    };

    const table = await fetchAndRenderTable("orderTypes", "auftragstyp", options);
    table.addEventListener("rowInsert", () => addOrderType(table, options));
}

const createWholesalerTable = async () => {
    const table = createTable("wholesalerTypes", {
        "styles": {
            "table": {
                "className": ["w-full"],
            },
        },
    });
    const config = tableConfig["einkauf"];
    createHeader(config.columns, table);

    const data = await ajax.get(`/api/v1/tables/einkauf`);

    data.data.forEach(row => {
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
    data.data.forEach(row => {
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
    };

    const table = createTable("pdfTextsCont", options);
    const config = tableConfig["pdf_texts"];
    createHeader(config.columns, table, options);

    const data = await ajax.get(`/api/v1/tables/pdf_texts`);
    data.data.forEach(row => {
        addRow(row, table, options);
    });

    table.addEventListener("rowInsert", () => addPDFText(table, options));
    table.addEventListener("rowDelete", (event) => {
        const data = event.detail;
        const id = data.id;

        const conditions = JSON.stringify({
            "id": id,
        });
        ajax.delete(`/api/v1/tables/pdf_texts`, {
            "conditions": conditions,
            "customerId": customerData.id,
        });
    });
}

const addPDFText = async (table, options) => {
    const lastRow = table.querySelector("tbody").lastChild.children;
    const data = {};
    Array.from(lastRow).forEach(el => {
        const key = el.dataset.key;
        if (key == undefined) {
            return;
        }
        const value = el.innerHTML;
        data[key] = value;
    });

    const response = await ajax.post(`/api/v1/tables/pdf_texts`, {
        "conditions": JSON.stringify(data),
    });
    for (var i in response.data) {
        data[i] = response.data[i];
    }
    addRow(data, table, options);
}

const addOrderType = async (table, options) => {
    const lastRow = table.querySelector("tbody").lastChild.children;
    const data = {};
    Array.from(lastRow).forEach(el => {
        const key = el.dataset.key;
        if (key == undefined) {
            return;
        }
        const value = el.innerHTML;
        data[key] = value;
    });
    const response = await ajax.post(`/api/v1/tables/auftragstyp`, {
        "conditions": JSON.stringify(data),
    });
    for (var i in response.data) {
        data[i] = response.data[i];
    }
    addRow(data, table, options);
}

const showFilesInfo = () => {
    ajax.get(`/api/v1/settings/files/info`).then(r => {
        const el = document.getElementById("showFilesInfo");
        el.innerHTML = `Es sind ${r.data.count} Dateien mit einer Gesamtgröße von ${r.data.size}MB hochgeladen/ generiert.`;
    });
}

fnNames.click_sendNewInvoiceNumber = () => {
    if (!confirm("Setzt die aktuelle Rechnungsnummer fest. Bitte nicht unnötig überschreiben.")) {
        return;
    }

    const invoiceNumber = document.getElementById("newInvoiceNumber").value;
    ajax.post(`/api/v1/invoice/init-invoice-number`, {
        "invoiceNumber": invoiceNumber
    }).then(r => {
        if (r.data.message == "OK") {
            notification("", "success");
        }
    });
}

if (document.readyState !== 'loading') {
    initEventListeners();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initEventListeners();
    });
}
