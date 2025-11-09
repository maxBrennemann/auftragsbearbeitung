import { ajax } from "js-classes/ajax.js";
import { addBindings } from "js-classes/bindings.js";

import { createPopup } from "../classes/helpers";

const textGenerationData = {
    textStyle: "",
    textType: null,
    product: null,
    stickerId: 0,
    stickerName: "",
};

const fnNames: { [key: string]: (...args: any[]) => void } = {};

/**
 * Iterates through the texts of the sticker
 * depending on the product and text type
 * @param {*} e 
 */
fnNames.click_iterateText = e => {
    const target = e.currentTarget;
    const direction = target.dataset.direction;
    const currentTextNode = target.parentNode.querySelector(".chatCount");

    const id = textGenerationData.stickerId;
    const type = target.parentNode.dataset.type;
    const text = target.parentNode.dataset.text;

    ajax.get(`/api/v1/sticker/texts/${id}/${type}/${text}`, {
        direction: direction,
        current: currentTextNode.innerHTML,
    }).then((r: any) => {
        if (r.data.status !== "success") {
            return;
        }

        if (direction === "next") {
            currentTextNode.innerHTML = parseInt(currentTextNode.innerHTML) + 1;
        } else {
            currentTextNode.innerHTML = parseInt(currentTextNode.innerHTML) - 1;
        }

        const text = r.data.text;
        const textarea = document.querySelector("textarea.data-input") as HTMLTextAreaElement;
        textarea.value = text;
    });
}

fnNames.click_textGeneration = e => {
    const title = textGenerationData.stickerName;
    const target = e.currentTarget.parentNode;
    const type = textGenerationData.product || target.dataset.type;
    const text = textGenerationData.textType || target.dataset.text;
    const id = textGenerationData.stickerId;
    const additionalInfo = getAdditionalInfo();

    ajax.post(`/api/v1/sticker/texts/${id}/${type}/${text}`, {
        title: title,
        additionalText: additionalInfo.text,
        additionalStyle: additionalInfo.style,
    }).then((r: any) => {
        console.log(r.data);
    });
}

const getAdditionalInfo = () => {
    const popup = document.getElementById("showTextSettings") as HTMLButtonElement;
    if (popup == null) {
        return {
            text: "",
            style: "",
        };
    }

    const text = (popup.querySelector("#additionalTextGPT") as HTMLInputElement).value;
    const style = textGenerationData.textStyle;

    return {
        text: text,
        style: style
    };
}

fnNames.click_showTextSettings = e => {
    const type = e.currentTarget.parentNode.dataset.type;
    const text = e.currentTarget.parentNode.dataset.text;

    textGenerationData.textType = text;
    textGenerationData.product = type;

    ajax.get(`/api/v1/sticker/texts/${textGenerationData.stickerId}/get-template`, {
        "text": text,
        "type": type,
    }).then((r: any) => {
        const template = r.data.template;
        const div = document.createElement("div");
        div.innerHTML = template;
        div.id = "showTextSettings";

        const textOptions = div.querySelectorAll(".selectTextStyle button");
        Array.from(textOptions).forEach(el => {
            el.addEventListener("click", selectTextOption);
        });

        const optionsContainer = createPopup(div);
        const btn = document.createElement("button");
        btn.innerHTML = "Neuen Text generieren";
        btn.classList.add("btn-primary");
        btn.addEventListener("click", fnNames.click_textGeneration);
        optionsContainer.appendChild(btn);

        addBindings(fnNames);
    })
}

function selectTextOption(e: Event) {
    const target = e.currentTarget;

    const popup = document.getElementById("showTextSettings") as HTMLButtonElement;
    const textOptions = popup.querySelectorAll(".selectTextStyle button");
    Array.from(textOptions).forEach(el => {
        if (el == target) {
            el.classList.add("btn-active");
            el.classList.remove("btn-inactive");
            textGenerationData.textStyle = el.innerHTML;
        } else {
            el.classList.add("btn-inactive");
            el.classList.remove("btn-active");
        }
    });
}

export function initTextGeneration(stickerId: number, stickerName: string) {
    textGenerationData.stickerId = stickerId;
    textGenerationData.stickerName = stickerName;
    addBindings(fnNames);
}
