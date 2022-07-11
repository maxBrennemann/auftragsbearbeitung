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
        leistungDate: document.getElementById("leistungsdatum")
    };

    var sip = new AjaxCall(params, "POST", window.location.href);
    sip.makeAjaxCall(function (response) {
        if (response == "ok") {
            infoSaveSuccessfull("success");
            var iframe = document.getElementById("showOffer");
            iframe.contentWindow.location.reload();
        }
    }.bind(this), false);
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
        date: date
    };
    sendDates(params);
}

function setPerformanceDate() {
    var date = document.getElementById("leistungsdatum").value;
    params =  {
        getReason: "setDatePerformance",
        date: date
    };
    sendDates(params);
}

function sendDates(params) {
    var send = new AjaxCall(params, "POST", window.location.href);
    send.makeAjaxCall(function (response) {
        if (response == "ok") {
            infoSaveSuccessfull("success");
            var iframe = document.getElementById("showOffer");
            iframe.contentWindow.location.reload();
        }

        console.log(response);
    });
}
