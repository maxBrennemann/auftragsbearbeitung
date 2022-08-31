var add = true;

if (document.readyState !== 'loading' ) {
    startInvoice();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        startInvoice();
    });
}

function startInvoice() {
    let elements = document.querySelectorAll(".standardtexte p");
    for (var i = 0; i < elements.length; i++) {
        elements[i].addEventListener("click", function(e) {
            e.target.classList.toggle("highlightBlue");
            if (e.target.classList.contains("highlightBlue"))
                sendText(e.target.textId, e.target.innerHTML);
            else
                removeText(e.target.textId);
        }, false);
        elements[i].textId = i;
    }

    document.getElementById("rechnungsdatum").addEventListener("change", setInvoiceDate, false);
    document.getElementById("leistungsdatum").addEventListener("change", setPerformanceDate, false);
    document.getElementById("addressId").addEventListener("change", setInvoiceParameters, false);
}

/**
 * Adds a text field to the predefinded texts and adds the correct eventlistener
 */
function addText() {
    var newText = document.getElementById("newText");
    newText.classList.toggle("visibility");
    add = !add;

    if (add == true) {
        var standardtexte = document.getElementsByClassName("standardtexte")[0];
        var p = document.createElement("p");
        p.innerHTML = newText.value;
        newText.value = "";
        standardtexte.insertBefore(p, standardtexte.lastElementChild);

        p.textId = document.getElementsByClassName("standardtexte").length;
        p.addEventListener("click", function(e) {
            e.target.classList.toggle("highlightBlue");
            if (e.target.classList.contains("highlightBlue"))
                sendText(e.target.textId, e.target.innerHTML);
            else
                removeText(e.target.textId);
        }, false);
    }
}

/**
 * This function is called from the eventlisteners added in {@link addText} and {@link startInvoice}
 * when the user clicks on a text and only
 * if it is not highlighted
 * @param {*} id the text id, which is page specific
 * @param {*} text the text which must be put on the invoice
 */
function sendText(id, text) {
    var sendTextToServer = new AjaxCall(`getReason=invoiceAddText&text=${text}&id=${id}`, "POST", window.location.href);
    sendTextToServer.makeAjaxCall(function (response) {
        var iframe = document.getElementById("showOffer");
        iframe.contentWindow.location.reload();
        console.log(response);
    });
}

/**
 * Removes a text by id, function is called from the eventlisteners from {@link addText} and {@link startInvoice}
 * @param {*} id the text id, which is page specific
 */
function removeText(id) {
    var sendTextToServer = new AjaxCall(`getReason=invoiceRemoveText&id=${id}`, "POST", window.location.href);
    sendTextToServer.makeAjaxCall(function (response) {
        var iframe = document.getElementById("showOffer");
        iframe.contentWindow.location.reload();
        console.log(response);
    });
} 

/**
 * Sends the invoice parameters to the server
 */
function setInvoiceParameters() {
    /* ajax parameter */
    let addressSelect = document.getElementById("addressId");
    let address = addressSelect.options[addressSelect.selectedIndex].value;

    let params = {
        getReason: "setInvoiceParameters",
        auftrag: document.getElementById("orderId").innerHTML,
        address: address,
        invoiceDate: document.getElementById("rechnungsdatum").value,
        leistungDate: document.getElementById("leistungsdatum").value
    };

    var sip = new AjaxCall(params, "POST", window.location.href);
    sip.makeAjaxCall(function (response) {
        if (response == "ok") {
            infoSaveSuccessfull("success");
            var iframe = document.getElementById("showOffer");
            iframe.contentWindow.location.reload();
        }
    }, false);
}

function generatePDF() {
    if (confirm('Möchtest Du die Rechnung erstellen und den Auftrag abschließen?')) {
        var openInvoiceTab = new AjaxCall(`getReason=generateInvoicePDF`, "POST", window.location.href);
        openInvoiceTab.makeAjaxCall(function (response) {
            var win = window.open(response, '_blank');
            win.focus();
            document.getElementById("goHome").click();
        });
    } else {
        /* Abbruch */
    }
}

