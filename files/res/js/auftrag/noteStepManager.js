const notes = [];

export function initNotes() {
    const nodeContainer = document.getElementById("noteContainer");
    if (nodeContainer != null) {
        getNotes().then(r => {
            displayNotes(r.data);
        });
    }
}

/**
 * Iterate through notes and display them
 * 
 * @param {*} notes 
 * @returns 
 */
function displayNotes(notes) {
    const noteContainer = document.getElementById("noteContainer");
    if (noteContainer == null) {
        return;
    }

    document.getElementById("notesContainer").classList.remove("hidden");
    notes.forEach((note, index) => {
        notes.push(note);

        /* clone templateNote */
        const templateNote = document.getElementById("templateNote");
        const clone = templateNote.content.cloneNode(true);
        
        const noteTitle = clone.querySelector(".noteTitle");
        noteTitle.innerHTML = note.note;
        noteTitle.dataset.id = note.id;
        noteTitle.dataset.type = "title";
        noteTitle.addEventListener("change", updateNote);

        const noteText = clone.querySelector(".noteText");
        noteText.innerHTML = note.note;
        noteText.dataset.id = note.id;
        noteText.dataset.type = "note";
        noteText.addEventListener("change", updateNote);

        const noteDate = clone.querySelector(".noteDate");
        noteDate.innerHTML = note.date;

        noteContainer.appendChild(clone);
    });
}

function updateNote(e) {
    const data = e.target.value;
    const id = e.target.dataset.id;
    const type = e.target.dataset.type;

    ajax.put(`/api/v1/notes/${globalData.auftragsId}`, {
        id: id,
        type: type,
        data: data
    }).then(r => {
        if (r.status == "success") {
            infoSaveSuccessfull("success");
        }
    });
}

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

export async function getNotes() {
    return ajax.get(`/api/v1/notes/${globalData.auftragsId}`);
}

/**
 * adds a note to the database
 * 
 * @returns 
 */
export function addNote() {
    var noteNode = document.querySelector("#addNotes");
    if (noteNode == undefined) {
        return null;
    }

    const title = noteNode.querySelector(".noteTitle").value;
    const note = noteNode.querySelector(".noteText").value;

    if (title == "") {
        return null;
    }

    ajax.post(`/api/v1/notes/${globalData.auftragsId}`, {
        title: title,
        note: note
    }).then(r => {
        if (r.status == "success") {
            infoSaveSuccessfull("success");
            displayNotes([{
                    title: title,
                    note: note,
                    date: r.date
            }]);

            noteNode.querySelector(".noteTitle").value = "";
            noteNode.querySelector(".noteText").value = "";

            noteNode.classList.toggle("hidden");
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

/* function for node button to remove the div */
window.notesClose = function(div) {
    div.parentNode.removeChild(div);
}

export function addNewNote() {
    const addNotes = document.getElementById("addNotes");
    addNotes.classList.toggle("hidden");
    const input = document.querySelector(".noteInput");
    input.focus();
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
