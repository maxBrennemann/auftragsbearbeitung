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

    let params = {
        getReason: "insertLeistung",
        lei: e.options[e.selectedIndex].value,
        bes: document.getElementById("bes").value,
        ekp: document.getElementById("ekp").value,
        pre: document.getElementById("pre").value,
        meh: document.getElementById("meh").value,
        anz: document.getElementById("anz").value,
        auftrag: globalData.auftragsId,
        ohneBerechnung: getOhneBerechnung() ? 1 : 0,
        addToInvoice: getAddToInvoice() ? 1 : 0,
        discount: document.getElementById("showDiscount").children[0].value
    };

    if (globalData.isOverwrite) {
        params.isOverwrite = true;
    }

    var add = new AjaxCall(params, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        updatePrice(response);
        reloadPostenListe();
        infoSaveSuccessfull("success");
        clearInputs({"ids":["bes", "ekp", "pre", "meh", "anz"]});
    });
}

export function addTime() {
    var zeiterfassung = {
        times: globalData.times,
        dates: {}
    }
    var dates = document.getElementsByClassName("dateInput");
    for (let i = 0; i < dates.length; i++) {
        zeiterfassung.dates[i] = dates[i].value;
    }

    let params = {
        getReason: "insTime",
        time: document.getElementById("time").value,
        wage: document.getElementById("wage").value,
        auftrag: globalData.auftragsId,
        descr: document.getElementById("descr").value,
        ohneBerechnung: getOhneBerechnung() ? 1 : 0,
        addToInvoice: getAddToInvoice() ? 1 : 0,
        discount: document.getElementById("showDiscount").children[0].value,
        zeiterfassung : JSON.stringify(zeiterfassung)
    };

    if (globalData.isOverwrite) {
        params.isOverwrite = true;
    }

    if (params.wage == "" || params.wage == null) {
        alert("Stundenlohn kann nicht leer sein.");
        return;
    }

    /* quick fix, if times didn't get a new value, set to empty */
    if (globalData.times[0] == "00:00") {
        params.zeiterfassung = "empty";
    }

    var add = new AjaxCall(params, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        updatePrice(response);
        reloadPostenListe();
        infoSaveSuccessfull("success");
        clearInputs({
            "ids": ["time", "wage", "descr"],
            "classes": ["timeInput", "dateInput"]
        });
    });
}

/**
 * this function gets executed when the "+" button is pressed to add a new timeframe
 * @param {*} event this is the passed event
 */
export function addTimeInputs(event) {
    var p = document.createElement("p");
    var text1 = document.createTextNode("von ");
    var text2 = document.createTextNode(" bis ");
    var text3 = document.createTextNode(" am ");
    var input1 = document.createElement("input");
    var input2 = document.createElement("input");
    var input3 = document.createElement("input");

    input1.classList.add("timeInput");
    input1.type = "time";
    input1.min = "05:00";
    input1.max = "23:00";

    input2.classList.add("timeInput");
    input2.type = "time";
    input2.min = "05:00";
    input2.max = "23:00";

    input3.classList.add("dateInput");
    input3.type = "date";

    p.classList.add("timeInputWrapper");
    p.appendChild(text1);
    p.appendChild(input1);
    p.appendChild(text2);
    p.appendChild(input2);
    p.appendChild(text3);
    p.appendChild(input3);

    event.target.parentNode.insertBefore(p, event.target);
    input1.addEventListener("change", calcTime, false);
    input2.addEventListener("change", calcTime, false);

    input1.focus();
}

export function selectLeistung(e) {
    globalData.aufschlag = parseInt(e.target.options[e.target.selectedIndex].dataset.aufschlag);
}

function cancle() {
    var timeBtn = document.getElementById("addTimeButton");
    timeBtn.innerHTML = "Hinzufügen";
    document.getElementById("cancleBtn").remove();
    if (globalData.isOverwrite) delete globalData.isOverwrite;
    timeBtn.removeEventListener("click", cancle, false);
}

function cancleLeistung() {
    var timeBtn = document.getElementById("addLeistungButton");
    timeBtn.innerHTML = "Hinzufügen";
    document.getElementById("cancleBtn2").remove();
    if (globalData.isOverwrite) delete globalData.isOverwrite;
    timeBtn.removeEventListener("click", cancleLeistung, false);
}

window.editRow = function(key, element) {
    var postentype = element.parentNode.parentNode.firstChild.firstChild.innerHTML;

    /* sends token to server to overwrite a posten */
    var table = document.getElementById("auftragsPostenTable").children[0].dataset.key;
    var postenId = key;
    var update = new AjaxCall(`getReason=overwritePosten&postenId=${postenId}&table=${table}`, "POST", window.location.href);
    update.makeAjaxCall(function (response) {
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
            var btn = document.createElement("button");
            btn.innerHTML = "Abbrechen";
            btn.id = "cancleBtn";
            timeBtn.parentNode.insertBefore(btn, timeBtn);

            timeBtn.addEventListener("click", cancle, false);

            btn.addEventListener("click", cancle, false);
            globalData.isOverwrite = true;

            break;
        case "Leistung":
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
            var btn = document.createElement("button");
            btn.innerHTML = "Abbrechen";
            btn.id = "cancleBtn2";
            leistungBtn.parentNode.insertBefore(btn, leistungBtn);

            leistungBtn.addEventListener("click", cancleLeistung, false);
            btn.addEventListener("click", cancleLeistung, false);
            globalData.isOverwrite = true;
            break;
        case "Produkt":
            btns[2].click();
            break;
        case "productcompact":
            break;
    }
}

export function initPostenFilter() {
    const inputRechnungspostenAusblenden = document.getElementById("rechnungspostenAusblenden");
    if (inputRechnungspostenAusblenden != null) {
        inputRechnungspostenAusblenden.addEventListener("change", function (e) {
            const value = e.target.checked;
            ajax.post({
                r: "setRechnungspostenAusblenden",
                value: value,
            });
            reloadPostenListe();
        });
    }
}

export function click_mehListener() {
    document.getElementById("selectReplacerMEH").classList.add("selectReplacerShow");
}

/**
 * this function gets executed when a time input is filled in
 * @param {*} e this is the passed event
 */
export function calcTime(e) {
    var time = e.target.value;
    var addPostenZeit = document.getElementById("addPostenZeit");
    var elements = addPostenZeit.getElementsByClassName("timeInput");
    var index = -1;
    for (var i = 0; i < elements.length; i++) {
        if (elements[i] === e.target) {
            index = i;
        }
    }

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

    globalData.times[index] = time;
    console.log(globalData.times);
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

function reloadPostenListe() {
    var reload = new AjaxCall(`getReason=reloadPostenListe&id=${globalData.auftragsId}`, "POST", window.location.href);
    reload.makeAjaxCall(function (response) {
        response = JSON.parse(response);
        document.getElementById("auftragsPostenTable").innerHTML = response[0];
        document.getElementById("invoicePostenTable").innerHTML = response[1];
    });
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
