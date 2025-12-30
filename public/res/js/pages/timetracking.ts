import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings"

import { addRow, clearRows, renderTable } from "../classes/table";
import { timeGlobalListener } from "../classes/timetracking";
import { FunctionMap } from "../types/types";
import { loader } from "../classes/helpers";

import { Router } from "../utils/router";
import { Url } from "../utils/url";

const fnNames = {} as FunctionMap;
const refs = {} as { [key: string]: HTMLElement };

refs.askTask = document.getElementById("askTask") as HTMLElement;
refs.getTask = document.getElementById("getTask") as HTMLInputElement;
refs.startStopChecked = document.getElementById("startStopChecked") as HTMLInputElement;
refs.updateStartStopName = document.getElementById("updateStartStopName") as HTMLSpanElement;
refs.pauseCurrentTracking = document.getElementById("pauseCurrentTracking") as HTMLButtonElement;

var started = false;

const init = () => {
    addBindings(fnNames);

    if (localStorage.getItem("startTime")) {
        started = true;
        (document.getElementById("updateStartStopName") as HTMLSpanElement).innerHTML = "stoppen";
        (document.getElementById("startStopChecked") as HTMLInputElement).checked = true;
        toggleIsPausable(true);
    }

    const getTask = document.getElementById("getTask") as HTMLInputElement;
    getTask.addEventListener("keydown", e => {
        if (e.key == "Enter") {
            fnNames.click_sendTimeTracking();
        }
    });

    getTimeTrackingEntries();
}

const toggleIsPausable = (status: boolean) => {
    const isPausable = document.getElementById("pauseCurrentTracking") as HTMLButtonElement;
    switch (status) {
        case true:
            isPausable.classList.add("btn-edit");
            isPausable.classList.remove("btn-cancel");
            isPausable.disabled = false;
            break;
        case false:
            isPausable.classList.remove("btn-edit");
            isPausable.classList.add("btn-cancel");
            isPausable.disabled = true;
            break;
    }
}

fnNames.click_startStopTime = () => {
    started = !started;
    switch (started) {
        case true:
            storeTimestamp("startTime");
            (document.getElementById("updateStartStopName") as HTMLSpanElement).innerHTML = "stoppen";
            timeGlobalListener();
            toggleIsPausable(true);
            break;
        case false:
            const askTask = document.getElementById("askTask") as HTMLElement;
            askTask.classList.add("flex");
            askTask.classList.remove("hidden");
            (document.getElementById("getTask") as HTMLInputElement).focus();
            toggleIsPausable(false);
            break;
    }
}

fnNames.click_sendTimeTracking = () => {
    const task = document.getElementById("getTask") as HTMLInputElement;

    ajax.post(`/api/v1/time-tracking/add`, {
        task: task.value,
        start: localStorage.getItem("startTime"),
        stop: new Date().getTime().toString(),
    }).then(response => {
        const table = document.querySelector("table");
        const options = {
            "hide": ["id"],
            "hideOptions": [
                "addRow",
                "check",
                "move",
                "add"
            ],
        };
        addRow(response.data, table, options);

        localStorage.removeItem("startTime");
        (document.getElementById("updateStartStopName") as HTMLSpanElement).innerHTML = "starten";

        task.value = "";

        const askTask = document.getElementById("askTask") as HTMLElement;
        askTask.classList.remove("flex");
        askTask.classList.add("hidden");
    });
}

fnNames.click_cancelTimeTracking = () => {
    const askTask = document.getElementById("askTask") as HTMLElement;
    askTask.classList.remove("flex");
    askTask.classList.add("hidden");
}

fnNames.click_cancelCurrentTracking = () => {
    if (!(confirm("Willst du die aktuelle Erfassung abbrechen?"))) {
        return;
    }
    localStorage.removeItem("startTime");
    const startStopChecked = document.getElementById("startStopChecked") as HTMLInputElement;
    startStopChecked.checked = false;
}

fnNames.click_pauseCurrentTracking = () => {
    
}

fnNames.write_selectDates = () => {
    const dateInputs = document.querySelectorAll(".timeDates") as NodeListOf<HTMLInputElement>;
    const startDate = dateInputs[0].value;
    const stopDate = dateInputs[1].value;

    Url.updateQuery({
        "start": startDate,
        "stop": stopDate,
    });

    updateTable({
        "start": startDate,
        "stop": stopDate,
    });
}

fnNames.click_selectEntries = async (e) => {
    const el = e.currentTarget;
    const value = el.dataset.value;
    const options = {
        "start": "",
        "stop": "",
    };
    const today = new Date().toISOString().slice(0, 10);

    switch (value) {
        case "all":
            break;
        case "today":
            options.start = today;
            options.stop = today;
            break;
        case "week":
            options.start = getFirstDayOfWeek();
            options.stop = today;
            break;
        case "month":
            options.start = getFirstDayOfMonth();
            options.stop = today;
            break;
    }

    Url.updateQuery({
        "start": options.start,
        "stop": options.stop,
    });

    updateTable(options);
}

const updateTable = async (options: any) => {
    const data = await ajax.get(`/api/v1/time-tracking/current-user`, options);
    const table = document.getElementById("timeTrackingTable");
    clearRows(table);
    const columnConfig = {
        "hide": ["id"],
        "hideOptions": ["addRow", "check", "move", "add"],
    };
    data.data.forEach((row: any) => {
        addRow(row, table, columnConfig);
    });
}

const getFirstDayOfWeek = (date = new Date()) => {
    const dayOfWeek = date.getDay();
    const firstDay = new Date(date);
    firstDay.setDate(date.getDate() - dayOfWeek + (dayOfWeek === 0 ? -6 : 1));
    return firstDay.toISOString().split('T')[0];
}

const getFirstDayOfMonth = (date = new Date()) => {
    const day = new Date(date.getFullYear(), date.getMonth(), 1);
    return day.toISOString().split("T")[0];
}

const storeTimestamp = (stamp: string) => {
    let time = new Date().getTime().toString();
    localStorage.setItem(stamp, time);
}

const getTimeTrackingEntries = async () => {
    const data = await ajax.get(`/api/v1/time-tracking/current-user`);
    const headers = [
        {
            "key": "start",
            "label": "Beginn",
        },
        {
            "key": "stop",
            "label": "Ende",
        },
        {
            "key": "time",
            "label": "Zeit",
        },
        {
            "key": "date",
            "label": "Datum",
        },
        {
            "key": "task",
            "label": "Aufgabe",
        },
        {
            "key": "edit",
            "label": "Bearbeitungsnotiz",
        },
    ];
    const options = {
        "hide": ["id"],
        "hideOptions": ["addRow", "check", "move", "add"],
    };
    const timeTrackingTable = renderTable("timeTrackingTable", headers, data.data, options);

    if (!timeTrackingTable) {
        return;
    }

    timeTrackingTable.addEventListener("rowDelete", async (event: any) => {
        const data = event.detail;
        const id = data.id;

        const status = await ajax.delete(`/api/v1/time-tracking/${id}`);
        if (status.data.message == "OK") {
            data.row.remove();
        }
    });
}

loader(init);
