function click_textGeneration(e) {
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

/*
{"id":"chatcmpl-733SsQWOmlXuP43yY9tZUgUhMGsZ9","object":"chat.completion","created":1680962658,"model":"gpt-3.5-turbo-0301","usage":{"prompt_tokens":53,"completion_tokens":50,"total_tokens":103},"choices":[{"message":{"role":"assistant","content":", unterhaltsam und verkaufsfördernd.\n\nStehst du auch regelmäßig vor dem Regal im Supermarkt und weißt einfach nicht, welches Produkt du kaufen sollst? Das Problem kennen wir alle!"},"finish_reason":"length","index":0}]}


{"id":"chatcmpl-733biNZdVxJLpBnn01pCX8HXHQg0Y","object":"chat.completion","created":1680963206,"model":"gpt-3.5-turbo-0301","usage":{"prompt_tokens":53,"completion_tokens":100,"total_tokens":153},"choices":[{"message":{"role":"assistant","content":"und ansprechend, aber auch informativ und überzeugend. Vielen Dank!\n\nGute Tests sind wie Schatzkarten – sie führen uns geradewegs zu den besten Produkten! Und damit jeder sieht, welches das beste Produkt ist, gibt es jetzt den \"guter test\" Aufkleber! Der grüne Smiley mit den Daumen hoch ist das neue Gütesiegel für alle, die Wert auf Qualität legen.\n\nDieser Aufkleber"},"finish_reason":"length","index":0}]}


{"id":"chatcmpl-733cUMhQ912ehjb07CZkxyLtJrNna","object":"chat.completion","created":1680963254,"model":"gpt-3.5-turbo-0301","usage":{"prompt_tokens":53,"completion_tokens":78,"total_tokens":131},"choices":[{"message":{"role":"assistant","content":"und humorvoll.\n\n\"Mit unserem Aufkleber 'guter test' sieht jeder sofort, wer hier den Durchblick hat! Denn wer den Test besteht, verdient ganz klar eine Medaille – oder zumindest diesen genialen Aufkleber. Zeig der ganzen Welt, dass du ein Sieger bist – und lass den Rest vor Neid erblassen!\""},"finish_reason":"stop","index":0}]}
*/
