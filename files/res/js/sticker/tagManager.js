import { ajax } from "../classes/ajax.js";

export function initTagManager() {
    addTagListeners();

    const tagInput = document.getElementById("tagInput");
    tagInput.addEventListener("keydown", addTagWithKey);

    const tagButton = document.getElementById("addNewTag");
    tagButton.addEventListener("click", addTag);

    const synonyms = document.getElementById("loadSynonyms");
    synonyms.addEventListener("click", loadMoreSuggestions, false);

    const showTagGroupManagerBtn = document.getElementById("showTaggroupManager");
    showTagGroupManagerBtn.addEventListener("click", showTaggroupManager, false);
}

function showTaggroupManager() {
    
}

/**
 * Listens for a hashtag to add a new tag
 * @param {*} event 
 */
const addTagWithKey = (event) => {
    if (event.key === '#') {
        addTag(event);
    }
}

const addTag = (e) => {
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
        "id": mainVariables.motivId.innerHTML,
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
 * @returns 
 */
const validateValue = (text) => {
    var valid = true;
    if (text.length <= 32) {
        const excludedChars = `"!<;>;?=+#"°{}_$%.`.split('');
        excludedChars.forEach(char => {
            if (text.includes(char)) {
                valid = false;
            }
        });
    }

    return valid;
}

const loadMoreSuggestions = () => {
    ajax.get(`/api/v1/sticker/tags/suggestions`, {
        id: mainVariables.motivId.innerHTML,
        name: document.getElementById("name").value,
    }).then((results) => {
        const synonyms = results["synonyms"];

        if (synonyms.length == 0) {

        } else {

        }
    });
}

const addTagListeners = () => {
    const dts = document.querySelectorAll("dt");
    Array.from(dts).forEach(dt => {
        dt.addEventListener("click", manageTagClick);
    });
}

const manageTagClick = (e) => {
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
