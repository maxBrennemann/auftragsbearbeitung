import { ajax } from "./classes/ajax.js";
import { initBindings } from "./classes/bindings.js";

const fnNames = {};

const init = () => {
    initBindings(fnNames);
}

fnNames.click_addEntry = () => {
    const title = document.getElementById("newTitle").value;
    const content = document.getElementById("newContent").value;

    ajax.post(`/api/v1/`, {
        "title": title,
        "content": content,
    });
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
