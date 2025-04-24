import {} from "./listcreator.js";

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
