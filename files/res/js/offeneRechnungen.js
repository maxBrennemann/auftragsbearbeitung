import { ajax } from "js-classes/ajax.js";

import { addRow, createHeader, createTable } from "./classes/table.js";

const init = () => {
    createInvoiceTable();
}

const getOpenInvoiceData = async () => {
    const data = await ajax.get(`/api/v1/invoice/open`);
    return data.data;
}

const createInvoiceTable = async () => {
    const table = createTable("openInvoiceTable");
    const columns = [
        {
            "key": "Nummer",
            "label": "Nummer"
        },
        {
            "key": "Rechnungsnummer",
            "label": "Rechnungsnummer"
        },
        {
            "key": "Bezeichnung",
            "label": "Bezeichnung"
        },
        {
            "key": "Beschreibung",
            "label": "Beschreibung"
        },
        {
            "key": "Kundennummer",
            "label": "Kundennummer"
        },
        {
            "key": "Datum",
            "label": "Datum"
        },
        {
            "key": "Firmenname",
            "label": "Firmenname"
        },
        {
            "key": "Summe",
            "label": "Summe"
        },
    ];
    const columnConfig = {
        "hideOptions": ["edit", "delete", "addRow", "add", "move"],
        "hide": [""],
        "primaryKey": "Nummer",
        "link": "/auftrag?id=",
        "styles": {
            "thead": {
                "className": ["sticky", "top-0"],
            },
            "key": {
                "Bezeichnung": ["w-40", "truncate"],
                "Beschreibung": ["w-96", "truncate"],
                "Firmenname": ["w-40", "truncate"],
            },
        },
    };

    createHeader(columns, table, columnConfig);

    const data = await getOpenInvoiceData();
    data.forEach(row => {
        addRow(row, table, columnConfig);
    });

    table.addEventListener("rowCheck", async (event) => {
        const data = event.detail;
        const id = data.Rechnungsnummer;

        const status = await ajax.post(`/api/v1/invoice/${id}/paid`);
        if (status.status == "success") {
            data.row.remove();
        }
    });
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
