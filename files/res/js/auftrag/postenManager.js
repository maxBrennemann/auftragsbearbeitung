import { ajax } from "../classes/ajax.js";

/**
 * cancles the edit time entry, resets the button, removes the cancle button and clears the inputs
 */
function cancle() {
    var timeBtn = document.getElementById("addTimeButton");
    timeBtn.innerHTML = "Hinzufügen";
    document.getElementById("cancleBtn").remove();

    if (globalData.isOverwrite) {
        delete globalData.isOverwrite;
    }
    timeBtn.removeEventListener("click", cancle, false);

    clearInputs({
        "ids": ["time", "wage", "descr"],
        "classes": ["timeInput", "dateInput"]
    });
}

function cancleLeistung() {
    var timeBtn = document.getElementById("addLeistungButton");
    timeBtn.innerHTML = "Hinzufügen";
    document.getElementById("cancleBtn2").remove();

    if (globalData.isOverwrite) {
        delete globalData.isOverwrite;
    }
    timeBtn.removeEventListener("click", cancleLeistung, false);

    clearInputs({ "ids": ["bes", "ekp", "pre", "meh", "anz"] });
}

export function showPostenAdd() {
    document.getElementById("showPostenAdd").classList.remove("hidden");
}

function editLeistungEntry(btns, parameters) {
    btns[1].click();
    document.getElementById("selectLeistung").value = parameters.type;
    document.getElementById("meh").value = parameters.unit;
    document.getElementById("anz").value = parameters.quantity;
    document.getElementById("bes").value = parameters.description;
    document.getElementById("ekp").value = parameters.buyingprice;
    document.getElementById("pre").value = parameters.price;
    document.getElementById("ohneBerechnung").checked = parameters.notcharged == "0" ? false : true;
    document.getElementById("addToInvoice").checked = parameters.isinvoice == "0" ? false : true;
    document.getElementById("getDiscount").value = parameters.discount;

    var leistungBtn = document.getElementById("addLeistungButton");
    leistungBtn.innerHTML = "Speichern";
    manageCancleBtn(leistungBtn, "cancleBtn2", cancleLeistung);

    leistungBtn.addEventListener("click", cancleLeistung, false);
    globalData.isOverwrite = true;
}

function editTimeEntry(btns, parameters) {
    btns[0].click();
    document.getElementById("time").value = parameters.time;
    document.getElementById("wage").value = parameters.wage;
    document.getElementById("descr").value = parameters.description;
    document.getElementById("ohneBerechnung").checked = parameters.notcharged == "0" ? false : true;
    document.getElementById("addToInvoice").checked = parameters.isinvoice == "0" ? false : true;
    document.getElementById("getDiscount").value = parameters.discount;

    for (let i = 0; i < parameters.timetable.length; i++) {
        if (i > 0) {
            var timeInputWrapper = document.getElementsByClassName("timeInputWrapper");
            timeInputWrapper = timeInputWrapper[timeInputWrapper.length - 1];
            timeInputWrapper.nextElementSibling.click();
        }
        var timeInputs = document.getElementsByClassName("timeInput");
        var dateInputs = document.getElementsByClassName("dateInput");

        var time_from = parseInt(parameters.timetable[i].from_time);
        time_from = Math.floor(time_from / 60).toString().padStart(2, '0') + ":" + (time_from % 60).toString().padStart(2, '0');

        var time_to = parseInt(parameters.timetable[i].to_time);
        time_to = Math.floor(time_to / 60).toString().padStart(2, '0') + ":" + (time_to % 60).toString().padStart(2, '0');

        timeInputs[i * 2].value = time_from;
        timeInputs[i * 2 + 1].value = time_to;
        dateInputs[i].value = parameters.timetable[i].date;

        var event1 = new Event('change');
        timeInputs[i * 2].dispatchEvent(event1);
        var event2 = new Event('change');
        timeInputs[i * 2 + 1].dispatchEvent(event2);
    }

    var timeBtn = document.getElementById("addTimeButton");
    timeBtn.innerHTML = "Speichern";
    manageCancleBtn(timeBtn, "cancleBtn", cancle);

    timeBtn.addEventListener("click", cancle, false);
    globalData.isOverwrite = true;
}

/**
 * checks if the cancle button is already there, if so it removes it and adds a new one
 * 
 * @param {*} insertBefore 
 * @param {*} id 
 * @param {*} cancleFunction 
 */
function manageCancleBtn(insertBefore, id, cancleFunction) {
    const cancleBtn = document.getElementById("cancleBtn");
    if (cancleBtn != null) {
        cancleBtn.remove();
    }

    const btn = document.createElement("button");
    btn.innerHTML = "Abbrechen";
    btn.id = id;
    btn.classList.add("btn-primary");
    btn.addEventListener("click", cancleFunction, false);
    insertBefore.parentNode.insertBefore(btn, insertBefore);
}

export function initPostenFilter() {
    const inputRechnungspostenAusblenden = document.getElementById("rechnungspostenAusblenden");
    if (inputRechnungspostenAusblenden == null) {
        return;
    }

    inputRechnungspostenAusblenden.addEventListener("change", function (e) {
        const value = e.target.checked;
        ajax.put(`/api/v1/settings/filter-order-posten`, {
            "value": value,
        }).then(() => {
            reloadPostenListe();
        })
    });
}

const reloadPostenListe = async () => {
    const response = await ajax.get(`/api/v1/order-items/${globalData.auftragsId}/all-old`);

    document.getElementById("auftragsPostenTable").innerHTML = response.data[0];
    document.getElementById("invoicePostenTable").innerHTML = response.data[1];
}
