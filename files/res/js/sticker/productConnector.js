import { ajax } from "js-classes/ajax.js";
import { notification } from "js-classes/notifications.js";

import { createPopup } from "../global.js";
import { getStickerId } from "../sticker.js";

class ProductConnector {

    constructor(type) {
        this.type = type;
        this.results = [];
        this.searchContainer;
    }

    getSearchResults() {
        const query = this.searchContainer.querySelector("#searchShopQuery").value;

        ajax.post({
            query: query,
            r: "searchShop"
        }).then((results) => {
            this.results = results;
            this.appendSearchResults()
        });
    }

    async showSearchContainer() {
        const template = document.createElement("template");
        template.innerHTML = await ajax.post({
            id: getStickerId(),
            type: this.type,
            r: "showSearch"
        }, true);

        const searchContainer = document.createElement("div");
        searchContainer.append(template.content.cloneNode(true));
        searchContainer.style.padding = "25px";
        searchContainer.classList.add("centeredDiv");

        createPopup(searchContainer);

        this.searchContainer = searchContainer;
        this.setEventListeners();
    }

    setEventListeners() {
        const searchQueryInput = this.searchContainer.querySelector("#searchShopQuery");
        searchQueryInput.addEventListener("keydown", (e) => {
            if (e.keyCode == 13) {
                this.getSearchResults();
            }
        });

        const searchBtn = this.searchContainer.querySelector("#searchShopBtn");
        searchBtn.addEventListener("click", () => this.getSearchResults());

        const alreadyConnected = this.searchContainer.querySelectorAll(`input[type="checkbox"]`);
        Array.from(alreadyConnected).forEach(input => {
            input.addEventListener("click", e => {
                this.removeProductAccessoire(e);
            })
        });
    }

    /* deletes the connection and removes it from the DOM */
    removeProductAccessoire(e) {
        const idProductReference = e.target.dataset.article;
        ajax.post({
            idProductReference: idProductReference,
            id: getStickerId(),
            type: this.type,
            r: "removeAccessoire",
        }).then(r => {
            if (r.status == "success") {
                const parent = e.target.parentNode;
                parent.parentNode.removeChild(parent);
            }
        });
    }

    appendSearchResults() {
        const showSearchResultsDiv = this.searchContainer.querySelector("#showSearchResults");
        showSearchResultsDiv.innerHTML = "";
        this.results.forEach(r => {
            const link = document.createElement("a");
            link.classList.add("text-blue-500", "font-semibold");
            link.href = r.link;
            link.target = "_blank";
            link.innerHTML = r.name;

            const span = document.createElement("span");
            span.classList.add("ml-2");
            span.appendChild(document.createTextNode(`Artikel ${r.id}: `));
            span.appendChild(link);

            const label = document.createElement("label");
            label.style.display = "block";
            
            const checkbox = document.createElement("input");
            checkbox.type = "checkbox";
            checkbox.addEventListener("click", e => this.connnectArticles(e));
            checkbox.dataset.id = r.id;
            
            label.appendChild(checkbox);
            label.appendChild(span);

            showSearchResultsDiv.appendChild(label);
        });
    }

    async connnectArticles(e) {
        const status = e.target.checked;
        const articleId = e.target.dataset.id;
        const title = e.target.value;
        const result = await ajax.post({
            articleId: articleId,
            id: getStickerId(),
            status: status,
            type: this.type,
            title: title,
            r: "connectAccessoire",
        });

        notification("", result.status);
    }
}

export const productConnector = (type) => {
    return new ProductConnector(type);
}
