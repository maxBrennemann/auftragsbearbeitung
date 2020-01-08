var globalData = {
    aufschlag: 0
}

function getSelections() {
    var e = document.getElementById("selectPosten");
    var strUser = e.options[e.selectedIndex].value;

    if (strUser == "zeit") {
        document.getElementById("addPostenZeit").style.display = "inline";
        document.getElementById("addPostenLeistung").style.display = "none";
    } else if (strUser == "leistung") {
        document.getElementById("addPostenLeistung").style.display = "inline";
        document.getElementById("addPostenZeit").style.display = "none";

        document.getElementById("ekp").addEventListener("input", function () {
            var startCalc = parseInt(document.getElementById("ekp").value);
            var price = startCalc * (1 + (globalData.aufschlag / 100));
            document.getElementById("pre").value = price;
        }, false);
    } else if (strUser == "produkt") {
        var showProducts = new AjaxCall(`getReason=createTable&type=produkt`, "POST", window.location.href);
        showProducts.makeAjaxCall(function (responseTable) {
            document.getElementById("addPosten").innerHTML = responseTable;

            addableTables();
        });
    }

    document.getElementById("showOhneBerechnung").style.display = "inline";
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

function addBearbeitungsschritte() {
    document.getElementById("bearbeitungsschritte").style.display = "inline";

    var btn = document.createElement("button");
    btn.innerHTML = "Hinzufügen";
    btn.addEventListener("click", function () {
        var tableData = document.getElementsByClassName("steps");
        var steps = [];
        for (var i = 0; i < tableData.length; i++) {
            steps.push(tableData[i].innerHTML);
        }
        var isFree = getOhneBerechnung() ? 1 : 0;
        var auftrag = new URL(window.location.href).searchParams.get("id");
        var add = new AjaxCall(`getReason=insertStep&bez=${steps[0]}&prio=${steps[1]}&auftrag=${auftrag}&ohneBerechnung=${isFree}`, "POST", window.location.href);
        add.makeAjaxCall(function (response) {
            console.log(response);
            location.reload();
        });
    }, false);

    document.getElementById("bearbeitungsschritte").appendChild(btn);
}

function performSearch(e) {
    var query = e.target.previousSibling.value;
    console.log(query);
    var search = new AjaxCall(`getReason=search&query=${query}&stype=produkt`, "POST", window.location.href);
    search.makeAjaxCall(function (responseTable) {
        document.getElementById("searchResults").innerHTML = responseTable;
        addableTables();
    });
}

function addTime() {
    var time = document.getElementById("time").value;
    var wage = document.getElementById("wage").value;
    var descr = document.getElementById("descr").value;
    var isFree = getOhneBerechnung() ? 1 : 0;
    var auftrag = new URL(window.location.href).searchParams.get("id");
    var add = new AjaxCall(`getReason=insTime&time=${time}&wage=${wage}&descr=${descr}&auftrag=${auftrag}&ohneBerechnung=${isFree}`, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        console.log(response);
        location.reload();
    });
}

function addLeistung() {
    var e = document.getElementById("selectLeistung");
    var lei = e.options[e.selectedIndex].value;
    var bes = document.getElementById("bes").value;
    var ekp = document.getElementById("ekp").value;
    var pre = document.getElementById("pre").value;
    var isFree = getOhneBerechnung() ? 1 : 0;
    var auftrag = new URL(window.location.href).searchParams.get("id");
    var add = new AjaxCall(`getReason=insertLeistung&lei=${lei}&bes=${bes}&ekp=${ekp}&pre=${pre}&auftrag=${auftrag}&ohneBerechnung=${isFree}`, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        console.log(response);
    });
}

function addFahrzeug() {
    var kfz = document.getElementById("kfz").value;
    var fahrzeug = document.getElementById("fahrzeug").value;
    var kundennummer = document.getElementById("kundennummer");
    var add = new AjaxCall(`getReason=insertCar&kfz=${kfz}&fahrzeug=${fahrzeug}&kdnr=${kundennummer}`, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        console.log(response);
    });
}

function deleteRow() {
    var add = new AjaxCall(`getReason=test`, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        console.log(response);
    });
}

function radio(val) {
    console.log(val);
    var stepTable = document.getElementById("stepTable");
    var auftrag = new URL(window.location.href).searchParams.get("id");
    var params = "";
    if (val == "show") {
        params = `getReason=getAllSteps&auftrag=${auftrag}`;
    } else if (val == "hide") {
        params = `getReason=getOpenSteps&auftrag=${auftrag}`;
    }
    
    var add = new AjaxCall(params, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        stepTable.innerHTML = response;
    });
}

function updateIsDone(input) {
    console.log(input);
    var auftrag = new URL(window.location.href).searchParams.get("id");
    var update = new AjaxCall(`getReason=setTo&auftrag=${auftrag}&row=${input}`, "POST", window.location.href);
    update.makeAjaxCall(function (response) {
        console.log(response);
    });
}

function selectLeistung(e) {
    if (e.target.value == 5) {
        document.getElementById("addKfz").style.display = "inline";
    }

    globalData.aufschlag = parseInt(e.target.options[e.target.selectedIndex].dataset.aufschlag);
}

function addColor() {
    document.getElementById("farbe").style.display = "inline";
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
    var data = [];
    var auftrag = new URL(window.location.href).searchParams.get("id");

    for (let i = 0; i < elements.length; i++) {
        data.push(elements[i].value);
    }
    
    var sendC = new AjaxCall(`getReason=newColor&auftrag=${auftrag}&farbname=${data[0]}&farbe=${data[1]}&bezeichnung=${data[2]}&hersteller=${data[3]}`);
    sendC.makeAjaxCall(function (colorHTML) {
        var showColors = document.getElementById("showColors");
        var farben = document.getElementById("farbe");
        var data = JSON.parse(colorHTML);
        showColors.innerHTML = data.farben;
        farben.innerHTML = data.addFarben;
    });
}
