import { addBindings } from "js-classes/bindings.js";
import { FunctionMap } from "../types/types.js";

const functionNames: FunctionMap = {};

const init = () => {
    addBindings(functionNames);
}

functionNames.click_testSearch = () => {
    const query = (document.querySelector("input#testInput") as HTMLInputElement).value;
    fetch(`/api/v1/search?query=${query}`);
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
