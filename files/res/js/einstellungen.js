import { ajax } from "./classes/ajax.js";
import { initBindings } from "./classes/bindings.js";
import { createHeader, createTable, addRow, fetchAndRenderTable } from "./classes/table.js";
import { tableConfig } from "./js/tableconfig.js";

const fnNames = {};

function initEventListeners() {
    initBindings(fnNames);
    timeTracking();
    
    const clearFiles = document.getElementById("clearFiles");
    clearFiles.addEventListener("click", () => {
        ajax.post(`/api/v1/upload/clear-files`);
    });

    const adjustFiles = document.getElementById("adjustFiles");
    adjustFiles.addEventListener("click", () => {
        ajax.post(`/api/v1/upload/adjust-files`);
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

    const addCategoryBtn = document.getElementById("addCategory");
    addCategoryBtn.addEventListener("click", addCategory);

    getCategories();
    showTree();

    createOrderTypeTable();
    createWholesalerTable();
    createUserTable();
}

const timeTracking = () => {
    const switchTimeTracking = document.getElementById("showTimeTracking");
    if (switchTimeTracking == null) {
        return;
    }
    
    switchTimeTracking.addEventListener("change", () => {
        ajax.put("/api/v1/settings/global-timetracking").then(r => {
            if (r.status == "success") {
                infoSaveSuccessfull("success");
                const el = document.getElementById("timeTrackingContainer");
                if (r.display == "false") {
                    el.classList.add("hidden");
                    el.classList.remove("inline-flex");
                } else {
                    el.classList.remove("hidden");
                    el.classList.add("inline-flex");
                }
            } else {
                infoSaveSuccessfull("failiure")
            }
        });
    });
}

function getCategories() {
    ajax.get("/api/v1/category").then(categories => {
        const select = document.getElementById("parentCategory");
        Object.keys(categories).forEach(key => {
            const category = categories[key];
            const option = document.createElement("option");
            option.value = category.id;
            option.text = category.name;
            select.appendChild(option);
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

function showTree() {
    ajax.get("/api/v1/category/tree").then(categories => {
        const tree = document.getElementById("categoryTree");
        tree.innerHTML = "";

        createCategoryTree(tree, categories);
    });
}

function createCategoryTree(anchor, categories) {
    const ul = document.createElement("ul");
    ul.classList.add("pl-2", "list-disc");
    anchor.appendChild(ul);

    categories.forEach(category => {
        const li = document.createElement("li");
        li.innerHTML =  `
            <span class="cursor-pointer" data-id="${category.id}">${category.name}</span>
            <button class="btn-primary">Bearbeiten</button>`;

        li.classList.add("cursor-pointer");
        li.dataset.id = category.id;
        ul.appendChild(li);

        createCategoryTree(ul, category.children);
    });
}

fnNames.write_toggleCache = (e) => {
    const el = e.currentTarget;
    const value = el.dataset.value;

    ajax.put(`/api/v1/settings/cache`, {
        "status": value,
    }).then(r => {
        if (r.status == "success") {
            infoSaveSuccessfull("success");
        }
    });
}

fnNames.write_toggleMinify = (e) => {
    const el = e.currentTarget;
    const value = el.dataset.value;

    ajax.put(`/api/v1/settings/minify`, {
        "status": value,
    }).then(r => {
        if (r.status == "success") {
            infoSaveSuccessfull("success");
        }
    });
}

fnNames.click_deleteCache = () => {
    ajax.delete(`/api/v1/settings/cache`).then(r => {
        if (r.status == "success") {
            infoSaveSuccessfull("success");
        }
    });
}

fnNames.click_minifyFiles = () => {
    ajax.post(`/api/v1/settings/minify`).then(r => {
        if (r.status == "success") {
            infoSaveSuccessfull("success");
        }
    });
}

fnNames.write_changeSetting = e => {
    const target = e.currentTarget;
    const value = target.value;
    const setting = target.dataset.setting;

    ajax.put(`/api/v1/settings/config/${setting}`, {
        "value": value,
    }).then(r => {
        if (r.status == "success") {
            infoSaveSuccessfull("success");
        } else {
            infoSaveSuccessfull("failure");
        }
    });
}

window.setCustomColor = setCustomColor;

function setCustomColor(value) {
    let color = value == 0 ? "" : cp.color;
    let type = document.querySelector("select")
    type = type.options[type.selectedIndex].value;

    ajax.put(`/api/v1/settings/color`, {
        "type": type,
        "color": color
    }).then(r => {
        if (r.status == "success") {
            location.reload();
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

    ajax.post(`/api/v1/settings/bakckup`).then(r => {
        document.getElementById("download_db").download = r.fileName;
        document.getElementById("download_db").href = r.url;
        document.getElementById("download_db").click();

        if (r.status == "success") {
            infoSaveSuccessfull("success");
        }
    });
}

const createOrderTypeTable = async () => {
    const config = tableConfig["auftragstyp"];
    const options = {
        "hideOptions": ["delete", "check", "move", "add"],
        "primaryKey": config.primaryKey,
        "autoSort": true,
        "styles": {
            "table": {
                "className": ["w-full"],
            },
        },
    };

    const table = await fetchAndRenderTable("orderTypes", "auftragstyp", options);
    table.addEventListener("rowAdd", () => addOrderType(table, options));
}

const createWholesalerTable = async () => {
    const table = createTable("wholesalerTypes", {
        "styles": {
            "table": {
                "className": ["w-full"],
            },
        },
    });
    const config = tableConfig["einkauf"];
    createHeader(config.columns, table);

    const data = await ajax.get(`/api/v1/tables/einkauf`);

    data.forEach(row => {
        addRow(row, table, {
            "hideOptions": ["delete", "check", "move", "add"],
        });
    });
}

const createUserTable = async () => {
    const options = {
        "styles": {
            "table": {
                "className": ["w-full"],
            },
        },
        "primaryKey": "id",
        "hideOptions": ["all"],
        "link": "/mitarbeiter?id=",
    };

    const table = createTable("userTable", options);
    const config = tableConfig["user"];
    createHeader(config.columns, table, options);

    const data = await ajax.get(`/api/v1/tables/user`);
    data.forEach(row => {
        addRow(row, table, options);
    });
}

const addOrderType = async (table, options) => {
    const lastRow = table.querySelector("tbody").lastChild.children;
    const data = {};
    Array.from(lastRow).forEach(el => {
        const key = el.dataset.key;
        if (key == undefined) {
            return;
        }
        const value = el.innerHTML;
        data[key] = value;
    });
    const response = await ajax.post(`/api/v1/tables/auftragstyp`, {
        "conditions": JSON.stringify(data),
    });
    for (var i in response) {
        data[i] = response[i];
    }
    addRow(data, table, options);
}

if (document.readyState !== 'loading' ) {
    initEventListeners();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initEventListeners();
    });
}
