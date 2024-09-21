import { ajax } from "../classes/ajax.js";
import { getStickerId } from "../sticker.js";
import { deleteButton, editButton, resetInputs } from "./helper.js";

const sizeData = {
    sizes: [],
    priceScheme: "price1",
    edit: false,
}

export const initSizeTable = async () => {
    await getSizeData();
    await getPriceScheme();
    createTable();
    initListeners();
}

const getSizeData = async () => {
    const idSticker = getStickerId();
    const data = await ajax.get(`/api/v1/sticker/${idSticker}/sizes`);
    sizeData.sizes = data.sizes;
}

const getPriceScheme = async () => {
    const idSticker = getStickerId();
    const data = await ajax.get(`/api/v1/sticker/${idSticker}/priceScheme`);
    sizeData.priceScheme = data.priceScheme;
}

const sendSizeData = () => {

}

const sendPriceScheme = () => {

}

const setSinglePrice = () => {

}

const resetPrice = () => {

}

const createTable = () => {
    const anchor = document.getElementById("sizeTableAnchor");
    
    if (sizeData.sizes.length == 0) {
        const row = anchor.insertRow();
        const col = anchor.insertCell();
        col.innerHTML = "Keine Einträge vorhanden.";
        col.colSpan = 5;
        col.classList.add("tw-p-2");
        return;
    }

    sizeData.sizes.forEach(s => {
        const row = anchor.insertRow();

        const width = row.insertCell();
        width.innerHTML = s.width / 10;

        const height = row.insertCell();
        height.innerHTML = s.height / 10;

        const price = row.insertCell();
        price.innerHTML = s.price / 100;

        const cost = row.insertCell();
        cost.innerHTML = s.costs / 100;

        const actions = row.insertCell();
        const actionsDiv = document.createElement("div");
        actions.appendChild(actionsDiv);
        actions.classList.add("tw-flex");

        actionsDiv.appendChild(editButton(s.id));
        const editBtn = actionsDiv.querySelector(".btn-edit");
        editBtn.addEventListener("click", editWidth);

        actionsDiv.appendChild(deleteButton(s.id))
        const deleteBtn = actionsDiv.querySelector(".btn-delete");
        deleteBtn.addEventListener("click", deleteWidth);
    });
}

const deleteWidth = (e) => {
    if (sizeData.edit) {
        return;
    }

    const id = e.currentTarget.dataset?.id;
    ajax.delete(`/api/v1/`).then(r => {

    });
}

const editWidth = (e) => {
    if (sizeData.edit) {
        return;
    }

    sizeData.edit = true;

    changeEditText();
    changeButtons();

    const id = e.currentTarget.dataset?.id;

    
    sizeWidthAnchor
    sizePriceAnchor
}

const changeEditText = () => {
    const text1 = document.getElementById("sizeActionTextAnchor");
    const text2 = document.getElementById("sizeInputTextAnchor");
    switch (sizeData.edit) {
        case true:
            text1.innerHTML = "Aktuelle Breite bearbeiten";
            text2.innerHTML = "Höhe in [cm]";
            break;
        case false:
            text1.innerHTML = "Neue Breite hinzufügen";
            text2.innerHTML = "Breite in [cm]";
            break;
    }
}

const changeButtons = () => {
    const showBtnAdd = document.getElementById("sizeBtnAddAnchor");
    const showBtnEdit = document.getElementById("sizeBtnEditAnchor");
    switch (sizeData.edit) {
        case true:
            showBtnAdd.classList.add("hidden");
            showBtnEdit.classList.remove("hidden");
            break;
        case false:
            showBtnEdit.classList.add("hidden");
            showBtnAdd.classList.remove("hidden");
            break;
    }
}

const initListeners = () => {
    const btnSave = document.getElementById("sizeBtnAdd");
    btbtnSavenEdit.addEventListener("click", () => {
        const newHeight = document.getElementById("sizeInputAnchor").value;
        const newPrice = document.getElementById("sizePriceAnchor").value;

        ajax.put().then(r => {});
    });

    const btnEdit = document.getElementById("sizeBtnEdit");
    btnEdit.addEventListener("click", () => {
        const newHeight = document.getElementById("sizeInputAnchor").value;
        const newPrice = document.getElementById("sizePriceAnchor").value;

        ajax.post().then(r => {
            changeAfterAction();
        });
    });

    const btnCancel = document.getElementById("sizeBtnCancel");
    btnCancel.addEventListener("click", () => {
        changeAfterAction();
    });
}

const changeAfterAction = () => {
    sizeData.edit = false;
    changeEditText();
    changeButtons();

    resetInputs([
        document.getElementById("sizeInputAnchor"),
        document.getElementById("sizePriceAnchor"),
    ]);
}
