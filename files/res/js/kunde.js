//@ts-nocheck

import { ajax } from "js-classes/ajax.js";
import { addBindings } from "js-classes/bindings.js"
import { notification } from "js-classes/notifications.js";

import { addRow, createHeader, createTable, fetchAndRenderTable, renderTable } from "./classes/table.js";
import { tableConfig } from "./classes/tableconfig.js";

const customerData = {
    id: document.getElementById("idCustomer")?.value ?? 0,
};

const fnNames = {};

const init = () => {
    if (customerData.id == 0) {
        return;
    }

    addBindings(fnNames);
    addressTable();
    colorTable();
    contactPersonTable();
    vehiclesTable();
}

fnNames.click_createNewOrder = () => {
    const link = `/neuer-auftrag?id=${customerData.id}`;
    const linkEl = document.createElement("a");
    linkEl.href = link;
    linkEl.click();
}

fnNames.click_deleteCustomer = () => {
    if (!confirm("Möchten Sie den Kunden wirklich löschen? Alle verbundenen Aufträge und Daten werden gelöscht.")) {
        return;
    }

    ajax.delete(`/api/v1/customer/${customerData.id}/`).then(() => {
        window.location.replace();
    });
}

fnNames.click_rearchive = async e => {
    const target = e.currentTarget;
    const id = target.dataset.orderId;
    const response = await ajax.put(`/api/v1/order/${id}/archive`, {
        "status": "unarchive",
    });

    if (response.status == "success") {
        notification("", "success");

        const orderCard = target.closest(".orderCard");
        const options = orderCard.querySelectorAll(".orderOptions");
        const orderDisabled = orderCard.querySelector(".orderDisabled");

        options.forEach(option => {
            option.parentNode.removeChild(option);
        })

        orderDisabled.parentNode.removeChild(orderDisabled);
    }
}

fnNames.click_saveCustomerData = () => {
    ajax.put(`/api/v1/customer/${customerData.id}`, {
        "prename": document.getElementById("prename").value,
        "lastname": document.getElementById("lastname").value,
        "companyname": document.getElementById("companyname").value,
        "email": document.getElementById("email").value,
        "website": document.getElementById("website").value,
        "phoneLandline": document.getElementById("phoneLandline").value,
        "phoneMobile": document.getElementById("phoneMobile").value,
        "fax": document.getElementById("fax").value,
    }).then(r => {
        if (r.message == "OK") {
            notification("", "success");
        }
    });
}

fnNames.write_setCustomerNote = async e => {
    const value = e.currentTarget.value;
    const r = await ajax.put(`/api/v1/customer/${customerData.id}/note`, {
        "note": value,
    });
    if (r.message == "OK") {
        notification("", "success");
    }
}

fnNames.click_mergeCustomer = () => {
    
}

const vehiclesTable = async () => {
    const table = createTable("vehiclesTable");
    const config = tableConfig["fahrzeuge"];
    const columnConfig = {
        "hide": ["Kundennummer"],
        "hideOptions": ["all"],
        "primaryKey": "Nummer",
        "link": "/fahrzeug?id=",
    };

    createHeader(config.columns, table, columnConfig);

    const conditions = JSON.stringify({
        "Kundennummer": customerData.id,
    });
    const data = await ajax.get(`/api/v1/tables/fahrzeuge`, {
        "conditions": conditions,
    });

    data.forEach(row => {
        addRow(row, table, columnConfig);
    });
}

const contactPersonTable = async () => {
    const table = createTable("contactPersonTable");
    const config = tableConfig["ansprechpartner"];
    const columnConfig = {
        "hide": ["Nummer", "Kundennummer"],
        "hideOptions": ["check", "add", "move"],
    };

    createHeader(config.columns, table, columnConfig);

    const conditions = JSON.stringify({
        "Kundennummer": customerData.id,
    });
    const data = await ajax.get(`/api/v1/tables/ansprechpartner`, {
        "conditions": conditions,
    });

    data.forEach(row => {
        addRow(row, table, columnConfig);
    });

    table.addEventListener("rowInsert", () => addContactPerson(table, columnConfig));
    table.addEventListener("rowDelete", (event) => {
        const data = event.detail;
        const id = data.Nummer;

        const conditions = JSON.stringify({
            "Nummer": id,
        });
        ajax.delete(`/api/v1/tables/ansprechpartner`, {
            "conditions": conditions,
            "customerId": customerData.id,
        });
    });
}

const addContactPerson = async (table, options) => {
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
    data["Kundennummer"] = customerData.id;

    const response = await ajax.post(`/api/v1/tables/ansprechpartner`, {
        "conditions": JSON.stringify(data),
    });
    for (var i in response) {
        data[i] = response[i];
    }
    addRow(data, table, options);
}

const addressTable = async () => {
    const options = {
        "conditions": {
            "id_customer": customerData.id,
        },
        "hide": ["id", "id_customer"],
        "hideOptions": ["check", "add", "move"],
    };
    const table = await fetchAndRenderTable("addressTable", "address", options);
    table.addEventListener("rowInsert", () => addAddress(table, options));
}

const addAddress = async (table, options) => {
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
    data["id_customer"] = customerData.id;

    if (data["art"] == "") {
        data["art"] = 3;
    }

    const response = await ajax.post(`/api/v1/tables/address`, {
        "conditions": JSON.stringify(data),
    });
    for (var i in response) {
        data[i] = response[i];
    }
    addRow(data, table, options);
}

const colorTable = async () => {
    const data = await ajax.get(`/api/v1/customer/${customerData.id}/colors`);
    const config = tableConfig["color"];
    config.columns.unshift({
        "key": "id_order",
        "label": "Auftrag",
    });
    const options = {
        "hide": ["id"],
        "hideOptions": ["all"],
    };
    renderTable("colorTable", config.columns, data, options);
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
