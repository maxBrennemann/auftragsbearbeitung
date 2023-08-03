if (document.readyState !== 'loading' ) {
    initEventListeners();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initEventListeners();
    });
}

function initEventListeners() {
    const switchTimeTracking = document.getElementById("showTimeTracking");
    if (switchTimeTracking != null) {
        switchTimeTracking.addEventListener("change", (e) => {
            const send = new AjaxCall({
                getReason: "toggleShowTime",
            }, "POST", window.location.href);
            send.makeAjaxCall(response => {
                response = JSON.parse(response);

                if (response.status == "success") {
                    infoSaveSuccessfull("success");
                }
            });
        });
    }

    const clearFiles = document.getElementById("clearFiles");
    clearFiles.addEventListener("click", () => {
        const send = new AjaxCall({
            getReason: "clearFiles",
        }, "POST", window.location.href);
        send.makeAjaxCall(response => {});
    });

    const adjustFiles = document.getElementById("adjustFiles");
    adjustFiles.addEventListener("click", () => {
        const send = new AjaxCall({
            getReason: "adjustFiles",
        }, "POST", window.location.href);
        send.makeAjaxCall(response => {});
    });

    const setDefaultWage = document.getElementById("defaultWage");
    setDefaultWage.addEventListener("change", e => {
        const wage = e.target.value;
        ajax.post({
            defaultWage: wage,
            r: "updateDefaultWage",
        });
    });

    const addDocs = document.getElementById("addDocs");
    addDocs.addEventListener("click", () => {
        ajax.post({
            r: "indexAll",
        });
    });

    const test = document.getElementById("test");
    test.addEventListener("click", () => {
        ajax.post({
            r: "testsearch",
        });
    });

    const deleteCacheBtn = document.getElementById("deleteCache");
    deleteCacheBtn.addEventListener("click", deleteCache);
}

function deleteCache() {
    ajax.post({
        r: "deleteCache",
    }).then(r => {
        if (r.status == "success") {
            infoSaveSuccessfull("success");
        }
    });
}

function setCustomColor(value) {
    let color = value == 0 ? "" : cp.color;
    let type = document.querySelector("select")
    type = type.options[type.selectedIndex].value;

    /* ajax parameter */
    let params = {
        getReason: "setCustomColor",
        type: type,
        color: color
    };

    var add = new AjaxCall(params, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        if (response == "ok") {
            location.reload();
        }
    });
}

function toggleCache(status) {
    /* ajax parameter */
    let params = {
        getReason: "toggleCache",
        status: status
    };

    var toggle = new AjaxCall(params, "POST", window.location.href);
    toggle.makeAjaxCall(function (response) {
        console.log(response);
        if (response == "ok") {
            infoSaveSuccessfull("success");
        }
    });
}

function toggleMinify(status) {
    /* ajax parameter */
    let params = {
        getReason: "toggleMinify",
        status: status
    };

    var toggle = new AjaxCall(params, "POST", window.location.href);
    toggle.makeAjaxCall(function (response) {
        console.log(response);
        if (response == "ok") {
            infoSaveSuccessfull("success");
        }
    });
}

function minifyFiles() {
    ajax.post({
        r: "minifyFiles",
    }).then(r => {
        if (r.status == "success") {
            infoSaveSuccessfull("success");
        }
    });
}

if (document.readyState !== 'loading' ) {
    document.getElementById("download_db").addEventListener("click", getFileName, false);
} else {
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById("download_db").addEventListener("click", getFileName, false);
    }, false);
}

function getFileName() {
    document.getElementById("download_db").removeEventListener("click", getFileName);

    /* ajax parameter */
    let params = {
        getReason: "getBackup"
    };

    var backup = new AjaxCall(params, "POST", window.location.href);
    backup.makeAjaxCall(function (response) {
        response = JSON.parse(response);
        document.getElementById("download_db").download = response.fileName;
        document.getElementById("download_db").href = response.url;
        document.getElementById("download_db").click();

        if (response.status == "ok") {
            infoSaveSuccessfull("success");
        }
    });
}
