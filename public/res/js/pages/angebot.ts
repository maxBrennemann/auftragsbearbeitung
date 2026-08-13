import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings"

import { getItemsTable, initInvoiceItems } from "../classes/invoiceItems";
import { FunctionMap } from "../types/types";

const functionNames: FunctionMap = {};

let currentOfferId = 0;

const init = () => {
    addBindings(functionNames);
}

const showOffer = (content: string, offerId: number) => {
    currentOfferId = offerId;

    (document.getElementById("insTemp") as HTMLElement).innerHTML = content;
    (document.getElementById("listOpenOffers") as HTMLElement).classList.add("hidden");
    (document.getElementById("newOffer") as HTMLElement).classList.add("hidden");

    getItemsTable("auftragsPostenTable", offerId, "offer");
    initInvoiceItems(offerId, "offer");
    getPDF();
}

functionNames.click_newOffer = () => {
    const customerId = (document.getElementById("kdnr") as HTMLInputElement).value;
    ajax.get(`/api/v1/order-items/offer/template/${customerId}`).then((r: any) => {
        const url = new URL(window.location.href);
        url.searchParams.set("kdnr", customerId);
        window.history.pushState({}, '', url);

        showOffer(r.data.content, r.data.offerId);
    });
}

functionNames.click_loadOffer = (e: CustomEvent) => {
    const target = (e.currentTarget as HTMLElement)!;
    const offerId = Number(target.dataset.id);

    ajax.get(`/api/v1/order-items/offer/${offerId}/edit`).then((r: any) => {
        showOffer(r.data.content, r.data.offerId);
    });
}

functionNames.click_storeOffer = () => {
    window.location.href = "/angebot";
}

functionNames.click_deleteOffer = () => {
    if (!confirm("Möchtest Du das Angebot wirklich löschen?")) {
        return;
    }

    ajax.delete(`/api/v1/order/offer/${currentOfferId}`).then(() => {
        window.location.href = "/angebot";
    });
}

const getPDF = () => {
    const iframe = document.getElementById("offerPDFPreview") as HTMLIFrameElement;
    const src = iframe.src;
    iframe.src = "";
    iframe.src = src;
}

if (document.readyState !== 'loading' ) {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
