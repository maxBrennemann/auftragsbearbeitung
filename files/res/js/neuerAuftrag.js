import { ajax } from "js-classes/ajax.js";
import { addBindings, getVariable } from "js-classes/bindings.js";

import { renderTable } from "./classes/table.js";

const fnNames = {};

function initNewOrder() {
    addBindings(fnNames);
    const input = document.getElementById("customerSearch");
    if (input != null) {
        input.addEventListener("keyup", e => {
            if (e.key === "Escape") {
                const searchResults = document.getElementById("searchResults");
                searchResults.innerHTML = "";
                searchResults.classList.add("hidden");
            } else if (e.key === "Enter") {
                const value = e.target.value;
                const number = parseInt(value);
                if (!isNaN(number)) {
                    openCustomer(value);
                }
            }
        });
    }
}

fnNames.click_sendData = () => {
    const bezeichnung = document.getElementById("bezeichnung").value;
    const beschreibung = document.getElementById("beschreibung").value;
    const termin = document.getElementById("termin").value;
    const angenommenVon = document.getElementById("selectMitarbeiter").value;
    const angenommenPer = document.getElementById("selectAngenommen").value;
    const ansprechpartner = document.getElementById("selectAnsprechpartner").value
    const typ = document.getElementById("selectTyp").value;

    ajax.post(`/api/v1/order`, {
        "customerId": getVariable("customerId"),
        "name": bezeichnung,
        "description": beschreibung,
        "type": typ,
        "deadline": termin,
        "acceptedBy": angenommenVon,
        "acceptedVia": angenommenPer,
        "contactperson": ansprechpartner
    }).then(response => {
        if (!response.success) {
            return;
        }

        const responseLink = response.responseLink;
        window.location.href = responseLink;
    });
}

fnNames.input_searchCustomer = e => {
    performAjaxSearch(e);
}

function openCustomer(id) {
    const url = new URL(window.location.href);
    url.searchParams.set("id", id);
    window.location.href = url.href;
}

async function performAjaxSearch(e) {
    const value = e.target.value;
    if (value.length == 0) {
        return
    }
    const data = await ajax.get(`/api/v1/search?query=type:kunde ${value}`);
    const parsedData = [];
    data.forEach(el => {
        parsedData.push(el.data);
    })

    const searchResults = document.getElementById("searchResults");
    searchResults.innerHTML = "";
    searchResults.classList.remove("hidden");

    const options = {
        "hideOptions": ["all"],
        "link": "/neuer-auftrag?id=",
        "primaryKey": "Kundennummer",
    }

    const header = [
        {
            "key": "Kundennummer",
            "label": "Kundennummer",
        },
        {
            "key": "Firmenname",
            "label": "Firmenname",
        },
        {
            "key": "Vorname",
            "label": "Vorname",
        },
        {
            "key": "Nachname",
            "label": "Nachname",
        },
    ];

    renderTable("searchResults", header, parsedData, options);
}

/* init */
if (document.readyState !== 'loading') {
    initNewOrder();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initNewOrder();
    });
}
