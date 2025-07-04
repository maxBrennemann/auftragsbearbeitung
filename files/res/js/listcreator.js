import { ajax } from "js-classes/ajax.js";

if (document.readyState !== 'loading' ) {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}

function init() {
    
}

function click_addListElementType() {
    const listElement = document.getElementById("listElementType").value;
}
