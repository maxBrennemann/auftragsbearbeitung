import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings"
import { notification } from "js-classes/notifications";

import { createPopup } from "../classes/helpers";
import { getItemsTable, initInvoiceItems } from "../classes/invoiceItems";
import { FunctionMap } from "../types/types";

const functionNames: FunctionMap = {};

const removedAltNames: number[] = [];
let currentOfferId = 0;
let currentCustomerId = 0;

const init = () => {
    addBindings(functionNames);
}

const showOffer = (content: string, offerId: number, customerId: number) => {
    currentOfferId = offerId;
    currentCustomerId = customerId;

    (document.getElementById("insTemp") as HTMLElement).innerHTML = content;
    (document.getElementById("listOpenOffers") as HTMLElement).classList.add("hidden");
    (document.getElementById("newOffer") as HTMLElement).classList.add("hidden");

    getItemsTable("auftragsPostenTable", offerId, "offer");
    initInvoiceItems(offerId, "offer");
    getPDF();

    /* insTemp's content (incl. the new address/contact/alt-name controls) is inserted after
     * init() already ran addBindings() once, so newly inserted [data-binding]/[data-write]
     * elements need to be bound again here */
    addBindings(functionNames);
}

functionNames.click_newOffer = () => {
    const customerId = (document.getElementById("kdnr") as HTMLInputElement).value;
    ajax.get(`/api/v1/order-items/offer/template/${customerId}`).then((r: any) => {
        const url = new URL(window.location.href);
        url.searchParams.set("kdnr", customerId);
        window.history.pushState({}, '', url);

        showOffer(r.data.content, r.data.offerId, r.data.customerId);
    });
}

functionNames.click_loadOffer = (e: CustomEvent) => {
    const target = (e.currentTarget as HTMLElement)!;
    const offerId = Number(target.dataset.id);

    ajax.get(`/api/v1/order-items/offer/${offerId}/edit`).then((r: any) => {
        showOffer(r.data.content, r.data.offerId, r.data.customerId);
    });
}

functionNames.click_storeOffer = () => {
    ajax.put(`/api/v1/order/offer/${currentOfferId}/complete`, {}).then((r: any) => {
        if (!r.success) {
            notification("", "failure", r.error);
            return;
        }
        window.location.href = "/angebot";
    });
}

functionNames.click_sendOffer = () => {
    if (!confirm("Angebot per E-Mail an den Kunden senden?")) {
        return;
    }

    ajax.post(`/api/v1/order/offer/${currentOfferId}/send`, {}).then((r: any) => {
        if (!r.success || !r.data.success) {
            notification("", "failure", r.error);
            return;
        }
        notification("", "success");
    });
}

functionNames.click_acceptOffer = () => {
    if (!confirm("Angebot annehmen und daraus einen Auftrag anlegen?")) {
        return;
    }

    const url = new URL(window.location.origin + "/neuer-auftrag");
    url.searchParams.set("id", String(currentCustomerId));
    url.searchParams.set("fromOffer", String(currentOfferId));
    window.location.href = url.href;
}

functionNames.click_rejectOffer = () => {
    if (!confirm("Möchtest Du das Angebot wirklich ablehnen?")) {
        return;
    }

    ajax.put(`/api/v1/order/offer/${currentOfferId}/reject`, {}).then((r: any) => {
        if (!r.success) {
            notification("", "failure", r.error);
            return;
        }
        window.location.href = "/angebot";
    });
}

functionNames.write_selectAddress = (e: Event) => {
    const target = e.currentTarget as HTMLInputElement;
    ajax.post(`/api/v1/order/offer/${currentOfferId}/address`, {
        "customerId": currentCustomerId,
        "addressId": target.value,
    }).then((r: any) => {
        if (r.data.message !== "OK") {
            notification("", "failure", r.data.message);
            return;
        }
        notification("", "success");
        getPDF();
    });
}

functionNames.write_selectContact = (e: Event) => {
    const target = e.currentTarget as HTMLInputElement;
    ajax.post(`/api/v1/order/offer/${currentOfferId}/contact`, {
        "customerId": currentCustomerId,
        "contactId": target.value,
    }).then((r: any) => {
        if (r.data.message !== "OK") {
            notification("", "failure", r.data.message);
            return;
        }
        notification("", "success");
        getPDF();
    });
}

functionNames.click_addAltName = async () => {
    const template = await ajax.get(`/api/v1/template/offer/alt-names`, {
        "offerId": currentOfferId,
        "customerId": currentCustomerId,
    });
    const div = document.createElement("div");
    div.innerHTML = template.data.template;
    const btnContainer = createPopup(div);

    const saveBtn = document.createElement("button");
    saveBtn.classList.add("btn-primary");
    saveBtn.innerHTML = "Übernehmen";

    saveBtn.addEventListener("click", () => {
        saveAltNames(div);
        const btnCancel = btnContainer.querySelector("button.btn-cancel") as HTMLButtonElement;
        btnCancel.click();
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

const saveAltNames = (container: HTMLElement) => {
    const inputs = container.querySelectorAll<HTMLInputElement>("div input");
    const add: string[] = [];
    const edit: { id: number, text: string }[] = [];
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

    ajax.post(`/api/v1/order/offer/${currentOfferId}/alt-names`, {
        "customerId": currentCustomerId,
        "add": JSON.stringify(add),
        "edit": JSON.stringify(edit),
        "remove": JSON.stringify(removedAltNames),
    }).then(() => {
        removedAltNames.length = 0;
        getPDF();
    })
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
