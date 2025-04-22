import { ajax } from "../classes/ajax.js";
import { notification } from "../notifications.js";

export async function setOrderFinished() {
    if (confirm('Möchtest Du den Auftrag als "Erledigt" markieren?')) {
        await ajax.post({
            r: "setOrderFinished",
            auftrag: globalData.auftragsId,
        });
        document.getElementById("home_link").click();
    }
}

export function updateDate(e) {
    const date = e.target.value;
    sendDate(1, date);
}

export function updateDeadline(e) {
    const date = e.target.value;
    sendDate(2, date);
}

export function setDeadlineState(e) {
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

/**
 * this function is called from auftrag.js init function
 * @returns 
 */
export function initExtraOptions() {
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

function showDeleteConfirmation() {
    const template = document.getElementById("templateAlertBox");
    const div = document.createElement("div");
    div.id = "alertBox";
    div.appendChild(template.content.cloneNode(true));

    document.body.appendChild(div);
    div.classList.add("absolute", "w-96", "z-20");

    const deleteOrderBtn = div.querySelector('#deleteOrder');
    deleteOrderBtn.addEventListener("click", deleteOrder, false);

    const closeAlertBtn = div.querySelector('#closeAlert');
    closeAlertBtn.addEventListener("click", closeAlert, false);


    centerAbsoluteElement(div);
    addActionButtonForDiv(div, "remove");
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

export function editDescription() {
    var text = document.querySelector(".orderDescription:not(.hidden)");

    ajax.post({
        r: "saveDescription",
        text: text.value,
        auftrag: globalData.auftragsId,
    }, true).then(response => {
        if (response == "saved") {
            notification("", "success");
        } else {
            notification("", "failure");
        }
    });
}

export const editOrderType = () => {
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

export const editTitle = () => {
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

export function archvieren() {
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
            centerAbsoluteElement(div);
            addActionButtonForDiv(div, 'remove');
            document.body.appendChild(div);
        }
    })
}
