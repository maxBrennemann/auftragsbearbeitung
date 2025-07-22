// @ts-ignore
import { ajax } from "js-classes/ajax.js";
// @ts-ignore
import { addBindings } from "js-classes/bindings.js"
// @ts-ignore
import { notification } from "js-classes/notifications.js";

import { DragSortManager } from "./classes/DragSortManager.js";
import { createPopup } from "./global.js";

const functionNames: { [key: string]: (...args: any[]) => void } = {};
const removedAltNames: number[] = [];
const config = {
    "invoiceId": 0,
    "orderId": 0,
    "positions": {},
}

function init() {
    addBindings(functionNames);

    const invoiceId = document.getElementById("invoiceId") as HTMLInputElement;
    if (invoiceId == null) {
        return;
    }

    config.invoiceId = parseInt(invoiceId.value);

    const orderId = document.getElementById("orderId") as HTMLInputElement;
    config.orderId = parseInt(orderId.value);

    const invoiceTexts = document.querySelectorAll<HTMLElement>(".invoiceTexts");
    invoiceTexts.forEach(text => {
        const active = text.dataset.active;
        if (active == "1") {
            text.classList.add("bg-blue-200");
            text.classList.remove("bg-white");
        }
    });
}

functionNames.click_addText = () => {
    ajax.post(`/api/v1/invoice/${config.invoiceId}/text`, {
        "text": (document.getElementById("newText") as HTMLInputElement).value,
    }).then((r: any) => {
        if (r.status !== "success") {
            notification("", "failiure", r.message);
            return;
        }
        notification("", "success");

        const newText = document.createElement("p");
        newText.className = "invoiceTexts bg-blue-200 rounded-xl cursor-pointer p-3 select-none";
        newText.dataset.active = "1";
        newText.dataset.id = r.id;
        newText.innerHTML = (document.getElementById("newText") as HTMLInputElement).value;
        newText.onclick = toggleText;

        document.querySelector(".defaultInvoiceTexts")?.appendChild(newText);
        (document.getElementById("newText") as HTMLInputElement).value = "";

        getPDF();
    });
}

const toggleText = (e: Event) => {
    const target = e.currentTarget as HTMLElement;
    target.classList.toggle("bg-blue-200");
    target.classList.toggle("bg-white");

    ajax.put(`/api/v1/invoice/${config.invoiceId}/text`, {
        "textId": target.dataset.id,
        "text": target.innerText,
    }).then((r: any) => {
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

functionNames.write_invoiceDate = (e: Event) => {
    const element = e.target as HTMLInputElement;
    const date = element.value;
    ajax.post(`/api/v1/invoice/${config.invoiceId}/invoice-date`, {
        "date": date,
    }).then((r: any) => {
        if (r.status !== "success") {
            notification("", "failiure", r.message);
            return;
        }
        notification("", "success");

        getPDF();
    });
}

functionNames.write_serviceDate = (e: Event) => {
    const element = e.target as HTMLInputElement;
    const date = element.value;
    ajax.post(`/api/v1/invoice/${config.invoiceId}/service-date`, {
        "date": date,
    }).then((r: any) => {
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
    }).then((r: any) => {
        if (r.status !== "success") {
            notification("", "failiure", r.message);
            return;
        }
        notification("", "success");
        window.history.go(-1);
    });
}

functionNames.write_selectAddress = (e: Event) => {
    const target = e.currentTarget as HTMLInputElement;
    const addressId = target.value;

    ajax.post(`/api/v1/invoice/${config.invoiceId}/address`, {
        "addressId": addressId,
    }).then((r: any) => {
        if (r.message !== "OK") {
            notification("", "failiure", r.message);
            return;
        }
        notification("", "success");

        getPDF();
    });
}

functionNames.write_selectContact = (e: Event) => {
    const target = e.currentTarget as HTMLInputElement;
    const contactId = target.value;

    ajax.post(`/api/v1/invoice/${config.invoiceId}/contact`, {
        "contactId": contactId,
    }).then((r: any) => {
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
        const btnCancel = btnContainer.querySelector("button.btn-cancel") as HTMLButtonElement;
        btnCancel.click()
    });
    btnContainer.appendChild(saveBtn);

    addBindings(functionNames);
}

functionNames.click_addNewAltName = (e: Event) => {
    const template = document.getElementById("invoiceAltNameTemplate") as HTMLTemplateElement;
    const target = e.target as HTMLElement;
    const content = template.content.cloneNode(true);
    target.parentNode?.insertBefore(content, target);
    addBindings(functionNames);
}

functionNames.click_removeAltName = (e: Event) => {
    const target = e.target as HTMLElement;
    const input = target.previousElementSibling as HTMLInputElement;

    if (input.hasAttribute("data-id")) {
        const id = parseInt(input.dataset.id ?? "");
        removedAltNames.push(id);
    }

    const div = target.parentNode as HTMLDivElement;
    div.parentNode?.removeChild(div);
}

functionNames.click_changeItemsOrder = async () => {
    const template = await ajax.get(`/api/v1/template/invoice/items-order`, {
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
        saveOrder();
        const btnCancel = btnContainer.querySelector("button.btn-cancel") as HTMLButtonElement;
        btnCancel.click()
    });
    btnContainer.appendChild(saveBtn);

    manageItemsOrder(div);
    addBindings(functionNames);
}

const manageItemsOrder = (div: HTMLDivElement) => {
    const group = div.querySelector(".invoiceItemsGroup") as HTMLElement;
    const sorter = new DragSortManager(group, {
        itemSelector: "div",
        dataFields: ["type"],
        onOrderChange: (positions, groupEl) => {
            config.positions = positions;
            let count = 1;
            const elements = div.querySelectorAll<HTMLInputElement>(".invoiceItemsGroup div input");
            elements.forEach(el => {
                el.value = count.toString();
                count++;
            });
        }
    });
}

const saveOrder = () => {
    ajax.put(`/api/v1/invoice/${config.invoiceId}/positions`, {
        "positions": JSON.stringify(config.positions),
        "orderId": config.orderId,
    }).then(() => {
        getPDF();
    });
}

const saveAltNames = (container: HTMLElement) => {
    const inputs = container.querySelectorAll<HTMLInputElement>("div input");
    const add: string[] = [];
    const edit: {id: number, text: string}[] = [];
    inputs.forEach(input => {
        if (input.hasAttribute("data-id")) {
            const id = parseInt(input.dataset.id ?? "");
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
    var iframe = document.getElementById("invoicePDFPreview") as HTMLIFrameElement;
    iframe.src = iframe.src;
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
