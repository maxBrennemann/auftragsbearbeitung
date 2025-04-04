import { initBindings } from "./classes/bindings.js";
import { ajax } from "./classes/ajax.js";
import { getTable } from "./classes/table.js";
import { renderTable } from "./classes/table_new.js";
import { loadFromLocalStorage, saveToLocalStorage } from "./global.js";
import { tableConfig } from "./tableconfig.js";

const fnNames = {
    click_createFbExport: click_createFbExport,
    click_openTagOverview: openTagOverview,
    click_manageImports: click_manageImports,
    click_crawlAll: crawlAll,
    click_crawlTags: crawlTags,
    click_createNewSticker: createNewSticker,
};

const tableOrder = {
    orderBy: "id",
    order: "asc",
}

function init() {
    initBindings(fnNames);

    const tblOrder = loadFromLocalStorage("stickerOverviewTableOrder");
    if (tblOrder) {
        tableOrder.orderBy = tblOrder.orderBy;
        tableOrder.order = tblOrder.order;
    }

    //createStickerTable(tableOrder);
    createStickerTable2();
    checkIfOverview();

    const newTitle = document.getElementById("newTitle");
    newTitle.addEventListener("keyup", function (event) {
        if (event.key !== "Enter") {
            return;
        }

        createNewSticker();
    });
}

const createStickerTable2 = async () => {
    const data = await ajax.get(`/api/v1/sticker/overview`);
    const config = tableConfig["module_sticker_sticker_data"];
    const headers = config.columns;
    const options = {};
    renderTable("stickerTable", headers, data, options);
    showStickerStatus();
}

const createStickerTable = async (tblOrder) => {
    const data = await ajax.get(`/api/v1/sticker/overview`, {
        orderBy: tblOrder.orderBy,
        order: tblOrder.order,
    });
    const tableConfig = {
        config: [
            {
                "name": "id",
                "title": "Nummer",
            },
            {
                "name": "name",
                "title": "Name",
            },
            {
                "name": "directory_name",
                "title": "Verzeichnis",
                "css": ["w-96", "overflow-x-hidden", "text-ellipsis"],
            },
            {
                "name": "is_plotted",
                "title": "geplottet",
            },
            {
                "name": "is_short_time",
                "title": "Werbeaufkleber",
            },
            {
                "name": "is_long_time",
                "title": "Hochleistungsfolie",
            },
            {
                "name": "is_multipart",
                "title": "mehrteilig",
            },
            {
                "name": "is_walldecal",
                "title": "Wandtattoo",
            },
            {
                "name": "is_shirtcollection",
                "title": "Textil",
            },
            {
                "name": "is_revised",
                "title": "Überarbeitet",
            },
            {
                "name": "is_marked",
                "title": "Gemerkt",
            },
        ],
        rows: data.sticker,
        tableCss: ["w-full", "table-auto"],
        callback: tblHeaderClicked,
        link: "/sticker?id=",
    }
    const table = getTable(tableConfig);
    const tableContainer = document.getElementById("stickerTable");
    tableContainer.innerHTML = "";
    tableContainer.appendChild(table);

    showStickerStatus();
}

const tblHeaderClicked = (col) => {
    if (tableOrder.orderBy === col) {
        tableOrder.order = tableOrder.order === "asc" ? "desc" : "asc";
    } else {
        tableOrder.order = "asc";
    }
    tableOrder.orderBy = col;
    createStickerTable(tableOrder);
    saveToLocalStorage("stickerOverviewTableOrder", tableOrder);
};

function click_manageImports() {
    
}

/**
 * makes the table for the sticker-overview page sticky
 */
function checkIfOverview() {
    let overviewTable = document.querySelector('[data-type="module_sticker_sticker_data"]');
    if (overviewTable != null) {
        let trElem = document.getElementsByClassName("tableHead");
        for (let i = 0; i < trElem.length; i++) {
            let tr = trElem[i];
            tr.style.position = "sticky";
            tr.style.top = 0;
        }
    }
}

function crawlAll() {
    ajax.post({
        r: "crawlAll",
    });
}

function crawlTags() {
    ajax.post({
        r: "crawlTags",
    }).then(r => {
        console.log("ready");
    });
}

function showStickerStatus() {
    const overviewTable = document.getElementById("stickerTable").querySelector("tbody");

    ajax.get("/api/v1/sticker/states").then(data => {
        let rows = Array.from(overviewTable.rows);
        rows.forEach(row => {
            let rowIndex = parseInt(row.children[0].textContent);
            let dataElement = data[rowIndex];

            if (dataElement.a) {
                row.children[3].classList.add("inShop");
            }

            if (dataElement.w) {
                row.children[7].classList.add("inShop");
            }

            if (dataElement.t) {
                row.children[8].classList.add("inShop");
            }
        });
    });
}

async function createNewSticker() {
    var title = document.getElementById("newTitle").value;
    if (title.length == 0) {
        return;
    }
    
    ajax.post("/api/v1/sticker", {
        "name": title,
    }).then(r => {
        window.location.href = r.link;
    }).catch(() => {
        alert("an error occured");
    });
}

function click_createFbExport() {
    ajax.post({
        "r": "createFbExport",
    }).then(fbExport => {
        if (fbExport.status !== "successful") {
            return;
        }

        infoSaveSuccessfull("success");

        const a = document.createElement("a");
        a.href = fbExport.file;
        a.download = fbExport.file;

        document.body.appendChild(a);
        a.click();

        console.log(fbExport.errorList);
    });
}

function openTagOverview() {
    ajax.post({
        r: "getTagOverview",
    }).then(r => {
        const tags = r.tags;
        const div = document.createElement("div");
        div.classList.add("absolute", "bg-white", "border", "border-black", "p-2", "rounded-lg", "shadow-lg", "z-20");
        document.body.appendChild(div);

        tags.forEach(tag => {
            const tagElement = document.createElement("div");
            tagElement.classList.add("rounded-lg", "inline-block");
            tagElement.textContent = tag.content;
            div.appendChild(tagElement);
        });

        centerAbsoluteElement(div);
    });
}

if (document.readyState !== 'loading' ) {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
