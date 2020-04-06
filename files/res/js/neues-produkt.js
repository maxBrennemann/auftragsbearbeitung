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
        addActionButtonForDiv(div, 'remove');
    });
}

function removeHTMLForAddingSource() {
    var child = document.getElementById("htmlForAddingSource");
    child.parentNode.removeChild(child);
    loadNewSelect();
}

function getHTMLForAttributes() {
    var getHTML = new AjaxCall(`getReason=getAttributeMatcher`, "POST", window.location.href);
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

/* attribute matcher functions */

function addToSelector() {
    var attributeSelector = document.getElementById("attributeSelector");
    var title = attributeSelector.options[attributeSelector.selectedIndex].innerHTML;
    attributeSelector = attributeSelector.options[attributeSelector.selectedIndex].value;

    var attValues = document.getElementById("showAttributeValues");
    var heading = document.createElement("h3");
    heading.innerHTML = title;

    attValues.appendChild(heading);
    loadAttributes(attributeSelector);
}

function loadAttributes(attributeGroupId) {
    var getAttributes = new AjaxCall(`getReason=getAttributes&attGroupId=${attributeGroupId}`, "POST", window.location.href);
    getAttributes.makeAjaxCall(function (responseHTML) {
        var attValues = document.getElementById("showAttributeValues");
        attValues.innerHTML += responseHTML;
    });
}

function addAttributeToProduct(attributeGroupId, attributeGroupName, bez) {
    var anchor = document.getElementById("addedValues");
    var div = document.getElementById( attributeGroupId + "addedValues");
    if (div == null) {
        div = document.createElement("div");
        div.id = attributeGroupId + "addedValues";
        div.classList.add("selectedAttList");
    }
    var span = document.createElement("span");
    var remove = document.createElement("span");

    span.innerHTML = bez;

    remove.innerHTML = "⊖";
    remove.style.cursor = "default";
    remove.addEventListener("click", function(event) {
        var child = event.target.parentNode;
        var parent = event.target.parentNode.parentNode;
        parent.removeChild(child);
    }, false);

    span.appendChild(remove);
    span.appendChild(document.createElement("br"));
    div.appendChild(span);
    anchor.appendChild(div);
}
