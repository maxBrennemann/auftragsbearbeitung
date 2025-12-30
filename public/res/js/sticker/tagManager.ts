import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings";

import { getStickerId, getStickerName } from "../pages/sticker";

const fnNames: { [key: string]: (...args: any[]) => void } = {};

export function initTagManager() {
    addBindings(fnNames);

    const tagInput = document.getElementById("tagInput") as HTMLInputElement;
    tagInput.addEventListener("keydown", addTagWithKey);

    ajax.get(`/api/v1/sticker/${getStickerId()}/tags-template`, {
        "title": getStickerName(),
    }).then((r: any) => {
        (document.getElementById("tagManager") as HTMLDivElement).innerHTML = r.data.template;
        addBindings(fnNames);
    });
}

fnNames.click_showTaggroupManager = () => {}

/**
 * Listens for a hashtag or enter to add a new tag
 * @param {KeyboardEvent} e 
 */
const addTagWithKey = (e: KeyboardEvent) => {
    if (e.key === "#" || e.key === "Enter") {
        addTag(e);
    }
}

fnNames.click_addNewTag = (e: Event) => {
    const dt = document.createElement("dt");
    dt.className = "cursor-default inline-flex rounded-lg font-semibold overflow-hidden";
    const tagInput = document.getElementById("tagInput") as HTMLInputElement;

    if (!validateValue(tagInput.value)) {
        alert("Der Tag ist zu lang oder enthält ein ungültiges Zeichen!");
        return;
    }

    const p = document.createElement("p");
    p.innerHTML = tagInput.value;
    p.className = "px-2 py-1 bg-blue-100";
    dt.appendChild(p);

    const remove = document.createElement("span");
    remove.innerHTML = "x";
    remove.className = "remove cursor-pointer px-2 py-1 bg-red-400 hover:bg-red-600";
    //remove.addEventListener("click", (e: Event) => this.manageTag);
    dt.appendChild(remove);

    tagInput.value = "";
    e.preventDefault();

    ajax.post(`/api/v1/sticker/tags`, {
        "id": getStickerId(),
        "tag": tagInput.value,
    });

    const noTags = document.getElementById("noTags") as HTMLElement;
    if (noTags) {
        noTags.parentNode!.removeChild(noTags);
    }

    const tagList = document.getElementById("tagList") as HTMLElement;
    tagList.appendChild(dt);
}

/**
 * Validates a text for the tag input
 * @param {string} text
 * @returns {boolean}
 */
const validateValue = (text: string) => {
    if (text.length > 32) {
        return false;
    }

    const excludedChars = `"!<;>;?=+#"°{}_$%.`.split('');
    excludedChars.forEach(char => {
        if (text.includes(char)) {
            return false;
        }
    });

    return true;
}

fnNames.click_loadSynonyms = () => {
    /*ajax.get(`/api/v1/sticker/tags/suggestions`, {
        id: getStickerId(),
        name: document.getElementById("name").value,
    }).then((results: any) => {
        const synonyms = results["synonyms"];

        if (synonyms.length == 0) {

        } else {

        }
    });*/
}

fnNames.click_manageTag = (e) => {
    const el = e.target;
    if (!el.classList.contains("remove")) {
        return;
    }

    const parent = el.parentNode.parentNode;
    const child = el.parentNode;

    /*if (element.parentNode.classList.contains("suggestionTag")) {
        parent.removeChild(child);
        return;
    }*/
}

const addTag = (e: KeyboardEvent) => {

}
