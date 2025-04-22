import { ajax } from "./classes/ajax.js";
import { initBindings } from "./classes/bindings.js";
import { notification } from "./notifications.js";

const functionNames = {};

const config = {
    "invoiceId": 0,
    "orderId": 0,
}

function init() {
    const invoiceId = document.getElementById("invoiceId");
    if (invoiceId == null) {
        return;
    }

    config.invoiceId = invoiceId.value;

    const orderId = document.getElementById("orderId");
    config.orderId = orderId.value;

    const invoiceTexts = document.querySelectorAll(".invoiceTexts");
    invoiceTexts.forEach(text => {
        const active = text.dataset.active;
        if (active == "1") {
            text.classList.add("bg-blue-200");
            text.classList.remove("bg-white");
        }
    });

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

functionNames.click_addText = () => {
    ajax.post(`/api/v1/invoice/${config.invoiceId}/text`, {
        "text": document.getElementById("newText").value,
    }).then(r => {
        if (r.status !== "success") {
            notification("", "failiure", r.message);
            return;
        }
        notification("", "success");

        const newText = document.createElement("p");
        newText.className = "invoiceTexts bg-blue-200 rounded-xl cursor-pointer p-3 select-none";
        newText.dataset.active = "1";
        newText.dataset.id = r.id;
        newText.innerHTML = document.getElementById("newText").value;
        newText.onclick = toggleText;

        document.querySelector(".defaultInvoiceTexts").appendChild(newText);
        document.getElementById("newText").value = "";

        getPDF();
    });
}

const toggleText = e => {
    const target = e.currentTarget;
    target.classList.toggle("bg-blue-200");
    target.classList.toggle("bg-white");

    ajax.put(`/api/v1/invoice/${config.invoiceId}/text`, {
        "textId": target.dataset.id,
        "text": target.innerText,
    }).then(r => {
        if (r.status !== "success") {
            notification("", "failiure", r.message);
            return;
        }
        notification("", "success");

        target.dataset.active = target.dataset.active == "1" ? "0" : "1";
        if (r.id) {
            target.dataset.id = r.id;
        }

        getPDF();
    });
}

functionNames.click_toggleText = toggleText;

functionNames.write_invoiceDate = e => {
    const date = e.target.value;
    ajax.post(`/api/v1/invoice/${config.invoiceId}/invoice-date`, {
        "date": date,
    }).then(r => {
        if (r.status !== "success") {
            notification("", "failiure", r.message);
            return;
        }
        notification("", "success");

        getPDF();
    });
}

functionNames.write_serviceDate = e => {
    const date = e.target.value;
    ajax.post(`/api/v1/invoice/${config.invoiceId}/service-date`, {
        "date": date,
    }).then(r => {
        if (r.status !== "success") {
            notification("", "failiure", r.message);
            return;
        }
        notification("", "success");

        getPDF();
    });
}

functionNames.click_completeInvoice = () => {
    ajax.post(`/api/v1/invoice/${config.invoiceId}/complete`, {
        "orderId": config.orderId,
    }).then(r => {
        if (r.status !== "success") {
            notification("", "failiure", r.message);
            return;
        }
        notification("", "success");
    });
}

functionNames.click_selectAddress = e => {
    const target = e.currentTarget;
    const addressId = target.dataset.id;

    ajax.post(`/api/v1/invoice/${config.invoiceId}/address`, {
        "addressId": addressId,
    }).then(r => {
        if (r.message !== "OK") {
            inotification("", "failiure", r.message);
            return;
        }
        notification("", "success");

        getPDF();
    });
}

functionNames.click_selectContact = e => {
    const target = e.currentTarget;
    const contactId = target.dataset.id;

    ajax.post(`/api/v1/invoice/${config.invoiceId}/contact`, {
        "contactId": contactId,
    }).then(r => {
        if (r.message !== "OK") {
            notification("", "failiure", r.message);
            return;
        }
        notification("", "success");

        getPDF();
    });
}

const getPDF = () => {
    var iframe = document.getElementById("invoicePDFPreview");
    iframe.src = iframe.src;
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
