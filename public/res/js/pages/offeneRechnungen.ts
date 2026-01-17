import { format } from "date-fns";
import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings";

import { loader } from "../classes/helpers";
import { addRow, createHeader, createTable } from "../classes/table";
import { FunctionMap } from "../types/types";

const fnNames: FunctionMap = {};
const config = {
    show: "all",
};

fnNames.click_showDueInvoices = () => {
    config.show = config.show === "due" ? "all" : "due";
    const button = document.querySelector('[data-fun="showDueInvoices"]') as HTMLButtonElement;
    button.textContent = config.show === "due" ? "Alle offenen Rechnungen" : "Fällige Rechnungen";

    createInvoiceTable();
}

const getOpenInvoiceData = async () => {
    const data = await ajax.get(`/api/v1/invoice/open?show=${config.show}`);
    return data.data.data;
}

const createInvoiceTable = async () => {
    document.getElementById("openInvoiceTable")!.innerHTML = "";
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
        {
            "key": "Faelligkeitsdatum",
            "label": "Fälligkeitsdatum"
        },
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

loader(() => {
    addBindings(fnNames);
    createInvoiceTable();
});
