import { ajax } from "js-classes/ajax.js";
import { addBindings } from "js-classes/bindings.js";
import { notification } from "js-classes/notifications.js";

import { createPopup } from "../classes/helpers";

const fnNames: { [key: string]: (...args: any[]) => void } = {};

const orderManagerConfig = {
    orderId: 0,
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
    p.innerHTML = "Möchtest Du den Auftrag sicher löschen?";

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
    div.classList.add("orderDetailsOpen");
    div.innerHTML = changeCustomer.data.content;

    const optionsContainer = createPopup(div);
    optionsContainer.addEventListener("closePopup", () => {
        const showExtraOptions = document.getElementById("showExtraOptions") as HTMLElement;
        showExtraOptions.classList.toggle("hidden");
    })

    const searchCustomers = div.querySelector("#searchCustomers") as HTMLElement;
    searchCustomers.addEventListener("change", async e => {
        const query = (e.target as HTMLInputElement).value;
        await ajax.get(`/api/v1/customer/search`, {
            "query": query,
        }).then((r: any) => {
            const customerResultBox = document.querySelector("#customerResultBox") as HTMLElement;
            customerResultBox.innerHTML = r.data.template;
        });
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
    })
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
