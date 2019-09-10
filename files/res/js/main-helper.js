var kundeninput = document.getElementById("kundeninput");
var rechnungsinput = document.getElementById("rechnungsinput");
var auftragsinput = document.getElementById("auftragsinput");

kundeninput.addEventListener("keyup", function (event) {
    if (event.key === "Enter") {
        document.getElementById("kundenLink").click();
    }
});

rechnungsinput.addEventListener("keyup", function (event) {
    if (event.key === "Enter") {
        document.getElementById("rechnungsLink").click();
    }
});

auftragsinput.addEventListener("keyup", function (event) {
    if (event.key === "Enter") {
        document.getElementById("auftragsLink").click();
    }
});
