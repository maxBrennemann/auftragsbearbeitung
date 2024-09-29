import { ajax } from "../classes/ajax.js";
import { getStickerId } from "../sticker.js";
import { deleteButton, editButton, parseEuro, parseInput, resetInputs } from "./helper.js";

const sizeData = {
    sizes: [],
    priceScheme: "price1",
    edit: false,
    editId: 0,
    ratio: 0,
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

    if (sizeData.priceScheme == "price1") {
        document.getElementById("sizesPrice1").checked = true;
    } else {
        document.getElementById("sizesPrice2").checked = true;
    }
}

const sendPriceScheme = () => {
    const sizesPrice1 = document.getElementById("sizesPrice1");
    const value = sizesPrice1.checked;

    if (value) {
        sizeData.priceScheme = "price1";
    } else {
        sizeData.priceScheme = "price2";
    }
    
    ajax.post(`/api/v1/sticker/${idSticker}/priceScheme`, {
        "priceScheme": sizeData.priceScheme == "price1" ? 0 : 1,
    });
}

const calcTable = (id) => {
    const el = sizeData.sizes.filter(s => s.id == id)[0];
    const ratio = el.height / el.width;
    sizeData.ratio = ratio;

    sizeData.sizes.forEach(s => {
        s.height = ratio * s.width;
        s.costs = (s.height * s.width) / 1000;
    });

    createTable();
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
        cost.innerHTML = Math.round((s.costs / 100 + Number.EPSILON) * 100) / 100

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
    sizeData.editId = parseInt(id);
    const el = sizeData.sizes.filter(s => s.id == id)[0];
    document.getElementById("sizeInputAnchor").value = el.height / 100;
    document.getElementById("sizePriceAnchor").value = el.price / 100;
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
    btnSave.addEventListener("click", addWidth);

    const btnEdit = document.getElementById("sizeBtnEdit");
    btnEdit.addEventListener("click", calculateEdit);

    const btnCancel = document.getElementById("sizeBtnCancel");
    btnCancel.addEventListener("click", () => {
        changeAfterAction();
    });

    const sizesPrice1 = document.getElementById("sizesPrice1");
    sizesPrice1.addEventListener("click", sendPriceScheme);

    const sizesPrice2 = document.getElementById("sizesPrice2");
    sizesPrice2.addEventListener("click", sendPriceScheme);
}

const addWidth = () => {
    const newWidth = document.getElementById("sizeInputAnchor").value;
    const newPrice = document.getElementById("sizePriceAnchor").value;

    const width = parseInput(newWidth);
    const height = sizeData.ratio * width;
    let price = parseEuro(newPrice);

    if (price == 0) {
        price = getDefaultPrice(width, height);
    }

    let isDefaultPrice = 0;
    if (price == getDefaultPrice(width, height)) {
        isDefaultPrice = 1;
    }

    ajax.put(`/api/v1/sticker/sizes`, {
        "width": width,
        "height": height,
        "price": price,
        "idSticker": getStickerId(),
        "isDefaultPrice": isDefaultPrice,
    }).then(async r => {
        if (r.status != "success") {
            return;
        }

        await getSizeData();
        createTable();
    });
}

const getDefaultPrice = (width, height) => {
    let base = 0;
    if (width >= 1200) {
        base = 2100;
    } else if (width >= 900) {
        base = 1950;
    } else if (width >= 600) {
        base = 1700;
    } else if (width >= 300) {
        base = 1500;
    } else {
        base = 1200;
    }

    const priceSchemeFactor = sizeData.priceScheme == 1 ? 1: 0;
    base = base + 200 * priceSchemeFactor;
    if (height >= 0.5 * width) {
        base += 100;
    }
    
    return base;
}

const calculateEdit = () => {
    const newHeight = document.getElementById("sizeInputAnchor").value;
    const newPrice = document.getElementById("sizePriceAnchor").value;

    const parsedHeight = parseInput(newHeight);
    const parsedPrice = parseEuro(newPrice);

    sizeData.sizes.forEach(el => {
        if (el.id !== sizeData.editId) {
            return;
        }

        el.height = parsedHeight;
        if (el.price !== 0) {
            el.price = parsedPrice;
        }
    })

    calcTable(sizeData.editId);

    ajax.post(`/api/v1/sticker/${getStickerId()}/sizes`, {
        "sizes": JSON.stringify(sizeData.sizes),
    }).then(async () => {
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
