var mainVariables = {};

if (document.readyState !== 'loading' ) {
    initBindings();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initBindings();
    });
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
}

function click_toggleCheckbox(e) {
    e.target.enabled = !e.target.enabled;
}

function click_aufkleberPlottClick(e) {
    if (mainVariables.aufkleberPlott.enabled == true) {
        enableInputSlide(mainVariables.aufkleberKurz);
        enableInputSlide(mainVariables.aufkleberLang);
        enableInputSlide(mainVariables.aufkleberMehrteilig);
        document.getElementById("aufkleberUebertragen").disabled = false;
    } else {
        disableInputSlide(mainVariables.aufkleberKurz);
        disableInputSlide(mainVariables.aufkleberLang);
        disableInputSlide(mainVariables.aufkleberMehrteilig);

        document.getElementById("aufkleberUebertragen").disabled = true;
    }

    mainVariables.aufkleberPlott.enabled = !mainVariables.aufkleberPlott.enabled;
}

function click_editName(e) {
    e.target.innerHTML = e.target.innerHTML == "✔" ? "✎" : "✔";
    if (e.target.innerHTML == "✔") {
        document.getElementById("name").contentEditable = 'true';
    } else {
        document.getElementById("name").contentEditable = 'false';
    }
    document.getElementById("name").classList.toggle("contentEditable");
}

function disableInputSlide(input) {
    input.checked = false;
    input.disabled = true;
    input.parentNode.children[1].classList.add("pointer-none");
}

function enableInputSlide(input) {
    input.disabled = false;
    input.parentNode.children[1].classList.remove("pointer-none");
}

async function click_saveAufkleber() {
    var data = {
        json: JSON.stringify({
                "plott": mainVariables.aufkleberPlott.checked == true ? 1: 0,
                "short": mainVariables.aufkleberKurz.checked == true ? 1: 0,
                "long": mainVariables.aufkleberLang.checked == true ? 1: 0,
                "multi": mainVariables.aufkleberMehrteilig.checked == true ? 1: 0,
        }),
        id: mainVariables.motivId.innerHTML,
    }
    var response = await send(data, "setAufkleberParameter");
    if (response == "success") {
        infoSaveSuccessfull("success");
    } else {
        console.log(response);
        infoSaveSuccessfull();
    }
}

async function click_transferAufkleber() {
    document.getElementById("productLoader").style.display = "inline";
    var data = {
        id: 428,
    };
    var response = await send(data, "transferAufkleber");
    console.log(response);
    document.getElementById("productLoader").style.display = "none";
}

function send(data, intent) {
    data.getReason = intent;

    /* temporarily copied here */
    let temp = "";
    for (let key in data) {
        temp += key + "=" + data[key] + "&";
    }

    paramString = temp.slice(0, -1);

    var response = makeAsyncCall("POST", paramString, "").then(result => {
        return result;
    });

    return response;
}

/* editRow for sizetable */
function editRow(key, reference) {
    var table = reference.parentNode.parentNode.parentNode;
    var heights = table.children;
    for (let i = 1; i < heights.length; i++) {
        var input = document.createElement("input");
        input.classList.add("inputHeight");
        input.dataset.heightChange = true;
        input.value = heights[i].children[2].innerHTML;
        heights[i].children[2].innerHTML = "";
        heights[i].children[2].appendChild(input);

        input.addEventListener("input", function(e) {
            var heights = document.querySelectorAll('[data-height-change]');
            var height = e.target.value;
            var width = e.target.parentNode.parentNode.children[1].innerHTML;
            height = parseInt(height);
            width = parseInt(width);
            if (height != NaN) {
                for (let i = 0; i < heights.length; i++) {
                    if (e.target !=  heights[i]) {
                        var width2 = heights[i].parentNode.parentNode.children[1].innerHTML;
                        width2 = parseInt(width2);
                        console.log(`breite 1: ${width}, höhe 1: ${height}, breite 2: ${width2}`)
                        var height2 = (height / width) * width2;
                        heights[i].value = height2 + "cm";
                    }
                }
            }
        }, false);
    }
}

function changeImage(e) {
    var main = document.querySelector(".imageBig");
    main.src = e.target.src;
    main.dataset.imageId = e.target.dataset.imageId;

    document.getElementById("aufkleberbild").checked = e.target.dataset.isAufkleber == 0 ? false : true;
    document.getElementById("wandtattoobild").checked = e.target.dataset.isWandtattoo == 0 ? false : true;
    document.getElementById("textilbild").checked = e.target.dataset.isTextil == 0 ? false : true;
}

async function changeImageParameters(e) {
    var main = document.querySelector(".imageBig");
    var is_aufkleber = document.getElementById("aufkleberbild").checked == true ? 1 : 0;
    var is_wandtatto = document.getElementById("wandtattoobild").checked == true ? 1 : 0;
    var is_textil = document.getElementById("textilbild").checked == true ? 1 : 0;

    var data = {
        is_aufkleber: is_aufkleber,
        is_wandtatto: is_wandtatto,
        is_textil: is_textil,
        id_image: main.dataset.imageId,
    }
    var response = await send(data, "changeImageParameters");
    if (response == "success") {
        infoSaveSuccessfull("success");
    } else {
        console.log(response);
        infoSaveSuccessfull();
    }
}
