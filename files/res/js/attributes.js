import { ajax } from "./classes/ajax.js";

function init() {
    const btnAddAttribute = document.getElementById("btnAddAttribute");
    btnAddAttribute.addEventListener("click", addNewAttribute);

    const btnAbortAttribute = document.getElementById("btnAbortAttribute");
    btnAbortAttribute.addEventListener("click", function() {
        clearInputs({"ids": ["newName", "descr"]});
    });

    const btnAddAttributeValue = document.getElementById("btnAddValue");
    btnAddAttributeValue.addEventListener("click", addNewAttributeValue);
}

function addNewAttribute() {
    var name = document.getElementById("newName").value;
    var descr = document.getElementById("descr").value;

    ajax.post(`/api/v1/attribute`, {
        name: name,
        descr: descr,
    }).then(() => {
        infoSaveSuccessfull("success");

        /* adds new attribute container to top */
        var div = document.createElement("div");
        div.classList.add("defCont");
        div.classList.add("singleAttribute");

        var h2 = document.createElement("h2");
        h2.dataset.id = response;
        h2.innerHTML = name;

        var p = document.createElement("p");

        var i = document.createElement("i");
        i.innerHTML = descr;

        var ul = document.createElement("ul");
        ul.id = "attributeValues_" + response;

        div.appendChild(h2);
        div.appendChild(p);
        p.appendChild(i);
        div.appendChild(ul);

        document.getElementsByClassName("attributesContainer")[0].appendChild(div);

        /* adds new attribte to select */
        var option = document.createElement("option");
        option.value = response;
        option.innerText = args[0];

        document.getElementById("selectAttribute").appendChild(option);
    }).catch((error) => {
        console.error(error);
    });
}

function addNewAttributeValue() {
    var value = document.getElementById("newVal").value;
    var attribute = document.getElementById("selectAttribute").value;

    ajax.post(`/api/v1/attribute/${attribute}/value`, {
        value: value,
    }).then((response) => {
        var li = document.createElement("li");
            li.innerText = value;
            li.classList.add("bg-white", "rounded-md", "p-1", "pl-2", "hover:bg-blue-300");
            var ul = document.getElementById("attributeValues_" + attribute);
            ul.appendChild(li);

            document.getElementById("newVal").value = "";
    }).catch((error) => {
        console.error(error);
    });
}

if (document.readyState !== 'loading' ) {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
