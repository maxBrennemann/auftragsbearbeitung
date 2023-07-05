export function addBearbeitungsschritt() {
    var tableData = document.getElementsByClassName("bearbeitungsschrittInput");
    var steps = [];
    for (var i = 0; i < tableData.length; i++) {
        steps.push(tableData[i].value);
    }

    if (steps[1] == "") {
        steps[1] = 0;
    }

    var el = document.getElementsByName("isAlreadyDone")[0];
    var radio = el.elements["isDone"];
    var hide;
    for (var i = 0; i < radio.length; i++) {
        if (radio[i].checked) {
            hide = radio[i].value;
            break;
        }
    }
    
    /* 0 = hide, 1 = show */
    hide = hide == "hide" ? 0 : 1;

    /* check for assigned task */
    let assigned = document.querySelector('input[name="assignTo"]');
    let assignedTo = "none";
    if (assigned.checked == true) {
        let e = document.getElementById("selectMitarbeiter");
        assignedTo = e.options[e.selectedIndex].value;
    }

    /* ajax parameter */
    let params = {
        getReason: "insertStep",
        bez: steps[0],
        datum: steps[1],
        auftrag: globalData.auftragsId,
        hide: hide,
        prio: steps[2],
        assignedTo: assignedTo
    };

    var add = new AjaxCall(params, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        document.getElementById("stepTable").innerHTML = response;

        /* clear inputs */
        var tableData = document.getElementsByClassName("bearbeitungsschrittInput");
        for (var i = 0; i < tableData.length; i++) {
            tableData[i].value = "";
        }

        document.getElementById("bearbeitungsschritte").style.display = "none";
    }.bind(this), false);
}

/* addes bearbeitungsschritte */
export function showBearbeitungsschritt() {
    var bearbeitungsschritte = document.getElementById("bearbeitungsschritte");
    bearbeitungsschritte.style.display = "block";

    const textarea = document.querySelector("textarea.bearbeitungsschrittInput");
    textarea.focus();
}

/* adds a note to the order */
export function addNote() {
    var noteNode = document.querySelector(".noteInput");
    if (noteNode == undefined)
        return null;

    note = noteNode.value;
    noteNode.value = "";

    /* ajax parameter */
    let params = {
        getReason: "addNoteOrder",
        auftrag: globalData.auftragsId,
        note: note
    };

    var add = new AjaxCall(params, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        document.getElementById("noteContainer").innerHTML = response;
        infoSaveSuccessfull("success");
    }.bind(this), false);
}

/* function creates a popup window that asks the user whether he wants the note to be deleted or not */
export function removeNote(event) {
    let note = event.target.parentNode.children[1].innerHTML;

    let number = indexInClass(event.target.parentNode);

    var div = document.createElement("div");
    let textnode = document.createTextNode(`Willst Du die Notiz "${note}" wirklich löschen?`);

    let btn_yes = document.createElement("button");
    btn_yes.innerHTML = "Ja";
    let btn_no = document.createElement("button");
    btn_no.innerHTML = "Nein";

    /* inner function to delete the node */
    function delNode(number, div) {
        div.parentNode.removeChild(div);

        console.log(number);

        /* ajax call to delete note from db, note is then removed from webpage */
        var del = new AjaxCall(`getReason=deleteNote&number=${number}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
        del.makeAjaxCall(function (response) {
            document.getElementById("noteContainer").innerHTML = response;
        });
    }

    /* inner function for node button to remove the div */
    function close(div) {
        div.parentNode.removeChild(div);
    }

    /* event listeners */
    btn_yes.addEventListener("click", function() {
        delNode(number, div);
    }, false);

    btn_no.addEventListener("click", function() {
        close(div);
    }, false);

    div.appendChild(textnode);
    div.appendChild(document.createElement("br"));
    div.appendChild(btn_yes);
    div.appendChild(btn_no);
    document.body.appendChild(div);

    addActionButtonForDiv(div, "remove");
    centerAbsoluteElement(div);
}

export function addNewNote() {
    document.getElementById("addNotes").style.display='block';
    const textarea = document.querySelector(".noteInput");
    textarea.focus();
}

window.radio = function(val) {
    var stepTable = document.getElementById("stepTable");
    var params = "", data;
    
    if (val == "show") {
        params = `getReason=getAllSteps&auftrag=${globalData.auftragsId}`;
        data = globalData.alleSchritte;
    } else if (val == "hide") {
        params = `getReason=getOpenSteps&auftrag=${globalData.auftragsId}`;
        data = globalData.erledigendeSchritte;
    }
    
    if (data == null) {
        var add = new AjaxCall(params, "POST", window.location.href);
        add.makeAjaxCall(function (response, data) {
            stepTable.innerHTML = response;
            switch (data[0]) {
                case "show":
                    globalData.alleSchritte = response;
                break;
                case "hide":
                    globalData.erledigendeSchritte = response;
                break;
            }
        }, val);
    } else {
        stepTable.innerHTML = data;
    }
}
