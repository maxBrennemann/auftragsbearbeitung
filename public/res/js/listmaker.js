import { } from "./listcreator.js";

/**
 * Listenersteller:
 * Block -> listElement | Block | condition
 * listElement -> text und selector
 * text -> nur text
 * selector -> checkbox, select, etc.
 * select -> texte
 * condition -> true false und dann kann der block in condition freigeschalten werden
 */

const list = {};
const fnNames = {};

const init = () => {

}

const render = () => {
    const listNode = document.querySelector("#listPreview");
    listNode.innerHTML = "";

}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
