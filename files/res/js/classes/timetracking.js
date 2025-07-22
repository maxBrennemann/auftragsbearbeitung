//@ts-nocheck

var globalTimerInterval;
export const timeGlobalListener = () => {
	const displayTime = document.getElementById("timeGlobal");
	if (displayTime != null) {
		const start = localStorage.getItem("startTime");
		if (start != null) {
			globalTimerInterval = setInterval(countTimeGlobal, 1000);
		}
	}
}

const countTimeGlobal = () => {
    let curr = new Date().getTime().toString();
    let startTime = parseInt(localStorage.getItem("startTime"));

    if (localStorage.getItem("startTime") == null) {
        clearInterval(globalTimerInterval);
        const displayTime = document.getElementById("timeGlobal");
        displayTime.innerHTML = "00:00:00";
        return;
    }

    let diff = curr - startTime;

    let sec = Math.floor(diff / 1000);
    let hou = Math.floor(sec / 60 / 60);
    sec = sec - hou * 60 * 60;
    let min = Math.floor(sec / 60);
    sec = sec - min * 60;

	const displayTime = document.getElementById("timeGlobal");
    displayTime.innerHTML = `${pad(hou)}:${pad(min)}:${pad(sec)}`;
}

const pad = (num) => {
    return ('00' + num).slice(-2);
}
