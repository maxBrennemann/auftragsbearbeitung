function showAddAttribute() {
    document.getElementById("showDivAddAttribute").style.display = "inline";
}

function addNewAttribute() {
    var name = document.getElementById("newName").value;
    var descr = document.getElementById("descr").value;
    var ajax = new AjaxCall(`getReason=addAtt&name=${name}&descr=${descr}`);
    ajax.makeAjaxCall(function (response, args) {
        if (response == "-1") {
            infoSaveSuccessfull();
        } else {
            infoSaveSuccessfull("success");

            /* adds new attribute container to top */
            var div = document.createElement("div");
            div.classList.add("defCont");
            div.classList.add("singleAttribute");

            var h2 = document.createElement("h2");
            h2.dataset.id = response;
            h2.innerHTML = args[0];

            var p = document.createElement("p");

            var i = document.createElement("i");
            i.innerHTML = args[1];

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
        }
    }, name, descr);
}

function addNewAttributeValue() {
    var value = document.getElementById("newVal").value;
    var attribute = document.getElementById("selectAttribute");
    attribute =  attribute.options[attribute.selectedIndex].value;

    var ajax = new AjaxCall(`getReason=addAttVal&att=${attribute}&value=${value}`);
    ajax.makeAjaxCall(function (response, args) {
        if (response == "-1") {
            infoSaveSuccessfull();
        } else {
            var li = document.createElement("li");
            li.innerText = args[0]
            var ul = document.getElementById("attributeValues_" + args[1]);
            ul.appendChild(li);
        }
    }, value, attribute);
}
