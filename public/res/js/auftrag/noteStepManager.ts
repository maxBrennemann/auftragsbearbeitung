import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings";
import { notification } from "js-classes/notifications";

import { fetchAndRenderTable, addRow } from "../classes/table";
import { tableConfig } from "../classes/tableconfig";
import { FunctionMap, TableOptions } from "../types/types";

const fnNames = {} as FunctionMap;
const notesConfig = {
    orderId: 0,
};
const stepsConfig = {
    tableOptions: {} as TableOptions,
}

const refs = {} as { [key: string]: HTMLElement };

interface Note {
    title: string,
    note: string,
    date: Date,
    id: Number
}

const initStepsTable = async () => {
    const options: TableOptions = {
        hideOptions: ["move", "add", "addRow"],
        hide: [
            "Schrittnummer",
            "Auftragsnummer",
            "istErledigt",
            "assignedTo",
            "finishingDate",
        ],
        primaryKey: tableConfig["schritte"].primaryKey,
        autoSort: true,
        styles: {
            table: {
                className: ["w-full"],
            },
        },
        conditions: {
            Auftragsnummer: notesConfig.orderId,
        },
        joins: {
            assignedToUser: 0
        }
    };

    const toggleSteps = (document.getElementById("toggleSteps") as HTMLInputElement).checked;
    if (!toggleSteps && options.conditions) {
        options.conditions["istErledigt"] = "1";
    }

    stepsConfig.tableOptions = options;
    const table = await fetchAndRenderTable("stepTable", "schritte", options);

    if (table == null) {
        notification("Fehler beim Laden der Bearbeitungsschritte", "failure");
        return;
    }

    table.addEventListener("rowEdit", editStep as EventListener);
    table.addEventListener("rowDelete", deleteStep as EventListener);
    table.addEventListener("rowCheck", checkStep as EventListener);
}

const editStep = (e: CustomEvent) => {

}

const deleteStep = (e: CustomEvent) => {
    const data = e.detail;
    const stepNumber = data["Schrittnummer"];

    ajax.delete(`/api/v1/notes/step/${stepNumber}`, {
        "orderId": notesConfig.orderId,
    }).then(r => {
        if (r.data.status == "success") {
            notification("", "success");
            data.row.remove();
        }
    });
}

const checkStep = (e: CustomEvent) => {
    
}

const displayNotes = (notes: Note[]) => {
    if (!refs.noteContainer) return;

    refs.notesContainer.classList.remove("hidden");
    notes.forEach(note => {
        notes.push(note);

        /* clone templateNote */
        const templateNote = document.getElementById("templateNote") as HTMLTemplateElement;
        const clone = templateNote.content.cloneNode(true) as HTMLElement;

        const noteTitle = clone.querySelector(".noteTitle") as HTMLInputElement;
        noteTitle.value = note.title;
        noteTitle.dataset.id = note.id.toString();
        noteTitle.dataset.type = "title";
        noteTitle.addEventListener("change", updateNote);

        const noteText = clone.querySelector(".noteText") as HTMLTextAreaElement;
        noteText.innerHTML = note.note;
        noteText.dataset.id = note.id.toString();
        noteText.dataset.type = "note";
        noteText.addEventListener("change", updateNote);

        noteTitle.addEventListener("keyup", function (e) {
            if (e.key == "Enter") {
                noteText.focus();
            }
        });

        const noteDate = clone.querySelector(".noteDate") as HTMLSpanElement;
        noteDate.innerHTML = note.date.toString();

        const noteShowDelete = clone.querySelector(".showDelete") as HTMLButtonElement;
        noteShowDelete.addEventListener("click", function (e: Event) {
            const target = e.target as HTMLElement;
            if (!target) return;
            const el = target.parentNode?.parentNode;
            const noteDelete = el?.querySelector(".noteDelete");
            noteDelete?.classList.toggle("hidden");
        });

        const noteDelete = clone.querySelector(".noteDelete") as HTMLButtonElement;
        noteDelete.addEventListener("click", removeNote);

        refs.noteContainer.appendChild(clone);
    });
}

function updateNote(e: any) {
    const data = e.target.value;
    const id = e.target.dataset.id;
    const type = e.target.dataset.type;

    ajax.put(`/api/v1/notes/${notesConfig.orderId}`, {
        id: id,
        type: type,
        data: data
    }).then(r => {
        if (r.data.status == "success") {
            notification("", "success");
        }
    });
}

