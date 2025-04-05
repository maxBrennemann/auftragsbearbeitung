import { initBindings } from "./classes/bindings.js";
import { ajax } from "./classes/ajax.js";
import { renderTable } from "./classes/table_new.js";
import { tableConfig } from "./tableconfig.js";

const fnNames = {
    click_createFbExport: click_createFbExport,
    click_openTagOverview: openTagOverview,
    click_manageImports: click_manageImports,
    click_crawlAll: crawlAll,
    click_crawlTags: crawlTags,
    click_createNewSticker: createNewSticker,
};

function init() {
    initBindings(fnNames);
    createStickerTable();
    checkIfOverview();

    const newTitle = document.getElementById("newTitle");
    newTitle.addEventListener("keyup", function (event) {
        if (event.key !== "Enter") {
            return;
        }

        createNewSticker();
    });
}

const createStickerTable = async () => {
    const data = await ajax.get(`/api/v1/sticker/overview`);
    const config = tableConfig["module_sticker_sticker_data"];
    const headers = config.columns;
    const options = {
        "hideOptions": ["all"],
        "hide": [
            "category",
            "is_colorable",
            "is_customizable",
            "is_for_configurator",
            "price_class",
            "size_summary",
            "creation_date",
            "additional_info",
            "additional_data",
        ],
        "styles": {
            "table": {
                "className": ["w-full", "table-auto"],
            },
            "key": {
                "directory_name": ["w-96", "overflow-x-hidden", "text-ellipsis"],
            },
        },
        "primaryKey": "id",
        "autoSort": true,
        "link": "/sticker?id=",
    };
    renderTable("stickerTable", headers, data, options);
    showStickerStatus();
}

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
