export default function click_textGeneration(e) {
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
