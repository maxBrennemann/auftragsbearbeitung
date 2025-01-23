import { initBindings } from "./classes/bindings.js";

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
}

fnNames.click_startStopTime = () => {
    started = !started;
    switch (started) {
        case true:
            storeTimestamp("startTime");
            document.getElementById("updateStartStopName").innerHTML = "stoppen";
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
        const row = table.insertRow(1);
        const cell1 = row.insertCell(0);
        const cell2 = row.insertCell(1);
        const cell3 = row.insertCell(2);
        const cell4 = row.insertCell(3);
        const cell5 = row.insertCell(4);

        cell1.innerHTML = response.start;
        cell2.innerHTML = response.stop;
        cell3.innerHTML = response.durationMs;
        cell4.innerHTML = response.task;

        localStorage.clear("startTime");
        document.getElementById("updateStartStopName").innerHTML = "starten";

        const askTask = document.getElementById("askTask");
        askTask.classList.add("flex");
        askTask.classList.remove("hidden");
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

const getTimeTrackingEntries = () => {
    
}

if (document.readyState !== 'loading' ) {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
