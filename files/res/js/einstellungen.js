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
