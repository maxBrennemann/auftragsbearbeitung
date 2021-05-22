
/* global variables */
var globalData = {
    aufschlag: 0,
    vehicleId: 0,
    erledigendeSchritte : null,
    alleSchritte : null,
    auftragsId : parseInt(new URL(window.location.href).searchParams.get("id"))
}

if (document.readyState !== 'loading' ) {
    console.log( 'document is already ready, just execute code here' );
    initCode();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        console.log( 'document was not ready, place code here' );
        initCode();
    });
}

function initCode() {
    if (document.getElementById("selectVehicle") == null)
        return null;
    
    document.getElementById("selectVehicle").addEventListener("change", function(event) {
        if (event.target.value == "addNew") {
            document.getElementById("addVehicle").style.display = "inline-block";
        }
    });
}

/* get selection for adding a posten */
function getSelections() {
    var e = document.getElementById("selectPosten");
    var strUser = e.options[e.selectedIndex].value;

    if (strUser == "zeit") {
        document.getElementById("addPostenZeit").style.display = "block";
        document.getElementById("addPostenLeistung").style.display = "none";
        document.getElementById("addPostenProdukt").style.display = "none";
    } else if (strUser == "leistung") {
        document.getElementById("addPostenLeistung").style.display = "flex";
        document.getElementById("addPostenZeit").style.display = "none";
        document.getElementById("addPostenProdukt").style.display = "none";

        document.getElementById("ekp").addEventListener("input", function () {
            var startCalc = parseInt(document.getElementById("ekp").value);
            var price = startCalc * (1 + (globalData.aufschlag / 100));
            document.getElementById("pre").value = price;
        }, false);
    } else if (strUser == "produkt") {
        /*var showProducts = new AjaxCall(`getReason=createTable&type=product_compact&sendTo=`, "POST", window.location.href);
        showProducts.makeAjaxCall(function (responseTable) {
            document.getElementById("addPosten").innerHTML = responseTable;

            addableTables("product_compact");
        });*/
        document.getElementById("addPostenLeistung").style.display = "none";
        document.getElementById("addPostenZeit").style.display = "none";
        document.getElementById("addPostenProdukt").style.display = "block";
    }

    document.getElementById("showOhneBerechnung").style.display = "inline";
}

function addTime() {
    var time = document.getElementById("time").value;
    var wage = document.getElementById("wage").value;
    var descr = document.getElementById("descr").value;
    var isFree = getOhneBerechnung() ? 1 : 0;

    var add = new AjaxCall(`getReason=insTime&time=${time}&wage=${wage}&descr=${descr}&auftrag=${globalData.auftragsId}&ohneBerechnung=${isFree}`, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        updatePrice(response);
        reloadPostenListe();
    });
}

function addLeistung() {
    var e = document.getElementById("selectLeistung");
    var lei = e.options[e.selectedIndex].value;
    var bes = document.getElementById("bes").value;
    var ekp = document.getElementById("ekp").value;
    var pre = document.getElementById("pre").value;
    var isFree = getOhneBerechnung() ? 1 : 0;

    var add = new AjaxCall(`getReason=insertLeistung&lei=${lei}&bes=${bes}&ekp=${ekp}&pre=${pre}&auftrag=${globalData.auftragsId}&ohneBerechnung=${isFree}`, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        updatePrice(response);
        reloadPostenListe();
    });
}

function addProductCompact() {
    var menge = document.getElementById("posten_produkt_menge").value;
    var marke = document.getElementById("posten_produkt_marke").value;
    var ekpreis = document.getElementById("posten_produkt_ek").value;
    var vkpreis = document.getElementById("posten_produkt_vk").value;
    var name = document.getElementById("posten_produkt_name").value;
    var beschreibung = document.getElementById("posten_produkt_besch").value;
    var isFree = getOhneBerechnung() ? 1 : 0;

    var add = new AjaxCall(`getReason=insertProductCompact&auftrag=${globalData.auftragsId}&menge=${menge}&marke=${marke}&ekpreis=${ekpreis}&vkpreis=${vkpreis}&name=${name}&beschreibung=${beschreibung}&ohneBerechnung=${isFree}`, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        console.log(response);
        reloadPostenListe();
    });
}

