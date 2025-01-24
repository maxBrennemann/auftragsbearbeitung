import { initBindings } from "./classes/bindings.js";
import { addRow, renderTable } from "./classes/table_new.js";
import { timeGlobalListener } from "./classes/timetracking.js";

const fnNames = {};

var started = false;
var interval = null;

const init = () => {
    initBindings(fnNames);

    if (localStorage.getItem("startTime")) {
        started = true;
        document.getElementById("updateStartStopName").innerHTML = "stoppen";
        document.getElementById("startStopChecked").checked = true;
    }

    const getTask = document.getElementById("getTask");
    getTask.addEventListener("keydown", e => {
        if (e.key == "Enter") {
            fnNames.click_sendTimeTracking();
        }
    })

    getTimeTrackingEntries();
}

fnNames.click_startStopTime = () => {
    started = !started;
    switch (started) {
        case true:
            storeTimestamp("startTime");
            document.getElementById("updateStartStopName").innerHTML = "stoppen";
            timeGlobalListener();
        break;
        case false:
            const askTask = document.getElementById("askTask");
            askTask.classList.add("flex");
            askTask.classList.remove("hidden");
            document.getElementById("getTask").focus();
        break;
    }
}

fnNames.click_sendTimeTracking = () => {
    const task = document.getElementById("getTask").value;

    ajax.post(`/api/v1/time-tracking/add`, {
        task: task,
        start: localStorage.getItem("startTime"),
        stop: new Date().getTime().toString(),
    }).then(response => {
        const table = document.querySelector("table");
        const options = {
            "hide": ["id"],
            "hideOptions": ["addRow", "check"],
        };
        addRow(response, table, options);

        localStorage.clear("startTime");
        document.getElementById("updateStartStopName").innerHTML = "starten";

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
        "hideOptions": ["addRow", "check"],
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

if (document.readyState !== 'loading' ) {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
