
function addToDB() {
    var title = document.querySelector("input").value;
    var content = document.querySelector("textarea").value;

    var add = new AjaxCall(`getReason=sendToDB&title=${title}&content=${content}`, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        if (response == "ok") {
            console.log("data sent to server");
        } else {
            console.log(response);
        }
    });
}
