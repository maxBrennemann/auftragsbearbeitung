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

        /* adjust div */
        div.classList.add("centeredDiv");
        centerAbsoluteElement(div);
        addActionButtonForDiv(div, "hide");
    });
}
