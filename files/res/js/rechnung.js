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
}

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

function sendText(id, text) {
    var sendTextToServer = new AjaxCall(`getReason=invoiceAddText&text=${text}&id=${id}`, "POST", window.location.href);
    sendTextToServer.makeAjaxCall(function (response) {
        var iframe = document.getElementById("showOffer");
        iframe.contentWindow.location.reload();
        console.log(response);
    });
}

function removeText(id) {
    var sendTextToServer = new AjaxCall(`getReason=invoiceRemoveText&id=${id}`, "POST", window.location.href);
    sendTextToServer.makeAjaxCall(function (response) {
        var iframe = document.getElementById("showOffer");
        iframe.contentWindow.location.reload();
        console.log(response);
    });
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
