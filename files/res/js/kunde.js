import { addRow, createHeader, createTable, fetchAndRenderTable, renderTable } from "./classes/table.js";
import { tableConfig } from "./classes/tableconfig.js";
import { addBindings } from "./classes/bindings.js";
import { ajax } from "./classes/ajax.js";
import { notification } from "./classes/notifications.js";

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
    const link = `/neuer-auftrag?kdnr=${customerData.id}`;
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
        "archive": false,
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

const addressTable = () => {
    const options = {
        "conditions": {
            "id_customer": customerData.id,
        },
        "hide": ["id", "id_customer"],
        "hideOptions": ["check", "add", "move"],
    };
    fetchAndRenderTable("addressTable", "address", options);
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
