import { getOrderId } from "../auftrag.js";
import { ajax } from "../classes/ajax.js";
import { addBindings } from "../classes/bindings.js";
import { notification } from "../classes/notifications.js";

const fnNames =  {};

export function initNotes() {
    addBindings(fnNames);

    const nodeContainer = document.getElementById("noteContainer");
    if (nodeContainer != null) {
        getNotes().then(r => {
            if (r.length == 0) {
                return;
            }

            displayNotes(r);
        });
    }

    const toggleStepsInput = document.getElementById("toggleSteps");
    toggleStepsInput?.addEventListener("change", toggleSteps);
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
    notes.forEach((note) => {
        notes.push(note);

        /* clone templateNote */
        const templateNote = document.getElementById("templateNote");
        const clone = templateNote.content.cloneNode(true);

        const noteTitle = clone.querySelector(".noteTitle");
        noteTitle.value = note.title;
        noteTitle.dataset.id = note.id;
        noteTitle.dataset.type = "title";
        noteTitle.addEventListener("change", updateNote);

        const noteText = clone.querySelector(".noteText");
        noteText.innerHTML = note.note;
        noteText.dataset.id = note.id;
        noteText.dataset.type = "note";
        noteText.addEventListener("change", updateNote);

        noteTitle.addEventListener("keyup", function (e) {
            if (e.key == "Enter") {
                noteText.focus();
            }
        });

        const noteDate = clone.querySelector(".noteDate");
        noteDate.innerHTML = note.date;

        const noteShowDelete = clone.querySelector(".showDelete");
        noteShowDelete.addEventListener("click", function (e) {
            const el = e.target.parentNode.parentNode;
            const noteDelete = el.querySelector(".noteDelete");
            noteDelete.classList.toggle("hidden");
        });

        const noteDelete = clone.querySelector(".noteDelete");
        noteDelete.addEventListener("click", removeNote);

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
            notification("", "success");
        }
    });
}

function addBearbeitungsschritt() {
    const name = document.getElementById("processingStepName").value;
    const date = document.getElementById("processingStepDate").value;
    const hideStatus = document.getElementById("processingStepStatus").value;
    const priority = document.getElementById("processingStepPriority").value;
    const assignedTo = document.getElementById("processingStepSelectBy").value;

    ajax.post(`/api/v1/notes/step/${getOrderId()}`, {
        "name": name,
        "date": date,
        "hide": hideStatus,
        "priority": priority,
        "assignedTo": assignedTo,
    }).then(r => {
    });
}

/* addes bearbeitungsschritte */
function addStep() {
    const bearbeitungsschritte = document.getElementById("bearbeitungsschritte");
    if (!bearbeitungsschritte.classList.toggle("hidden")) {
        const textarea = document.querySelector("input.bearbeitungsschrittInput");
        textarea.focus();
    }
}

async function getNotes() {
    return ajax.get(`/api/v1/notes/${globalData.auftragsId}`);
}

/**
 * adds a note to the database
 * 
 * @returns 
 */
function sendNote() {
    const note = document.querySelector("#addNotes");
    if (note == undefined) {
        return null;
    }

    const title = note.querySelector(".noteTitle").value;
    const content = note.querySelector(".noteText").value;

    const addNewNoteBtn = document.getElementById("addNewNote");
    addNewNoteBtn.classList.remove("hidden");

    if (title == "") {
        return null;
    }

    ajax.post(`/api/v1/notes/${globalData.auftragsId}`, {
        "title": title,
        "note": content
    }).then(r => {
        notification("", "success");
        displayNotes([{
            "title": title,
            "note": content,
            "date": r.date,
            "id": r.id,
        }]);

        note.querySelector(".noteTitle").value = "";
        note.querySelector(".noteText").value = "";

        note.classList.toggle("hidden");
    });
}

const cancelNote = () => {
    const note = document.querySelector("#addNotes");
    const title = note.querySelector(".noteTitle");
    const content = note.querySelector(".noteText");

    title.value = "";
    content.value = "";
    note.classList.toggle("hidden");

    const addNewNoteBtn = document.getElementById("addNewNote");
    addNewNoteBtn.classList.remove("hidden");
}

/* function creates a popup window that asks the user whether he wants the note to be deleted or not */
function removeNote(event) {
    const id = event.target.parentNode.querySelector(".noteTitle").dataset.id;
    ajax.delete(`/api/v1/notes/${id}`).then(r => {
        if (r.status == "success") {
            notification("", "success");
            const noteContainer = event.target.parentNode;
            noteContainer.parentNode.removeChild(noteContainer);
        }
    });
}

/* function for node button to remove the div */
window.notesClose = function (div) {
    div.parentNode.removeChild(div);
}

function addNewNote() {
    const addNotes = document.getElementById("addNotes");
    addNotes.classList.toggle("hidden");

    const addNewNoteBtn = document.getElementById("addNewNote");
    addNewNoteBtn.classList.add("hidden");

    const input = addNotes.querySelector(".noteTitle");
    input.focus();
    input.addEventListener("keyup", function (e) {
        if (e.key == "Enter") {
            const addNotes = document.getElementById("addNotes");
            const noteText = addNotes.querySelector(".noteText");
            noteText.focus();
        }
    });
}

const toggleSteps = (e) => {
    const value = e.currentTarget.value;
    ajax.get(`/api/v1/order/${globalData.auftragsId}/steps`, {
        "type": value.checked ? "getAllSteps" : "getOpenSteps",
    }).then(r => {
        if (r.status != "success") {
            return;
        }

        const stepTable = document.getElementById("stepTable");
        stepTable.innerHTML = r.table;
    });
}

fnNames.click_updateSelectBy = () => {
    const el = document.getElementById("processingStepSelectBy");
    el.disabled = !el.disabled;
}

fnNames.click_addBearbeitungsschritt = addBearbeitungsschritt;
fnNames.click_addStep = addStep;
fnNames.click_sendNote = sendNote;
fnNames.click_removeNote = removeNote;
fnNames.click_addNewNote = addNewNote;
fnNames.click_cancelNote = cancelNote;
