function addList() {
    var el = document.getElementById("listauswahl");
    el.style.display = "block";

    centerAbsoluteElement(el);
    addActionButtonForDiv(el, 'hide');
}

function chooseList(listid) {
    var get = new AjaxCall(`getReason=getList&listId=${listid}`, "POST", window.location.href);
    get.makeAjaxCall(function (response) {
        var anchor = document.getElementsByClassName("liste")[0];
        anchor.innerHTML = response;
    });
}

function saveListData() {
    var add = new AjaxCall(`getReason=saveListData&listId=${listid}&data=${data}`, "POST", window.location.href);
    add.makeAjaxCall(function (response) {});
}
