import { ajax } from "../classes/ajax.js";
import { createPopup } from "../global.js";
import { getStickerId } from "../sticker.js";

const textGenerationData = {
    textStyleNode: null,
    textType: null,
    product: null,
};

/**
 * Iterates through the texts of the sticker
 * depending on the product and text type
 * @param {*} e 
 */
export function click_iterateText(e) {
    const target = e.currentTarget;
    const direction = target.dataset.direction;
    const currentTextNode = target.parentNode.querySelector(".chatCount");

    const id = getStickerId();
    const type = target.parentNode.dataset.type;
    const text = target.parentNode.dataset.text;

    ajax.get(`/api/v1/sticker/${id}/texts/${type}/${text}`, {
        direction: direction,
        current: currentTextNode.innerHTML,
    }).then(r => {
        if (r.status !== "success") {
            return;
        }

        if (direction === "next") {
            currentTextNode.innerHTML = parseInt(currentTextNode.innerHTML) + 1;
        } else {
            currentTextNode.innerHTML = parseInt(currentTextNode.innerHTML) - 1;
        }
        
        const text = r.text;
        const textarea = document.querySelector("textarea.data-input");
        textarea.value = text;
    });
}

export function click_textGeneration(e) {
    const title = document.getElementById("name").value;
    const target = e.currentTarget.parentNode;
    const type = textGenerationData.product || target.dataset.type;
    const text = textGenerationData.textType || target.dataset.text;
    const id = getStickerId();
    const additionalInfo = getAdditionalInfo();

    ajax.post(`/api/v1/sticker/${id}/texts/${type}/${text}`, {
        title: title,
        additionalText: additionalInfo.text,
        additionalStyle: additionalInfo.style,
    }).then(r => {
        console.log(r.choices[0].message.content);
    });
}

function getAdditionalInfo() {
    const window = document.getElementById("showTextSettings");
    
    if (window != null) {
        const text = window.querySelector("#additionalTextGPT").value;
        const style = textGenerationData.textStyleNode.innerHTML;
        
        return {
            text: text, 
            style: style
        };
    }
    
    return {
        text: "",
        style: "",
    };
}

export function click_showTextSettings(e) {
    const type = e.currentTarget.parentNode.dataset.type;
    const text = e.currentTarget.parentNode.dataset.text;

    textGenerationData.textType = text;
    textGenerationData.product = type;

    ajax.post({
        id: getStickerId(),
        text: text,
        type: type,
        r: "showGTPOptions",
    }).then(r => {
        const template = r.template;
        const div = document.createElement("div");
        div.innerHTML = template;
        div.classList.add("overflow-y-auto", "overflow-x-hidden", "fixed", "top-0", "right-0", "left-0", "z-50", "justify-center", "items-center", "w-full", "md:inset-0", "h-[calc(100%-1rem)]", "max-h-full", "bg-white/75");
        div.id = "showTextSettings";

        const textOptions = div.querySelectorAll("dt");
        Array.from(textOptions).forEach(dt => {
            dt.addEventListener("click", selectTextOption);
        });

        createPopup(div);

        const newTextBtn = div.querySelector("#generateNewText");
        newTextBtn.addEventListener("click", click_textGeneration);
    });
}

function selectTextOption(e) {
    const target = e.currentTarget;

    if (textGenerationData.textStyleNode != null) {
        textGenerationData.textStyleNode.classList.remove("bg-indigo-500");
        textGenerationData.textStyleNode.classList.add("bg-blue-200");
    }

    if (target != textGenerationData.textStyleNode) {
        target.classList.add("bg-indigo-500");
        target.classList.remove("bg-blue-200");
        textGenerationData.textStyleNode = target;
    }
}
