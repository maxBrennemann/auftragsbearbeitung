// @ts-ignore
import { ajax } from "js-classes/ajax.js";
// @ts-ignore
import { addBindings } from "js-classes/bindings.js";
// @ts-ignore
import { notification } from "js-classes/notifications.js";

import { createPopup } from "../global.js";

const fnNames: { [key: string]: (...args: any[]) => void } = {};
const orderManagerConfig = {
    orderId: 0,
};

fnNames.click_setOrderFinished = async () => {
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
    }
}

function sendDate(type: number, date: Date|string) {
    ajax.post(`/api/v1/order/${orderManagerConfig.orderId}/update-date`, {
        "date": date,
        "type": type,
    }).then((r: any) => {
        if (r.status == "success") {
            notification("", "success");
        } else {
            notification("", "failure");
        }
    });
}

fnNames.click_deleteOrder = () => {
    const template = document.getElementById("templateAlertBox") as HTMLTemplateElement;
    const div = document.createElement("div");
    div.id = "alertBox";
    div.appendChild(template.content.cloneNode(true));

    div.classList.add("absolute", "w-96", "z-20");

    const deleteOrderBtn = div.querySelector('#deleteOrder') as HTMLButtonElement;
    deleteOrderBtn.addEventListener("click", deleteOrder, false);

    const closeAlertBtn = div.querySelector('#closeAlert') as HTMLButtonElement;
    closeAlertBtn.addEventListener("click", closeAlert, false);

    createPopup(div);
}

function deleteOrder() {
    ajax.post({
        r: "deleteOrder",
        id: orderManagerConfig.orderId,
    }).then((r: any) => {
        if (r.success) {
            window.location.href = r.home;
        }
    });
}

fnNames.click_changeCustomer = async () => {
    const changeCustomer = await ajax.get(`/api/v1/template/orderChangeCustomer`);
    const div = document.createElement("div");
    div.innerHTML = changeCustomer.content;

    const optionsContainer = createPopup(div);
    const btnCancel = optionsContainer.querySelector("button.btn-cancel");

    const searchCustomers = div.querySelector("#searchCustomers") as HTMLElement;
    searchCustomers.addEventListener("change", async e => {
        const query = (e.target as HTMLInputElement).value;
        const template = await ajax.get(`/api/v1/customer/search`, {
            "query": query,
        }).then((r: any) => {
            const customerResultBox = document.querySelector("#customerResultBox") as HTMLElement;
            customerResultBox.innerHTML = r.template;
        });
    });
}

function closeAlert() {
    (document.getElementById("alertBox") as HTMLElement).remove();
}

fnNames.write_editDescription = () => {
    var text = document.querySelector(".orderDescription:not(.hidden)") as HTMLInputElement;

    ajax.put(`/api/v1/order/${orderManagerConfig.orderId}/description`, {
        "text": text.value,
    }).then((r: any) => {
        if (r.message == "OK") {
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
        if (r.status == "success") {
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
        if (r.status == "success") {
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
        if (r.status == "success") {
            var div = document.createElement("div");
            var a = document.createElement("a") as HTMLAnchorElement;
            a.href = (document.getElementById("home_link") as HTMLAnchorElement).href;
            a.innerText = "Zurück zur Startseite";
            div.appendChild(a);
            createPopup(div);
        }
    })
}

export const initOrderManager = (orderId: number) => {
    orderManagerConfig.orderId = orderId;
    addBindings(fnNames);

    const inputExtraOptions = document.getElementById("extraOptions");
    if (inputExtraOptions == null) {
        return;
    }
    inputExtraOptions.addEventListener("click", function (e) {
        const showExtraOptions = document.getElementById("showExtraOptions") as HTMLElement;
        showExtraOptions.classList.toggle("hidden");
    });
}
