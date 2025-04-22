import { StatusInfoHandler } from "../classes/statusInfo.js";
import { notification } from "../classes/notifications.js";

/* https://www.therogerlab.com/sandbox/pages/how-to-reorder-table-rows-in-javascript?s=0ea4985d74a189e8b7b547976e7192ae.4122809346f6a15e41c9a43f6fcb5fd5 */
var row;
var rows;
window.move = function(event) {
    if (event.target.classList.contains("moveRow")) {
        event.preventDefault();

        if (rows.indexOf(event.target.parentNode.parentNode) > rows.indexOf(row))
            event.target.parentNode.parentNode.after(row);
        else
            event.target.parentNode.parentNode.before(row);
    }
}

function moveStart(event) {
    row = event.target;
}

/* called from moveBtn */
window.moveInit = function(event) {
    var table = event.target;
    while (table.nodeName != "TABLE") {
        table = table.parentNode;
    }

    rows = Array.from(table.getElementsByTagName("tr"));
    for (let i = 0; i < rows.length; i++) {
        if (i == 0)
            continue;

        rows[i].draggable = "true";
        rows[i].addEventListener("dragstart", function(event) {
            moveStart(event)
        }, false);
        rows[i].addEventListener("dragover", function(event) {
            move(event)
        }, false);
        rows[i].addEventListener("dragend", function(event) {
            sendPostenOrder(event)
        }, false);
    }
}

/* called from moveBtn */
window.moveRemove = function(event) {
    var table = event.target;
    while (table.nodeName != "TABLE") {
        table = table.parentNode;
    }

    rows = Array.from(table.getElementsByTagName("tr"));
    for (let i = 0; i < rows.length; i++) {
        if (i == 0)
            continue;

        rows[i].draggable = "false";
        rows[i].removeEventListener("dragstart", function(event) {
            moveStart(event)
        }, false);
        rows[i].removeEventListener("dragover", function(event) {
            move(event)
        }, false);
        rows[i].removeEventListener("dragend", function(event) {
            sendPostenOrder(event)
        }, false);
    }
} 

function sendPostenOrder(event) {
    var table = event.target;
    while (table.nodeName != "TABLE") {
        table = table.parentNode;
    }

    var btns = Array.from(table.getElementsByClassName("moveRow"));
    var positions = [];
    for (let i = 0; i < btns.length; i++) {
        positions.push(btns[i].dataset.fileId);
    }

    ajax.post({
        r: "setImageOrder",
        order: JSON.stringify(positions),
    }).then(r => {
        if (r.status) {
            notification("Bildreihenfolge erfolgreich geändert", r.status);
        } else {
            const infoHandler = new StatusInfoHandler();
            const infoBox = infoHandler.addInfoBox(StatusInfoHandler.TYPE_ERRORCOPY, "wird übertragen");

            infoBox.statusUpdate(StatusInfoHandler.STATUS_FAILURE, "Fehler beim Speichern der Reihenfolge", r.messsage);
        }
    });
}
