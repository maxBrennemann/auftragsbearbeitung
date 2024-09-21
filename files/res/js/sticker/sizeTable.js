import { ajax } from "../classes/ajax.js";
import { getStickerId } from "../sticker.js";
import { deleteButton, editButton, parseInput, resetInputs } from "./helper.js";

const sizeData = {
    sizes: [],
    priceScheme: "price1",
    edit: false,
    editId: 0,
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

const sendPriceScheme = () => {
    ajax.post(``);
}

const calcTable = (id) => {
    const el = sizeData.sizes.filter(s => s.id == id)[0];
    const ratio = el.height / el.widht;

    sizeData.sizes.forEach(s => {
        s.height = ratio * s.width;
        s.costs = (s.height * s.width) / 100000;
    });
}

const createTable = () => {
    const anchor = document.getElementById("sizeTableAnchor");
    anchor.innerHTML = "";
    
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
    ajax.delete(`/api/v1/sticker/sizes/${id}`).then(async r => {
        if (r.status != "success") {
            return;
        }

        await getSizeData();
        createTable();
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
    sizeData.editId = id;
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

/**
 * listeners for edit, delete and add
 */
const initListeners = () => {
    const btnSave = document.getElementById("sizeBtnAdd");
    btnSave.addEventListener("click", () => {
        const newHeight = document.getElementById("sizeInputAnchor").value;
        const newPrice = document.getElementById("sizePriceAnchor").value;

        const height = parseInput(newHeight);
        const price = parseInput(newPrice);

        ajax.put(`/api/v1/sticker/sizes`, {
            "height": height,
            "price": price,
        }).then(async r => {
            if (r.status != "success") {
                return;
            }
    
            await getSizeData();
            createTable();
        });
    });

    const btnEdit = document.getElementById("sizeBtnEdit");
    btnEdit.addEventListener("click", calculateEdit);

    const btnCancel = document.getElementById("sizeBtnCancel");
    btnCancel.addEventListener("click", () => {
        changeAfterAction();
    });
}

const calculateEdit = () => {
    const newHeight = document.getElementById("sizeInputAnchor").value;
    const newPrice = document.getElementById("sizePriceAnchor").value;

    const parsedHeight = parseInput(newHeight);
    sizeData.sizes.forEach(s => {

    });

    ajax.post(`/api/v1/`, {

    }).then(async r => {
        await getSizeData();
        changeAfterAction();
    });
}

/**
 * changes back various changes for edit
 */
const changeAfterAction = () => {
    sizeData.edit = false;
    sizeData.editId = 0;

    changeEditText();
    changeButtons();

    resetInputs([
        document.getElementById("sizeInputAnchor"),
        document.getElementById("sizePriceAnchor"),
    ]);
}
