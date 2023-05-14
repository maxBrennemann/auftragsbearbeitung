const textGenerationData = {
    currentTextStyleNode: null,
};

export function click_textGeneration(e) {
    const title = document.getElementById("name").value;
    const type = e.currentTarget.dataset.type;
    const text = e.currentTarget.dataset.text;
    ajax.post({
        title: title,
        id: mainVariables.motivId.innerHTML,
        text: text,
        type: type,
        r: "generateText",
    }).then(r => {
        console.log(r.choices[0].message.content);
    });
}

export function click_showTextSettings(e) {
    // TODO: genrate template, show settings and show all texts
    ajax.post({
        id: mainVariables.motivId.innerHTML,
        type: "",
        r: "showGTPOptions",
    }).then(r => {
        const template = r.template;
        const div = document.createElement("div");
        div.innerHTML = template;
        document.body.appendChild(div);

        const textOptions = div.querySelectorAll("dt");
        Array.from(textOptions).forEach(dt => {
            dt.addEventListener("click", selectTextOption);
        });

        /* adjust div */
        div.classList.add("centeredDiv");
        centerAbsoluteElement(div);
        addActionButtonForDiv(div, "hide");
    });
}

function selectTextOption(e) {
    const target = e.currentTarget;

    if (textGenerationData.currentTextStyleNode != null) {
        textGenerationData.currentTextStyleNode.classList.remove("bg-indigo-300");
        textGenerationData.currentTextStyleNode.classList.add("bg-slate-100");
    }

    target.classList.add("bg-indigo-300");
    target.classList.remove("bg-slate-100");
    textGenerationData.currentTextStyleNode = target;
}
