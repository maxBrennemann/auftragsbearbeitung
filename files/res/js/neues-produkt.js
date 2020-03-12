
document.getElementById("selectSource").addEventListener("change", function(event) {
    if (event.target.value == "addNew") {
        getHTMLForAddingSource();
    }
});

function getHTMLForAddingSource() {
    var getHTML = new AjaxCall(`getReason=fileRequest&file=source.html`, "POST", window.location.href);
    getHTML.makeAjaxCall(function (responseHTML) {
        var div = document.createElement("div");
        div.innerHTML = responseHTML;
        div.id = "htmlForAddingSource";
        div.classList.add("ajaxBox");
        document.body.appendChild(div);
        centerAbsoluteElement(div);
    });
}

function removeHTMLForAddingSource() {
    var child = document.getElementById("htmlForAddingSource");
    child.parentNode.removeChild(child);
    loadNewSelect();
}

function getHTMLForAttributes() {
    var getHTML = new AjaxCall(`getReason=fileRequest&file=attributes.html`, "POST", window.location.href);
    getHTML.makeAjaxCall(function (responseHTML) {
        var div = document.createElement("div");
        div.innerHTML = responseHTML;
        div.id = "htmlForAddingAttributes";
        div.classList.add("ajaxBox");
        document.body.appendChild(div);
        centerAbsoluteElement(div);
    });
}

function removeHTMLForAttributes() {
    var child = document.getElementById("htmlForAddingAttributes");
    child.parentNode.removeChild(child);
    loadNewSelect();
}

function loadNewSelect() {
    var getSelect = new AjaxCall(`getReason=getSelect`, "POST", window.location.href);
    getSelect.makeAjaxCall(function (responseHTML) {
        var select = document.getElementById("selectSource");
        select.innerHTML = responseHTML;
    });
}

function sendSource() {
    var name = document.getElementById("getName").value;
    var desc = document.getElementById("getDesc").value;

    var send = new AjaxCall(`getReason=sendSource&name=${name}&desc=${desc}`, "POST", window.location.href);
    send.makeAjaxCall(function (responseHTML) {
        removeHTMLForAddingSource();
    });
}