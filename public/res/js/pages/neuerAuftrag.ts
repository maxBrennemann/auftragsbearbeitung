import { ajax } from "js-classes/ajax";
import { addBindings, getVariable } from "js-classes/bindings";

import { renderTable } from "../classes/table";
import { loader } from "../classes/helpers";
import { FunctionMap } from "../types/types";

const fnNames = {} as FunctionMap;

function initNewOrder() {
    addBindings(fnNames);
    const input = document.getElementById("customerSearch");
    if (input != null) {
        input.addEventListener("keyup", (e: any) => {
            if (e.key === "Escape") {
                const searchResults = document.getElementById("searchResults") as HTMLElement;
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
    const bezeichnung = document.getElementById("bezeichnung") as HTMLInputElement;
    const beschreibung = document.getElementById("beschreibung") as HTMLInputElement;
    const termin = document.getElementById("termin") as HTMLInputElement;
    const angenommenVon = document.getElementById("selectMitarbeiter") as HTMLInputElement;
    const angenommenPer = document.getElementById("selectAngenommen") as HTMLInputElement;
    const ansprechpartner = document.getElementById("selectAnsprechpartner") as HTMLInputElement;
    const typ = document.getElementById("selectTyp") as HTMLInputElement;

    ajax.post(`/api/v1/order`, {
        "customerId": getVariable("customerId"),
        "name": bezeichnung.value,
        "description": beschreibung.value,
        "type": typ.value,
        "deadline": termin.value,
        "acceptedBy": angenommenVon.value,
        "acceptedVia": angenommenPer.value,
        "contactperson": ansprechpartner.value,
    }).then(response => {
        if (!response.data.success) {
            return;
        }

        const responseLink = response.data.responseLink;
        window.location.href = responseLink;
    });
}

fnNames.input_searchCustomer = e => {
    performAjaxSearch(e);
}

function openCustomer(id: string) {
    const url = new URL(window.location.href);
    url.searchParams.set("id", id);
    window.location.href = url.href;
}

async function performAjaxSearch(e: any) {
    const value = e.target.value;
    
    if (value.length == 0) return;

    const data = await ajax.get(`/api/v1/search?query=type:kunde ${value}`);
    const parsedData: any = [];
    data.data.forEach((el: any) => {
        parsedData.push(el.data);
    })

    const searchResults = document.getElementById("searchResults") as HTMLElement;
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

loader(initNewOrder);
