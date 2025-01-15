import { addRow, createHeader, createTable } from "./classes/table_new.js";
import { tableConfig } from "./js/tableconfig.js";

const globalProperties = {
    changedData: {},
    search: null,
    addressSet: [],
    addressCount: 0,
    addrCount: null,
};

const customerData = {
    id: document.getElementById("kdnr")?.value ?? 0,
};

function initialize() {
    if (customerData.id == 0) {
        return;
    }

    contactPersonTable();

    var showKundendaten = document.getElementById("showKundendaten");
    if (showKundendaten == null) return;
    var inputs = showKundendaten.getElementsByTagName("input");

    for (var i = 0; i < inputs.length; i++) {
        inputs[i].addEventListener("input", function (e) {
            document.getElementById("sendKundendaten").disabled = false;
            var column = e.target.id;
            switch(column) {
                case "vorname":
                    column = "Vorname";
                    break;
                case "nachname":
                    column = "Nachname";
                    break;
                case "firmenname":
                    column = "Firmenname";
                    break;
                case "email":
                    column = "Email";
                    break;
                case "festnetz":
                    column = "TelefonFestnetz";
                    break;
                case "mobil":
                    column = "TelefonMobil";
                    break;
                case "website":
                    column = "Website";
                    break;
            }

            globalProperties.changedData[column] = e.target.value;
        }, false);
    }

    var pseudo = document.getElementById("pseudo");
    pseudo.addEventListener("click", function(event) {
        var mouseX = event.clientX;
        var width = window.innerWidth;
        
        if (mouseX < width / 2) {
            if (globalProperties.addressCount > 0)
                globalProperties.addressCount--;
            console.log("left");
        } else {
            if (globalProperties.addressCount < globalProperties.addressSet.length - 1)
                globalProperties.addressCount++;
            console.log("right");
        }

        document.getElementById("strasse").value = globalProperties.addressSet[globalProperties.addressCount].strasse;
        document.getElementById("hausnr").value = globalProperties.addressSet[globalProperties.addressCount].hausnr;
        document.getElementById("plz").value = globalProperties.addressSet[globalProperties.addressCount].plz;
        document.getElementById("ort").value = globalProperties.addressSet[globalProperties.addressCount].ort;

        globalProperties.addrCount.innerHTML = (globalProperties.addressCount + 1) + "/" + globalProperties.addressSet.length;
    }, false);

    var kdnr = document.getElementById("kdnr").value;
    return;
    getAddresses = new AjaxCall(`getReason=getAddresses&kdnr=${kdnr}`, "POST", window.location.href);
    getAddresses.makeAjaxCall(function (response) {
        globalProperties.addressSet = JSON.parse(response);
        globalProperties.addrCount.innerHTML = (globalProperties.addressCount + 1) + "/" + globalProperties.addressSet.length;
    });
}

function kundendatenAbsenden() {
    var kdnr = document.getElementById("kdnr").value;
    var data = `getReason=setData&type=kunde&kdnr=${kdnr}&addressCount=${globalProperties.addressCount}&`;
    var count = 0;

    for (var key in globalProperties.changedData) {
        if (globalProperties.changedData.hasOwnProperty(key)) {
            data += key + "=" + globalProperties.changedData[key] + "&" + "dataKey" + count + "=" + key + "&";
            count++;
        }
    }

    data += "number=" + count;

    insertKundendaten = new AjaxCall(data, "POST", window.location.href);
    insertKundendaten.makeAjaxCall(function (response) {
        console.log(response);
        if (response == "ok")
            infoSaveSuccessfull("success");
        else
            infoSaveSuccessfull();
        
        /* reset object, so that values are not sended twice */
        globalProperties.changedData = {};
    });
}

/*
 * klappt mehr Optionen für die Kundendaten aus,
 * außerdem kann man hier die verschiedenen Adressen durchgehen
 */
function showMore(e) {
    var website = document.getElementById("websiteCont");
    var divs = document.getElementById("showKundendaten").getElementsByClassName("row");
    var pseudo = document.getElementById("pseudo");
    if (e.target.dataset.show == "more") {
        e.target.dataset.show = "less";
        e.target.innerHTML = "Weniger";
        website.style.display = "";
        pseudo.style.display = "";
        globalProperties.addrCount.style.display = "";
        
        divs[3].classList.add("background");
        divs[4].classList.add("background");
        pseudo.classList.add("pseudo");
    } else {
        e.target.dataset.show = "more";
        e.target.innerHTML = "Mehr";
        website.style.display = "none";
        pseudo.style.display = "none";
        globalProperties.addrCount.style.display = "none";

        divs[3].classList.remove("background");
        divs[4].classList.remove("background");
        pseudo.classList.remove("pseudo");
    }
}

