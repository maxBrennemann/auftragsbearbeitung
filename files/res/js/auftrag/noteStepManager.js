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

    ajax.post({
        r: "addNoteOrder",
        auftrag: globalData.auftragsId,
        note: noteNode.value,
    }).then(r => {
        if (r.status == "success") {
            infoSaveSuccessfull("success");
            noteNode.value = "";

            /* update note container */
            const newNote = r.content;
            const noteContainer = document.getElementById("noteContainer");
            noteContainer.innerHTML = noteContainer.innerHTML + newNote;
            // TODO: note event listener via bindings adden
        }
    });
}

/* function creates a popup window that asks the user whether he wants the note to be deleted or not */
export function removeNote(event) {
    let note = event.target.parentNode.children[1].innerHTML;
    let number = indexInClass(event.target.parentNode);

    var div = document.createElement("div");
    const html = `
        <div class="p-4">
            <span>Willst Du die Notiz "${note}" wirklich löschen?</span>
            <br>
            <button class="btn-primary" onclick="notesDeleteNode(${number}, this.parentNode)">Ja</button>
            <button class="btn-primary" onclick="notesClose(this.parentNode)">Nein</button>
        </div>
    `;

    div.innerHTML = html;
    document.body.appendChild(div);

    addActionButtonForDiv(div, "remove");
    centerAbsoluteElement(div);
}

/* function to delete the node */
window.notesDeleteNode = function(number, div) {
    div.parentNode.removeChild(div);
    console.log(number);

    ajax.post({
        r: "deleteNote",
        number: number,
        auftrag: globalData.auftragsId,
    }, true).then(r => {
        document.getElementById("noteContainer").innerHTML = r;
    });
}

/* function for node button to remove the div */
window.notesClose = function(div) {
    div.parentNode.removeChild(div);
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
