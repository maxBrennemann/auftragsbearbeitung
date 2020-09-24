
/* global variables */
var globalData = {
    aufschlag: 0,
    vehicleId: 0,
    erledigendeSchritte : null,
    alleSchritte : null,
    auftragsId : parseInt(new URL(window.location.href).searchParams.get("id"))
}

/* get selection for adding a posten */
function getSelections() {
    var e = document.getElementById("selectPosten");
    var strUser = e.options[e.selectedIndex].value;

    if (strUser == "zeit") {
        document.getElementById("addPostenZeit").style.display = "inline";
        document.getElementById("addPostenLeistung").style.display = "none";
    } else if (strUser == "leistung") {
        document.getElementById("addPostenLeistung").style.display = "flex";
        document.getElementById("addPostenZeit").style.display = "none";

        document.getElementById("ekp").addEventListener("input", function () {
            var startCalc = parseInt(document.getElementById("ekp").value);
            var price = startCalc * (1 + (globalData.aufschlag / 100));
            document.getElementById("pre").value = price;
        }, false);
    } else if (strUser == "produkt") {
        var showProducts = new AjaxCall(`getReason=createTable&type=product_compact`, "POST", window.location.href);
        showProducts.makeAjaxCall(function (responseTable) {
            document.getElementById("addPosten").innerHTML = responseTable;

            addableTables();
        });
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
        console.log(response);
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
            hide = hide == "hide" ? 0 : 1; // 0 = hide, 1 = show

            var add = new AjaxCall(`getReason=insertStep&bez=${steps[0]}&datum=${steps[1]}&auftrag=${globalData.auftragsId}&hide=${hide}&prio=${steps[2]}`, "POST", window.location.href);
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
            document.getElementById("fahrzeugTable").innerHTML = response;
        });
    }
}

function selectVehicle(event) {
    globalData.vehicleId = event.target.value;
}

function deleteRow(row) {
    var add = new AjaxCall(`getReason=delete&auftrag=${globalData.auftragsId}&row=${row}`, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
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

function updateIsDone(input) {
    var update = new AjaxCall(`getReason=setTo&auftrag=${globalData.auftragsId}&row=${input}`, "POST", window.location.href);
    update.makeAjaxCall(function (response) {});
}

function selectLeistung(e) {
    if (e.target.value == 5) {
        document.getElementById("addKfz").style.display = "inline";
    }

    globalData.aufschlag = parseInt(e.target.options[e.target.selectedIndex].dataset.aufschlag);
}

function addColor() {
    var div = document.getElementById("farbe");
    div.style.display = "block";
    addActionButtonForDiv(div, "hide");
    centerAbsoluteElement(div);
}

function rechnungErstellen() {
    var url = window.location.href.split('?')[0];
    url += "?create=" + document.getElementById("auftragsnummer").innerHTML;
    window.location.href = url;
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
        var data = JSON.parse(colorHTML);
        showColors.innerHTML = data.farben;
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
