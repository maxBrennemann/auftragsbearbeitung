var changedData = {};
var isOnEdit = true;
var search = document.getElementById("performSearch");

var addressSet = [];
var addressCount = 0;
var addrCount = document.getElementById("addrCount");

if (search != null) {
    search.addEventListener("keyup", function (event) {
        if (event.key === "Enter") {
            location.href = event.target.dataset.url + "?mode=search&query=" + event.target.value;
        }
    });
}

function initialize() {
    var inputs = document.getElementById("showKundendaten").getElementsByTagName("input");
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

            changedData[column] = e.target.value;
        }, false);
    }

    var pseudo = document.getElementById("pseudo");
    pseudo.addEventListener("click", function(event) {
        var mouseX = event.clientX;
        var width = window.innerWidth;
        
        if (mouseX < width / 2) {
            if (addressCount > 0)
                addressCount--;
            console.log("left");
        } else {
            if (addressCount < addressSet.length - 1)
                addressCount++;
            console.log("right");
        }

        document.getElementById("strasse").value = addressSet[addressCount].strasse;
        document.getElementById("hausnr").value = addressSet[addressCount].hausnr;
        document.getElementById("plz").value = addressSet[addressCount].plz;
        document.getElementById("ort").value = addressSet[addressCount].ort;

        addrCount.innerHTML = (addressCount + 1) + "/" + addressSet.length;
    }, false);

    var kdnr = document.getElementById("kdnr").value;
    getAddresses = new AjaxCall(`getReason=getAddresses&kdnr=${kdnr}`, "POST", window.location.href);
    getAddresses.makeAjaxCall(function (response) {
        addressSet = JSON.parse(response);
        addrCount.innerHTML = (addressCount + 1) + "/" + addressSet.length;
    });
}

function kundendatenAbsenden() {
    var kdnr = document.getElementById("kdnr").value;
    var data = `getReason=setData&type=kunde&kdnr=${kdnr}&addressCount=${addressCount}&`;
    var count = 0;

    for (var key in changedData) {
        if (changedData.hasOwnProperty(key)) {
            data += key + "=" + changedData[key] + "&" + "dataKey" + count + "=" + key + "&";
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
    var addrCount = document.getElementById("addrCount");
    if (e.target.dataset.show == "more") {
        e.target.dataset.show = "less";
        e.target.innerHTML = "Weniger";
        website.style.display = "";
        pseudo.style.display = "";
        addrCount.style.display = "";
        
        divs[3].classList.add("background");
        divs[4].classList.add("background");
        pseudo.classList.add("pseudo");
    } else {
        e.target.dataset.show = "more";
        e.target.innerHTML = "Mehr";
        website.style.display = "none";
        pseudo.style.display = "none";
        addrCount.style.display = "none";

        divs[3].classList.remove("background");
        divs[4].classList.remove("background");
        pseudo.classList.remove("pseudo");
    }
}

function addDataToDB() {
    var tableCont = document.getElementsByClassName("ansprTableCont");
    var nextId = document.getElementById("kundennummer").innerHTML;
    var data = `getReason=insertAnspr&nextId=${nextId}&`;

    for (let i = 0; i < tableCont.length; i++) {
        data += tableCont[i].dataset.col + "=" + tableCont[i].innerHTML;
        i != tableCont.length - 1 ? data += "&" : 1;
    }

    let sendToDB = new AjaxCall(data, "POST", window.location.href);
    sendToDB.makeAjaxCall(function (res) {
        document.getElementById("resetAnsprechpartnerTable").innerHTML = res;
    });
}

function showAddAnsprechpartner() {
    document.getElementById("addAnsprechpartner").style.display = "inline-block";
    document.getElementById("addAnsprechpartnerBtn").style.display = "inline";
    document.getElementById("showAddAnsprechpartner").style.display = "none";
}

function getServerMessage() {
    let getServerMsg = new AjaxCall(`getReason=getServerMsg`, "POST", window.location.href);
    getServerMsg.makeAjaxCall(function (res) {
        console.log(res);
    });
}

function editText(event) {
    if (isOnEdit) {
        var editText = document.getElementById("editNotes");
        editText.contentEditable = true;
        editText.style.backgroundColor = "#FFFFFF";
        editText.style.borderRadius = "6px";
        editText.style.padding = "5px";
        event.target.innerHTML = "Absenden";
        isOnEdit = false;
    } else {
        var kundennummer = document.getElementById("kdnr").value;
        var notes = document.getElementById("editNotes").innerHTML;
        let sendNotes = new AjaxCall(`getReason=setNotes&kdnr=${kundennummer}&notes=${notes}`, "POST", window.location.href);
        sendNotes.makeAjaxCall(function (res) {
            console.log(res);
            if (res == "ok")
                infoSaveSuccessfull("success");
            else
                infoSaveSuccessfull();
        });
    }
}

initialize();

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
        addressSet = JSON.parse(response);
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
