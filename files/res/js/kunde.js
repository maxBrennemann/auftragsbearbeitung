var changedData = {};
var isOnEdit = true;
var search = document.getElementById("performSearch");

if (search != null) {
    search.addEventListener("keyup", function (event) {
        if (event.key === "Enter") {
            location.href = event.target.dataset.url + "?mode=search&query=" + event.target.value;
        }
    });
}

function initialize() {
    var editableElements = document.getElementsByClassName("editable");

    for (var i = 0; i < editableElements.length; i++) {
        editableElements[i].addEventListener("input", function (e) {
            document.getElementById("sendKundendaten").disabled = false;
            var column = e.target.dataset.col;
            changedData[column] = e.target.innerHTML;
        }, false);
    }
}

function kundendatenAbsenden() {
    var data = "getReason=setData&type=kunde&";

    for (var key in changedData) {
        if (changedData.hasOwnProperty(key)) {
            data += key + "=" + changedData.key + "&";
        }
    }

    data = data.slice(0, -1);

    insertKundendaten = new AjaxCall(data, "POST", window.location.href);
    insertKundendaten.makeAjaxCall();
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
        console.log(res);
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
        event.target.innerHTML = "Absenden";
        isOnEdit = false;
    } else {
        var kundennummer = document.getElementById("kundennummer").innerHTML;
        var notes = document.getElementById("editNotes").innerHTML;
        let sendNotes = new AjaxCall(`getReason=setNotes&kdnr=${kundennummer}&notes=${notes}`, "POST", window.location.href);
        sendNotes.makeAjaxCall(function (res) {
            console.log(res);
        });
    }
}

initialize();