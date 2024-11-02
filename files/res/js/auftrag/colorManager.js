export function addColor() {
    const template = document.getElementById("templateFarbe");
	const div = document.createElement("div");
    div.id = "selectColor";
	div.appendChild(template.content.cloneNode(true));
    div.classList.add("w-2/3");
    
    document.body.appendChild(div);

    const cp = new Colorpicker(div.querySelector("#cpContainer"));
    const c = div.querySelector("canvas");
    c.style.margin = "auto";

    c.addEventListener("mouseup", function() {
        const element = document.querySelector("input.colorInput.jscolor");

        if (!element) {
            return;
        }

        const hex = cp.color.toUpperCase();
        if (isHexValid(hex)) {
            element.value = hex;
            element.classList.add("outline-green-500");
            element.classList.remove("outline-red-500");
        } else {
            element.classList.remove("outline-green-500");
            element.classList.add("outline-red-500");
        }
    }, false);

    const colorInputHex = document.querySelector("input.colorInput.jscolor");
    colorInputHex.addEventListener("change", checkHexCode);

    const sendColorBtn = div.querySelector('[data-fun="sendColor"]');
    sendColorBtn.addEventListener("click", sendColor, false);

    const toggleCSBtn = div.querySelector('[data-fun="toggleCS"]');
    toggleCSBtn.addEventListener("click", toggleCS, false);

    addActionButtonForDiv(div, "remove");
    centerAbsoluteElement(div);
}

export function removeColor(e) {
    var colorId = e.currentTarget.dataset.color;

    ajax.post({
        r: "removeColor",
        auftrag: globalData.auftragsId,
        colorId: colorId,
    }, true).then(colorHTML => {
        var showColors = document.getElementById("showColors");
        showColors.innerHTML = colorHTML;
    });
}

/*
 * you can select multiple existing colors, which are added to this variable via the function
 * beneath;
 * all colors are highlighted via the colorElementUnderline class
 */
var addToOrderColors = [];
export function toggleCS() {
    var container = document.getElementById("csContainer");
    container.style.display = "block";
    centerAbsoluteElement(document.getElementById("farbe"));

    var elements = container.getElementsByClassName("singleColorContainer");
    for (let i = 0; i < elements.length; i++) {
        var e = elements[i];
        e.addEventListener("click", function(event) {
            event.currentTarget.classList.toggle("colorElementUnderline");
            let id = event.currentTarget.dataset.colorid;
            if (addToOrderColors.includes(id)) {
                let index = addToOrderColors.indexOf(id);
                addToOrderColors.slice(index, -1);
            } else {
                addToOrderColors.push(id);
            }
        }, false);
    }
}

/*
 * adds all selected colors to the order;
 */
export function addSelectedColors() {
    ajax.post({
        r: "existingColors",
        auftrag: globalData.auftragsId,
        ids: JSON.stringify(addToOrderColors),
    }, true).then(colorHTML => {
        var showColors = document.getElementById("showColors");
        var data = JSON.parse(colorHTML);
        showColors.innerHTML = data.farben;
        
        var elements = document.getElementsByClassName("colorInput");
        for (let i = 0; i < elements.length; i++) {
            elements[i].value = "";
        }
    });
}

/*
 * sends the newly created color to the backend;
 * then resets the form and shows the newly added color
 */
export function sendColor() {
    var elements = document.getElementsByClassName("colorInput");
    var data = [], currVal;

    for (let i = 0; i < elements.length; i++) {
        currVal = elements[i].value;
        if (currVal == null || currVal == "") {
            alert("Felder dürfen nicht leer sein!");
            return null;
        }
        data.push(currVal);
    }

    ajax.post({
        r: "newColor",
        auftrag: globalData.auftragsId,
        farbname: data[0],
        farbwert: data[3],
        bezeichnung: data[1],
        hersteller: data[2],
    }, true).then(colorHTML => {
        var showColors = document.getElementById("showColors");
        var data = JSON.parse(colorHTML);
        showColors.innerHTML = data.farben;
        
        var elements = document.getElementsByClassName("colorInput");
        for (let i = 0; i < elements.length; i++) {
            elements[i].value = "";
        }
    });
}

export function checkHexCode(e) {
    const el = e.currentTarget;

    if (isHexValid(el.value)) {
        el.classList.add("outline-green-500");
        el.classList.remove("outline-red-500");
        return null;
    }

    el.classList.remove("outline-green-500");
    el.classList.add("outline-red-500");
}

export const isHexValid = (hex) => {
    return /^[0-9a-fA-F]{6}$/.test(hex);
}
