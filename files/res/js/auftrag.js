let buttons = document.getElementsByTagName("button");
buttons[0].addEventListener("click", getSelection, false);
buttons[1].addEventListener("click", performSearch, false);
buttons[2].addEventListener("click", function () { showSelection(event.target); }, false);
buttons[3].addEventListener("click", addBearbeitungsschritte, false);

function getSelection() {
    var e = document.getElementById("selectPosten");
    var strUser = e.options[e.selectedIndex].value;

    if (strUser != "produkt") {
        var insertPosten = new AjaxCall(`getReason=createTable&type=${strUser}&showData=false`, "POST", window.location.href);
        insertPosten.makeAjaxCall(function (responseTable) {
            document.getElementById("addPosten").innerHTML = responseTable;

            addableTables();
        });
    } else if (strUser == "produkt") {
        var showProducts = new AjaxCall(`getReason=createTable&type=produkt`, "POST", window.location.href);
        showProducts.makeAjaxCall(function (responseTable) {
            document.getElementById("addPosten").innerHTML = responseTable;

            addableTables();
        });
    }
}

function showSelection(element) {
    document.getElementById('newPosten').style.display = 'inline';
    element.style.display = 'none';

    var getGeneralPosten = new AjaxCall("getReason=createTable&type=custom", "POST", window.location.href);
    getGeneralPosten.makeAjaxCall(function (responseTable) {
        document.getElementById("generalPosten").innerHTML = responseTable;
    });
}

function addBearbeitungsschritte() {
    var bearbeitungsschritte = new AjaxCall("getReason=createTable&type=schritte", "POST", window.location.href);
    bearbeitungsschritte.makeAjaxCall(function (responseTable) {
        document.getElementById("bearbeitungsschritte").innerHTML = responseTable;
        addableTables();
    });
}

function performSearch(e) {
    var query = e.target.previousSibling.value;
    console.log(query);
    var search = new AjaxCall(`getReason=search&query=${query}&stype=produkt`, "POST", window.location.href);
    search.makeAjaxCall(function (responseTable) {
        document.getElementById("searchResults").innerHTML = responseTable;
        addableTables();
    });
}
