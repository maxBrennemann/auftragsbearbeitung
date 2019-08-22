function print() {
    let auftragsnummer = document.getElementById("auftragsnummer").value;
    if (auftragsnummer != "") {
        let getData = new AjaxCall(`getReason=fillForm&file=Auftrag&nr=${auftragsnummer}`, "POST", window.location.href);
        getData.makeAjaxCall(function (htmlCode) {
            var win = window.open("", "", ""); //width=900,height=700
            win.document.write(htmlCode);
            win.document.close();
            win.focus();
            win.print();
            win.close();
        });
    } else {
        alert("Bitte geben Sie eine Auftragsnummer ein!");
    }
}