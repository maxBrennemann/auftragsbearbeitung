import { addBindings, getVariable } from "./classes/bindings.js";
import { initColors } from "./auftrag/colorManager.js";
import { initNotes } from "./auftrag/noteStepManager.js";
import { setOrderFinished, updateDate, updateDeadline, setDeadlineState, initExtraOptions, editDescription, editOrderType, editTitle, archvieren } from "./auftrag/orderManager.js";
import { initVehicles } from "./auftrag/vehicleManager.js";
import "./auftrag/calculateGas.js";
import { ajax } from "./classes/ajax.js";
import { getItemsTable, initInvoiceItems } from "./classes/invoiceItems.js";
import { notification } from "./classes/notifications.js";
import { createPopup } from "./global.js";
import { initFileUploader } from "./classes/upload.js";

/* global variables */
window.globalData = {
    aufschlag: 0,
    auftragsId: parseInt(new URL(window.location.href).searchParams.get("id")),
    times: [],
    table: null,
}

const fnNames = {};

export const getOrderId = () => {
    return parseInt(globalData.auftragsId);
}

export const getCustomerId = () => {
    return parseInt(getVariable("customerId"));
}

const initCode = async () => {
    if (isNaN(globalData.auftragsId)) {
        return;
    }

    addBindings(fnNames);

    if (document.getElementById("orderFinished")) {
        initInvoice();
        return;
    }

    addSearchEventListeners();

    initFileUploader({
        "order": {
            "location": `/api/v1/order/${globalData.auftragsId}/add-files`,
        },
    });
    initExtraOptions();
    initNotes();
    initVehicles();
    initColors();

    globalData.table = await getItemsTable("auftragsPostenTable", globalData.auftragsId, "order");
    globalData.table.addEventListener("rowInsert", reloadPostenListe);
    initInvoiceItems();
}

function addSearchEventListeners() {
    var data = document.getElementsByClassName("searchProductEvent");
    for (let i = 0; i < data.length; i++) {
        data[i].addEventListener("click", performProductSearch, false);
    }
    document.getElementById("productSearch").addEventListener("change", performProductSearch, false);
}

function performProductSearch() {
    var query = document.getElementById("productSearch").value;

    var params = {
        getReason: "searchProduct",
        query: query
    };

    var search = new AjaxCall(params, "POST", window.location.href);
    search.makeAjaxCall(function (response) {
        var element = document.getElementById("resultContainer");
        element.innerHTML = response;
    });
}

/* changes the contact person connected with the order */
const changeContact = (e) => {
    const value = e.currentTarget.value;

    ajax.post(`/api/v1/order/${globalData.auftragsId}/contact-person`, {
        "idContact": value,
    }).then(r => {
        if (r.status == "success") {
            notification("", "success");
        } else {
            notification("", "");
        }
    });
}

