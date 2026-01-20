import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings";
import { notification } from "js-classes/notifications";

import { createPopup } from "../classes/helpers";
import { FunctionMap } from "../types/types";

const fnNames: FunctionMap = {};
const orderManagerConfig = {
    orderId: 0,

    /* specific config for changing customer */
    change_selectedCustomer: null as number | null,
    change_selectedCustomerDiv: null as HTMLDivElement | null,
    change_availableCards: null as NodeListOf<HTMLDivElement> | null,
};

fnNames.click_setOrderFinished = async () => {
    if (orderManagerConfig.orderId === 0) {
        return;
    }

    if (confirm('Möchtest Du den Auftrag als "Erledigt" markieren?')) {
        await ajax.put(`/api/v1/order/${orderManagerConfig.orderId}/finish`);

        (document.getElementById("home_link") as HTMLAnchorElement).click();
    }
}

fnNames.click_updateDate = e => {
    const date = e.target.value;
    sendDate(1, date);
}

fnNames.write_updateDeadline = e => {
    const date = e.target.value;
    sendDate(2, date);
}

fnNames.click_setDeadlineState = e => {
    const checked = e.target.checked;
    if (checked) {
        (document.getElementById("inputDeadline") as HTMLInputElement).value = "";
        sendDate(2, "unset");
    } else {
        const focus = document.getElementById("inputDeadline") as HTMLInputElement;
        if (focus) focus.focus();
    }
}

function sendDate(type: number, date: Date | string) {
    ajax.post(`/api/v1/order/${orderManagerConfig.orderId}/update-date`, {
        "date": date,
        "type": type,
    }).then((r: any) => {
        if (r.data.status == "success") {
            notification("", "success");
        } else {
            notification("", "failure");
        }
    });
}

fnNames.click_deleteOrder = () => {
    const div = document.createElement("div");
    const p = document.createElement("p");
    p.innerHTML = "Möchtest Du den Auftrag endgültig löschen?";

    div.appendChild(p);
    div.classList.add("orderDetailsOpen");

    const btnDeleteOrder = document.createElement("button");
    btnDeleteOrder.classList.add("btn-delete");
    btnDeleteOrder.innerHTML = "Ja";
    btnDeleteOrder.addEventListener("click", () => {
        const showExtraOptions = document.getElementById("showExtraOptions") as HTMLElement;
        showExtraOptions.classList.toggle("hidden");
        deleteOrder();
    });

    const settingsContainer = createPopup(div);
    settingsContainer.appendChild(btnDeleteOrder);

    settingsContainer.addEventListener("closePopup", () => {
        const showExtraOptions = document.getElementById("showExtraOptions") as HTMLElement;
        showExtraOptions.classList.toggle("hidden");
    })
}

function deleteOrder() {
    ajax.delete(`/api/v1/order/${orderManagerConfig.orderId}`).then((r: any) => {
        if (r.data.success) {
            window.location.href = r.data.home;
        }
    });
}

fnNames.click_changeCustomer = async () => {
    const changeCustomer = await ajax.get(`/api/v1/template/orderChangeCustomer`);
    const div = document.createElement("div");
    div.classList.add("orderDetailsOpen", "flex", "flex-col", "flex-1", "min-h-0");
    div.innerHTML = changeCustomer.data.content;

    const optionsContainer = createPopup(div, ["w-2/3", "max-h-[85vh]", "flex", "flex-col"]);

    const selectButton = document.createElement("button") as HTMLButtonElement;
    selectButton.innerHTML = "Kunde übernehmen";
    selectButton.classList.add("btn-primary");
    selectButton.disabled = true;
    selectButton.addEventListener("click", changeCustomerAjax);

    optionsContainer.appendChild(selectButton);

    const searchInput = div.querySelector("#searchCustomers") as HTMLInputElement;
    searchInput.focus();

    const searchCustomers = div.querySelector("#searchCustomers") as HTMLElement;
    searchCustomers.addEventListener("change", async e => {
        const query = (e.target as HTMLInputElement).value;
        await ajax.get(`/api/v1/customer/search`, {
            "query": query,
        }).then((r: any) => {
            const customerResultBox = document.querySelector("#customerResultBox") as HTMLElement;
            customerResultBox.innerHTML = r.data.template;

            if (r.data.count === 0) {
                const p = document.createElement("p");
                p.classList.add("italic");
                p.innerHTML = "Keine Ergebnisse gefunden.";
                customerResultBox.appendChild(p);
            }

            const customerCards = customerResultBox.querySelectorAll<HTMLDivElement>("div[data-customer-id]");
            orderManagerConfig.change_selectedCustomer = null;
            orderManagerConfig.change_selectedCustomerDiv = null;

            selectButton.disabled = true;
            manageSelectCustomer(customerCards, selectButton);
        });
    });
}

