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
    ajax.post({
        r: "updateDate",
        auftrag: globalData.auftragsId,
        date: value,
        type: type,
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
    var text = document.getElementById("orderDescription");

    ajax.post({
        r: "saveDescription",
        text: text.value,
        auftrag: globalData.auftragsId,
    }, true).then(response => {
        if (response == "saved") {
            infoSaveSuccessfull("success");
        } else {
            infoSaveSuccessfull();
        }
    });
}

export function editOrderType() {
    const select = document.getElementById("orderType");
    const value = select.value;

    ajax.post({
        r: "saveOrderType",
        type: value,
        auftrag: globalData.auftragsId,
    }, true).then(response => {
        if (response == "saved") {
            infoSaveSuccessfull("success");
        } else {
            infoSaveSuccessfull();
        }
    });
}

export function editTitle() {
    var text = document.getElementById("orderTitle");

    ajax.post({
        r: "saveTitle",
        text: text.value,
        auftrag: globalData.auftragsId,
    }, true).then(response => {
        if (response == "saved") {
            infoSaveSuccessfull("success");
        } else {
            infoSaveSuccessfull();
        }
    });
}

export function archvieren() {
    ajax.post({
        r: "archivieren",
        auftrag: globalData.auftragsId,
    }, true).then(() => {
       var div = document.createElement("div");
       var a = document.createElement("a");
       a.href = document.getElementById("home_link").href;
       a.innerText = "Zurück zur Startseite";
       div.appendChild(a);
       centerAbsoluteElement(div);
       addActionButtonForDiv(div, 'remove');
       document.body.appendChild(div);
    });
}
