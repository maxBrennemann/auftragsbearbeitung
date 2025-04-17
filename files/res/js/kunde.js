import { addRow, createHeader, createTable, fetchAndRenderTable, renderTable } from "./classes/table.js";
import { tableConfig } from "./js/tableconfig.js";
import { initBindings } from "./classes/bindings.js";
import { ajax } from "./classes/ajax.js";
import { infoSaveSuccessfull } from "./classes/statusInfo.js";

const globalProperties = {
    changedData: {},
    search: null,
    addressSet: [],
    addressCount: 0,
    addrCount: null,
};

const customerData = {
    id: document.getElementById("kdnr")?.value ?? 0,
};

const fnNames = {};

const init = () => {
    if (customerData.id == 0) {
        return;
    }

    initBindings(fnNames);
    initCustomer();
    initialize();
    addressTable();
    colorTable();
}

function initialize() {
    contactPersonTable();
    vehiclesTable();

    var showKundendaten = document.getElementById("showKundendaten");
    if (showKundendaten == null) {
        return;
    }

    const sendKundendaten = document.getElementById("sendKundendaten");
    sendKundendaten.addEventListener("click", kundendatenAbsenden);

    var inputs = showKundendaten.getElementsByTagName("input");
    for (var i = 0; i < inputs.length; i++) {
        inputs[i].addEventListener("input", function (e) {
            document.getElementById("sendKundendaten").disabled = false;
            var column = e.target.id;
            switch (column) {
                case "vorname":
                    column = "Vorname";
                    break;
                case "nachname":
                    column = "Nachname";
                    break;
                case "firmenname":
                    column = "Firmenname";
                    break;
                case "email":
                    column = "Email";
                    break;
                case "festnetz":
                    column = "TelefonFestnetz";
                    break;
                case "mobil":
                    column = "TelefonMobil";
                    break;
                case "website":
                    column = "Website";
                    break;
            }

            globalProperties.changedData[column] = e.target.value;
        }, false);
    }
}

function kundendatenAbsenden() {
    var kdnr = document.getElementById("kdnr").value;
    var data = `getReason=setData&type=kunde&kdnr=${kdnr}&addressCount=${globalProperties.addressCount}&`;
    var count = 0;

    for (var key in globalProperties.changedData) {
        if (globalProperties.changedData.hasOwnProperty(key)) {
            data += key + "=" + globalProperties.changedData[key] + "&" + "dataKey" + count + "=" + key + "&";
            count++;
        }
    }

    data += "number=" + count;

    const insertKundendaten = new AjaxCall(data, "POST", window.location.href);
    insertKundendaten.makeAjaxCall(function (response) {
        console.log(response);
        if (response == "ok")
            infoSaveSuccessfull("success");
        else
            infoSaveSuccessfull();

        /* reset object, so that values are not sended twice */
        globalProperties.changedData = {};
    });
}

function initCustomer() {
    const notesTextarea = document.getElementById('notesTextarea');
    if (notesTextarea == null) {
        return;
    }

    notesTextarea.addEventListener('input', function () {
        notesTextarea.style.height = 'auto';
        notesTextarea.style.height = notesTextarea.scrollHeight + 'px';

        const btn = document.getElementById('btnSendNotes');
        btn.disabled = false;
    });

    const btn = document.getElementById('btnSendNotes');
    btn.addEventListener('click', function () {
        const kundennummer = document.getElementById("kdnr").value;
        const notes = document.getElementById("notesTextarea").value;

        ajax.post({
            r: 'setNotes',
            kdnr: kundennummer,
            notes: notes
        });
    });

    globalProperties.addrCount = document.getElementById("addrCount");
    globalProperties.search = document.getElementById("performSearch");
    if (globalProperties.search != null) {
        globalProperties.search.addEventListener("keyup", function (event) {
            if (event.key === "Enter") {
                location.href = event.target.dataset.url + "?mode=search&query=" + event.target.value;
            }
        });
    }
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
        infoSaveSuccessfull("success");

        const orderCard = target.closest(".orderCard");
        const options = orderCard.querySelectorAll(".orderOptions");
        const orderDisabled = orderCard.querySelector(".orderDisabled");

        options.forEach(option => {
            option.parentNode.removeChild(option);
        })

        orderDisabled.parentNode.removeChild(orderDisabled);
    }
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
