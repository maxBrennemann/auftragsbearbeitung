function showAddAttribute() {
    document.getElementById("showDivAddAttribute").style.display = "inline";
}

function showAddAttributeValue() {
    document.getElementById("showDiv").style.display = "inline";
}

function addNewAttribute() {
    var name = document.getElementById("newName").value;
    var descr = document.getElementById("descr").value;
    var loadHTMLTemplate = new AjaxCall(`getReason=addAtt&name=${name}&descr=${descr}`);
    loadHTMLTemplate.makeAjaxCall(function (response) {
        if (response == "-1") {
            alert("Fehler beim Hinzufügen des Wertes!");
        } else {
            location.reload();
        }
    });
}

function addNewAttributeValue() {
    var value = document.getElementById("newVal").value;
    var attribute = document.getElementById("selectAttribute");
    attribute =  attribute.options[attribute.selectedIndex].value;
    var loadHTMLTemplate = new AjaxCall(`getReason=addAttVal&att=${attribute}&value=${value}`);
    loadHTMLTemplate.makeAjaxCall(function (response) {
        if (response == "-1") {
            alert("Fehler beim Hinzufügen des Wertes!");
        } else {
            var span = document.createElement("span");
            span.innerText = document.getElementById("newVal").value;
            document.getElementById("newVal").value = "";
            var div = document.getElementById("attributes" + response);
            div.appendChild(span);
        }
    });
}