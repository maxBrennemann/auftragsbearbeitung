import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings"
import { notification } from "js-classes/notifications";
import { Check } from "lucide";

import { DragSortManager } from "../classes/DragSortManager";
import { bindToggles, createPopup, closePopupFor } from "../classes/helpers";
import { iconHtml } from "../classes/icon";
import { getItemsTable, initInvoiceItems } from "../classes/invoiceItems";
import { FunctionMap } from "../types/types";

const functionNames: FunctionMap = {};

const removedAltNames: number[] = [];
let currentOfferId = 0;
let currentCustomerId = 0;
let currentPositions: unknown = {};

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

    // Auf-/Zuklappen im nachgeladenen Template binden (js-classes erfasst data-toggle nur beim Start)
    bindToggles(document.getElementById("insTemp") as HTMLElement);

    // Ausgewählte Texte bringt offerView.php bereits als aria-pressed="true" mit.
    // Textkarten sind role="button": auch per Tastatur (Enter/Leertaste) umschaltbar
    document.querySelector(".defaultOfferTexts")?.addEventListener("keydown", (e: Event) => {
        const event = e as KeyboardEvent;
        const card = (event.target as HTMLElement).closest<HTMLElement>(".offerTexts");
        if (card && event.target === card && (event.key === "Enter" || event.key === " ")) {
            event.preventDefault();
            card.click();
        }
    });

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

functionNames.click_addText = () => {
    const input = document.getElementById("newOfferText") as HTMLInputElement;

    ajax.post(`/api/v1/order/offer/${currentOfferId}/text`, {
        "text": input.value,
    }).then((r: any) => {
        if (r.data.status !== "success") {
            notification("", "failure", r.data.message);
            return;
        }
        notification("", "success");

        const newText = document.createElement("div");
        newText.className = "offerTexts choice-card";
        newText.setAttribute("role", "button");
        newText.tabIndex = 0;
        newText.setAttribute("aria-pressed", "true");
        newText.dataset.active = "1";
        newText.dataset.id = r.data.id;
        const mark = document.createElement("span");
        mark.className = "choice-mark";
        mark.setAttribute("aria-hidden", "true");
        mark.innerHTML = iconHtml(Check, { size: 12 });
        const newTextContent = document.createElement("p");
        newTextContent.className = "max-h-20 overflow-auto flex-auto text-sm";
        newTextContent.textContent = input.value;
        newText.append(mark, newTextContent);
        newText.addEventListener("click", toggleText);

        document.querySelector(".defaultOfferTexts")?.appendChild(newText);
        input.value = "";

        getPDF();
    });
}

functionNames.click_editText = (e: Event) => {
    const target = e.currentTarget as HTMLElement;
    e.stopPropagation();

    const id = target.dataset.id;
    const card = target.closest(".offerTexts") as HTMLElement;
    const textEl = card.querySelector("p") as HTMLElement;

    const div = document.createElement("div");
    div.classList.add("w-96");

    const title = document.createElement("p");
    title.classList.add("font-semibold");
    title.innerHTML = "Text bearbeiten";

    const textarea = document.createElement("textarea");
    textarea.className = "input-primary w-full mt-2";
    textarea.rows = 4;
    textarea.value = textEl.innerText;

    div.appendChild(title);
    div.appendChild(textarea);

    const btnContainer = createPopup(div);

    const saveBtn = document.createElement("button");
    saveBtn.classList.add("btn-primary");
    saveBtn.innerHTML = "Übernehmen";

    saveBtn.addEventListener("click", () => {
        const newText = textarea.value;
        ajax.put(`/api/v1/order/offer/${currentOfferId}/text/${id}`, {
            "text": newText,
        }).then((r: any) => {
            if (r.data.status !== "success") {
                notification("", "failure", r.data.message);
                return;
            }
            notification("", "success");
            textEl.innerText = newText;
            getPDF();
        });
        closePopupFor(btnContainer);
    });
    btnContainer.appendChild(saveBtn);
}

functionNames.click_deleteText = (e: Event) => {
    const target = e.currentTarget as HTMLElement;
    e.stopPropagation();

    if (!confirm("Soll dieser Text wirklich gelöscht werden?")) {
        return;
    }

    const id = target.dataset.id;
    ajax.delete(`/api/v1/order/offer/${currentOfferId}/text/${id}`).then((r: any) => {
        if (r.data.status !== "success") {
            notification("", "failure", r.data.message);
            return;
        }
        notification("", "success");
        target.closest(".offerTexts")?.remove();
        getPDF();
    });
}

const toggleText = (e: Event) => {
    const target = e.currentTarget as HTMLElement;
    const wasPressed = target.getAttribute("aria-pressed") === "true";
    // sofort sichtbar umschalten, bei Fehler zurücksetzen
    target.setAttribute("aria-pressed", String(!wasPressed));

    ajax.put(`/api/v1/order/offer/${currentOfferId}/text`, {
        "textId": target.dataset.id,
        "text": target.innerText,
    }).then((r: any) => {
        if (r.data.status !== "success") {
            target.setAttribute("aria-pressed", String(wasPressed));
            notification("", "failure", r.data.message);
            return;
        }
        notification("", "success");

        target.dataset.active = target.dataset.active == "1" ? "0" : "1";
        if (r.data.id) {
            target.dataset.id = r.data.id;
        }

        getPDF();
    });
}

functionNames.click_toggleText = toggleText;

functionNames.click_changeItemsOrder = async () => {
    const template = await ajax.get(`/api/v1/template/offer/items-order`, {
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
        saveOrder();
        closePopupFor(btnContainer);
    });
    btnContainer.appendChild(saveBtn);

    manageItemsOrder(div);
    addBindings(functionNames);
}

const manageItemsOrder = (div: HTMLDivElement) => {
    const group = div.querySelector(".invoiceItemsGroup") as HTMLElement;
    new DragSortManager(group, {
        itemSelector: "div",
        dataFields: ["type"],
        onOrderChange: (positions) => {
            currentPositions = positions;
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
    ajax.put(`/api/v1/order/offer/${currentOfferId}/positions`, {
        "positions": JSON.stringify(currentPositions),
        "customerId": currentCustomerId,
    }).then(() => {
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
        closePopupFor(btnContainer);
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

    ajax.delete(`/api/v1/order/offer/${currentOfferId}`).then((r: any) => {
        if (!r.success) {
            notification("", "failure", r.error);
            return;
        }
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
