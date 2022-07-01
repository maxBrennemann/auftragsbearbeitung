
if (document.readyState !== 'loading' ) {
    console.log( 'document is already ready, just execute code here' );
    initializeEditButtons();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        console.log( 'document was not ready, place code here' );
        initializeEditButtons();
    });
}

function initializeEditButtons() {
    var buttons = document.getElementsByTagName("button");

    buttons[0].addEventListener("click", function() {setText(this, 1)}, false);
    buttons[1].addEventListener("click", function() {setText(this, 2)}, false);
    buttons[2].addEventListener("click", function() {setText(this, 3)}, false);
}

function setText(button, id) {
    var node = getEdibleNode(button);
    node.contentEditable = node.isContentEditable ? "false" : "true";

    if (node.contentEditable == "true") {
        button.innerText = "💾";
    } else {
        button.innerText = "✎";
        sendToServer(id, node.innerText);
    }
}

function getEdibleNode(button) {
    return button.parentNode.children[0];
}

function sendToServer(type, content) {
    console.log(type + " " + content);
    if (type == 1 && content.length > 64)
        return;

    var productId = document.getElementById("product-id").innerText;
    var update = new AjaxCall(`getReason=updateProductValues&productId=${productId}&type=${type}&content=${content}`, "POST", window.location.href);
    update.makeAjaxCall(function (response) {
        infoSaveSuccessfull(response);
        console.log(response);
    });
}
