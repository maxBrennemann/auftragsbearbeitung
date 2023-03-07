var started = false;
var interval = null;
var startTime = null;
var stopTime = null;

if (document.readyState !== 'loading' ) {
    code = document.querySelector("code");
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        code = document.querySelector("code");
        init();
    });
}

function init() {
    let bindings = document.querySelectorAll('[data-binding]');
    [].forEach.call(bindings, function(el) {
        var fun_name = "click_" + el.id;
        el.addEventListener("click", function() {
            var fun = window[fun_name];
            if (typeof fun === "function") {
                fun();
            }
        }.bind(fun_name), false);
    });

    if (localStorage.getItem("startTime")) {
        started = true;
        interval = setInterval(countTime, 1000);
        document.getElementById("updateStartStopName").innerHTML = "stoppen";
        document.getElementById("startStopChecked").checked = true;
    }
}

function click_startStopTime() {
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
            document.getElementById("askTask").style.display = "inline";
        break;
    }
}

async function click_sendTimeTracking() {
    var task = document.getElementById("getTask").value;
    var table = await send({task:task}, "sendTimeTracking");
    document.getElementById("showTaskTable").innerHTML = table;
    localStorage.clear("startTime");
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

function send(data, intent) {
    data.getReason = intent;

    /* temporarily copied here */
    let temp = "";
    for (let key in data) {
        temp += key + "=" + data[key] + "&";
    }

    paramString = temp.slice(0, -1);

    var response = makeAsyncCall("POST", paramString, "").then(result => {
        return result;
    });

    return response;
}