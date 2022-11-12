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

function click_transferAufkleber() {

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
