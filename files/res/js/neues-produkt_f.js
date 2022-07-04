/* further reading:
https://stackoverflow.com/questions/24923469/modeling-product-variants
https://dba.stackexchange.com/questions/123467/schema-design-for-products-with-multiple-variants-attributes?newreg=9504cc9890d1461ea745070f28f70543
https://stackoverflow.com/questions/19144200/designing-a-sql-schema-for-a-combination-of-many-to-many-relationship-variation
*/

document.getElementById("selectSource").addEventListener("change", function(event) {
    if (event.target.value == "addNew") {
        getHTMLForAddingSource();
    }
});

function getHTMLForAddingSource() {
    var getHTML = new AjaxCall(`getReason=fileRequest&file=source.html`, "POST", window.location.href);
    getHTML.makeAjaxCall(function (responseHTML) {
        var div = document.createElement("div");
        div.innerHTML = responseHTML;
        div.id = "htmlForAddingSource";
        div.classList.add("ajaxBox");
        document.body.appendChild(div);
        centerAbsoluteElement(div);
        addActionButtonForDiv(div, 'remove');
    });
}

function removeHTMLForAddingSource() {
    var child = document.getElementById("htmlForAddingSource");
    child.parentNode.removeChild(child);
    loadNewSelect();
}

function loadNewSelect() {
    var getSelect = new AjaxCall(`getReason=getSelect`, "POST", window.location.href);
    getSelect.makeAjaxCall(function (responseHTML) {
        var select = document.getElementById("selectSource");
        select.innerHTML = responseHTML;
    });
}

function sendSource() {
    var name = document.getElementById("getName").value;
    var desc = document.getElementById("getDesc").value;

    var send = new AjaxCall(`getReason=sendSource&name=${name}&desc=${desc}`, "POST", window.location.href);
    send.makeAjaxCall(function (responseHTML) {
        removeHTMLForAddingSource();
    });
}

function saveProduct() {
    var data = "",
        marke = document.getElementsByName("marke")[0].value,
        source = document.getElementById("selectSource"),
        quelle =  source.options[source.selectedIndex].value,
        vkNetto = document.getElementsByName("vk_netto")[0].value,
        ekNetto = document.getElementsByName("ek_netto")[0].value,
        title = document.getElementsByName("short_description")[0].value,
        desc = document.getElementsByName("description")[0].value;

    var send = new AjaxCall(`getReason=saveProduct&attData=${data}&marke=${marke}&quelle=${quelle}&vkNetto=${vkNetto}&ekNetto=${ekNetto}&title=${title}&desc=${desc}`, "POST", window.location.href);
    send.makeAjaxCall(function (responseLink) {
        console.log(responseLink);
        //window.location.href = responseLink;
    });
}
