import { ajax } from "js-classes/ajax.js";

import { format } from "date-fns";
import { addRow, createHeader, createTable } from "../classes/table.js";

const init = () => {
    createInvoiceTable();
}

const getOpenInvoiceData = async () => {
    const data = await ajax.get(`/api/v1/invoice/open`);
    return data.data.data;
}

const createInvoiceTable = async () => {
    const table = createTable("openInvoiceTable") as HTMLTableElement;
    const columns = [
        {
            "key": "Nummer",
            "label": "Nummer"
        },
        {
            "key": "invoice_number",
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
            "label": "Auftragsdatum"
        },
        {
            "key": "Rechnungsdatum",
            "label": "Rechnungsdatum"
        },
        /*{
            "key": "Faelligkeitsdatum",
            "label": "Fälligkeitsdatum"
        },*/
        {
            "key": "Name",
            "label": "Name"
        },
        {
            "key": "Summe",
            "label": "Summe (netto)"
        },
        {
            "key": "Summe_mwst",
            "label": "Summe (brutto)"
        },
    ];
    const columnConfig = {
        "hideOptions": ["edit", "delete", "addRow", "add", "move"],
        "hide": ["Rechnungsnummer"],
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
    data.forEach((row: any) => {
        addRow(row, table, columnConfig);
    });

    table.addEventListener("rowCheck", async (event: any) => {
        const data = event.detail;
        const id = data.Rechnungsnummer;

        const status = await ajax.post(`/api/v1/invoice/${id}/paid`, {
            "date": format(new Date(), "yyy-MM-dd"),
        });
        if (status.data.status == "success") {
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
