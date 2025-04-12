export function initBindings(fnNames) {
    let bindings = document.querySelectorAll('[data-binding]');
    [].forEach.call(bindings, function(el) {
        var fun_name = "";
        if (el.dataset.fun) {
            fun_name = "click_" + el.dataset.fun;
        } else {
            fun_name = "click_" + el.id;
        }
        
        el.addEventListener("click", function(e) {
            var fun = fnNames[fun_name];
            if (typeof fun === "function") {
                fun(e);
            } else {
                console.warn(`event listener may not be defined or wrong for ${fun_name}`);
            }
        }, false);
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
            var fun = fnNames[fun_name];
            if (typeof fun === "function") {
                fun(e);
            } else {
                console.warn(`event listener may not be defined or wrong for ${fun_name}`);
            }
        }, false);
    });
}

export function addBindings(elements, fnNames) {
    Array.from(elements).forEach(el => {
        addBinding(el, fnNames);
    });
}

function addBinding(el, fnNames) {
    let fun_name = "";
    if (el.dataset.fun) {
        fun_name = "click_" + el.dataset.fun;
    } else if (el.id) {
        fun_name = "click_" + el.id;
    } else {
        return;
    }

    el.addEventListener("click", (e) => {
        const fun = fnNames[fun_name];
        if (typeof fun === "function") {
            fun(e);
        } else {
            console.warn(`event listener may not be defined or wrong for ${fun_name}`);
        }
    }, false);
}
