
var checkboxes =  {};
var table;

if (document.readyState !== 'loading' ) {
    startInvoice();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        startInvoice();
    });
}

function startInvoice() {
    table = document.getElementsByTagName("table")[0].dataset.key;
}

function check(all = false) {
    if (all == true) {
        var completeInvoice = new AjaxCall(`getReason=completeInvoice&auftrag=${auftragsId}&rows=0&table=${table}`, "POST", window.location.href);
        completeInvoice.makeAjaxCall(function (response) {
            console.log(response);
        });
    } else {
        var rows = JSON.stringify(checkboxes);
        var auftragsId = document.getElementById("orderId").innerHTML;
        var completeInvoice = new AjaxCall(`getReason=completeInvoice&auftrag=${auftragsId}&rows=${rows}&table=${table}`, "POST", window.location.href);
        completeInvoice.makeAjaxCall(function (response) {
            console.log(response);
        });
    }
}

function generatePDF() {
    var openInvoiceTab = new AjaxCall(`getReason=generateInvoicePDF&`, "POST", window.location.href);
    openInvoiceTab.makeAjaxCall(function (response) {
        var win = window.open(response, '_blank');
        win.focus();
    });
}

function changeInput(event, key) {
    checkboxes[key] = event.target.checked;
    check();
}
