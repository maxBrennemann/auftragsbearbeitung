import {AjaxCall, ajax} from "./classes/ajax.js";

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
        switchTimeTracking.addEventListener("change", () => {
            ajax.put("/api/v1/settings/global-timetracking").then(r => {
                if (r.message == "ok") {
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

    const addCategoryBtn = document.getElementById("addCategory");
    addCategoryBtn.addEventListener("click", addCategory);

    getCategories();
}

function getCategories() {
    ajax.get("/api/v1/category").then(categories => {
        const select = document.getElementById("categories");
        const select2 = document.getElementById("parentCategory");
        Object.keys(categories).forEach(key => {
            const category = categories[key];
            const option = document.createElement("option");
            option.value = category.id;
            option.text = category.name;
            select.appendChild(option);
            select2.appendChild(option.cloneNode(true));
        });
    });
}

function addCategory() {
    const newCategory = document.getElementById("newCategory").value;
    const parentCategory = document.getElementById("parentCategory").value;

    if (newCategory === "") {
        infoSaveSuccessfull("error");
        return;
    }

    ajax.post(`/api/v1/category`, {
        name: newCategory,
        parent: parentCategory,
    }).then(r => {
        clearInputs({"id": "newCategory"});
        
        if (r.status !== "success") {
            infoSaveSuccessfull("error");
            return;
        }
        
        infoSaveSuccessfull("success");
    });
}

function editCategory() {
    const category = document.getElementById("category").value;
    const newName = document.getElementById("newName").value;
    const newParent = document.getElementById("newParent").value;

    ajax.put(`/api/v1/category/${category}`, {
        name: newName,
        parent: newParent,
    }).then(r => {
        if (r.message == "ok") {
            infoSaveSuccessfull("success");
        }
    });
}

window.deleteCache = deleteCache;
window.setCustomColor = setCustomColor;
window.toggleCache = toggleCache;
window.toggleMinify = toggleMinify;
window.minifyFiles = minifyFiles;

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
