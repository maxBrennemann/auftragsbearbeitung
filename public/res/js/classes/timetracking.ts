var globalTimerInterval: number | undefined;

export const timeGlobalListener = () => {
    const displayTime = document.getElementById("timeGlobal") as HTMLElement;
    if (!displayTime) return;

    const start = localStorage.getItem("startTime");
    if (start) {
        if (globalTimerInterval !== undefined) clearInterval(globalTimerInterval);

        globalTimerInterval = window.setInterval(() => countTimeGlobal(displayTime), 1000);
        countTimeGlobal(displayTime);
    }
}

const countTimeGlobal = (displayTime: HTMLElement) => {
    const startRaw = localStorage.getItem("startTime");
    if (!startRaw) {
        if (globalTimerInterval !== undefined) clearInterval(globalTimerInterval);
        globalTimerInterval = undefined;
        displayTime.textContent = "00:00:00";
        return;
    }

    const curr = Date.now();
    const startTime = Number(startRaw);

    const diffMs = curr - startTime;

    const totalSec = Math.floor(diffMs / 1000);
    const hou = Math.floor(totalSec / 3600);
    const min = Math.floor((totalSec % 3600) / 60);
    const sec = totalSec % 60;

    displayTime.textContent = `${pad(hou)}:${pad(min)}:${pad(sec)}`;
};

const pad = (num: number) => {
    return ('00' + num).slice(-2);
}
