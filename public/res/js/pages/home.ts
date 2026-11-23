import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings";
import { notification } from "js-classes/notifications";
import { Check } from "lucide";

import { createPopup, loader } from "../classes/helpers";
import { iconHtml } from "../classes/icon";
import { renderTable } from "../classes/table";
import { FunctionMap } from "../types/types";

const fnNames: FunctionMap = {};

type HomeLink = {
    key: string;
    name: string;
    icon: string | false;
};

/**
 * Dialog „Anpassen“: Schnellzugriffe der Startseite an- und abwählen.
 * Gespeichert wird die Liste der Schlüssel als Benutzer-Einstellung „homeLinks“.
 */
fnNames.click_adjustLinks = async (e: Event) => {
    e.preventDefault();

    const response = await ajax.get(`/api/v1/settings/links/available`);
    const links: HomeLink[] = response.data.links;
    const selected: string[] = response.data.selected;

    const div = document.createElement("div");
    div.className = "w-[32rem] max-w-full";
    div.innerHTML = `
        <p class="section-title">Schnellzugriff anpassen</p>
        <p class="field-hint mt-1 mb-3">Gewählte Einträge erscheinen auf der Startseite. Die Reihenfolge ist fest.</p>`;

    const list = document.createElement("div");
    list.className = "grid grid-cols-1 sm:grid-cols-2 gap-2";
    links.forEach(link => {
        const card = document.createElement("div");
        card.className = "choice-card items-center";
        card.setAttribute("role", "checkbox");
        card.tabIndex = 0;
        card.dataset.key = link.key;
        card.setAttribute("aria-checked", String(selected.includes(link.key)));
        card.setAttribute("aria-pressed", String(selected.includes(link.key)));
        card.innerHTML = `<span class="choice-mark" aria-hidden="true">${iconHtml(Check, { size: 12 })}</span><span class="text-sm">${link.name}</span>`;

        const toggle = () => {
            const isOn = card.getAttribute("aria-pressed") !== "true";
            card.setAttribute("aria-pressed", String(isOn));
            card.setAttribute("aria-checked", String(isOn));
        };
        card.addEventListener("click", toggle);
        card.addEventListener("keydown", (event: KeyboardEvent) => {
            if (event.key === "Enter" || event.key === " ") {
                event.preventDefault();
                toggle();
            }
        });
        list.appendChild(card);
    });
    div.appendChild(list);

    const optionsContainer = createPopup(div);

    const btnSave = document.createElement("button");
    btnSave.type = "button";
    btnSave.classList.add("btn-primary", "ml-2");
    btnSave.innerText = "Speichern";
    btnSave.addEventListener("click", async () => {
        const keys = Array.from(list.querySelectorAll<HTMLElement>('[aria-pressed="true"]')).map(card => card.dataset.key);
        if (keys.length === 0) {
            notification("Bitte mindestens einen Eintrag wählen.", "warning");
            return;
        }

        const result = await ajax.put(`/api/v1/settings/config/homeLinks`, {
            "value": JSON.stringify(keys),
        });
        if (result.data?.status === "success") {
            window.location.reload();
        } else {
            notification("", "failure", "Die Auswahl konnte nicht gespeichert werden.");
        }
    });
    optionsContainer.appendChild(btnSave);
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
    (document.getElementById("orderCount") as HTMLSpanElement).textContent = orderCount === 1 ? "1 Auftrag" : `${orderCount} Aufträge`;

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
