//@ts-nocheck

import { addBindings } from "js-classes/bindings.js"

import { clearInputs } from "../global.js";

const fnNames = {};

function init() {
    addBindings(fnNames);
}

fnNames.click_cancel = () => {
    clearInputs({
        "ids": ["addName", "addDescription", "addSource", "addSurcharge"],
    });
}

fnNames.click_add = () => {
    ajax.post(`/api/v1/tables/leistung`, {
        "conditions": JSON.stringify(data),
    }).then(r => {
        clearInputs({
            "ids": ["addName", "addDescription", "addSource", "addSurcharge"],
        });
    });
}

fnNames.click_save = e => {
    const target = e.currentTarget;
    const element = target.closest("div[data-service-id]");
    const id = element.dataset.serviceId;
}

fnNames.click_delete = e => {
    const target = e.currentTarget;
    const element = target.closest("div[data-service-id]");
    const id = element.dataset.serviceId;

    //ajax.delete(`/api/v1/`)
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
