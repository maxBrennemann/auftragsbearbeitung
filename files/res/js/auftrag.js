let buttons = document.getElementsByTagName("button");
buttons[0].addEventListener("click", getSelection, false);
buttons[1].addEventListener("click", function () { showSelection(event.target); }, false);

function getSelection() {
    var e = document.getElementById("selectPosten");
    var strUser = e.options[e.selectedIndex].value;

    if (strUser != "produkt") {
        insertPosten = new AjaxCall(`getReason=createTable&type=${strUser}&showData=false`, "POST", window.location.href);
        insertPosten.makeAjaxCall(function (responseTable) {
            document.getElementById("addPosten").innerHTML = responseTable;

            addableTables();
        });
    }
}

function showSelection(element) {
    document.getElementById('newPosten').style.display = 'inline';
    element.style.display = 'none';

    getGeneralPosten = new AjaxCall("getReason=createTable&type=custom", "POST", window.location.href);
    getGeneralPosten.makeAjaxCall(function (responseTable) {
        document.getElementById("generalPosten").innerHTML = responseTable;
    });
}
