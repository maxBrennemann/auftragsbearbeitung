/* global variables */
var globalData = {
    aufschlag: 0,
    vehicleId: 0,
    erledigendeSchritte : null,
    alleSchritte : null,
    auftragsId : parseInt(new URL(window.location.href).searchParams.get("id")),
    times : {
        0: "00:00",
        1: "00:00"
    },
}

if (document.readyState !== 'loading' ) {
    initCode();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initCode();
    });
}

function initCode() {
    document.getElementById("meh_dropdown").addEventListener("click", meh_eventListener, false);
    document.getElementById("meh").addEventListener("click", meh_eventListener, false);

    /* auto sizes textareas on page load */
	var timeInputs = document.getElementsByClassName("timeInput");
	for (t of timeInputs) {
		t.addEventListener("change", calcTime, false);
	}

    addSearchEventListeners();

    if (document.getElementById("selectVehicle") == null)
        return null;
    
    document.getElementById("selectVehicle").addEventListener("change", function(event) {
        if (event.target.value == "addNew") {
            document.getElementById("addVehicle").style.display = "inline-block";
        }
    });

    initPostenFilter();
}

function initPostenFilter() {
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

function addSearchEventListeners() {
    var data = document.getElementsByClassName("searchProductEvent");
    for (let i = 0; i < data.length; i++) {
        data[i].addEventListener("click", performProductSearch, false);
    }
    document.getElementById("productSearch").addEventListener("change", performProductSearch, false);
}

function performProductSearch() {
    var query = document.getElementById("productSearch").value;
    
    var params = {
        getReason: "searchProduct",
        query: query
    };
    
    var search = new AjaxCall(params, "POST", window.location.href);
    search.makeAjaxCall(function (response) {
        var element = document.getElementById("resultContainer");
        element.innerHTML = response;
    });
}

function meh_eventListener() {
    document.getElementById("selectReplacerMEH").classList.add("selectReplacerShow");
}

/**
 * this function gets executed when a time input is filled in
 * @param {*} e this is the passed event
 */
function calcTime(e) {
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

/**
 * this function gets executed when the "+" button is pressed to add a new timeframe
 * @param {*} event this is the passed event
 */
function addTimeInputs(event) {
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

function addTime() {
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

function addLeistung() {
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

function addProductCompactOld() {
    let params = {
        getReason: "insertProductCompact",
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
    };

    var add = new AjaxCall(params, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        console.log(response);
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
    });
}

function addProductCompact() {
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

class PostenListe {
    constructor() {

    }
}

function getOhneBerechnung() {
    return document.getElementById("ohneBerechnung").checked;
}

function getAddToInvoice() {
    return document.getElementById("addToInvoice").checked;
}

function addBearbeitungsschritt() {
    var tableData = document.getElementsByClassName("bearbeitungsschrittInput");
    var steps = [];
    for (var i = 0; i < tableData.length; i++) {
        steps.push(tableData[i].value);
    }

    if (steps[1] == "") {
        steps[1] = 0;
    }

    var el = document.getElementsByName("isAlreadyDone")[0];
    var radio = el.elements["isDone"];
    var hide;
    for (var i = 0; i < radio.length; i++) {
        if (radio[i].checked) {
            hide = radio[i].value;
            break;
        }
    }
    
    /* 0 = hide, 1 = show */
    hide = hide == "hide" ? 0 : 1;

    /* check for assigned task */
    let assigned = document.querySelector('input[name="assignTo"]');
    let assignedTo = "none";
    if (assigned.checked == true) {
        let e = document.getElementById("selectMitarbeiter");
        assignedTo = e.options[e.selectedIndex].value;
    }

    /* ajax parameter */
    let params = {
        getReason: "insertStep",
        bez: steps[0],
        datum: steps[1],
        auftrag: globalData.auftragsId,
        hide: hide,
        prio: steps[2],
        assignedTo: assignedTo
    };

    var add = new AjaxCall(params, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        console.log(response);
        document.getElementById("stepTable").innerHTML = response;

        var tableData = document.getElementsByClassName("bearbeitungsschrittInput");
        for (var i = 0; i < tableData.length; i++) {
            tableData[i].value = "";
        }

        document.getElementById("bearbeitungsschritte").removeChild(document.getElementById("sendStepToServer"));
        document.getElementById("bearbeitungsschritte").style.display = "none";
    }.bind(this), false);
}

/* addes bearbeitungsschritte */
function showBearbeitungsschritt() {
    var bearbeitungsschritte = document.getElementById("bearbeitungsschritte");
    bearbeitungsschritte.style.display = "block";

    const textarea = document.querySelector("textarea.bearbeitungsschrittInput");
    textarea.focus();
}

/* adds a note to the order */
function addNote() {
    var noteNode = document.querySelector(".noteInput");
    if (noteNode == undefined)
        return null;

    note = noteNode.value;
    noteNode.value = "";

    /* ajax parameter */
    let params = {
        getReason: "addNoteOrder",
        auftrag: globalData.auftragsId,
        note: note
    };

    var add = new AjaxCall(params, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        document.getElementById("noteContainer").innerHTML = response;
        infoSaveSuccessfull("success");
    }.bind(this), false);
}

/* function creates a popup window that asks the user whether he wants the note to be deleted or not */
function removeNote(event) {
    let note = event.target.parentNode.children[1].innerHTML;

    let number = indexInClass(event.target.parentNode);

    var div = document.createElement("div");
    let textnode = document.createTextNode(`Willst Du die Notiz "${note}" wirklich löschen?`);

    let btn_yes = document.createElement("button");
    btn_yes.innerHTML = "Ja";
    let btn_no = document.createElement("button");
    btn_no.innerHTML = "Nein";

    /* inner function to delete the node */
    function delNode(number, div) {
        div.parentNode.removeChild(div);

        console.log(number);

        /* ajax call to delete note from db, note is then removed from webpage */
        var del = new AjaxCall(`getReason=deleteNote&number=${number}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
        del.makeAjaxCall(function (response) {
            document.getElementById("noteContainer").innerHTML = response;
        });
    }

    /* inner function for node button to remove the div */
    function close(div) {
        div.parentNode.removeChild(div);
    }

    /* event listeners */
    btn_yes.addEventListener("click", function() {
        delNode(number, div);
    }, false);

    btn_no.addEventListener("click", function() {
        close(div);
    }, false);

    div.appendChild(textnode);
    div.appendChild(document.createElement("br"));
    div.appendChild(btn_yes);
    div.appendChild(btn_no);
    document.body.appendChild(div);

    addActionButtonForDiv(div, "remove");
    centerAbsoluteElement(div);
}

/* changes the contact */
async function changeContact() {
    var div = document.createElement("div");

    var kdnr = document.getElementById("kundennummer").innerHTML;
    var response = await makeAsyncCall("POST", `getReason=getAnspr&id=${kdnr}`, window.location.href)
        .then(function (response) {
            return response;
        })
        .catch(function (err) {
            console.error('Augh, there was an error!', err);
    });

    div.innerHTML = response;
    document.body.appendChild(div);

    var btn = div.getElementsByTagName("button");
    btn[0].addEventListener("click", function(event) {
        var checkedId = document.querySelector('input[name="anspr"]:checked');
        if (checkedId == null)
            return;
        checkedId = checkedId.dataset.ansprid;
        var sendAnsprId = new AjaxCall(`getReason=setAnspr&order=${globalData.auftragsId}&ansprId=${checkedId}`, "POST", window.location.href);
        sendAnsprId.makeAjaxCall(function (ansprResponse) {
            var data = JSON.parse(ansprResponse);
            var error = data[0];
            if (error == "ok") {
                infoSaveSuccessfull("success");
                document.getElementById("showAnspr").innerHTML = data[1];
            } else {
                alert(response);
                infoSaveSuccessfull();
            }
        });

        /* accesses the first childnode of the parent container, this child contains a close button */
        event.target.parentNode.children[0].click();
    }, false);

    addActionButtonForDiv(div, "hide");
    centerAbsoluteElement(div);
}

function performSearch(e) {
    var query = e.target.previousSibling.value;
    console.log(query);

    var search = new AjaxCall(`getReason=search&query=${query}&stype=produkt&shortSummary=true`, "POST", window.location.href);
    search.makeAjaxCall(function (responseTable) {
        var div = document.createElement("div");
        div.innerHTML = responseTable;
        div.id = "prodcutSearchContainer";
        document.body.appendChild(div);
        centerAbsoluteElement(div);
        addActionButtonForDiv(div, 'remove');

        addableTables();
    });
}

function addFahrzeug(param) {
    var kfz, fahrzeug;
    if (param != null && param) {
        if (globalData.vehicleId != 0) {
            var ajax = new AjaxCall(`getReason=attachCar&auftrag=${globalData.auftragsId}&fahrzeug=${globalData.vehicleId}`);
            ajax.makeAjaxCall(function (response) {
                document.getElementById("fahrzeugTable").innerHTML = response;
            });
        }
    } else {
        kfz = document.getElementById("kfz").value;
        fahrzeug = document.getElementById("fahrzeug").value;

        var kundennummer = document.getElementById("kundennummer").innerText;
        var add = new AjaxCall(`getReason=insertCar&kfz=${kfz}&fahrzeug=${fahrzeug}&kdnr=${kundennummer}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
        add.makeAjaxCall(function (response) {
            /* table is in the div after the addVehicle form */
            let el = document.getElementById("addVehicle");
            el = el.nextElementSibling;
            el.innerHTML = response;
        });
    }
}

function selectVehicle(event) {
    globalData.vehicleId = event.target.value;
}

function showDeleteMessage(row, header, key, type) {
    var div = document.createElement("div");

    /* creates table */
    let table = document.createElement("table");
    tbody = document.createElement("tbody");
    table.appendChild(tbody);
    tbody.appendChild(header.cloneNode(true));
    tbody.appendChild(row.cloneNode(true));

    /* creates inner text of deletion verification */
    let content = `Willst Du diese Zeile wirklich löschen?:<br>`;
    let contentNode = document.createElement("p");
    contentNode.innerHTML = content;
    contentNode.appendChild(table);

    /* creates the yes and no buttons */
    let btn_yes = document.createElement("button");
    btn_yes.innerHTML = "Ja";
    let btn_no = document.createElement("button");
    btn_no.innerHTML = "Nein";

    /* inner function to delete the node
     * type => type of data to be deleted
     * key => the key for the server so that the correct data is deleted
     * row => row for the frontend to be deleted 
     */
    function delNode(type, key, row) {
        var del = new AjaxCall(`getReason=delete&type=${type}&key=${key}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
        del.makeAjaxCall(function (response) {
            console.log(response);
        });

        while (row.nodeName != "TR") {
            row = row.parentNode;
        }

        row.parentNode.removeChild(row);
    }

    /* inner function for node button to remove the div */
    function close(div) {
        div.parentNode.removeChild(div);
    }

    /* event listeners */
    btn_yes.addEventListener("click", function() {
        delNode(type, key, row);
        close(div);
    }, false);

    btn_no.addEventListener("click", function() {
        close(div);
    }, false);

    /* adds all relevant nodes to the div */
    div.appendChild(contentNode);
    div.appendChild(document.createElement("br"));
    div.appendChild(btn_yes);
    div.appendChild(btn_no);
    document.body.appendChild(div);

    addActionButtonForDiv(div, "remove");
    centerAbsoluteElement(div);
}

function editRow(key, element) {
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

/* function starts deletion of the row */
function deleteRow(key, type = "schritte", node) {
    let row = node.parentNode.parentNode;
    let header = row.parentNode.children[0];

    showDeleteMessage(row, header, key, type);
}

/* https://www.therogerlab.com/sandbox/pages/how-to-reorder-table-rows-in-javascript?s=0ea4985d74a189e8b7b547976e7192ae.4122809346f6a15e41c9a43f6fcb5fd5 */
var row;
var rows;
function move(event) {
    if (event.target.classList.contains("moveRow")) {
        event.preventDefault();

        if (rows.indexOf(event.target.parentNode.parentNode) > rows.indexOf(row))
            event.target.parentNode.parentNode.after(row);
        else
            event.target.parentNode.parentNode.before(row);
    }
}

function moveStart(event) {
    row = event.target;
}

/* called from moveBtn */
function moveInit(event) {
    var table = event.target;
    while (table.nodeName != "TABLE") {
        table = table.parentNode;
    }

    rows = Array.from(table.getElementsByTagName("tr"));
    for (let i = 0; i < rows.length; i++) {
        if (i == 0)
            continue;

        rows[i].draggable = "true";
        rows[i].addEventListener("dragstart", function(event) {
            moveStart(event)
        }, false);
        rows[i].addEventListener("dragover", function(event) {
            move(event)
        }, false);
        rows[i].addEventListener("dragend", function(event) {
            sendPostenOrder(event)
        }, false);
    }
}

/* called from moveBtn */
function moveRemove(event) {
    var table = event.target;
    while (table.nodeName != "TABLE") {
        table = table.parentNode;
    }

    rows = Array.from(table.getElementsByTagName("tr"));
    for (let i = 0; i < rows.length; i++) {
        if (i == 0)
            continue;

        rows[i].draggable = "false";
        rows[i].removeEventListener("dragstart", function(event) {
            moveStart(event)
        }, false);
        rows[i].removeEventListener("dragover", function(event) {
            move(event)
        }, false);
        rows[i].removeEventListener("dragend", function(event) {
            sendPostenOrder(event)
        }, false);
    }
} 

function sendPostenOrder(event) {
    var table = event.target;
    while (table.nodeName != "TABLE") {
        table = table.parentNode;
    }

    var btns = Array.from(table.getElementsByClassName("moveRow"));
    var positions = [];
    for (let i = 0; i < btns.length; i++) {
        positions.push(btns[i].dataset.key);
    }
    positions = JSON.stringify(positions);

    let params = {
        getReason: "sendPostenPositions",
        auftrag: globalData.auftragsId,
        order: positions,
        tablekey: table.dataset.key
    };

    var send = new AjaxCall(params, "POST", window.location.href);
    send.makeAjaxCall(function (response) {
        if (response == "ok") {
            infoSaveSuccessfull("success");
        } else {
            infoSaveSuccessfull();
        }

        console.log(response);
    });
}

function updateIsDone(key, event) {
    var update = new AjaxCall(`getReason=update&key=${key}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
    update.makeAjaxCall(function (response, args) {
        console.log(response);
        /* removes the row */
        let button = args[0];
        let row = button.parentNode.parentNode;
        row.parentNode.removeChild(row);
    }, event.target);
}

function radio(val) {
    var stepTable = document.getElementById("stepTable");
    var params = "", data;
    if (val == "show") {
        params = `getReason=getAllSteps&auftrag=${globalData.auftragsId}`;
        data = globalData.alleSchritte;
    } else if (val == "hide") {
        params = `getReason=getOpenSteps&auftrag=${globalData.auftragsId}`;
        data = globalData.erledigendeSchritte;
    }
    
    if (data == null) {
        var add = new AjaxCall(params, "POST", window.location.href);
        add.makeAjaxCall(function (response, data) {
            stepTable.innerHTML = response;
            switch (data[0]) {
                case "show":
                    globalData.alleSchritte = response;
                break;
                case "hide":
                    globalData.erledigendeSchritte = response;
                break;
            }
        }, val);
    } else {
        stepTable.innerHTML = data;
    }
}

function selectLeistung(e) {
    globalData.aufschlag = parseInt(e.target.options[e.target.selectedIndex].dataset.aufschlag);
}

function addColor() {
    const template = document.getElementById("templateFarbe");
	const div = document.createElement("div");
    div.id = "selectColor";
	div.appendChild(template.content.cloneNode(true));
    div.classList.add("w-2/3");
    
    document.body.appendChild(div);

    const cp = new Colorpicker(div.querySelector("#cpContainer"));
    const c = div.querySelector("canvas");
    c.style.margin = "auto";

    c.addEventListener("mouseup", function() {
        const element = document.querySelector("input.colorInput.jscolor");
        element.value = cp.color.toUpperCase();
        checkHexCode(element);
    }, false);

    addActionButtonForDiv(div, "hide");
    centerAbsoluteElement(div);
}

function removeColor(colorId) {
    console.log("removing color : " + colorId);

    var arch = new AjaxCall(`getReason=removeColor&auftrag=${globalData.auftragsId}&colorId=${colorId}`);
    arch.makeAjaxCall(function (colorHTML) {
        var showColors = document.getElementById("showColors");
        //var data = JSON.parse(colorHTML);
        showColors.innerHTML = colorHTML; //data.farben;
    });
}

/*
 * you can select multiple existing colors, which are added to this variable via the function
 * beneath;
 * all colors are highlighted via the colorElementUnderline class
 */
var addToOrderColors = [];
function toggleCS() {
    var container = document.getElementById("csContainer");
    container.style.display = "block";
    centerAbsoluteElement(document.getElementById("farbe"));

    var elements = container.getElementsByClassName("singleColorContainer");
    for (let i = 0; i < elements.length; i++) {
        var e = elements[i];
        e.addEventListener("click", function(event) {
            event.currentTarget.classList.toggle("colorElementUnderline");
            let id = event.currentTarget.dataset.colorid;
            if (addToOrderColors.includes(id)) {
                let index = addToOrderColors.indexOf(id);
                addToOrderColors.slice(index, -1);
            } else {
                addToOrderColors.push(id);
            }
        }, false);
    }
}

/*
 * adds all selected colors to the server;
 */
function addSelectedColors() {
    var addcolors = new AjaxCall(`getReason=existingColors&auftrag=${globalData.auftragsId}&ids=${JSON.stringify(addToOrderColors)}`);
    addcolors.makeAjaxCall(function (colorHTML) {
        var showColors = document.getElementById("showColors");
        var data = JSON.parse(colorHTML);
        showColors.innerHTML = data.farben;
        
        var elements = document.getElementsByClassName("colorInput");
        for (let i = 0; i < elements.length; i++) {
            elements[i].value = "";
        }
    });
}

/*
 * sends the newly created color to the server;
 * then resets the form and shows the newly added color
 */
function sendColor() {
    var elements = document.getElementsByClassName("colorInput");
    var data = [], currVal;

    for (let i = 0; i < elements.length; i++) {
        currVal = elements[i].value;
        if (currVal == null || currVal == "") {
            alert("Felder dürfen nicht leer sein!");
            return null;
        }
        data.push(currVal);
    }
    
    var sendC = new AjaxCall(`getReason=newColor&auftrag=${globalData.auftragsId}&farbname=${data[0]}&farbwert=${data[3]}&bezeichnung=${data[1]}&hersteller=${data[2]}`);
    sendC.makeAjaxCall(function (colorHTML) {
        var showColors = document.getElementById("showColors");
        var data = JSON.parse(colorHTML);
        showColors.innerHTML = data.farben;
        
        var elements = document.getElementsByClassName("colorInput");
        for (let i = 0; i < elements.length; i++) {
            elements[i].value = "";
        }
    });
}

function showAuftrag() {
    var url = window.location.href;
    url += "&show=t";
    window.location.href = url;
}

function checkHexCode(el) {
    if (/^[0-9a-fA-F]{6}$/.test(el.value)) {
        el.parentNode.classList.add("validInput");
        el.parentNode.classList.remove("invalidInput");
        return null;
    }
    el.parentNode.classList.add("invalidInput");
    el.parentNode.classList.remove("validInput");
}

function showAuftragsverlauf() {
    var container = document.createElement("div");
}

function archvieren() {
    var arch = new AjaxCall(`getReason=archivieren&auftrag=${globalData.auftragsId}`);
    arch.makeAjaxCall(function () {
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

/* product section */

function chooseProduct(productId) {
    var amount = document.getElementById(productId + "_getAmount").value;
    var isFree = getOhneBerechnung() ? 1 : 0;
    var addToInvoice = getAddToInvoice() ? 1 : 0;
    var add = new AjaxCall(`getReason=insertProduct&product=${productId}&amount=${amount}&auftrag=${globalData.auftragsId}&ohneBerechnung=${isFree}&addToInvoice=${addToInvoice}`, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        console.log(response);
        reloadPostenListe();
    });
}

/* edit title */
function editTitle() {
    var text = document.getElementById("orderTitle");
    
    var saveDescription = new AjaxCall(`getReason=saveTitle&text=${text.value}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
    saveDescription.makeAjaxCall(function (response) {
        if (response == "saved") {
            infoSaveSuccessfull("success");
        } else
            infoSaveSuccessfull();
    });
}

/* edit description */
function editDescription() {
    var text = document.getElementById("orderDescription");
    
    var saveDescription = new AjaxCall(`getReason=saveDescription&text=${text.value}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
    saveDescription.makeAjaxCall(function (response) {
        if (response == "saved") {
            infoSaveSuccessfull("success");
        } else
            infoSaveSuccessfull();
    });
}

/**
 * changes the order type
 * TODO: alles auf neues ajax module umstellen
 * TODO: alten code vereinfachen
 * TODO: sql injectsion preventen
 */
function editOrderType() {
    const select = document.getElementById("orderType");
    const value = select.value;

    const sendToServer = new AjaxCall(`getReason=saveOrderType&type=${value}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
    sendToServer.makeAjaxCall(function (response) {
        if (response == "saved") {
            infoSaveSuccessfull("success");
        } else
            infoSaveSuccessfull();
    });
}

/* shows auftragsblatt, from: https://stackoverflow.com/questions/19851782/how-to-open-a-url-in-a-new-tab-using-javascript-or-jquery */
function showPreview() {
    let link = document.getElementById("home_link").href + "pdf?type=auftrag&id=" + globalData.auftragsId;
    var win = window.open(link, '_blank');
    if (win) {
       win.focus();
    }
}

function updateDate(e) {
    const date = e.target.value;
    sendDate(1, date);
}

function updateDeadline(e) {
    const date = e.target.value;
    sendDate(2, date);
}

function setDeadlineState(e) {
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
    }).then(r => {

    });
}

/* from https://www.w3schools.com/howto/tryit.asp?filename=tryhow_js_tabs and modified */
function openTab(evt, id) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" activetab", "");
    }
    document.getElementsByClassName("tabcontent")[id].style.display = "block";
    evt.currentTarget.className += " activetab";
}

function showPostenAdd() {
    document.getElementById("showPostenAdd").style.display = "";
}

/* performAction section of the table */
function performAction(key, event) {
    /* centered upload div */
    var div = document.createElement("div");
    var form = document.createElement("form");
    var input = document.createElement("input");

    input.type = "file";
    input.name = "uploadedFile";
    input.setAttribute("form", "uploadFilesPosten");
    form.classList.add("fileUploader");
    form.name = "postenAttachment";
    form.dataset.target = "postenAttachment";
    form.id = "uploadFilesPosten";

    form.appendChild(input);
    div.appendChild(form);

    /* hidden key input form */
    let hidden = document.createElement("input");
    hidden.name = "key";
    hidden.hidden = true;
    hidden.type = "text";
    hidden.value = key;
    form.appendChild(hidden);

    /* hidden key input form */
    let tableKey = document.createElement("input");
    tableKey.name = "tableKey";
    tableKey.hidden = true;
    tableKey.type = "text";
    tableKey.value = event.target.parentNode.parentNode.parentNode.parentNode.dataset.key;
	form.appendChild(tableKey);

    document.body.appendChild(div);
    addActionButtonForDiv(div, "remove");

    /* add new file uploader */
    fileUploaders.push(new FileUploader(form));
    centerAbsoluteElement(div);
}

/*
 * adds files to vehicles
 */
function addFileVehicle(key, event) {
    var form = document.getElementById("fileVehicle");
    form.style.display = "";

    /* hidden key input form */
    let hidden = document.createElement("input");
    hidden.name = "key";
    hidden.hidden = true;
    hidden.type = "text";
    hidden.value = key;
    form.appendChild(hidden);

    /* hidden key input form */
    let tableKey = document.createElement("input");
    tableKey.name = "tableKey";
    tableKey.hidden = true;
    tableKey.type = "text";
    tableKey.value = event.target.parentNode.parentNode.parentNode.parentNode.dataset.key;
    form.appendChild(tableKey);
}

/*
 * asks if an order should be set to be finished
 */
function setOrderFinished() {
    if (confirm('Möchtest Du den Auftrag als "Erledigt" markieren?')) {
        /* Erledigt */
        var send = new AjaxCall(`getReason=setOrderFinished&auftrag=${globalData.auftragsId}`);
        send.makeAjaxCall(function () {});
        document.getElementById("home_link").click();
    } else {
        /* Abbruch */
    }
}

function addNewNote() {
    document.getElementById("addNotes").style.display='block';
    const textarea = document.querySelector(".noteInput");
    textarea.focus();
}
