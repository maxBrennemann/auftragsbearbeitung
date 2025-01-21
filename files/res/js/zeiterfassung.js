import { initBindings } from "./classes/bindings.js";

const fnNames = {};

var started = false;
var interval = null;
var startTime = null;
var stopTime = null;

function init() {
    initBindings(fnNames);

    if (localStorage.getItem("startTime")) {
        started = true;
        interval = setInterval(countTime, 1000);
        document.getElementById("updateStartStopName").innerHTML = "stoppen";
        document.getElementById("startStopChecked").checked = true;
    }

    initTimeTracking();
}

/**
 * shows the time tracking status and adds a listenre to the checkbox
 */
function initTimeTracking() {
    const status = document.getElementById("statusTimeTracking");
    const input = document.getElementById("inputTimeTracking");

    if (input.checked) {
        status.innerHTML = "Zeiterfassung stoppen";
    } else {
        status.innerHTML = "Zeiterfassung starten";
    }

    input.addEventListener("change", () => {
        if (input.checked) {
            status.innerHTML = "Zeiterfassung stoppen";
        } else {
            status.innerHTML = "Zeiterfassung starten";
        }
        setTimeTracking();
    });
}

/**
 * 
 */
function setTimeTracking() {

}

fnNames.click_startStopTime = () => {
    started = !started;
    switch (started) {
        case true:
            storeTimestamp("startTime");
            interval = setInterval(countTime, 1000);
            document.getElementById("updateStartStopName").innerHTML = "stoppen";
        break;
        case false:
            clearInterval(interval);
            document.getElementById("updateStartStopName").innerHTML = "starten";
            document.getElementById("askTask").style.display = "block";
        break;
    }
}

/**
 * 
 */
fnNames.click_sendTimeTracking = () => {
    var task = document.getElementById("getTask").value;

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
    });
}

function countTime() {
    let curr = new Date().getTime().toString();
    let startTime = parseInt(localStorage.getItem("startTime"));

    let diff = curr - startTime;

    let sec = Math.floor(diff / 1000);
    let hou = Math.floor(sec / 60 / 60);
    sec = sec - hou * 60 * 60;
    let min = Math.floor(sec / 60);
    sec = sec - min * 60;

    document.getElementById("timer").innerHTML = `${this.pad(hou)}:${this.pad(min)}:${this.pad(sec)}`;
}

function storeTimestamp(stamp) {
    let time = new Date().getTime().toString();
    localStorage.setItem(stamp, time);
}

function pad(num) {
    return ('00' + num).slice(-2);
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