function setInvoiceDate() {
    var date = document.getElementById("rechnungsdatum").value;
    params = {
        getReason: "setDateInvoice",
        date: date,
        id: document.getElementById("orderId").innerHTML
    };
    sendDates(params);
}

function setPerformanceDate() {
    var date = document.getElementById("leistungsdatum").value;
    params =  {
        getReason: "setDatePerformance",
        date: date,
        id: document.getElementById("orderId").innerHTML
    };
    sendDates(params);
}

function sendDates(params) {
    var send = new AjaxCall(params, "POST", window.location.href);
    send.makeAjaxCall(function (response) {
        response = JSON.parse(response);
        if (response[0] == "ok") {
            infoSaveSuccessfull("success");
            var iframe = document.getElementById("showOffer");
            iframe.contentWindow.location.reload();
        }

        document.getElementById("allInvoiceItemsTable").innerHTML = response[1];

        console.log(response);
    });
}

/* move in table */

/* https://www.therogerlab.com/sandbox/pages/how-to-reorder-table-rows-in-javascript?s=0ea4985d74a189e8b7b547976e7192ae.4122809346f6a15e41c9a43f6fcb5fd5 */
var row;
var rows;
function move(event) {
    if (event.target.classList.contains("moveRow")) {
        event.preventDefault();

        if (rows.indexOf(event.target.parentNode.parentNode) > rows.indexOf(row))
            event.target.parentNode.parentNode.after(row);
        else
            event.target.parentNode.parentNode.before(row);
    }
}

function moveStart(event) {
    row = event.target;
}

/* called from moveBtn */
function moveInit(event) {
    var table = event.target;
    while (table.nodeName != "TABLE") {
        table = table.parentNode;
    }

    rows = Array.from(table.getElementsByTagName("tr"));
    for (let i = 0; i < rows.length; i++) {
        if (i == 0)
            continue;

        rows[i].draggable = "true";
        rows[i].addEventListener("dragstart", function(event) {
            moveStart(event)
        }, false);
        rows[i].addEventListener("dragover", function(event) {
            move(event)
        }, false);
        rows[i].addEventListener("dragend", function(event) {
            sendPostenOrder(event)
        }, false);
    }
}

/* called from moveBtn */
function moveRemove(event) {
    var table = event.target;
    while (table.nodeName != "TABLE") {
        table = table.parentNode;
    }

    rows = Array.from(table.getElementsByTagName("tr"));
    for (let i = 0; i < rows.length; i++) {
        if (i == 0)
            continue;

        rows[i].draggable = "false";
        rows[i].removeEventListener("dragstart", function(event) {
            moveStart(event)
        }, false);
        rows[i].removeEventListener("dragover", function(event) {
            move(event)
        }, false);
        rows[i].removeEventListener("dragend", function(event) {
            sendPostenOrder(event)
        }, false);
    }
} 

function sendPostenOrder(event) {
    var table = event.target;
    while (table.nodeName != "TABLE") {
        table = table.parentNode;
    }

    var btns = Array.from(table.getElementsByClassName("moveRow"));
    var positions = [];
    for (let i = 0; i < btns.length; i++) {
        positions.push(btns[i].dataset.key);
        btns[i].parentNode.parentNode.children[0].innerHTML = i + 1;
    }
    positions = JSON.stringify(positions);

    let params = {
        getReason: "sendInvoicePositions",
        auftrag: document.getElementById("orderId").innerHTML,
        order: positions,
        tablekey: table.dataset.key
    };

    return null;

    var send = new AjaxCall(params, "POST", window.location.href);
    send.makeAjaxCall(function (response) {
        if (response == "ok") {
            infoSaveSuccessfull("success");
        } else {
            infoSaveSuccessfull();
        }

        console.log(response);
    });
}
