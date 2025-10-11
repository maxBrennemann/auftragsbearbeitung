//@ts-nocheck

import { ajax } from "js-classes/ajax.js";
import { addBindings } from "js-classes/bindings.js"
import { notification } from "js-classes/notifications.js";

import { createPopup } from "../global.js";

const fnNames = {};

const init = () => {
    addBindings(fnNames);
}

fnNames.click_cancel = () => {
    window.history.go(-1);
    return false;
}

function sendSource() {
    const name = document.getElementById("sourceName").value;
    const desc = document.getElementById("sourceDescription").value;

    ajax.post("/api/v1/product/source", {
        name: name,
        desc: desc
    }).then(r => {
        if (r.id) {
            notification(`Quelle ${r.id} hinzugefügt`, "success");
            const source = document.getElementById("source");
            const option = document.createElement("option");
            option.value = r.id;
            option.innerHTML = name;
            source.insertBefore(option, source.lastChild);
        } else {
            notification(`Quelle nicht hinzugefügt`, "failure", JSON.stringify(r));
        }
    });
}

fnNames.write_addSource = e => {
    if (e.target.value != "addNew") {
        return;
    }

    const template = document.getElementById("addSource");
    const div = document.createElement("div");
    div.appendChild(template.content.cloneNode(true));
    const settingsContainer = createPopup(div);

    const btnSaveSource = document.createElement("button");
    btnSaveSource.classList.add("btn-primary");
    btnSaveSource.innerHTML = "Speichern";
    btnSaveSource.addEventListener("click", () => {
        sendSource();
    });

    settingsContainer.appendChild(btnSaveSource);
    settingsContainer.addEventListener("closePopup", () => {
        document.getElementById("source").value = "-1";
    })
}

fnNames.click_save = () => {
    const title = document.getElementById("productName").value;
    const brand = document.getElementById("productBrand").value;
    const category = document.getElementById("category").value;
    const source = document.getElementById("source").value;
    const price = document.getElementById("productPrice").value;
    const purchasePrice = document.getElementById("purchasingPrice").value;
    const description = document.getElementById("productDescription").value;

    if (title == ""
        || brand == ""
        || source == ""
        || price == ""
        || purchasePrice == ""
        || description == ""
        || category == "") {
        return;
    }

    ajax.post("/api/v1/product", {
        title: title,
        brand: brand,
        category: category,
        source: source,
        price: price.replace(",", "."),
        purchasePrice: purchasePrice.replace(",", "."),
        description: description,
    }).then(data => {
        const id = data.id;
        window.location.href = `produkt?id=${id}`;
    });
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
