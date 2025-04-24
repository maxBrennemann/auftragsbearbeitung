import { ajax } from "./classes/ajax.js";

function initNewOrder() {
    const input = document.getElementById("kundensuche");
    if (input != null) {
        input.addEventListener("input", performAjaxSearch);
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

    const button = document.getElementById("absenden");
    if (button != null) {
        button.addEventListener("click", addOrder);
    }
}

function openCustomer(id) {
    const url = new URL(window.location.href);
    url.searchParams.set("kdnr", id);
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

function getCustomerId() {
    const url = new URL(window.location.href);
    const id = url.searchParams.get("kdnr") || url.searchParams.get("id");
    return id;
}

function addOrder() {
    const btn = document.getElementById("absenden");
    btn.disabled = true;
    sendOrder();
}

async function sendOrder() {
    console.log("send order");

    var bezeichnung = document.getElementById("bezeichnung").value;
    var beschreibung = document.getElementById("beschreibung").value;
    var termin = document.getElementById("termin").value;
    var customerId = getCustomerId();
    var angenommenVon = document.getElementById("selectMitarbeiter").value;
    var angenommenPer = document.getElementById("selectAngenommen").value;
    var ansprechpartner = document.getElementById("selectAnsprechpartner").value
    var typ = document.getElementById("selectTyp").value;

    const response = await ajax.post({
        r: "createAuftrag",
        bezeichnung: bezeichnung,
        beschreibung: beschreibung,
        termin: termin,
        customerId: customerId,
        angenommenVon: angenommenVon,
        angenommenPer: angenommenPer,
        ansprechpartner: ansprechpartner,
        typ: typ
    });

    if (!response.success) {
        return;
    }

    const responseLink = response.responseLink;

    window.location.href = responseLink;
}

/* init */
if (document.readyState !== 'loading' ) {
    initNewOrder();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initNewOrder();
    });
}
