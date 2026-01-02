import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings";

import { renderTable } from "../classes/table";
import { createPopup, loader } from "../classes/helpers";
import { FunctionMap } from "../types/types";

const fnNames: FunctionMap = {};

fnNames.click_adjustLinks = async () => {
    const div = document.createElement("div");
    const linkSetting = await ajax.get(`/api/v1/settings/user/self/linkBehavior`);
    const availabeLinks = await ajax.get(`/api/v1/settings/links/available`);

    const divAvailable = document.createElement("div");
    const divSelected = document.createElement("div");

    div.classList.add("grid", "grid-cols-2", "gap-4", "mb-4");
    div.appendChild(divAvailable);
    div.appendChild(divSelected);

    const optionsContainer = createPopup(div);

    const btnSave = document.createElement("button");
    btnSave.classList.add("btn-primary", "ml-2");
    btnSave.innerText = "Speichern";

    const btnAdd = document.createElement("button");
    btnAdd.classList.add("btn-primary", "ml-2");
    btnAdd.innerText = "⮕";

    const btnRemove = document.createElement("button");
    btnRemove.classList.add("btn-primary");
    btnRemove.innerText = "⬅";

    optionsContainer.appendChild(btnSave);
    optionsContainer.appendChild(btnAdd);
    optionsContainer.appendChild(btnRemove);
}

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

const init = () => {
    addBindings(fnNames);
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
            key: "Auftragsnummer",
            label: "Nr.",
        },
        {
            key: "Datum",
            label: "Datum",
        },
        {
            key: "Termin",
            label: "Termin",
        },
        {
            key: "Kunde",
            label: "Kunde",
        },
        {
            key: "Auftragsbezeichnung",
            label: "Auftragsbezeichnung",
        },
    ];

    const options = {
        hideOptions: ["all"],
        styles: {
            key: {
                Termin: ["w-32"],
            },
        },
        primaryKey: "Auftragsnummer",
        autoSort: true,
        link: "/auftrag?id=",
    };

    renderTable("openOrders", columns, data, options);
}

loader(init);