fnNames.click_addBearbeitungsschritt = () => {
    const nameInput = document.getElementById("processingStepName") as HTMLInputElement;

    const name = nameInput.value;
    const date = (document.getElementById("processingStepDate") as HTMLInputElement).value;
    const hideStatus = (document.getElementById("processingStepStatus") as HTMLInputElement).checked;
    const priority = (document.getElementById("processingStepPriority") as HTMLInputElement).value;

    let assignedTo = 0;
    if ((document.getElementById("processingStepBy") as HTMLInputElement).checked) {
        assignedTo = parseInt((document.getElementById("processingStepSelectBy") as HTMLInputElement).value);
    }

    if (name.length < 3) {
        nameInput.classList.add("ring-red-500");
        return;
    }

    nameInput.classList.remove("ring-red-500");

    ajax.post(`/api/v1/notes/step/${notesConfig.orderId}`, {
        "name": name,
        "date": date,
        "hide": hideStatus,
        "priority": priority,
        "assignedTo": assignedTo,
    }).then(r => {
        const table = document.querySelector("#stepTable table") as HTMLTableElement;
        const row = {
            Schrittnummer: r.data.stepId,
            Auftragsnummer: notesConfig.orderId,
            assignedTo: assignedTo,
            Bezeichnung: name,
            Datum: date,
            Priority: r.data.priority,
            finishingDate: "0000-00-00",
            istErledigt: hideStatus ? "1" : "0",
            name: document.querySelector(`#processingStepSelectBy option[value='${assignedTo}']`)?.textContent || "",
        };
        addRow(row, table, stepsConfig.tableOptions);
    });
}

fnNames.click_toggleAddStep = () => {
    const bearbeitungsschritte = document.getElementById("bearbeitungsschritte") as HTMLDivElement;
    if (!bearbeitungsschritte.classList.toggle("hidden")) {
        const textarea = document.querySelector("input.bearbeitungsschrittInput") as HTMLInputElement;
        textarea.focus();
    }
}

async function getNotes() {
    return await ajax.get(`/api/v1/notes/${notesConfig.orderId}`);
}

fnNames.click_sendNote = () => {
    const note = document.querySelector("#addNotes") as HTMLDivElement;
    if (note == undefined) {
        return;
    }

    const title = (note.querySelector(".noteTitle") as HTMLInputElement).value;
    const content = (note.querySelector(".noteText") as HTMLTextAreaElement).value;
    const date = (note.querySelector(".noteDate") as HTMLInputElement).value;

    const addNewNoteBtn = document.getElementById("addNewNote") as HTMLButtonElement;
    addNewNoteBtn.classList.remove("hidden");

    if (title == "") {
        return;
    }

    ajax.post(`/api/v1/notes/${notesConfig.orderId}`, {
        "title": title,
        "note": content,
        "date": date,
    }).then(r => {
        notification("", "success");
        displayNotes([{
            "title": title,
            "note": content,
            "date": r.data.date,
            "id": r.data.id,
        }]);

        (note.querySelector(".noteTitle") as HTMLInputElement).value = "";
        (note.querySelector(".noteText") as HTMLTextAreaElement).value = "";

        note.classList.toggle("hidden");
    });
}

fnNames.click_cancelNote = () => {
    const note = document.querySelector("#addNotes") as HTMLElement;
    const title = note.querySelector(".noteTitle") as HTMLInputElement;
    const content = note.querySelector(".noteText") as HTMLTextAreaElement;

    title.value = "";
    content.value = "";
    note.classList.toggle("hidden");

    const addNewNoteBtn = document.getElementById("addNewNote") as HTMLButtonElement;
    addNewNoteBtn.classList.remove("hidden");
}

/* function creates a popup window that asks the user whether he wants the note to be deleted or not */
function removeNote(event: any) {
    const id = event.target.parentNode.querySelector(".noteTitle").dataset.id;
    ajax.delete(`/api/v1/notes/${id}`).then(r => {
        if (r.data.status == "success") {
            notification("", "success");
            const noteContainer = event.target.parentNode;
            noteContainer.parentNode.removeChild(noteContainer);
        }
    });
}

function addNewNote() {
    const addNotes = document.getElementById("addNotes") as HTMLElement;
    addNotes.classList.toggle("hidden");

    const addNewNoteBtn = document.getElementById("addNewNote") as HTMLButtonElement;
    addNewNoteBtn.classList.add("hidden");

    const input = addNotes.querySelector(".noteTitle") as HTMLInputElement;
    input.focus();
    input.addEventListener("keyup", function (e) {
        if (e.key == "Enter") {
            const addNotes = document.getElementById("addNotes") as HTMLElement;
            const noteText = addNotes.querySelector(".noteText") as HTMLTextAreaElement;
            noteText.focus();
        }
    });
}

fnNames.write_toggleSteps = () => {
    (document.getElementById("stepTable") as HTMLDivElement).innerHTML = "";
    initStepsTable();
}

fnNames.click_updateSelectBy = () => {
    const el = document.getElementById("processingStepSelectBy") as HTMLSelectElement;
    el.disabled = !el.disabled;
}

fnNames.click_removeNote = removeNote;
fnNames.click_addNewNote = addNewNote;

export function initNotes(orderId: any) {
    notesConfig.orderId = parseInt(orderId);
    addBindings(fnNames);

    refs.noteContainer = document.getElementById("noteContainer") as HTMLDivElement;
    refs.notesContainer = document.getElementById("notesContainer") as HTMLDivElement;

    if (refs.noteContainer != null) {
        getNotes().then(r => {
            if (r.data.length == 0) {
                return;
            }

            displayNotes(r.data);
        });
    }

    initStepsTable();
}
