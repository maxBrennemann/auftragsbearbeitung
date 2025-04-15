import { ajax } from "./classes/ajax.js";
import { initBindings } from "./classes/bindings.js";

const functionNames = {};

const config = {
    "invoiceId": 0,
    "customerId": 0,
}

function init() {
    initBindings(functionNames);
}

functionNames.click_togglePredefinedTexts = () => {
    const toggleUp = document.querySelector(".toggle-up");
    const toggleDown = document.querySelector(".toggle-down");

    const el = document.querySelector(".predefinedTexts.hidden");
    const rep = document.querySelector(".predefinedTexts:not(.hidden)");

    el.classList.toggle("hidden");
    rep.classList.toggle("hidden");

    toggleUp.classList.toggle("hidden");
    toggleDown.classList.toggle("hidden");
}

functionNames.click_toggleText = e => {
    const target = e.currentTarget;
    target.classList.toggle("bg-blue-200");
    target.classList.toggle("bg-white");

    getPDF();
}

functionNames.write_invoiceDate = e => {
    const date = e.target.value;
    ajax.post(`/api/v1/invoice/${config.invoiceId}/invoice-date`, {
        "date": date,
    }).then(r => {
        if (r.status !== "success") {
            infoSaveSuccessfull("failiure", r.message);
            return;
        }
        infoSaveSuccessfull("success");
    });
    getPDF();
}

functionNames.write_serviceDate = e => {
    const date = e.target.value;
    ajax.post(`/api/v1/invoice/${config.invoiceId}/service-date`, {
        "date": date,
    }).then(r => {
        if (r.status !== "success") {
            infoSaveSuccessfull("failiure", r.message);
            return;
        }
        infoSaveSuccessfull("success");
    });
    getPDF();
}

const getPDF = () => {
    var iframe = document.getElementById("offerPDFPreview");
    iframe.src = iframe.src;
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
