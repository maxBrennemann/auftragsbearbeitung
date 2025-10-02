import { ajax } from "js-classes/ajax.js";
import { addBindings } from "js-classes/bindings.js"

import { addRow, clearRows, renderTable } from "./classes/table.js";
import { timeGlobalListener } from "./classes/timetracking.js";

const fnNames = {};

var started = false;

const init = () => {
    addBindings(fnNames);

    if (localStorage.getItem("startTime")) {
        started = true;
        document.getElementById("updateStartStopName").innerHTML = "stoppen";
        document.getElementById("startStopChecked").checked = true;
        toggleIsPausable(true);
    }

    const getTask = document.getElementById("getTask");
    getTask.addEventListener("keydown", e => {
        if (e.key == "Enter") {
            fnNames.click_sendTimeTracking();
        }
    });

    getTimeTrackingEntries();
}

const toggleIsPausable = (status) => {
    const isPausable = document.getElementById("pauseCurrentTracking");
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
            document.getElementById("updateStartStopName").innerHTML = "stoppen";
            timeGlobalListener();
            toggleIsPausable(true);
            break;
        case false:
            const askTask = document.getElementById("askTask");
            askTask.classList.add("flex");
            askTask.classList.remove("hidden");
            document.getElementById("getTask").focus();
            toggleIsPausable(false);
            break;
    }
}

fnNames.click_sendTimeTracking = () => {
    const task = document.getElementById("getTask");

    ajax.post(`/api/v1/time-tracking/add`, {
        task: task.value,
        start: localStorage.getItem("startTime"),
        stop: new Date().getTime().toString(),
    }).then(response => {
        const table = document.querySelector("table");
        const options = {
            "hide": ["id"],
            "hideOptions": ["addRow", "check", "move", "add"],
        };
        addRow(response, table, options);

        localStorage.clear("startTime");
        document.getElementById("updateStartStopName").innerHTML = "starten";

        task.value = "";

        const askTask = document.getElementById("askTask");
        askTask.classList.remove("flex");
        askTask.classList.add("hidden");
    });
}

fnNames.click_cancelTimeTracking = () => {
    const askTask = document.getElementById("askTask");
    askTask.classList.remove("flex");
    askTask.classList.add("hidden");
}

fnNames.click_cancelCurrentTracking = () => {
    if (!(confirm("Willst du die aktuelle Erfassung abbrechen?"))) {
        return;
    }
    localStorage.clear("startTime");
    const startStopChecked = document.getElementById("startStopChecked");
    startStopChecked.checked = false;
}

fnNames.click_pauseCurrentTracking = () => {
    
}

fnNames.click_selectEntries = async (e) => {
    const el = e.currentTarget;
    const value = el.dataset.value;
    const options = {};
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

    const data = await ajax.get(`/api/v1/time-tracking/current-user`, options);
    const table = document.getElementById("timeTrackingTable");
    clearRows(table);
    const columnConfig = {
        "hide": ["id"],
        "hideOptions": ["addRow", "check", "move", "add"],
    };
    data.forEach(row => {
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

const storeTimestamp = (stamp) => {
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
    renderTable("timeTrackingTable", headers, data, options);
    document.getElementById("timeTrackingTable").addEventListener("rowDelete", async (event) => {
        const data = event.detail;
        const id = data.id;

        const status = await ajax.delete(`/api/v1/time-tracking/${id}`);
        if (status.message == "OK") {
            data.row.remove();
        }
    });
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
