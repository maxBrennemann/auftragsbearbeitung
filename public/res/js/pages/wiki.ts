import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings"
import { notification } from "js-classes/notifications";

import { loader } from "../classes/helpers";
import { FunctionMap } from "../types/types";

const fnNames = {} as FunctionMap;

fnNames.click_addEntry = () => {
    const title = (document.getElementById("newTitle") as HTMLInputElement).value;
    const content = (document.getElementById("newContent") as HTMLTextAreaElement).value;

    if (title.trim() === "") {
        notification("Bitte einen Titel angeben.", "warning");
        return;
    }

    ajax.post(`/api/v1/wiki`, {
        "title": title,
        "content": content,
    }).then((r: any) => {
        if (r.data?.status === "success") {
            window.location.reload();
        } else {
            notification("", "failure", r.error ?? "Der Eintrag konnte nicht gespeichert werden.");
        }
    });
}

loader(() => {
    addBindings(fnNames);
});
