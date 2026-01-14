import { addBindings } from "js-classes/bindings";

import { loader } from "../classes/helpers";
import { FunctionMap } from "../types/types";

const fnNames: FunctionMap = {};

fnNames.click_showOrder = () => {
    const idInput = document.getElementById("idInput") as HTMLInputElement;
    const id = idInput.value;

    const link = document.createElement("a") as HTMLAnchorElement;
    link.href = "/auftrag?id=" + id;
    link.click();
}

fnNames.click_searchOrder = () => {
    const queryInput = document.getElementById("queryInput") as HTMLInputElement;
    const query = queryInput.value;

    /* TODO: properly implement this with the new planned router */
    const link = document.createElement("a") as HTMLAnchorElement;
    link.href = "order-overview?query=" + query;
    link.click();
}

loader(() => {
    addBindings(fnNames);
});
