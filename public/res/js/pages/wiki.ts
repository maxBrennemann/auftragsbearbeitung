import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings"
import { FunctionMap } from "../types/types";
import { loader } from "../classes/helpers";

const fnNames = {} as FunctionMap;

fnNames.click_addEntry = () => {
    const title = (document.getElementById("newTitle") as HTMLInputElement).value;
    const content = (document.getElementById("newContent") as HTMLTextAreaElement).value;

    ajax.post(`/api/v1/`, {
        "title": title,
        "content": content,
    });
}

loader(() => {
    addBindings(fnNames);
});
