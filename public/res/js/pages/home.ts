import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings";

import { createPopup, loader } from "../classes/helpers";
import { renderTable } from "../classes/table";
import { FunctionMap } from "../types/types";

const fnNames: FunctionMap = {};

type Link = {
    name: string;
    url: string;
    icon: string;
};

type Links = Array<Link>;

const customizeConfig = {
    selectedLinks: [] as Links,
    availableLinks: [] as Links,

    highlightAdd: [] as Links,
    highlightRemove: [] as Links, 
}

fnNames.click_adjustLinks = async () => {
    const div = document.createElement("div");
    const linkSetting = await ajax.get(`/api/v1/settings/user/self/linkBehavior`);
    const availabeLinks = await ajax.get(`/api/v1/settings/links/available`);

    const divAvailable = document.createElement("div");
    const divSelected = document.createElement("div");

    fillLinks(divAvailable, availabeLinks.data.links);

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

const fillLinks = (container: HTMLDivElement, links: Array<{ name: string; url: string; icon: string; }>) => {
    container.innerHTML = "";
    
    links.forEach(link => {
        const linkDiv = document.createElement("div");
        linkDiv.classList.add("btn-cancel", "block", "mb-3");
        linkDiv.innerHTML = link.name;

        linkDiv.addEventListener("click", () => {
            linkDiv.classList.toggle("bg-blue-200");

        });

        container.appendChild(linkDiv);
    });
}

function ajaxSearch(query: string) {
    const link = document.createElement("a") as HTMLAnchorElement;

    if (!Number(query)) {
        link.href = "/customer-overview?query=" + query;
    } else {
        link.href = "/kunde?id=" + query;
    }

    link.click();
}

const init = () => {
    addBindings(fnNames);
    initOpenOrdersTable();

    const kundeninput = document.getElementById("kundeninput") as HTMLInputElement | null;
    const rechnungsinput = document.getElementById("rechnungsinput") as HTMLInputElement | null;
    const auftragsinput = document.getElementById("auftragsinput") as HTMLInputElement | null;

    kundeninput?.addEventListener("keyup", function (event: KeyboardEvent) {
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

    rechnungsinput?.addEventListener("keyup", function (event) {
        if (event.key === "Enter") {
            const link = document.createElement("a") as HTMLAnchorElement;
            link.href = "/rechnung?target=view&id=" + rechnungsinput.value;
            link.click();
        }
    });

    auftragsinput?.addEventListener("keyup", function (event) {
        if (event.key !== "Enter") {
            return;
        }

        const query = (event.target as HTMLInputElement).value;
        const link = document.createElement("a") as HTMLAnchorElement;

        if (!Number(query)) {
            link.href = "order-overview?query=" + query;
        } else {
            link.href = "/auftrag?id=" + query;
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
