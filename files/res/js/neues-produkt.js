
function getHTMLForAddingSource() {
    var getHTML = new AjaxCall(`getReason=getHTML&source=getAddingSource`, "POST", window.location.href);
    search.makeAjaxCall(function (responseHTML) {
        var div = document.createElement("div");
        div.innerHTML = responseHTML;
        document.body.appendChild(div);
    });
}