function getServerMessage() {
    let getServerMsg = new AjaxCall(`getReason=getServerMsg`, "POST", window.location.href);
    getServerMsg.makeAjaxCall(function (res) {
        console.log(res);
    });
}

/* functions for addresses */
function showAddressForm() {
    let div = document.getElementById("addressForm");
    div.style.display = "inline";
    addActionButtonForDiv(div, "hide");
    centerAbsoluteElement(div);
}

function sendAddressForm() {
    /* ajax parameter */
    let params = {
        getReason: "sendNewAddress",
        customer: document.getElementById("kdnr").value,
        plz: document.getElementById("newPlz").value,
        ort: document.getElementById("newOrt").value,
        strasse: document.getElementById("newStrasse").value,
        hnr: document.getElementById("newHausnr").value,
        zusatz: document.getElementById("newZusatz").value,
        land: document.getElementById("newCountry").value
    };

    var add = new AjaxCall(params, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        globalProperties.addressSet = JSON.parse(response);
        infoSaveSuccessfull("success");
    });
}

function editRow(key, pointer) {
    var row = pointer.parentNode.parentNode;

    if (pointer.dataset.editable == "true") {
        pointer.innerHTML = "✎";
        pointer.dataset.editable = "false";

        var data = {};
        for (var i = 0; i < row.children.length - 1; i++) {
            row.children[i].contentEditable = "false";
            data[i] = row.children[i].innerHTML;
        }

        var tKey = pointer.parentNode.parentNode.parentNode.parentNode.dataset.key;
        if (tKey == null || tKey == 0)
            return;

        data = JSON.stringify(data);
        var edit = new AjaxCall(`getReason=editAnspr&key=${key}&name=${tKey}&data=${data}`, "POST", window.location.href);
        edit.makeAjaxCall(function (response) {
            if (response == "ok")
                infoSaveSuccessfull("success");
            else {
                alert(response);
                infoSaveSuccessfull();
            }
        });
    } else {
        pointer.innerHTML = "✔";
        pointer.dataset.editable = "true";
        for (var i = 0; i < row.children.length - 1; i++) {
            row.children[i].contentEditable = "true";
        }
    }
}

function deleteRow(key, type, pointer) {
    var tKey = pointer.parentNode.parentNode.parentNode.parentNode.dataset.key;
    if (tKey == null || tKey == 0)
        return;
    if (confirm('Möchtest Du den Ansprechpartner wirklich löschen?')) {
        /* Erledigt */
        var send = new AjaxCall(`getReason=table&key=${key}&name=${tKey}&action=delete`);
        send.makeAjaxCall(function () {});
        document.getElementById("home_link").click();
    } else {
        /* Abbruch */
    }
}

function initCustomer() {
    const notesTextarea = document.getElementById('notesTextarea');

    if (notesTextarea == null) return;

    notesTextarea.addEventListener('input', function () {
        notesTextarea.style.height = 'auto';
        notesTextarea.style.height = notesTextarea.scrollHeight + 'px';

        const btn = document.getElementById('btnSendNotes');
        btn.disabled = false;
    });

    const btn = document.getElementById('btnSendNotes');
    btn.addEventListener('click', function () {
        const kundennummer = document.getElementById("kdnr").value;
        const notes = document.getElementById("notesTextarea").value;

        ajax.post({
            r: 'setNotes',
            kdnr: kundennummer,
            notes: notes
        });
    });

    globalProperties.addrCount = document.getElementById("addrCount");
    globalProperties.search = document.getElementById("performSearch");
    if (globalProperties.search != null) {
        globalProperties.search.addEventListener("keyup", function (event) {
            if (event.key === "Enter") {
                location.href = event.target.dataset.url + "?mode=search&query=" + event.target.value;
            }
        });
    }
}

/**
 * changes the archive state to false
 * 
 * @param {int} id 
 */
function rearchive(id) {
    ajax.post({
        r: 'rearchive',
        auftrag: id
    }).then(() => {
        location.reload();
    });
}

const contactPersonTable = async () => {
    const table = createTable("contactPersonTable");
    const config = tableConfig["ansprechpartner"];
    createHeader(config.columns, table);

    const conditions = JSON.stringify({
        "Kundennummer": customerData.id,
    });
    const data = await ajax.get(`/api/v1/tables/ansprechpartner`, {
        "conditions": conditions,
    });

    data.forEach(row => {
        addRow(row, table, {
            "hide": ["Nummer", "Kundennummer"],
        });
    });

    table.addEventListener("rowDelete", (event) => {
        const data = event.detail;
        const id = data.Nummer;

        const conditions = JSON.stringify({
            "Nummer": id,
        });
        ajax.delete(`/api/v1/tables/ansprechpartner`, {
            "conditions": conditions,
            "customerId": customerData.id,
        });
    });
}

if (document.readyState !== 'loading' ) {
    initCustomer();
    initialize();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initCustomer();
        initialize();
    });
}
