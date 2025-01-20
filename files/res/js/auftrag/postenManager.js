import { ajax } from "../classes/ajax.js";

export async function addProductCompactOld() {
    await ajax.post({
        r: "insertProductCompact",
        menge: document.getElementById("posten_produkt_menge").value,
        marke: document.getElementById("posten_produkt_marke").value,
        ekpreis: document.getElementById("posten_produkt_ek").value,
        vkpreis: document.getElementById("posten_produkt_vk").value,
        name: document.getElementById("posten_produkt_name").value,
        beschreibung: document.getElementById("posten_produkt_besch").value,
        auftrag: globalData.auftragsId,
        ohneBerechnung: getOhneBerechnung() ? 1 : 0,
        addToInvoice: getAddToInvoice() ? 1 : 0,
        discount: document.getElementById("showDiscount").children[0].value
    });

    reloadPostenListe();
    clearInputs({
        "ids": [
            "posten_produkt_menge",
            "posten_produkt_marke",
            "posten_produkt_ek",
            "posten_produkt_vk",
            "posten_produkt_name",
            "posten_produkt_besch"
        ]
    });
}

export function addLeistung() {
    var e = document.getElementById("selectLeistung");

    let ekp = document.getElementById("ekp").value;
    let pre = document.getElementById("pre").value;
    let anz = document.getElementById("anz").value;

    let params = {
        lei: e.options[e.selectedIndex].value,
        bes: document.getElementById("bes").value,
        ekp: ekp,
        pre: pre,
        meh: document.getElementById("meh").value,
        anz: anz,
        auftrag: globalData.auftragsId,
        ohneBerechnung: getOhneBerechnung() ? 1 : 0,
        addToInvoice: getAddToInvoice() ? 1 : 0,
        discount: document.getElementById("showDiscount").children[0].value
    };

    if (globalData.isOverwrite) {
        params.isOverwrite = true;
    }

    ajax.post(`/api/v1/order-items/${globalData.auftragsId}/services`, params).then(response => {
        updatePrice(response);
        reloadPostenListe();
        infoSaveSuccessfull("success");
        clearInputs({"ids":["bes", "ekp", "pre", "meh", "anz"]});
    });
}

export const addTime = () => {
    const wage = document.getElementById("wage").value;
    if (wage == "" || wage == null) {
        alert("Stundenlohn kann nicht leer sein.");
        return;
    }

    const time = document.getElementById("time").value;
    const description = document.getElementById("descr").value;
    const noPayment = getOhneBerechnung() ? 1 : 0;
    const addToInvoice = getAddToInvoice() ? 1 : 0;
    const discount = document.getElementById("showDiscount").children[0].value;
    const overwrite = globalData.isOverwrite ? 1 : 0;
    const times = JSON.stringify(globalData.times);

    ajax.post(`/api/v1/order-items/${globalData.auftragsId}/times`, {
        "time": time,
        "wage": wage,
        "description": description,
        "noPayment": noPayment,
        "addToInvoice": addToInvoice,
        "discount": discount,
        "overwrite": overwrite,
        "times": times,
    }).then(r => {
        updatePrice(r.price);
        reloadPostenListe();
        infoSaveSuccessfull("success");
        clearInputs({
            "ids": ["time", "wage", "descr"],
            "classes": ["timeInput", "dateInput"]
        });
    });
}

/**
 * this function gets executed when the "+" button is pressed to add a new timeframe or on init
 * @param {*} event this is the passed event
 */
export function createTimeInputRow() {
    const template = document.getElementById("templateTimeInput");
	const div = document.createElement("div");
	div.appendChild(template.content.cloneNode(true));
    
    const extendedTimeInput = document.getElementById("extendedTimeInput");
    extendedTimeInput.appendChild(div);
    
    const dateInput = div.querySelector(".dateInput");
    dateInput.dataset.index = globalData.times.length;
    dateInput.addEventListener("change", () => {
        globalData.times[dateInput.dataset.index].date = dateInput.value;
    }, false);

    const timeInputs = div.getElementsByClassName("timeInput");
    const start = timeInputs[0];
    const end = timeInputs[1];

	start.addEventListener("change", calcTime, false);
    end.addEventListener("change", calcTime, false);

    start.dataset.index = globalData.times.length;
    start.dataset.type = "start";
    end.dataset.index = globalData.times.length;
    end.dataset.type = "end";

    globalData.times.push({start: "00:00", end: "00:00", date: ""});

    start.focus();
}

