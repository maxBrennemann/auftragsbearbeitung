import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings"

import { getItemsTable, initInvoiceItems } from "../classes/invoiceItems";
import { FunctionMap } from "../types/types";

const functionNames: FunctionMap = {};

const init = () => {
    addBindings(functionNames);
}

functionNames.click_newOffer = () => {
    const customerId = (document.getElementById("kdnr") as HTMLInputElement).value;
    ajax.get(`/api/v1/order-items/offer/template/${customerId}`).then((r: any) => {
        const url = new URL(window.location.href);
        url.searchParams.set("kdnr", customerId);
        window.history.pushState({}, '', url);

        (document.getElementById("insTemp") as HTMLElement).innerHTML = r.data.content;
        (document.getElementById("listOpenOffers") as HTMLElement).classList.add("hidden");
        (document.getElementById("newOffer") as HTMLElement).classList.add("hidden");

        getItemsTable("auftragsPostenTable", r.data.offerId, "offer");
        initInvoiceItems();
        getPDF();
    });
}

functionNames.click_loadOffer = (e: CustomEvent) => {
    const target = (e.currentTarget as HTMLElement)!;
    const offerId = target.dataset.id;
}

functionNames.click_storeOffer = () => {

}

functionNames.click_deleteOffer = () => {

}

const getPDF = () => {
    var iframe = document.getElementById("offerPDFPreview") as HTMLIFrameElement;
    iframe.src = iframe.src;
}

if (document.readyState !== 'loading' ) {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
