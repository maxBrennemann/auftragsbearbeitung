
function neuesAngebot() {
    var customerId = document.getElementById("kdnr").value;
    var loadHTMLTemplate = new AjaxCall(`getReason=loadTemplateOrder&customerId=${customerId}`);
    loadHTMLTemplate.makeAjaxCall(function (customerData) {
        document.getElementById("insTemp").innerHTML = customerData;
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
    
    var div = document.createElement("div");
    var text = `Zeit: ${time} min, Stundenlohn: ${wage}€ für ${descr}`;
    if (isFree) {
        text += ", wird nicht berechnet";
    }

    div.innerText = text;
    document.getElementById("allePosten").appendChild(div);
}

function addLeistung() {
    var e = document.getElementById("selectLeistung");
    var lei = e.options[e.selectedIndex].innerHTML;
    var bes = document.getElementById("bes").value;
    var ekp = document.getElementById("ekp").value;
    var pre = document.getElementById("pre").value;
    var isFree = getOhneBerechnung() ? 1 : 0;
    
    var div = document.createElement("div");
    var text = `Zeit: ${time} min, Stundenlohn: ${wage}€ für ${descr}`;
    if (isFree) {
        text += ", wird nicht berechnet";
    }

    div.innerText = text;
    document.getElementById("allePosten").appendChild(div);
}