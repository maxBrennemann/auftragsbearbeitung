import { ajax } from "./classes/ajax.js";
import { addBindings, getVariable } from "./classes/bindings.js";

const fnNames = {};

function initNewOrder() {
    addBindings(fnNames);
    const input = document.getElementById("kundensuche");
    if (input != null) {
        input.addEventListener("keyup", function (e) {
            if (e.key === "Escape") {
                const searchResults = document.getElementById("searchResults");
                searchResults.innerHTML = "";
                searchResults.classList.add("hidden");
            } else if (e.key === "Enter") {
                const value = e.target.value;
                if (!isNaN(value)) {
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

function performAjaxSearch(e) {
    const value = e.target.value;
    if (value.length == 0) {
        return
    }
    ajax.post({
        r: "search",
        query: value,
        stype: "kunde",
        urlid: 1,
        shortSummary: false,
    }, true).then(r => {
        const searchResults = document.getElementById("searchResults");
        searchResults.innerHTML = r;
        searchResults.classList.remove("hidden");

        searchResults.classList.add("overflow-x-auto");
        const table = document.querySelector("table");

        if (!table) {
            return;
        }

        table.classList.add("table-fixed", "w-full");

        tableRowClickable(table);
    });
}

function tableRowClickable(table) {
    Array.from(table.querySelectorAll("tr")).forEach(tr => {
        if (tr.children[0].classList.contains("tableHead")) {
            return;
        }

        tr.classList.add("cursor-pointer");
        tr.addEventListener("click", function (e) {
            const kdnr = e.currentTarget.children[0].innerHTML;
            openCustomer(kdnr);
        })
    });
}

/* init */
if (document.readyState !== 'loading') {
    initNewOrder();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initNewOrder();
    });
}
