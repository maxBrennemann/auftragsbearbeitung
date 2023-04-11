var tagManager = null;

function initTagManager() {
    tagManager = new TagManager();
    tagManager.addTagEventListeners();
}

function loadTags() {
    tagManager.loadMoreSuggestions();
}

function showTaggroupManager() {
    tagManager.showTagGroupManager();
}

function addTag(event) {
    if (event.key === '#') {
        tagManager.addTag(event);
    }
}

class TagManager {
    constructor() {
        this.tagGroupContainer = null;
        this.tagGroupGrid = null;
        this.taggroups = [];
        this.groups = [];
        this.data = [];
    }

    showTagGroupManager() {
        if (this.tagGroupContainer == null) {
            this.getTagGroupContainer();
        } else {
            this.tagGroupContainer.style.display = "block";
        }
    }

    getTagGroupContainer() {
        let div = document.createElement("div");
        let grid = document.createElement("div");
        grid.classList.add("tagGroupGrid");

        let p = document.createElement("p");
        p.innerHTML = "Taggruppen:";
        div.appendChild(p);
    
        document.body.appendChild(div);
        div.classList.add("centeredDiv");
        centerAbsoluteElement(div);
        addActionButtonForDiv(div, "hide");

        ajax.post({
            r: "getTagGroups",
        }).then(results => {
            this.data = results["tagGroups"];
            this.data.forEach(row => {
                const groupId = parseInt(row["groupId"]);
                const groupName = row["groupName"];
    
                const tagId = row["tagId"];
                const tagName = row["tagName"];

                if (!this.groups[groupId]) {
                    this.groups[groupId] = this.createTagGroupContainer(groupId, groupName);
                    grid.appendChild(this.groups[groupId]);
                }
    
                let dt = document.createElement("dt");
                dt.classList.add("inline");
                dt.innerHTML = tagName;
                dt.id = "tag" + tagId;
                this.groups[groupId].appendChild(dt);
            });

            centerAbsoluteElement(this.tagGroupContainer);
        });

        let input = document.createElement("input");
        input.id = "newTagGroupTitle";
        let btn = document.createElement("button");
        btn.innerHTML = "Neue Taggruppe hinzufügen";
        btn.addEventListener("click", () => this.newTagGroup());

        div.appendChild(input);
        div.appendChild(btn);
        div.appendChild(grid);
    
        this.tagGroupContainer = div;
        this.tagGroupGrid = grid;
    }

    createTagGroupContainer(id, title) {
        const div = document.createElement("div");
        const p = document.createElement("p");
        p.innerHTML = title;
        p.dataset.tagGroupId = id;

        const button = document.createElement("button");
        button.innerHTML = "Alle übernehmen";
        button.addEventListener("click", (e) => this.addTagGroup());
        p.appendChild(button);

        div.appendChild(p);
        div.classList.add("defCont");
        return div;
    }

    /**
     * adds all tags in that group to the current product
     */
    addTagGroup() {

    }

    /**
     * adds a tag to the current product
     */
    addTagToProduct() {

    }

    /**
     * adds a tag to a tag group
     */
    addTagToGroup() {

    }

    newTagGroup() {
        const title = document.getElementById("newTagGroupTitle").value;
        ajax.post({
            title: title,
            r: "addNewTagGroup",
        }).then(r => {
            if (r["status"] == "success") {
                const idTagGroup = r["idTagGroup"];
                this.groups[idTagGroup] = this.createTagGroupContainer(idTagGroup, title);
                this.tagGroupGrid.appendChild(this.groups[idTagGroup]);
            } else {
                infoSaveSuccessfull();
            }
        });
    }

    validateValue(text) {
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

    addTag(e) {
        var dt = document.createElement("dt");
        var newTagValue = e.target.value;

        const valid = this.validateValue(newTagValue);
        if (valid) {
            dt.innerHTML = newTagValue;

            var remove = document.createElement("span");
            remove.innerHTML = "x";
            remove.classList.add("remove");
            remove.addEventListener("click", e => this.manageTag);
    
            dt.appendChild(remove);
            e.target.parentNode.children[0].appendChild(dt);
            e.target.value = "";
            e.preventDefault();
    
            ajax.post({
                id: mainVariables.motivId.innerHTML,
                tag: newTagValue,
                r: "addTag",
            });
        } else {
            alert("Der Tag ist zu lang oder enthält ein ungültiges Zeichen!");
        }
    }

    loadMoreSuggestions() {
        ajax.post({
            id: mainVariables.motivId.innerHTML,
            name: document.getElementById("name").value,
            r: "getMoreTagSuggestions"
        }).then((results) => {
            const synonyms = results["synonyms"];

            if (synonyms.length == 0) {

            } else {

            }
        });
    }

    manageTag(e) {
        let element = e.target;
        if (element.classList.contains("remove")) {
            var parent = element.parentNode.parentNode;
            var child = element.parentNode;

            if (element.parentNode.classList.contains("suggestionTag")) {
                parent.removeChild(child);
                return;
            }

            ajax.post({
                id: mainVariables.motivId.innerHTML,
                tag: child.childNodes[0].textContent,
                r: "removeTag",
            }).then(r => {
                if (r["status"] == "success") {
                    parent.removeChild(child);
                    infoSaveSuccessfull("success");
                }
            });
        } else if (element.classList.contains("suggestionTag")) {
            element.classList.remove("suggestionTag");
            ajax.post({
                id: mainVariables.motivId.innerHTML,
                tag: e.target.childNodes[0].textContent,
                r: "addTag",
            });
        } else {
            //load more suggestions
        }
    }

    addTagEventListeners() {
        var dts = document.querySelectorAll("dt");
        for (let i = 0; i < dts.length; i++) {
            let node = dts[i];
            node.addEventListener("click", this.manageTag);
        };
    }

}
