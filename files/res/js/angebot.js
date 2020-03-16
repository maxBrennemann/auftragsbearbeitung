
function neuesAngebot() {
    var customerId = document.getElementById("kdnr").value;
    var loadHTMLTemplate = new AjaxCall(`getReason=loadTemplateOrder&customerId=${customerId}`);
    loadHTMLTemplate.makeAjaxCall(function (customerData) {
        document.getElementById("insTemp").innerHTML = customerData;
        loadCachedPosten();
    });
}

function loadCachedPosten() {
    var customerId = document.getElementById("kdnr").value;
    var loadCache = new AjaxCall(`getReason=loadCachedPosten&customerId=${customerId}`);
    loadCache.makeAjaxCall(function (data) {
        console.log(data);
        document.getElementById("allePosten").innerHTML += data;
    });
}

var globalData = {
    aufschlag: 0,
    vehicleId: 0
}

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

function addTime() {
    var time = document.getElementById("time").value;
    var wage = document.getElementById("wage").value;
    var descr = document.getElementById("descr").value;
    var isFree = getOhneBerechnung() ? 1 : 0;
    var customerId = document.getElementById("kdnr").value;
    
    var div = document.createElement("div");
    var text = `Zeit: ${time} min, Stundenlohn: ${wage}€ für ${descr}`;
    if (isFree) {
        text += ", wird nicht berechnet";
    }

    div.innerText = text;
    document.getElementById("allePosten").appendChild(div);

    var addTimeOffer = new AjaxCall(`getReason=addTimeOffer&customerId=${customerId}&time=${time}&wage=${wage}&descr=${descr}&isFree=${isFree}`);
    addTimeOffer.makeAjaxCall(function (response) {
        reloadIFrame();
    });
}

function addLeistung() {
    var e = document.getElementById("selectLeistung");
    var lei = e.options[e.selectedIndex].innerHTML;
    var leiNr = e.options[e.selectedIndex].value;
    var bes = document.getElementById("bes").value;
    var ekp = document.getElementById("ekp").value;
    var pre = document.getElementById("pre").value;
    var isFree = getOhneBerechnung() ? 1 : 0;
    var customerId = document.getElementById("kdnr").value;

    var div = document.createElement("div");
    var text = `Leistung: ${lei}, Preis ${pre}€, EK Preis ${ekp} für: ${bes}`;
    if (isFree) {
        text += ", wird nicht berechnet";
    }

    div.innerText = text;
    document.getElementById("allePosten").appendChild(div);

    var addLeistungOffer = new AjaxCall(`getReason=addLeistungOffer&customerId=${customerId}&lei=${leiNr}&bes=${bes}&ekp=${ekp}&pre=${pre}&isFree=${isFree}`);
    addLeistungOffer.makeAjaxCall(function (response) {
        reloadIFrame();
    });
}

function showOffer() {
    //showOffer
}

function reloadIFrame() {
    var iframe = document.getElementById("showOffer");
    iframe.src = iframe.src;
}

function storeOffer() {
    var customerId = document.getElementById("kdnr").value;
    var storeOffer = new AjaxCall(`getReason=storeOffer&customerId=${customerId}`);
    storeOffer.makeAjaxCall(function (response) {
       location.reload();
    });
}
