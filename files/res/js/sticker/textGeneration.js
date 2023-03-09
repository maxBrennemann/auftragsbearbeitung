export default async function generateText(title, tags, type, length) {
    const settings = {
        title: title,
        tags: tags,
        type: type,
        length: length,
    }

    const text = await send(settings, "generateText");
}
