import { addRow, createHeader, createTable } from "./classes/table_new.js";

const init = () => {
    createInvoiceTable();
}

function updateIsDone(id, event) {
    const targetRow = event.currentTarget.parentNode.parentNode;
    const invoiceId = parseInt(targetRow.children[1].innerHTML);

    ajax.post(`/api/v1/invoice/${invoiceId}/paid`).then(r => {
        if (r.status == "success") {
            targetRow.parentNode.removeChild(targetRow);
        }
    });
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
        "hideOptions": ["edit", "delete", "addRow"],
        "hide": [""],
        "primaryKey": "Nummer",
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

if (document.readyState !== 'loading' ) {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
