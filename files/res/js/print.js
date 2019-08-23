function print(id, file) {
    let nummer = document.getElementById(id).value || document.getElementById(id).innerHTML;
    if (nummer != "") {
        let getData = new AjaxCall(`getReason=fillForm&file=${file}&nr=${nummer}`, "POST", window.location.href);
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