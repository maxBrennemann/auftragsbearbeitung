import { ajax } from "js-classes/ajax.js";
import { addBindings } from "js-classes/bindings.js";
import { notification } from "js-classes/notifications.js";

import { createPopup } from "../global.js";

const fnNames = {};

fnNames.click_setOrderFinished = async () => {
    if (confirm('Möchtest Du den Auftrag als "Erledigt" markieren?')) {
        await ajax.put(`/api/v1/order/${globalData.auftragsId}/finish`);

        document.getElementById("home_link").click();
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
        document.getElementById("inputDeadline").value = "";
        sendDate(2, "unset");
    }
}

function sendDate(type, value) {
    ajax.post(`/api/v1/order/${globalData.auftragsId}/update-date`, {
        "date": value,
        "type": type,
    }).then(r => {
        if (r.status == "success") {
            notification("", "success");
        } else {
            notification("", "failure");
        }
    });
}

function showDeleteConfirmation() {
    const template = document.getElementById("templateAlertBox");
    const div = document.createElement("div");
    div.id = "alertBox";
    div.appendChild(template.content.cloneNode(true));

    div.classList.add("absolute", "w-96", "z-20");

    const deleteOrderBtn = div.querySelector('#deleteOrder');
    deleteOrderBtn.addEventListener("click", deleteOrder, false);

    const closeAlertBtn = div.querySelector('#closeAlert');
    closeAlertBtn.addEventListener("click", closeAlert, false);

    createPopup(div);
}

function deleteOrder() {
    ajax.post({
        r: "deleteOrder",
        id: globalData.auftragsId,
    }).then(r => {
        if (r.success) {
            window.location.href = r.home;
        }
    });
}

function closeAlert() {
    document.getElementById("alertBox").remove();
}

fnNames.write_editDescription = () => {
    var text = document.querySelector(".orderDescription:not(.hidden)");

    ajax.put(`/api/v1/order/${globalData.auftragsId}/description`, {
        "text": text.value,
    }).then(r => {
        if (r.message == "OK") {
            notification("", "success");
        } else {
            notification("", "failure");
        }
    });
}

fnNames.write_editOrderType = () => {
    const el = document.getElementById("orderType");
    const value = el.value;

    ajax.post(`/api/v1/order/${globalData.auftragsId}/type`, {
        "type": value,
    }).then(r => {
        if (r.status == "success") {
            notification("", "success");
        } else {
            notification("", "failure");
        }
    });
}

fnNames.write_editTitle = () => {
    const el = document.getElementById("orderTitle");
    const value = el.value;

    ajax.post(`/api/v1/order/${globalData.auftragsId}/title`, {
        "title": value,
    }).then(r => {
        if (r.status == "success") {
            notification("", "success");
        } else {
            notification("", "failure");
        }
    });
}

fnNames.click_archivieren = () => {
    const id = globalData.auftragsId;
    ajax.put(`/api/v1/order/${id}/archive`, {
        "archive": true,
    }).then(r => {
        if (r.status == "success") {
            var div = document.createElement("div");
            var a = document.createElement("a");
            a.href = document.getElementById("home_link").href;
            a.innerText = "Zurück zur Startseite";
            div.appendChild(a);
            createPopup(div);
        }
    })
}

export const initOrderManager = () => {
    addBindings(fnNames);

    const inputExtraOptions = document.getElementById("extraOptions");
    if (inputExtraOptions == null) {
        return;
    }
    inputExtraOptions.addEventListener("click", function (e) {
        const showExtraOptions = document.getElementById("showExtraOptions");
        showExtraOptions.classList.toggle("hidden");
    });

    const deleteOrder = document.getElementById("deleteOrder");
    deleteOrder.addEventListener("click", showDeleteConfirmation);
}