function showDeleteMessage(row, header, key, type) {
    var div = document.createElement("div");

    /* creates table */
    let table = document.createElement("table");
    const tbody = document.createElement("tbody");
    table.appendChild(tbody);
    tbody.appendChild(header.cloneNode(true));
    tbody.appendChild(row.cloneNode(true));

    /* remove last column */
    let lastColumn = tbody.rows[0].lastChild;
    lastColumn.parentNode.removeChild(lastColumn);
    lastColumn = tbody.rows[1].lastChild;
    lastColumn.parentNode.removeChild(lastColumn);

    /* creates inner text of deletion verification */
    let contentNode = document.createElement("p");

    /* create the delete note */
    const note = document.createElement("p");
    note.innerHTML = "Willst Du diese Zeile wirklich löschen?";
    note.classList.add("font-bold", "mb-2");
    contentNode.appendChild(note);

    /* adds the table to the content node */
    contentNode.appendChild(table);

    /* creates the yes and no buttons */
    let btn_yes = document.createElement("button");
    btn_yes.classList.add("btn-primary", "mr-2");
    btn_yes.innerHTML = "Ja";
    let btn_no = document.createElement("button");
    btn_no.classList.add("btn-primary");
    btn_no.innerHTML = "Nein";

    /**
     * inner function to delete the node
     * type => type of data to be deleted
     * key => the key for the server so that the correct data is deleted
     * row => row for the frontend to be deleted 
     */
    function delNode(type, key, row) {
        var del = new AjaxCall(`getReason=delete&type=${type}&key=${key}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
        del.makeAjaxCall(function (response) {
            console.log(response);
        });

        while (row.nodeName != "TR") {
            row = row.parentNode;
        }

        row.parentNode.removeChild(row);
    }

    /* inner function for node button to remove the div */
    function close(div) {
        div.parentNode.removeChild(div);
    }

    /* event listeners */
    btn_yes.addEventListener("click", function () {
        delNode(type, key, row);
        close(div);
    }, false);

    btn_no.addEventListener("click", function () {
        close(div);
    }, false);

    /* adds all relevant nodes to the div */
    div.appendChild(contentNode);
    div.appendChild(document.createElement("br"));
    div.appendChild(btn_yes);
    div.appendChild(btn_no);

    createPopup(div);
}

const toggleOrderDescription = () => {
    const toggleUp = document.querySelector(".toggle-up");
    const toggleDown = document.querySelector(".toggle-down");

    const el = document.querySelector(".orderDescription.hidden");
    const rep = document.querySelector(".orderDescription:not(.hidden)");

    el.classList.toggle("hidden");
    rep.classList.toggle("hidden");

    toggleUp.classList.toggle("hidden");
    toggleDown.classList.toggle("hidden");
}

/* function starts deletion of the row */
window.deleteRow = function (key, type = "schritte", node) {
    let row = node.parentNode.parentNode;
    let header = row.parentNode.children[0];

    showDeleteMessage(row, header, key, type);
}

window.updateIsDone = function (key, event) {
    var update = new AjaxCall(`getReason=update&key=${key}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
    update.makeAjaxCall(function (response, args) {
        console.log(response);
        /* removes the row */
        let button = args[0];
        let row = button.parentNode.parentNode;
        row.parentNode.removeChild(row);
    }, event.target);
}


fnNames.click_showAuftrag = () => {
    const url = window.location.href + "&show=t";
    window.location.href = url;
}

fnNames.click_showAuftragsverlauf = function () { }

fnNames.click_toggleInvoiceItems = e => {
    const value = e.currentTarget.checked;
    ajax.put(`/api/v1/settings/filter-order-posten`, {
        "value": value,
    }).then(async () => {
        globalData.table.parentNode.removeChild(globalData.table);
        globalData.table = await getItemsTable("auftragsPostenTable", globalData.auftragsId, "order");
        globalData.table.addEventListener("rowInsert", reloadPostenListe);
    });
}

fnNames.click_showMoreOrderHistory = e => {
    const orderHistory = document.querySelector(".orderHistory");
    const elements = orderHistory.querySelectorAll(".hidden");
    elements.forEach(el => {
        el.classList.remove("hidden");
    });
    e.target.classList.add("hidden");
}

const reloadPostenListe = async () => {
    const response = await ajax.get(`/api/v1/order-items/${globalData.auftragsId}/invoice`);
    document.getElementById("invoicePostenTable").innerHTML = response["invoicePostenTable"];
}

fnNames.write_changeContact = changeContact;

fnNames.click_setOrderFinished = setOrderFinished;
fnNames.write_updateDate = updateDate;
fnNames.write_updateDeadline = updateDeadline;
fnNames.write_editDescription = editDescription;
fnNames.write_editOrderType = editOrderType;
fnNames.write_editTitle = editTitle;
fnNames.click_setDeadlineState = setDeadlineState;
fnNames.click_archvieren = archvieren;
fnNames.click_toggleOrderDescription = toggleOrderDescription;

fnNames.click_setPayed = () => {
    const date = document.getElementById('inputPayDate').value;
    const paymentType = document.getElementById('paymentType').value;
    const invoiceId = getVariable("invoiceId");

    ajax.post(`"/invoice/${invoiceId}/paid"`, {
        "date": date,
        "paymentType": paymentType,
    }).then(r => {
        if (r.status == "success") {
            document.getElementById("orderPaymentState").innerHTML = `<p>Die Rechnung wurde am ${date} mit ${paymentType} bezahlt.</p>`;
        }
    });
}

const initInvoice = () => {
    const invoiceEmbed = document.getElementById("invoiceEmbed");
    fetch(invoiceEmbed.src).then(response => {
        if (response.status == 404) {
            const el = document.getElementById("showMissingFileWarning");
            el.classList.remove("hidden");
            el.classList.add("flex");
        }
    });
}

fnNames.click_recreateInvoice = () => {
    const invoiceId = getVariable("invoiceId");
    ajax.post(`/api/v1/invoice/${invoiceId}/complete`, {
        "orderId": getOrderId(),
    }).then(r => {
        if (r.status !== "success") {
            notification("", "failiure", r.message);
            const invoiceEmbed = document.getElementById("invoiceEmbed");
            invoiceEmbed.src = invoiceEmbed.src;
            const el = document.getElementById("showMissingFileWarning");
            el.classList.add("hidden");
            return;
        }
        notification("", "success");
    });
}

fnNames.click_resetInvoice = () => {
    ajax.post(`/api/v1/order/${getOrderId()}/reset-invoice`).then(r => {
        if (r.message == "OK") {
            location.reload();
        } else {
            notification("", "failure", JSON.stringify(r));
        }
    })
}

if (document.readyState !== 'loading') {
    initCode();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initCode();
    });
}
