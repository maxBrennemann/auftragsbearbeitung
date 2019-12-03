var changedData = {};
var search = document.getElementById("performSearch");

search.addEventListener("keyup", function (event) {
    if (event.key === "Enter") {
        location.href = event.target.dataset.url + "?mode=search&query=" + event.target.value;
    }
});

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

initialize();