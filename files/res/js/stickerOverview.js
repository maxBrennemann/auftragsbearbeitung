var mainVariables = {};

if (document.readyState !== 'loading' ) {
    initStickerOverview();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initStickerOverview();
    });
}

function initStickerOverview() {
    initBindings();
    checkIfOverview();
    showStickerStatus();
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

function initBindings() {
    let bindings = document.querySelectorAll('[data-binding]');
    [].forEach.call(bindings, function(el) {
        var fun_name = "";
        if (el.dataset.fun) {
            fun_name = "click_" + el.dataset.fun;
        } else {
            fun_name = "click_" + el.id;
        }
        
        el.addEventListener("click", function(e) {
            var fun = window[fun_name];
            if (typeof fun === "function") {
                fun(e);
            } else {
                console.warn("event listener may not be defined or wrong");
            }
        }.bind(fun_name), false);
    });
    let variables = document.querySelectorAll('[data-variable]');
    [].forEach.call(variables, function(v) {
        mainVariables[v.id] = v;
    });
    let autowriter = document.querySelectorAll('[data-write]');
    [].forEach.call(autowriter, function(el) {
        var fun_name = "";
        if (el.dataset.fun) {
            fun_name = "write_" + el.dataset.fun;
        } else {
            fun_name = "write_" + el.id;
        }
        
        el.addEventListener("change", function(e) {
            var fun = window[fun_name];
            if (typeof fun === "function") {
                fun(e);
            } else {
                console.warn("event listener may not be defined or wrong");
            }
        }.bind(fun_name), false);
    });
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
    if (overviewTable == null) 
        return;

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
    if (title.length != 0) {
        ajax.post({
            "newTitle": title,
            "r": "createNewSticker",
        }, true).then(redirectLink => {
            if (redirectLink == "-1") {
                alert("an error occured");
            } else {
                window.location.href = redirectLink;
            }
        });
    }
}

function click_createFbExport() {
    ajax.post({
        "r": "createFbExport",
    }).then(fbExport => {
        if (fbExport.status == "successful") {
            infoSaveSuccessfull("success");
    
            const a = document.createElement("a");
            a.href = fbExport.file;
            a.download = fbExport.file;
    
            document.body.appendChild(a);
            a.click();
    
            console.log(fbExport.errorList);
        }
    });
}
