import { addBindings, getVariable } from "js-classes/bindings"
import { ajax } from "js-classes/ajax";
import { notification } from "js-classes/notifications";

import { getItemsTable, initInvoiceItems } from "../classes/invoiceItems";
import { initFileUploader } from "../classes/upload";
import { initImagePreviewListener } from "../classes/imagePreview";
import { loader } from "../classes/helpers";

import { initColors } from "../auftrag/colorManager";
import { initNotes } from "../auftrag/noteStepManager";
import { initOrderManager } from "../auftrag/orderManager";
import { initVehicles } from "../auftrag/vehicleManager";

import { FunctionMap } from "../types/types";

import "../auftrag/calculateGas";

/* global variables */
const orderConfig = {
    aufschlag: 0,
    auftragsId: 0,
    times: [],
    table: null as HTMLTableElement | null,
}

const fnNames = {} as FunctionMap;

export const getOrderId = () => {
    return orderConfig.auftragsId;
}

export const getCustomerId = () => {
    const customerId = getVariable("customerId");
    if (customerId === undefined) {
        return -1;
    }
    return parseInt(customerId);
}

const initCode = async () => {
    const url = new URL(window.location.href);
    const idParam = url.searchParams.get("id") ?? "";
    const orderId = parseInt(idParam);

    if (isNaN(orderId) || orderId <= 0) {
        return;
    }

    orderConfig.auftragsId = orderId;

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

fnNames.click_deleteOrderFile = (e: any) => {
    const fileId = e.currentTarget.dataset.fileId;

    if (!confirm("Möchten Sie diese Datei wirklich löschen?")) {
        return;
    }

    ajax.delete(`/api/v1/order/${orderConfig.auftragsId}/files/${fileId}`).then((r: any) => {
        const showFilePrev = document.getElementById("showFilePrev") as HTMLElement;
        showFilePrev.innerHTML = r.data.files;
        initImagePreviewListener();
    });
}

/* changes the contact person connected with the order */
const changeContact = (e: any) => {
    const value = e.currentTarget.value;

    ajax.post(`/api/v1/order/${orderConfig.auftragsId}/contact-person`, {
        "idContact": value,
    }).then(r => {
        if (r.data.status == "success") {
            notification("", "success");
        } else {
            notification("", "failure");
        }
    });
}

fnNames.click_toggleOrderDescription = () => {
    const toggleUp = document.querySelector(".toggle-up") as HTMLElement;
    const toggleDown = document.querySelector(".toggle-down") as HTMLElement;

    const el = document.querySelector(".orderDescription.hidden") as HTMLElement;
    const rep = document.querySelector(".orderDescription:not(.hidden)") as HTMLElement;

    el.classList.toggle("hidden");
    rep.classList.toggle("hidden");

    toggleUp.classList.toggle("hidden");
    toggleDown.classList.toggle("hidden");
}

fnNames.click_showAuftrag = () => {
    const url = new URL(window.location.href);
    url.searchParams.set("show", "true");
    window.location.href = url.toString();
}

fnNames.click_showAuftragsverlauf = function () { }

fnNames.click_toggleInvoiceItems = e => {
    const value = e.currentTarget.checked;
    ajax.put(`/api/v1/settings/filter-order-posten`, {
        "status": value,
    }).then(async () => {
        orderConfig.table?.parentNode?.removeChild(orderConfig.table);
        orderConfig.table = await getItemsTable("auftragsPostenTable", orderConfig.auftragsId, "order");
        orderConfig.table.addEventListener("rowInsert", reloadPostenListe);
    });
}

fnNames.click_showMoreOrderHistory = e => {
    const orderHistory = document.querySelector(".orderHistory") as HTMLElement;
    const elements = orderHistory.querySelectorAll(".hidden");
    elements.forEach(el => {
        el.classList.remove("hidden");
    });
    e.target.classList.add("hidden");
}

const reloadPostenListe = async () => {
    const response = await ajax.get(`/api/v1/order-items/${orderConfig.auftragsId}/invoice`);
    (document.getElementById("invoicePostenTable") as HTMLElement).innerHTML = response.data["invoicePostenTable"];
}

fnNames.write_changeContact = changeContact;

fnNames.click_setPayed = () => {
    const date = (document.getElementById("inputPayDate") as HTMLInputElement).value;
    const paymentType = (document.getElementById("paymentType") as HTMLInputElement).value;
    const invoiceId = getVariable("invoiceId");

    if (!date) {
        notification("Bitte ein Zahlungsdatum angeben.", "failure");
        return;
    }

    ajax.post(`/invoice/${invoiceId}/paid`, {
        "date": date,
        "paymentType": paymentType,
    }).then(r => {
        if (r.data.status == "success") {
            (document.getElementById("orderPaymentState") as HTMLElement).innerHTML = `<p>Die Rechnung wurde am ${date} mit ${paymentType} bezahlt.</p>
                <button class="btn-primary mt-2" data-binding="true" data-fun="setUnpaid">Als unbezahlt markieren</button>`;
            addBindings(fnNames);
        }
    });
}

fnNames.click_createCreditNote = () => {
    const amountInput = document.getElementById("creditNoteAmount") as HTMLInputElement;
    const reasonInput = document.getElementById("creditNoteReason") as HTMLInputElement;
    const sendMail = (document.getElementById("creditNoteSendMail") as HTMLInputElement).checked;

    const amount = parseFloat(amountInput.value.replace(",", "."));
    const reason = reasonInput.value.trim();

    if (isNaN(amount) || amount <= 0) {
        notification("Bitte einen Betrag größer 0 angeben.", "failure");
        return;
    }

    if (reason === "") {
        notification("Bitte einen Grund für die Gutschrift angeben.", "failure");
        return;
    }

    const formatted = amount.toLocaleString("de-DE", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    if (!confirm(`Gutschrift über ${formatted} € netto erstellen? Sie erhält eine eigene Nummer und kann danach nicht mehr geändert werden.`)) {
        return;
    }

    const invoiceId = getVariable("invoiceId");
    ajax.post(`/api/v1/invoice/${invoiceId}/credit-notes`, {
        "orderId": getOrderId(),
        "amount": amount,
        "reason": reason,
        "sendMail": sendMail ? 1 : 0,
    }).then((r: any) => {
        if (!r.success || r.data?.status !== "success") {
            notification("", "failure", r.error ?? r.data?.message ?? "Die Gutschrift konnte nicht erstellt werden");
            return;
        }

        if (r.data.mailRequested && !r.data.mailSent) {
            alert(`Gutschrift Nr. ${r.data.number} wurde erstellt, konnte aber nicht per E-Mail versendet werden.`);
        }

        window.location.reload();
    });
}

fnNames.click_sendCreditNote = (e: any) => {
    const creditNoteId = e.currentTarget.dataset.id;

    if (!confirm("Gutschrift per E-Mail an den Kunden senden?")) {
        return;
    }

    const invoiceId = getVariable("invoiceId");
    ajax.post(`/api/v1/invoice/${invoiceId}/credit-notes/${creditNoteId}/send`, {
        "orderId": getOrderId(),
    }).then((r: any) => {
        if (!r.success || r.data?.status !== "success") {
            notification("", "failure", r.error ?? r.data?.message ?? "Die Gutschrift konnte nicht versendet werden");
            return;
        }
        notification("", "success");
    });
}

fnNames.click_setUnpaid = () => {
    if (!confirm("Soll die Rechnung wirklich als unbezahlt markiert werden?")) {
        return;
    }

    const invoiceId = getVariable("invoiceId");

    ajax.delete(`/invoice/${invoiceId}/paid`).then(r => {
        if (r.data.status == "success") {
            (document.getElementById("orderPaymentState") as HTMLElement).innerHTML = `<p>Die Rechnung wurde noch nicht beglichen.</p>
                <label>
                    <input type="date" id="inputPayDate" class="input-primary">
                </label>
                <select id="paymentType" class="input-primary">
                    <option value="unbezahlt">Unbezahlt</option>
                    <option value="ueberweisung">Überweisung</option>
                    <option value="bar">Bar</option>
                    <option value="paypal">PayPal</option>
                    <option value="kreditkarte">Kreditkarte</option>
                    <option value="amazonpay">AmazonPay</option>
                    <option value="weiteres">Weiteres</option>
                </select>
                <button class="btn-primary" data-binding="true" data-fun="setPayed">Rechnung wurde bezahlt</button>`;
            addBindings(fnNames);
        }
    });
}

const initInvoice = () => {
    const invoiceEmbed = document.getElementById("invoiceEmbed") as HTMLEmbedElement;
    fetch(invoiceEmbed.src).then(response => {
        if (response.status == 404) {
            const el = document.getElementById("showMissingFileWarning") as HTMLDivElement;
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
            notification("", "failure", r.data.message);
            const invoiceEmbed = document.getElementById("invoiceEmbed") as HTMLEmbedElement;
            const src = invoiceEmbed.src;
            invoiceEmbed.src = "";
            invoiceEmbed.src = src;
            const el = document.getElementById("showMissingFileWarning") as HTMLDivElement;
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

fnNames.click_showInvoice = () => {
    const el = document.querySelector("#showInvoice") as HTMLElement;
    const link = el.dataset.link ?? location.href;
    location.href = link;
}

fnNames.click_showInvoicePreview = () => {
    const el = document.querySelector("#showInvoicePreview") as HTMLElement;
    const link = el.dataset.link ?? location.href;
    location.href = link;
}

loader(initCode);
