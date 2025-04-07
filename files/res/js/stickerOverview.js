import { initBindings } from "./classes/bindings.js";
import { ajax } from "./classes/ajax.js";
import { renderTable } from "./classes/table.js";
import { tableConfig } from "./tableconfig.js";

const fnNames = {};

const init = () => {
    initBindings(fnNames);
    createStickerTable();

    const newTitle = document.getElementById("newTitle");
    newTitle.addEventListener("keyup", function (event) {
        if (event.key !== "Enter") {
            return;
        }

        fnNames.click_createNewSticker();
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
    stickHeader();
}

fnNames.click_manageImports = () => { }

const stickHeader = () => {
    const overviewTable = document.querySelector("#stickerTable table");
    if (overviewTable == null) {
        return;
    }
    
    const trElem = overviewTable.querySelectorAll("th");
    Array.from(trElem).forEach(th => {
        th.classList.add("sticky", "top-0");
    });
}

fnNames.click_crawlAll = () => {
    ajax.post(`/api/v1/sticker/crawl/all`);
}

fnNames.click_crawlTags = () => {
    ajax.post(`/api/v1/sticker/tags/crawl`).then(r => {
        console.log("ready");
    });
}

const showStickerStatus = () => {
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

fnNames.click_createNewSticker = async () => {
    const title = document.getElementById("newTitle").value;
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

fnNames.click_createFbExport = () => {
    ajax.post(`/api/v1/sticker/export/facebook`).then(fbExport => {
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

fnNames.click_openTagOverview = () => {
    ajax.get(`/api/v1/sticker/tags/overview`).then(tags => {
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

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
