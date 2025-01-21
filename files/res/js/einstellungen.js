import { ajax } from "./classes/ajax.js";
import { createHeader, createTable, addRow } from "./classes/table_new.js";
import { tableConfig } from "./js/tableconfig.js";

function initEventListeners() {
    timeTracking();
    
    const clearFiles = document.getElementById("clearFiles");
    clearFiles.addEventListener("click", () => {
        ajax.post(`/api/v1/upload/clear-files`);
    });

    const adjustFiles = document.getElementById("adjustFiles");
    adjustFiles.addEventListener("click", () => {
        ajax.post(`/api/v1/upload/adjust-files`);
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

    ajax.put(`/api/v1/settings/color`, {
        "type": type,
        "color": color
    }).then(r => {
        if (r.status == "success") {
            location.reload();
        }
    });
}

function toggleCache(status) {
    ajax.put(`/api/v1/settings/cache`, {
        "status": status,
    }).then(r => {
        if (r.status == "success") {
            infoSaveSuccessfull("success");
        }
    });
}

function toggleMinify(status) {
    ajax.put(`/api/v1/settings/cache`, {
        "status": status,
    }).then(r => {
        if (r.status == "success") {
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
    const table = createTable("orderTypes", {
        "styles": {
            "table": {
                "className": "w-full",
            },
        },
    });
    const config = tableConfig["auftragstyp"];
    createHeader(config.columns, table);

    const data = await ajax.get(`/api/v1/tables/auftragstyp`);

    data.forEach(row => {
        addRow(row, table, {
            "hideOptions": ["delete", "check"],
        });
    });
}

const createWholesalerTable = async () => {
    const table = createTable("wholesalerTypes", {
        "styles": {
            "table": {
                "className": "w-full",
            },
        },
    });
    const config = tableConfig["einkauf"];
    createHeader(config.columns, table);

    const data = await ajax.get(`/api/v1/tables/einkauf`);

    data.forEach(row => {
        addRow(row, table, {
            "hideOptions": ["delete", "check"],
        });
    });
}

const createUserTable = async () => {
    const table = createTable("userTable", {
        "styles": {
            "table": {
                "className": "w-full",
            },
        },
    });
    const config = tableConfig["user"];
    const columnConfig = {
        "hideOptions": ["all"],
    };
    createHeader(config.columns, table, columnConfig);

    const data = await ajax.get(`/api/v1/tables/user`);

    data.forEach(row => {
        addRow(row, table, columnConfig);
    });
}

if (document.readyState !== 'loading' ) {
    initEventListeners();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initEventListeners();
    });
}