function reloadPostenListe() {
    var reload = new AjaxCall(`getReason=reloadPostenListe&id=${globalData.auftragsId}`, "POST", window.location.href);
    reload.makeAjaxCall(function (response) {
        document.getElementById("auftragsPostenTable").innerHTML = response;
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

function showSelection(element) {
    document.getElementById('newPosten').style.display = 'inline';
    element.style.display = 'none';

    var getGeneralPosten = new AjaxCall("getReason=createTable&type=custom", "POST", window.location.href);
    getGeneralPosten.makeAjaxCall(function (responseTable) {
        document.getElementById("generalPosten").innerHTML = responseTable;
    });
}

/* addes bearbeitungsschritte */
function addBearbeitungsschritte() {
    var bearbeitungsschritte = document.getElementById("bearbeitungsschritte");
    bearbeitungsschritte.style.display = "block";

    if (document.getElementById("sendStepToServer") == null) {
        var btn = document.createElement("button");
        btn.id = "sendStepToServer";
        btn.innerHTML = "Hinzufügen";
        btn.addEventListener("click", function () {
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
        }, false);

        bearbeitungsschritte.appendChild(btn);
    }
}

/* adds a note to the order */
function addNote() {
    var note = document.querySelector(".noteInput");
    if (note == undefined)
        return null;

    note = note.value;   

    /* ajax parameter */
    let params = {
        getReason: "addNoteOrder",
        auftrag: globalData.auftragsId,
        note: note
    };

    var add = new AjaxCall(params, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        document.getElementById("noteContainer").innerHTML = response;
    }.bind(this), false);
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
            let el = document.getElementById("addVehicle").nextSibling;
            el.innerHTML = response;
        });
    }
}

function selectVehicle(event) {
    globalData.vehicleId = event.target.value;
}

function deleteRow(key) {
    var del = new AjaxCall(`getReason=delete&key=${key}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
    del.makeAjaxCall(function (response) {
        console.log(response);
    });
}

function updateIsDone(key) {
    var update = new AjaxCall(`getReason=update&key=${key}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
    update.makeAjaxCall(function (response) {
        console.log(response);
    });
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
    var div = document.getElementById("farbe");
    div.style.display = "block";
    addActionButtonForDiv(div, "hide");
    centerAbsoluteElement(div);
}

function showAuftrag() {
    var url = window.location.href;
    url += "&show=t";
    window.location.href = url;
}

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
    
    var sendC = new AjaxCall(`getReason=newColor&auftrag=${globalData.auftragsId}&farbname=${data[0]}&farbe=${data[1]}&bezeichnung=${data[2]}&hersteller=${data[3]}`);
    sendC.makeAjaxCall(function (colorHTML) {
        var showColors = document.getElementById("showColors");
        var farben = document.getElementById("farbe");
        var data = JSON.parse(colorHTML);
        showColors.innerHTML = data.farben;
        farben.innerHTML = data.addFarben;
    });
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

function removeColor(colorId) {
    console.log("removing color : " + colorId);

    var arch = new AjaxCall(`getReason=removeColor&auftrag=${globalData.auftragsId}&colorId=${colorId}`);
    arch.makeAjaxCall(function (colorHTML) {
        var showColors = document.getElementById("showColors");
        //var data = JSON.parse(colorHTML);
        showColors.innerHTML = colorHTML; //data.farben;
    });
}

/* product section */

function chooseProduct(productId) {
    var amount = document.getElementById(productId + "_getAmount").value;
    var isFree = getOhneBerechnung() ? 1 : 0;
    var add = new AjaxCall(`getReason=insertProduct&product=${productId}&amount=${amount}&auftrag=${globalData.auftragsId}&ohneBerechnung=${isFree}`, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        console.log(response);
        reloadPostenListe();
    });
}

/* edit description */
function editDescription(event) {
    var text = document.getElementById("orderDescription");
    text.contentEditable = true;
    text.classList.add("descriptionEditable");
    
    if (event.target.innerText == "Speichern") {
        var saveDescription = new AjaxCall(`getReason=saveDescription&text=${text.innerText}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
        saveDescription.makeAjaxCall(function (response) {
            if (response == "saved") {
                var text = document.getElementById("orderDescription");
                text.classList.remove("descriptionEditable");
                text.contentEditable = false;

                var btn = text.nextElementSibling;
                btn.innerText = "Bearbeiten";
            } else
                console.log("not saved");
        });
    } else {
        event.target.innerText = "Speichern";
    }
}

/* shows auftragsblatt, from: https://stackoverflow.com/questions/19851782/how-to-open-a-url-in-a-new-tab-using-javascript-or-jquery */
function showPreview() {
    let link = document.getElementById("home_link").href + "pdf?type=auftrag&id=" + globalData.auftragsId;
    var win = window.open(link, '_blank');
    if (win) {
       win.focus();
    }
}


