var kundeninput = document.getElementById("kundeninput");
var rechnungsinput = document.getElementById("rechnungsinput");
var auftragsinput = document.getElementById("auftragsinput");

kundeninput.addEventListener("keyup", function (event) {
    if (event.key === "Enter") {
        ajaxSearch(event.target.value);
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

function ajaxSearch(query) {
    var link = document.getElementById('kundenLink');
    if (isNaN(query)) {
        link.href = link.dataset.url + '?mode=search&query=' + query;
    } else {
        link.href = link.dataset.url + '?id=' + query;
    }
    document.getElementById("kundenLink").click();
}

function showCustomizeOptions() {
    const div = document.createElement("div");
    // TODO: linkauswahl zusammenstellen, die dann angepinnt werden kann
}