const manageSelectCustomer = (customerCards: NodeListOf<HTMLDivElement>, selectButton: HTMLButtonElement) => {
    orderManagerConfig.change_availableCards = customerCards;
    customerCards.forEach(card => {
        card.addEventListener("click", () => {
            const customerId = Number(card.dataset.customerId);
            const alreadySelected = orderManagerConfig.change_selectedCustomer === customerId;

            if (!alreadySelected) {
                selectCard(card);
                selectButton.disabled = false;
            } else {
                toggleColors(card, false);
                selectButton.disabled = true;
            }
        });
    });
}

const selectCard = (card: HTMLDivElement) => {
    orderManagerConfig.change_selectedCustomer = Number(card.dataset.customerId);
    orderManagerConfig.change_selectedCustomerDiv = card;

    orderManagerConfig.change_availableCards?.forEach(c => {
        if (c === card) {
            toggleColors(c, true);
        } else {
            toggleColors(c, false);
        }
    });
}

const toggleColors = (customerCard: HTMLDivElement, isActive: boolean) => {
    if (isActive) {
        customerCard.classList.add("outline-none");
        customerCard.classList.add("ring-2");
        customerCard.classList.add("ring-blue-200");
        customerCard.classList.remove("ring-gray-200");
        customerCard.classList.add("hover:ring-blue-300");
        customerCard.classList.remove("hover:ring-gray-300");
    } else {
        customerCard.classList.remove("outline-none");
        customerCard.classList.remove("ring-2");
        customerCard.classList.remove("ring-blue-200");
        customerCard.classList.add("ring-gray-200");
        customerCard.classList.remove("hover:ring-blue-300");
        customerCard.classList.add("hover:ring-gray-300");
    }
}

const changeCustomerAjax = () => {
    ajax.post(`/api/v1/order/${orderManagerConfig.orderId}/change-customer`, {
        newCustomerId: orderManagerConfig.change_selectedCustomer,
    }).then((r: any) => {
        if (r.data.message == "OK") {
            location.reload();
        }
    }).catch(() => {
        notification("", "failure");
    });
}

fnNames.write_editDescription = () => {
    var text = document.querySelector(".orderDescription:not(.hidden)") as HTMLInputElement;

    ajax.put(`/api/v1/order/${orderManagerConfig.orderId}/description`, {
        "text": text.value,
    }).then((r: any) => {
        if (r.data.message == "OK") {
            notification("", "success");
        } else {
            notification("", "failure");
        }
    });
}

fnNames.write_editOrderType = () => {
    const el = document.getElementById("orderType") as HTMLInputElement;
    const value = el.value;

    ajax.post(`/api/v1/order/${orderManagerConfig.orderId}/type`, {
        "type": value,
    }).then((r: any) => {
        if (r.data.status == "success") {
            notification("", "success");
        } else {
            notification("", "failure");
        }
    });
}

fnNames.write_editTitle = () => {
    const el = document.getElementById("orderTitle") as HTMLInputElement;
    const value = el.value;

    ajax.post(`/api/v1/order/${orderManagerConfig.orderId}/title`, {
        "title": value,
    }).then((r: any) => {
        if (r.data.status == "success") {
            notification("", "success");
        } else {
            notification("", "failure");
        }
    });
}

fnNames.click_archivieren = () => {
    ajax.put(`/api/v1/order/${orderManagerConfig.orderId}/archive`, {
        "status": "archive",
    }).then((r: any) => {
        if (r.data.status == "success") {
            var div = document.createElement("div");
            var a = document.createElement("a") as HTMLAnchorElement;
            a.href = (document.getElementById("home_link") as HTMLAnchorElement).href;
            a.innerText = "Zurück zur Startseite";
            div.appendChild(a);
            createPopup(div);
        }
    });
}

fnNames.click_rearchiveOrder = () => {
    ajax.put(`/api/v1/order/${orderManagerConfig.orderId}/archive`, {
        "status": "unarchive",
    }).then((r: any) => {
        if (r.data.status == "success") {
            window.location.reload();
        }
    });
}

export const initOrderManager = (orderId: number) => {
    orderManagerConfig.orderId = orderId;
    addBindings(fnNames);

    const inputExtraOptions = document.getElementById("extraOptions");
    if (inputExtraOptions == null) {
        return;
    }

    inputExtraOptions.addEventListener("click", function () {
        const showExtraOptions = document.getElementById("showExtraOptions") as HTMLElement;
        showExtraOptions.classList.toggle("hidden");
    });
}

window.addEventListener("click", function (event: MouseEvent) {
    const target = event.target as HTMLElement | null;
    if (!target) return;

    if (!target.closest(".orderDetailsOpen")) {
        const showExtraOptions = document.getElementById("showExtraOptions") as HTMLElement;
        if (showExtraOptions) showExtraOptions.classList.add("hidden");
    }
}, false);
