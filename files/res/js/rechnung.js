import { ajax } from "./classes/ajax.js";
import { addBindings } from "./classes/bindings.js";
import { notification } from "./classes/notifications.js";
import { createPopup } from "./global.js";

const functionNames = {};

const config = {
    "invoiceId": 0,
    "orderId": 0,
}

const removedAltNames = [];

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

    addBindings(functionNames);
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

functionNames.write_selectAddress = e => {
    const target = e.currentTarget;
    const addressId = target.value;

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

functionNames.write_selectContact = e => {
    const target = e.currentTarget;
    const contactId = target.value;

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

functionNames.click_addAltName = async () => {
    const template = await ajax.get(`/api/v1/template/invoice/alt-names`, {
        "invoiceId": config.invoiceId,
        "orderId": config.orderId,
    });
    const div = document.createElement("div");
    div.innerHTML = template.template;
    const btnContainer = createPopup(div);

    const saveBtn = document.createElement("button");
    saveBtn.classList.add("btn-primary");
    saveBtn.innerHTML = "Übernehmen";

    saveBtn.addEventListener("click", () => {
        saveAltNames(div);
        const btnCancel = btnContainer.querySelector("button.btn-cancel");
        btnCancel.click()
    });
    btnContainer.appendChild(saveBtn);

    addBindings(functionNames);
}

functionNames.click_addNewAltName = e => {
    const template = document.getElementById("invoiceAltNameTemplate");
    const target = e.target;
    const content = template.content.cloneNode(true);
    target.parentNode.insertBefore(content, target);
    addBindings(functionNames);
}

functionNames.click_removeAltName = e => {
    const target = e.target;
    const input = target.previousElementSibling;

    if (input.hasAttribute("data-id")) {
        const id = parseInt(input.dataset.id);
        removedAltNames.push(id);
    }

    const div = target.parentNode;
    div.parentNode.removeChild(div);
}

const saveAltNames = container => {
    const inputs = container.querySelectorAll("div input");
    const add = [];
    const edit = [];
    inputs.forEach(input => {
        if (input.hasAttribute("data-id")) {
            const id = parseInt(input.dataset.id);
            edit.push({
                "id": id,
                "text": input.value,
            });
        } else {
            add.push(input.value);
        }
    })

    ajax.post(`/api/v1/invoice/${config.invoiceId}/alt-names`, {
        "add": JSON.stringify(add),
        "edit": JSON.stringify(edit),
        "remove": JSON.stringify(removedAltNames),
    }).then(() => {
        removedAltNames.length = 0;
        getPDF();
    })
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
