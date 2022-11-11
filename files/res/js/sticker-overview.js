var aufkleberPlottChecked = true;

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
        var fun_name = "click_" + el.id;
        el.addEventListener("click", function(e) {
            var fun = window[fun_name];
            if (typeof fun === "function") {
                fun(e);
            }
        }.bind(fun_name), false);
    });
}

function click_aufkleberPlottClick(e) {
    if (aufkleberPlottChecked == true) {
        enableInputSlide("aufkleberKurz");
        enableInputSlide("aufkleberLang");
        enableInputSlide("aufkleberMehrteilig");
        document.getElementById("aufkleberUebertragen").disabled = false;
    } else {
        disableInputSlide("aufkleberKurz");
        disableInputSlide("aufkleberLang");
        disableInputSlide("aufkleberMehrteilig");

        document.getElementById("aufkleberUebertragen").disabled = true;
    }

    aufkleberPlottChecked = !aufkleberPlottChecked;
}

function disableInputSlide(input) {
    input = document.getElementById(input);
    input.checked = false;
    input.disabled = true;
    input.parentNode.children[1].classList.add("pointer-none");
}

function enableInputSlide(input) {
    input = document.getElementById(input);
    input.disabled = false;
    input.parentNode.children[1].classList.remove("pointer-none");
}
