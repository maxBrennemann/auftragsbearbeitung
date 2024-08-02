import { initBindings } from "./classes/bindings.js";

const fnNames = {};
fnNames.click_createFbExport = click_createFbExport;
fnNames.click_openTagOverview = openTagOverview;
fnNames.click_loadSticker = click_loadSticker;
fnNames.click_manageImports = click_manageImports;
fnNames.click_crawlAll = crawlAll;
fnNames.click_crawlTags = crawlTags;
fnNames.click_createNewSticker = createNewSticker;

if (document.readyState !== 'loading' ) {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}

function init() {
    initBindings(fnNames);
    checkIfOverview();
    showStickerStatus();

    const newTitle = document.getElementById("newTitle");
    newTitle.addEventListener("keyup", function (event) {
        if (event.key !== "Enter") {
            return;
        }

        createNewSticker();
    });
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
    let overviewTable = document.querySelector('[data-type="module_sticker_sticker_data"]');
    if (overviewTable == null) {
        return;
    }

    ajax.post({
        "r": "getStickerStatus",
    }).then(data => {
        let rows = Array.from(overviewTable.rows);
        rows = rows.slice(1);
        rows.forEach(row => {
            rowIndex = parseInt(row.children[0].textContent);
            dataElement = data[rowIndex];

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
    }).then(redirectLink => {
        if (redirectLink == "-1") {
            alert("an error occured");
        } else {
            window.location.href = redirectLink;
        }
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
