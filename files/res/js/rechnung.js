
var checkboxes =  {};
var table;

if (document.readyState !== 'loading' ) {
    startInvoice();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        startInvoice();
    });
}

function startInvoice() {}

function generatePDF() {
    if (confirm('Möchtest Du die Rechnung erstellen und den Auftrag abschließen?')) {
        var openInvoiceTab = new AjaxCall(`getReason=generateInvoicePDF&`, "POST", window.location.href);
        openInvoiceTab.makeAjaxCall(function (response) {
            var win = window.open(response, '_blank');
            win.focus();
        });
    } else {
        /* Abbruch */
    }
}
