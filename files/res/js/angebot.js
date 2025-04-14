import { initBindings } from "./classes/bindings.js";
import { ajax } from "./classes/ajax.js";

const functionNames = {};

const init = () => {
    initBindings(functionNames);
}

var globalData = {
    aufschlag: 0,
    vehicleId: 0
}

functionNames.click_newOffer = () => {
    const customerId = document.getElementById("kdnr").value;
    ajax.get(`/api/v1/order-items/offer/template/${customerId}`).then(r => {
        const url = new URL(window.location.href);
        url.searchParams.set("kdnr", customerId);
        window.history.pushState({}, '', url);

        document.getElementById("insTemp").innerHTML = r.content;
        document.getElementById("listOpenOffers").classList.add("hidden");
        document.getElementById("newOffer").classList.add("hidden");

        loadItems(r.offerId);
    });
}

const loadItems = async (offerId) => {
    const items = await ajax.get(`/api/v1/order-items/offer/${offerId}/all`).then(r => {});
}

function getSelections() {
    var e = document.getElementById("selectPosten");
    var strUser = e.options[e.selectedIndex].value;

    if (strUser == "zeit") {
        document.getElementById("addPostenZeit").style.display = "inline";
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
        document.getElementById("addPostenLeistung").style.display = "none";
        document.getElementById("addPostenZeit").style.display = "none";

        var showProducts = new AjaxCall(`getReason=createTable&type=produkt`, "POST", window.location.href);
        showProducts.makeAjaxCall(function (responseTable) {
            document.getElementById("addPostenProdukt").innerHTML = responseTable;
            document.getElementById("addPostenProdukt").style.display = "inline";
        });
    }
}

function showSelection(element) {
    document.getElementById('newPosten').style.display = 'inline';
    element.style.display = 'none';

    var getGeneralPosten = new AjaxCall("getReason=createTable&type=custom", "POST", window.location.href);
    getGeneralPosten.makeAjaxCall(function (responseTable) {
        document.getElementById("generalPosten").innerHTML = responseTable;
    });
}

function getOhneBerechnung() {
    return document.getElementById("ohneBerechnung").checked;
}

/* Zeit */

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
        console.log(response);
    });
}

/* Leistung */

function selectLeistung() {
    var e = document.getElementById("selectLeistung");
    globalData.aufschlag = parseInt(e.options[e.selectedIndex].dataset.aufschlag);
}

function addLeistung() {
    var e = document.getElementById("selectLeistung");
    var lei = e.options[e.selectedIndex].innerHTML;
    var leiNr = e.options[e.selectedIndex].value;
    var bes = document.getElementById("bes").value;
    var ekp = document.getElementById("ekp").value;
    var pre = document.getElementById("pre").value;
    var anz = document.getElementById("anz").value;
    var meh = document.getElementById("meh").value;
    var isFree = getOhneBerechnung() ? 1 : 0;
    var customerId = document.getElementById("kdnr").value;

    var div = document.createElement("div");
    var text = `Leistung: ${lei}, Preis ${pre}€, EK Preis ${ekp} für: ${bes}`;
    if (isFree) {
        text += ", wird nicht berechnet";
    }

    div.innerText = text;
    document.getElementById("allePosten").appendChild(div);

    var addLeistungOffer = new AjaxCall(`getReason=addLeistungOffer&customerId=${customerId}&lei=${leiNr}&bes=${bes}&ekp=${ekp}&pre=${pre}&isFree=${isFree}&qty=${anz}&meh=${meh}`);
    addLeistungOffer.makeAjaxCall(function (response) {
        reloadIFrame();
        console.log(response);
    });
}

/* Verschiedenes */

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
    storeOffer.makeAjaxCall(function (response, args) {
        window.location.href = (document.getElementById("home_link").href) + "neuer-auftrag?kdnr=" + args[0];
    }, customerId);
}

if (document.readyState !== 'loading' ) {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
