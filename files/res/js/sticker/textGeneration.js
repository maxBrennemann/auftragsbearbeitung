const textGenerationData = {
    textStyleNode: null,
    textType: null,
    product: null,
};

export function click_iterateText(e) {
    const target = e.currentTarget;
    const direction = target.dataset.direction;
    const currentTextNode = target.parentNode.querySelector(".chatCount");

    ajax.post({
        id: mainVariables.motivId.innerHTML,
        direction: direction,
        current: currentTextNode.innerHTML,
        type: target.parentNode.dataset.type,
        text: target.parentNode.dataset.text,
        r: "iterateText",
    }).then(r => {
        if (r.status == "success") {
            if (direction == "next") {
                currentTextNode.innerHTML = parseInt(currentTextNode.innerHTML) + 1;
            } else {
                currentTextNode.innerHTML = parseInt(currentTextNode.innerHTML) - 1;
            }
            
            const text = r.text;
            const textarea = document.querySelector("textarea.data-input");
            textarea.value = text;
        }
    });
}

export function click_textGeneration(e) {
    const title = document.getElementById("name").value;
    const target = e.currentTarget.parentNode;
    const type = textGenerationData.product || target.dataset.type;
    const text = textGenerationData.textType || target.dataset.text;
    const additionalInfo = getAdditionalInfo();

    ajax.post({
        title: title,
        id: mainVariables.motivId.innerHTML,
        text: text,
        type: type,
        additionalText: additionalInfo.text,
        additionalStyle: additionalInfo.style,
        r: "generateText",
    }).then(r => {
        console.log(r.choices[0].message.content);
    });
}

function getAdditionalInfo() {
    const window = document.getElementById("showTextSettings");
    
    if (window != null) {
        const text = window.querySelector("#additionalTextGPT").value;
        const style = textGenerationData.textStyleNode.innerHTML;
        return {text: text, style: style};
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
        id: mainVariables.motivId.innerHTML,
        text: text,
        type: type,
        r: "showGTPOptions",
    }).then(r => {
        const template = r.template;
        const div = document.createElement("div");
        div.innerHTML = template;
        div.classList.add("flex", "h-2/3", "lg:h-4/5", "w-4/5", "z-50", "max-h-fit", "fixed", "bg-white", "rounded-lg", "shadow-lg");
        div.id = "showTextSettings";
        document.body.appendChild(div);

        const textOptions = div.querySelectorAll("dt");
        Array.from(textOptions).forEach(dt => {
            dt.addEventListener("click", selectTextOption);
        });

        centerAbsoluteElement(div);
        addActionButtonForDiv(div, "hide");
        div.classList.remove("centeredContainer");

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
