import { ajax } from "js-classes/ajax.js";
import { addBindings } from "js-classes/bindings.js";
import { Colorpicker } from "colorpicker/colorpicker.js";
import { createPopup } from "../global.js";

var cp = null;
const addToOrderColors = [];
const fnNames = {};

fnNames.click_addColor = () => {
    const template = document.getElementById("templateFarbe");
	const div = document.createElement("div");
    div.id = "selectColor";
	div.appendChild(template.content.cloneNode(true));

    cp = new Colorpicker(div.querySelector("#cpContainer"));
    const c = div.querySelector("canvas");
    c.style.margin = "auto";

    c.addEventListener("mouseup", colorPanelMouseUp);

    createPopup(div);

    const colorInputHex = document.querySelector("input.colorInput.jscolor");
    colorInputHex.addEventListener("change", checkHexCode);

    const sendColorBtn = div.querySelector('[data-fun="sendColor"]');
    sendColorBtn.addEventListener("click", sendColor, false);
}

const colorPanelMouseUp = () => {
    const element = document.querySelector("input.colorInput.jscolor");

    if (!element) {
        return;
    }

    const hex = cp.color.toUpperCase();
    if (isHexValid(hex)) {
        element.value = hex;
        element.classList.add("outline-green-500");
        element.classList.remove("outline-red-500");
    } else {
        element.classList.remove("outline-green-500");
        element.classList.add("outline-red-500");
    }
}

fnNames.click_removeColor = e => {
    var colorId = e.currentTarget.dataset.colorId;
    ajax.delete(`/api/v1/order/${globalData.auftragsId}/colors/${colorId}`).then(r => {
        const showColors = document.getElementById("showColors");
        showColors.innerHTML = r.colors;
    });
}

/**
 * you can select multiple existing colors, which are added to this variable via the function
 * beneath;
 */
fnNames.click_toggleCS = async () => {
    const template = document.getElementById("templateExistingColor");
	const div = document.createElement("div");
	div.appendChild(template.content.cloneNode(true));
    const settingsContainer = createPopup(div);

    const existingColorsTemplate = await ajax.get(`/api/v1/template/colors/render`);
    const container = div.querySelector("div");
    container.innerHTML = existingColorsTemplate.template;

    const sendColorsBtn = document.createElement("button");
    sendColorsBtn.classList.add("btn-primary");
    sendColorsBtn.innerHTML = "Farbe(n) übernehmen";
    sendColorsBtn.addEventListener("click", addSelectedColors, false);
    settingsContainer.appendChild(sendColorsBtn);

    const elements = div.getElementsByClassName("singleColorContainer");
    Array.from(elements).forEach(element => {
        element.addEventListener("click", (e) => {
            e.currentTarget.classList.toggle("bg-white");
            e.currentTarget.classList.toggle("italic");
            e.currentTarget.classList.toggle("rounded-md");

            let id = e.currentTarget.dataset.colorId;

            if (addToOrderColors.includes(id)) {
                let index = addToOrderColors.indexOf(id);
                addToOrderColors.slice(index, -1);
            } else {
                addToOrderColors.push(id);
            }
        }, false);
    });
}

/**
 * adds all selected colors to the order;
 */
function addSelectedColors() {
    ajax.post(`/api/v1/order/${globalData.auftragsId}/colors/multiple`, {
        "colors": JSON.stringify(addToOrderColors),
    }).then(r => {
        const showColors = document.getElementById("showColors");
        showColors.innerHTML = r.colors;

        const elements = document.getElementsByClassName("colorInput");
        for (let i = 0; i < elements.length; i++) {
            elements[i].value = "";
        }
    });
}

/**
 * sends the newly created color to the backend;
 * then resets the form and shows the newly added color
 */
export function sendColor() {
    var elements = document.getElementsByClassName("colorInput");
    var data = [], currVal;

    for (let i = 0; i < elements.length; i++) {
        currVal = elements[i].value;
        if (currVal == null || currVal == "") {
            alert("Felder dürfen nicht leer sein!");
            return null;
        }
        data.push(currVal);
    }

    ajax.post(`/api/v1/order/${globalData.auftragsId}/colors/add`, {
        "colorName": data[0],
        "hexValue": data[3],
        "shortName": data[1],
        "producer": data[2],
    }).then(r => {
        const showColors = document.getElementById("showColors");
        showColors.innerHTML = r.colors;

        const elements = document.getElementsByClassName("colorInput");
        for (let i = 0; i < elements.length; i++) {
            elements[i].value = "";
        }
    });
}

function checkHexCode(e) {
    const el = e.currentTarget;

    if (isHexValid(el.value)) {
        el.classList.add("outline-green-500");
        el.classList.remove("outline-red-500");
        return null;
    }

    el.classList.remove("outline-green-500");
    el.classList.add("outline-red-500");
}

export const isHexValid = (hex) => {
    return /^[0-9a-fA-F]{6}$/.test(hex);
}

fnNames.click_addSelectedColors = addSelectedColors;
fnNames.write_checkHexCode = checkHexCode;

export const initColors = () => {
    addBindings(fnNames);
}
