//@ts-nocheck

import { addBindings, getVariable } from "js-classes/bindings.js"

import { initColors } from "../auftrag/colorManager.js";
import { initNotes } from "../auftrag/noteStepManager.js";
import { initOrderManager } from "../auftrag/orderManager.js";
import { initVehicles } from "../auftrag/vehicleManager.ts";

import "../auftrag/calculateGas.js";
import { ajax } from "js-classes/ajax.js";

import { getItemsTable, initInvoiceItems } from "../classes/invoiceItems.ts";
import { notification } from "js-classes/notifications.js";
import { initFileUploader } from "../classes/upload.js";
import { createPopup } from "../global.js";

/* global variables */
const orderConfig = {
    aufschlag: 0,
    auftragsId: parseInt(new URL(window.location.href).searchParams.get("id")),
    times: [],
    table: null,
}

const fnNames = {};

export const getOrderId = () => {
    return parseInt(orderConfig.auftragsId);
}

export const getCustomerId = () => {
    return parseInt(getVariable("customerId"));
}

const initCode = async () => {
    if (isNaN(orderConfig.auftragsId) || orderConfig.auftragsId <= 0) {
        return;
    }

    addBindings(fnNames);

    if (document.getElementById("orderFinished")) {
        initInvoice();
        return;
    }

    initFileUploader({
        "order": {
            "location": `/api/v1/order/${orderConfig.auftragsId}/add-files`,
        },
    });
    initOrderManager(orderConfig.auftragsId);
    initNotes(orderConfig.auftragsId);
    initVehicles(getCustomerId(), orderConfig.auftragsId);
    initColors(orderConfig.auftragsId);

    orderConfig.table = await getItemsTable("auftragsPostenTable", orderConfig.auftragsId, "order");
    orderConfig.table.addEventListener("rowInsert", reloadPostenListe);
    initInvoiceItems(orderConfig.auftragsId);
}

/* changes the contact person connected with the order */
const changeContact = (e) => {
    const value = e.currentTarget.value;

    ajax.post(`/api/v1/order/${orderConfig.auftragsId}/contact-person`, {
        "idContact": value,
    }).then(r => {
        if (r.data.status == "success") {
            notification("", "success");
        } else {
            notification("", "");
        }
    });
}

fnNames.click_toggleOrderDescription = () => {
    const toggleUp = document.querySelector(".toggle-up");
    const toggleDown = document.querySelector(".toggle-down");

    const el = document.querySelector(".orderDescription.hidden");
    const rep = document.querySelector(".orderDescription:not(.hidden)");

    el.classList.toggle("hidden");
    rep.classList.toggle("hidden");

    toggleUp.classList.toggle("hidden");
    toggleDown.classList.toggle("hidden");
}

fnNames.click_showAuftrag = () => {
    const url = window.location.href + "&show=true";
    window.location.href = url;
}

fnNames.click_showAuftragsverlauf = function () { }

fnNames.click_toggleInvoiceItems = e => {
    const value = e.currentTarget.checked;
    ajax.put(`/api/v1/settings/filter-order-posten`, {
        "value": value,
    }).then(async () => {
        orderConfig.table.parentNode.removeChild(orderConfig.table);
        orderConfig.table = await getItemsTable("auftragsPostenTable", orderConfig.auftragsId, "order");
        orderConfig.table.addEventListener("rowInsert", reloadPostenListe);
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
    const response = await ajax.get(`/api/v1/order-items/${orderConfig.auftragsId}/invoice`);
    document.getElementById("invoicePostenTable").innerHTML = response.data["invoicePostenTable"];
}

fnNames.write_changeContact = changeContact;

fnNames.click_setPayed = () => {
    const date = document.getElementById("inputPayDate").value;
    const paymentType = document.getElementById("paymentType").value;
    const invoiceId = getVariable("invoiceId");

    ajax.post(`/invoice/${invoiceId}/paid`, {
        "date": date,
        "paymentType": paymentType,
    }).then(r => {
        if (r.data.status == "success") {
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
        if (r.data.status !== "success") {
            notification("", "failiure", r.data.message);
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
        if (r.data.message == "OK") {
            location.reload();
        } else {
            notification("", "failure", JSON.stringify(r.data));
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
