export class List {

    constructor(title) {
        this.title = title;
        this.listElements = [];
    }

    addListElement() {

    }
}

export class ListElement {

    constructor(elementTitle) {
        this.elementTitle = elementTitle;
        this.listElementNodes = [];
    }

    addListElementNode() {

    }
}

export class ListElementNode {

    constructor(nodeTitle, nodeType) {
        this.nodeTitle = nodeTitle;
        this.nodeType = nodeType;
    }

}
