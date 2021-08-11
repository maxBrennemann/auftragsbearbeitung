
function closeOrder(ids) {
    var url_string = window.location.href
    var url = new URL(url_string);
    var auftragsId = url.searchParams.get("create");

    if (ids == null) {
        var completeInvoice = new AjaxCall(`getReason=completeInvoice&auftrag=${auftragsId}&rows=0`, "POST", window.location.href);
        completeInvoice.makeAjaxCall(function (response) {});
    } else {
        if (Array.isArray(ids)) {
            var rows = JSON.stringify(ids);
            var completeInvoice = new AjaxCall(`getReason=completeInvoice&auftrag=${auftragsId}&rows=${rows}`, "POST", window.location.href);
            completeInvoice.makeAjaxCall(function (response) {});
        } else {
            return null;
        }
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
    var status = event.target.checked;
    var sendInputKey = new AjaxCall(`getReason=tableInput&key=${key}&status=${status}`, "POST", window.location.href);
    sendInputKey.makeAjaxCall(function (response) {
        console.log(response);
    });
}
