class ProductConnector {

    constructor() {
        this.results = [];
        this.searchContainer;
    }

    getSearchResults() {
        const query = this.searchContainer.querySelector("#searchShopQuery").value;

        const results = ajax.post({
            query: query,
            r: "searchShop"
        }).then((results) => {
            results.forEach(r => {
                if (!this.results.includes(r)) {
                    this.results.push(r);
                }            
            });
            this.appendSearchResults()
        });
    }

    async showSearchContainer() {
        const template = document.createElement("template");
        template.innerHTML = await ajax.post({
            id: mainVariables.motivId.innerHTML,
            r: "showSearch"
        }, true);

        const searchContainer = document.createElement("div");
        searchContainer.append(template.content.cloneNode(true));
        document.body.appendChild(searchContainer);
        searchContainer.style.padding = "25px";
        searchContainer.classList.add("centeredDiv");

        const searchBtn = searchContainer.querySelector("#searchShopBtn");
        searchBtn.addEventListener("click", () => this.getSearchResults());

        addActionButtonForDiv(searchContainer, "hide");
        centerAbsoluteElement(searchContainer);

        this.searchContainer = searchContainer;
    }

    appendSearchResults() {
        const showSearchResultsDiv = this.searchContainer.querySelector("#showSearchResults");
        this.results.forEach(r => {
            const link = document.createElement("a");
            link.href = r.link;
            link.innerHTML = r.name;

            const span = document.createElement("span");
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
        const status = e.checked;
        const articleId = e.target.dataset.id;
        const result = await ajax.post({
            articleId: articleId,
            id: mainVariables.motivId.innerHTML,
            status: status,
            r: this.connnectArticles,
        });

        infoSaveSuccessfull(result);
    }
}