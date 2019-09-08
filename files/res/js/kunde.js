var changedData = {};

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

initialize();