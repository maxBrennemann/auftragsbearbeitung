import { format } from "date-fns";
import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings";

import { loader } from "../classes/helpers";
import { addRow, createHeader, createSumRow, createTable } from "../classes/table";
import { FunctionMap } from "../types/types";

const fnNames: FunctionMap = {};
const config = {
    show: "all",
};

fnNames.write_showDueInvoices = () => {
    config.show = config.show === "due" ? "all" : "due";
    const input = document.querySelector('[data-fun="showDueInvoices"]') as HTMLInputElement;
    const text = input.nextElementSibling?.nextElementSibling as HTMLSpanElement;
    text.innerHTML = config.show === "due" ? "Fällige Rechnungen" : "Alle offenen Rechnungen";

    createInvoiceTable();
}

const getOpenInvoiceData = async () => {
    const response = await ajax.get(`/api/v1/invoice/open?show=${config.show}`);
    return response.data.data;
}

const createInvoiceTable = async () => {
    document.getElementById("openInvoiceTable")!.innerHTML = "";
    const table = createTable("openInvoiceTable") as HTMLTableElement;
    const rate = parseInt((document.getElementById("inputVatHidden") as HTMLInputElement).value ?? "0");
    const grossLabel = rate > 0 ? `Summe (brutto, inkl. ${rate}% MwSt.)` : 'Summe (brutto)';
    const columns = [
        {
            "key": "invoice_number",
            "label": "Re-Nr."
        },
        {
            "key": "Nummer",
            "label": "Nummer"
        },
        {
            "key": "Summe",
            "label": "Summe (netto)"
        },
        {
            "key": "Summe_mwst",
            "label": grossLabel
        },
        {
            "key": "Kundennummer",
            "label": "Kdnr."
        },
        {
            "key": "Name",
            "label": "Kundenname"
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
    ];
    const columnConfig = {
        "hideOptions": ["edit", "delete", "addRow", "add", "move"],
        "hide": ["Rechnungsnummer"],
        "primaryKey": "Nummer",
        "link": "/auftrag?id=",
        "sum": [
            { "key": "Summe", "format": "EUR" },
            { "key": "Summe_mwst", "format": "EUR" },
        ],
        "styles": {
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
        addRow(row, table, columnConfig, columns);
        addReminderActions(row, table);
    });

    createSumRow(data, table, columnConfig, columns);

    table.addEventListener("rowCheck", async (event: any) => {
        const data = event.detail;
        const id = data.Rechnungsnummer;

        const status = await ajax.post(`/api/v1/invoice/${id}/paid`, {
            "date": format(new Date(), "yyy-MM-dd"),
        });
        if (status.data.status == "success") {
            createInvoiceTable();
        }
    });
}

const addReminderActions = (row: any, table: HTMLTableElement) => {
    const tbody = table.querySelector("tbody") as HTMLTableSectionElement;
    const tr = tbody.lastElementChild as HTMLTableRowElement;
    const actionsCell = tr.lastElementChild as HTMLTableCellElement;
    const invoiceId = row.Rechnungsnummer;
    const orderId = row.Nummer;

    const previewLink = document.createElement("a");
    previewLink.href = `/api/v1/invoice/${invoiceId}/reminder/pdf?orderId=${orderId}`;
    previewLink.target = "_blank";
    previewLink.title = "Mahnung-Vorschau anzeigen";
    previewLink.textContent = "Vorschau";
    previewLink.className = "inline-flex border-0 bg-zinc-400 text-white p-1 rounded-md ml-1 cursor-pointer";
    actionsCell.appendChild(previewLink);

    const sendBtn = document.createElement("button");
    sendBtn.title = "Mahnung an den Kunden senden";
    sendBtn.textContent = "Mahnung senden";
    sendBtn.className = "inline-flex border-0 bg-orange-400 text-white p-1 rounded-md ml-1 cursor-pointer";
    sendBtn.addEventListener("click", async () => {
        if (!confirm(`Soll dem Kunden eine Mahnung zu Rechnung ${row.invoice_number ?? invoiceId} per E-Mail zugestellt werden?`)) {
            return;
        }

        const response = await ajax.post(`/api/v1/invoice/${invoiceId}/reminder/send`, {
            "orderId": orderId,
        });
        if (response.data.status == "success") {
            createInvoiceTable();
        }
    });
    actionsCell.appendChild(sendBtn);
}

loader(() => {
    addBindings(fnNames);
    createInvoiceTable();
});
