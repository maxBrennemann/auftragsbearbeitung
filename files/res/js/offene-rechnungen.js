
function updateIsDone(data) {
    var update = new AjaxCall(`getReason=setTo&rechnung=${data}`, "POST", window.location.href);
    update.makeAjaxCall(function (response) {
        document.getElementById("table").innerHTML = response;
    });
}
