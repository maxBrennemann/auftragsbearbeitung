import { ajax } from "js-classes/ajax.js";
import { addBindings } from "js-classes/bindings.js";
import { getStickerId, getStickerName } from "../sticker.js";

const fnNames = {};

export function initTagManager() {
    addBindings(fnNames);

    const tagInput = document.getElementById("tagInput");
    tagInput.addEventListener("keydown", addTagWithKey);

    ajax.get(`/api/v1/sticker/${getStickerId()}/tags-template`, {
        "title": getStickerName(),
    }).then(r => {
        document.getElementById("tagManager").innerHTML = r.template;
        addBindings(fnNames);
    });
}

fnNames.click_showTaggroupManager = () => {
    
}

/**
 * Listens for a hashtag or enter to add a new tag
 * @param {*} e 
 */
const addTagWithKey = e => {
    if (e.key === "#" || e.key === "Enter") {
        addTag(e);
    }
}

fnNames.click_addNewTag = () => {
    const dt = document.createElement("dt");
    dt.className  = "cursor-default inline-flex rounded-lg font-semibold overflow-hidden";
    const tagInput = document.getElementById("tagInput");

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
    remove.addEventListener("click", e => this.manageTag);
    dt.appendChild(remove);

    tagInput.value = "";
    e.preventDefault();

    ajax.post(`/api/v1/sticker/tags`, {
        "id": getStickerId(),
        "tag": tagInput.value,
    });

    const noTags = document.getElementById("noTags");
    if (noTags) {
        noTags.parentNode.removeChild(noTags);
    }

    const tagList = document.getElementById("tagList");
    tagList.appendChild(dt);
}

/**
 * Validates a text for the tag input
 * @param {string} text 
 * @returns {boolean}
 */
const validateValue = (text) => {
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
    ajax.get(`/api/v1/sticker/tags/suggestions`, {
        id: getStickerId(),
        name: document.getElementById("name").value,
    }).then((results) => {
        const synonyms = results["synonyms"];

        if (synonyms.length == 0) {

        } else {

        }
    });
}

fnNames.click_manageTag = (e) => {
    const el = e.target;
    if (!el.classList.contains("remove")) {
        return;
    }

    const parent = el.parentNode.parentNode;
    const child = el.parentNode;

    if (element.parentNode.classList.contains("suggestionTag")) {
        parent.removeChild(child);
        return;
    }
}
