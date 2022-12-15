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
        let top = document.querySelector("header");
        top = top.getBoundingClientRect();
        top = top.height;
        top = Math.ceil(top);
        for (let i = 0; i < trElem.length; i++) {
            let tr = trElem[i];
            tr.style.position = "sticky";
            tr.style.top = top + "px";
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

function send(data, intent, json = false) {
    data.getReason = intent;

    if (json) {
        paramString = "getReason=" + intent + "&json=" + JSON.stringify(data);
    } else {
        /* temporarily copied here */
        let temp = "";
        for (let key in data) {
            temp += key + "=" + data[key] + "&";
        }

        paramString = temp.slice(0, -1);
    }

    var response = makeAsyncCall("POST", paramString, "").then(result => {
        return result;
    });

    return response;
}

var responseLength = 0;
function crawlAll(e) {
    e.preventDefault();
    document.getElementById("crawlAll").style.display = "inline";

    var ajaxCall = new XMLHttpRequest();
    ajaxCall.onload = function() {
        if (this.readyState == 4 && this.status == 200) {
            console.log(this.responseText);
            document.getElementById("loaderCrawlAll").style.display = "none";
        }
    }
    ajaxCall.onprogress = function() {
        /* https://stackoverflow.com/questions/42838609/how-to-flush-php-output-buffer-properly */
        if (this.readyState == 3) {
            let json = this.responseText.substring(responseLength);
            responseLength = this.responseText.length;
            console.log(json);
            json = json.replace(/ /g,'');
            json = JSON.parse(json);
            if (json.products) {
                document.getElementById("productProgress").max = json.products;
                document.getElementById("maxProgress").innerHTML = json.products;
            } else if (json.shopId) {
                document.getElementById("productProgress").value = json.count;
                document.getElementById("currentProgress").innerHTML = json.count;
                if (json.existing) {
                    document.getElementById("statusProgress").innerHTML = "wurde schon gecrawlt";
                } else {
                    document.getElementById("statusProgress").innerHTML = "neu angelegt oder geupdatet";
                }
            }
        }
    }
    ajaxCall.open("POST", "", true);
    ajaxCall.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    ajaxCall.setRequestHeader("X-Accel-Buffering", "no");
    ajaxCall.send("getReason=crawlAll");
}

async function showStickerStatus() {
    let overviewTable = document.querySelector('[data-type="module_sticker_sticker_data"]');
    if (overviewTable == null) return;

    var data = await send({}, "getStickerStatus");
    data = JSON.parse(data);
    var rows = overviewTable.children[0].children;

    for (i in data) {
        let el = data[i];
        var isA = el.in_shop_aufkleber;
        var isW = el.in_shop_wandtattoo;
        var isT = el.in_shop_textil;
        
        let numb = parseInt(i);
        var currRow = rows[numb + 1];
        if (currRow != null) {
            if (isA == 1)
                currRow.children[3].classList.add("inShop");

            if (isW == 1)
                currRow.children[7].classList.add("inShop");

            if (isT == 1)
                currRow.children[8].classList.add("inShop");
        }
    }
}

async function createNewSticker() {
    var title = document.getElementById("newTitle").value;
    if (title.length != 0) {
        let redirectLink = await send({newTitle: title}, "createNewSticker");

        if (redirectLink == "-1") {
            alert("an error occured");
        } else {
            window.location.href = redirectLink;
        }
    }
}
