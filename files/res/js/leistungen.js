function remove(id) {
    console.log(id)

    var remove = new AjaxCall("", "POST", window.location.href);
    remove.makeAjaxCall(function (responseLink) {
        
    });
}

function add() {
    var bezeichnung = document.getElementById("bezeichnung").value;
    var description = document.getElementById("description").value;
    var source = document.getElementById("source").value;
    var aufschlag = document.getElementById("aufschlag").value;

    var addLeistung = new AjaxCall(`getReason=addLeistung&bezeichung=${bezeichnung}&description=${description}&source=${source}&aufschlag=${aufschlag}`, "POST", window.location.href);
    addLeistung.makeAjaxCall(function (responseLink) {
        location.reload();
    });
}
