
function addToDB() {
    var title = document.getElementById("newTitle").value;
    var content = document.getElementById("newContent").value;

    var add = new AjaxCall(`getReason=sendToDB&title=${title}&content=${content}`, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        if (response == "ok") {
            console.log("data sent to server");
        } else {
            console.log(response);
        }
    });
}
