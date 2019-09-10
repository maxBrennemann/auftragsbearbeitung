function getSelections() {
    var e = document.getElementById("selectPosten");
    var strUser = e.options[e.selectedIndex].value;

    if (strUser == "zeit") {
        document.getElementById("addPostenZeit").style.display = "inline";
        document.getElementById("addPostenLeistung").style.display = "none";
    } else if (strUser == "leistung") {
        document.getElementById("addPostenLeistung").style.display = "inline";
        document.getElementById("addPostenZeit").style.display = "none";
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

function addTime() {
    var time = document.getElementById("time").value;
    var wage = document.getElementById("wage").value;
    var descr = document.getElementById("descr").value;
    var auftrag = new URL(window.location.href).searchParams.get("id");
    var add = new AjaxCall(`getReason=insTime&time=${time}&wage=${wage}&descr=${descr}&auftrag=${auftrag}`, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        console.log(response);
        location.reload();
    });
}