/**
 * this function gets executed when a time input is filled in
 * @param {*} e this is the passed event
 */
function calcTime(e) {
    var time = e.target.value;
    var addPostenZeit = document.getElementById("addPostenZeit");
    var elements = addPostenZeit.getElementsByClassName("timeInput");

    const index = e.target.dataset.index;
    const type = e.target.dataset.type;

    var timeDiff = 0;
    for (var i = 0; i < elements.length; i += 2) {
        var start = elements[i].value.split(":");
        var stop = elements[i + 1].value.split(":");

        var temp = parseInt(stop[0]) * 60 + parseInt(stop[1]) - parseInt(start[0]) * 60 - parseInt(start[1]);

        if (temp > 0) {
            timeDiff += temp;
            elements[i].parentNode.classList.remove("timeInputWrapperRed");
        } else {
            elements[i].parentNode.classList.add("timeInputWrapperRed");
        }
    }

    document.getElementById("showTimeSummary").innerHTML = timeDiff + " Minuten";
    document.getElementById("time").value = timeDiff;

    globalData.times[index][type] = time;
    console.log(globalData.times);
}

export function selectLeistung(e) {
    globalData.aufschlag = parseInt(e.target.options[e.target.selectedIndex].dataset.aufschlag);
}

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

    clearInputs({"ids":["bes", "ekp", "pre", "meh", "anz"]});
}

/**
 * editRow onclick gets executed when the edit button is pressed
 * @param {*} key 
 * @param {*} element 
 */
window.editRow = function(key, element) {
    var postentype = element.parentNode.parentNode.firstChild.firstChild.innerHTML;

    /* sends token to server to overwrite a posten */
    var table = document.getElementById("auftragsPostenTable").children[0].dataset.key;
    var postenId = key;

    ajax.post({
        "getReason": "overwritePosten",
        "postenId": postenId,
        "table": table,
    }).then(response => {
        var data = JSON.parse(response);
        console.log(data);

        setParameters(postentype, data.data);
    });
}

export function showPostenAdd() {
    document.getElementById("showPostenAdd").style.display = "";
}

function setParameters(postentype, parameters) {
    var btns = document.getElementsByClassName("tablinks");
    showPostenAdd();
    switch (postentype) {
        case "Zeit":
            editTimeEntry(btns, parameters);
            break;
        case "Leistung":
            editLeistungEntry(btns, parameters);
            break;
        case "Produkt":
            btns[2].click();
            break;
        case "productcompact":
            break;
    }
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
    document.getElementById("discountInput").value = parameters.discount;

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
    document.getElementById("discountInput").value = parameters.discount;

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
        time_to =  Math.floor(time_to / 60).toString().padStart(2, '0') + ":" + (time_to % 60).toString().padStart(2, '0');

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

export function click_mehListener() {
    document.getElementById("selectReplacerMEH").classList.add("selectReplacerShow");
}

/* https://www.w3schools.com/howto/tryit.asp?filename=tryhow_css_js_dropdown */
window.addEventListener("click", function(event) {
    if (!event.target.matches('.selectReplacer') && !event.target.matches('#meh_dropdown') && !event.target.matches('#meh')) {
        var dropdowns = document.getElementsByClassName("selectReplacer");
        var i;
        for (i = 0; i < dropdowns.length; i++) {
            var openDropdown = dropdowns[i];
            if (openDropdown.classList.contains('selectReplacerShow')) {
                openDropdown.classList.remove('selectReplacerShow');
            }
        }
    }
}, false);

document.addEventListener("keyup", (e) => {
    if (e.code === "Enter" || e.code === "NumpadEnter") {
        var element = document.activeElement;
        if (element.parentNode.classList.contains("timeInputWrapper")) {
            element.parentNode.parentNode.querySelector("button").click();
        }
    }
});

export function addProductCompact() {
    var btns = document.getElementsByClassName("tablinks");
    btns[1].click();
}

const reloadPostenListe = async () => {
    const response = await ajax.get(`/api/v1/order-items/${globalData.auftragsId}/all`);

    document.getElementById("auftragsPostenTable").innerHTML = response.data[0];
    document.getElementById("invoicePostenTable").innerHTML = response.data[1];
}

function updatePrice(newPrice) {
    document.getElementById("gesamtpreis").innerText = new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(newPrice);
}

function getOhneBerechnung() {
    return document.getElementById("ohneBerechnung").checked;
}

function getAddToInvoice() {
    return document.getElementById("addToInvoice").checked;
}
