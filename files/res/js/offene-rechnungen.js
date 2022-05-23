
function updateIsDone(data, event) {
    var auftragsnummer = event.target.parentNode.parentNode.children[0].innerHTML;
    console.log(auftragsnummer);

    var update = new AjaxCall(`getReason=setTo&rechnung=${auftragsnummer}`, "POST", window.location.href);
    update.makeAjaxCall(function (response) {
        document.getElementById("table").innerHTML = response;
    });

    var setInvoiceData = new AjaxCall(`getReason=setInvoiceData&rechnung=${auftragsnummer}`, "POST", window.location.href);
    setInvoiceData.makeAjaxCall(function (response) {});
}
