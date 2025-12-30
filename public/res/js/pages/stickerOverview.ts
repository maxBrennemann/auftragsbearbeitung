//@ts-nocheck

import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings"
import { notification } from "js-classes/notifications";

import { renderTable } from "../classes/table";
import { tableConfig } from "../classes/tableconfig";
import { createPopup } from "../classes/helpers";

const fnNames = {};

const init = () => {
    addBindings(fnNames);
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
    const response = await ajax.get(`/api/v1/sticker/overview`);
    const data = response.data;
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
            "thead": {
                "className": ["sticky", "top-0"],
            }
        },
        "primaryKey": "id",
        "autoSort": true,
        "link": "/sticker?id=",
    };
    renderTable("stickerTable", headers, data, options);
    showStickerStatus();
}

fnNames.click_manageImports = () => { }

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
            let dataElement = data.data[rowIndex];

            if (dataElement.a) {
                row.children[3].classList.add("bg-lime-500");
            }

            if (dataElement.w) {
                row.children[7].classList.add("bg-lime-500");
            }

            if (dataElement.t) {
                row.children[8].classList.add("bg-lime-500");
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
        window.location.href = r.data.link;
    }).catch(() => {
        alert("an error occured");
    });
}

fnNames.click_createFbExport = () => {
    ajax.post(`/api/v1/sticker/export/facebook`).then(fbExport => {
        if (fbExport.data.status !== "successful") {
            return;
        }

        notification("", "success");

        const a = document.createElement("a");
        a.href = fbExport.data.file;
        a.download = fbExport.data.file;

        document.body.appendChild(a);
        a.click();

        console.log(fbExport.data.errorList);
    });
}

fnNames.click_openTagOverview = () => {
    ajax.get(`/api/v1/sticker/tags/overview`).then(tags => {
        const div = document.createElement("div");
        div.classList.add("h-[34rem]", "overflow-y-scroll");

        const p = document.createElement("p");
        p.innerHTML = "Tagübersicht";
        p.classList.add("font-semibold");
        div.appendChild(p);

        tags.data.forEach(tag => {
            const tagElement = document.createElement("div");
            tagElement.classList.add("btn-inactive");
            tagElement.textContent = `${tag.content} (${tag.tagCount})`;
            div.appendChild(tagElement);
        });

        createPopup(div);
    });
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
