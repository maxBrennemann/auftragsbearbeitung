import { ajax } from "js-classes/ajax";

import { renderTable } from "../classes/table";

function ajaxSearch(query: string) {
    const customerOverview = (document.getElementById("kundenLink") as HTMLElement).dataset.customerOverview;
    const customer = (document.getElementById("kundenLink") as HTMLElement).dataset.customer;

    const link = document.getElementById("kundenLink") as HTMLLinkElement;

    if (!Number(query)) {
        link.href = customerOverview + '?query=' + query;
    } else {
        link.href = customer + '?id=' + query;
    }

    link.click();
}

function init() {
    initOpenOrdersTable();

    var kundeninput = document.getElementById("kundeninput") as HTMLInputElement;
    var rechnungsinput = document.getElementById("rechnungsinput") as HTMLInputElement;
    var auftragsinput = document.getElementById("auftragsinput") as HTMLInputElement;

    kundeninput.addEventListener("keyup", function (event: KeyboardEvent) {
        if (event.key !== "Enter") {
            return;
        }

        const target = event.target as HTMLInputElement;

        if (target.value === "" || target.value.length == 0) {
            var link = document.getElementById('kundenLink') as HTMLLinkElement;
            link.href = link.dataset.url + "?showDetails=list";
            link.click();
            return;
        }

        ajaxSearch(target.value);
    });

    rechnungsinput.addEventListener("keyup", function (event) {
        if (event.key === "Enter") {
            (document.getElementById("rechnungsLink") as HTMLButtonElement).click();
        }
    });

    auftragsinput.addEventListener("keyup", function (event) {
        if (event.key !== "Enter") {
            return;
        }

        const query = (event.target as HTMLInputElement).value;

        const orderOverview = document.getElementById("auftragsLink")?.dataset.orderOverview;
        const order = document.getElementById("auftragsLink")?.dataset.order;

        const link = document.getElementById("auftragsLink") as HTMLLinkElement;

        if (!Number(query)) {
            link.href = orderOverview + '?query=' + query;
        } else {
            link.href = order + '?id=' + query;
        }

        link.click();
    });
}

const initOpenOrdersTable = async () => {
    const response = await ajax.get(`/api/v1/order/open`);
    const data = response.data;
    const orderCount = data.length;
    (document.getElementById("orderCount") as HTMLSpanElement).innerHTML = `(${orderCount})`;

    const columns = [
        {
            "key": "Auftragsnummer",
            "label": "Nr.",
        },
        {
            "key": "Datum",
            "label": "Datum",
        },
        {
            "key": "Termin",
            "label": "Termin",
        },
        {
            "key": "Kunde",
            "label": "Kunde",
        },
        {
            "key": "Auftragsbezeichnung",
            "label": "Auftragsbezeichnung",
        },
    ];

    const options = {
        "hideOptions": ["all"],
        "styles": {
            "key": {
                "Termin": ["w-32"],
            },
        },
        "primaryKey": "Auftragsnummer",
        "autoSort": true,
        "link": "/auftrag?id=",
    };

    renderTable("openOrders", columns, data, options);
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
