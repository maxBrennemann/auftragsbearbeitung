import { addBindings } from "./classes/bindings.js";
import { ajax } from "./classes/ajax.js";
import { getItems, getItemsTable, initInvoiceItems } from "./classes/invoiceItems.js";

const functionNames = {};

const init = () => {
    addBindings(functionNames);
}

functionNames.click_newOffer = () => {
    const customerId = document.getElementById("kdnr").value;
    ajax.get(`/api/v1/order-items/offer/template/${customerId}`).then(r => {
        const url = new URL(window.location.href);
        url.searchParams.set("kdnr", customerId);
        window.history.pushState({}, '', url);

        document.getElementById("insTemp").innerHTML = r.content;
        document.getElementById("listOpenOffers").classList.add("hidden");
        document.getElementById("newOffer").classList.add("hidden");

        getItemsTable("auftragsPostenTable", r.offerId, "offer");
        initInvoiceItems();
        getPDF();
    });
}

functionNames.click_loadOffer = e => {
    const offerId = e.currentTarget.dataset.id;
}

functionNames.click_storeOffer = () => {

}

functionNames.click_deleteOffer = () => {

}

const getPDF = () => {
    var iframe = document.getElementById("offerPDFPreview");
    iframe.src = iframe.src;
}

if (document.readyState !== 'loading' ) {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
