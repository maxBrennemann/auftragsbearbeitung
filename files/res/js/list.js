function addList() {
    var el = document.getElementById("listauswahl");
    el.style.display = "block";

    centerAbsoluteElement(el);
    addActionButtonForDiv(el, 'hide');
}

function chooseList(listid) {
    var get = new AjaxCall(`getReason=getList&listId=${listid}`, "POST", window.location.href);
    get.makeAjaxCall(function (response) {
        /* appending list response as div to liste div */
        var anchor = document.getElementsByClassName("liste")[0];
        var div = document.createElement("div");
        div.innerHTML = response;
        anchor.appendChild(div);
    });

    /* checking if globalData from order page exists, if so, list is added to order */
    if (globalData != null && globalData.auftragsId != null)
        addListToOrder(listid);
}

function saveListData(listid, listname, listvalue, listtype) {
    var add = new AjaxCall(`getReason=saveListData&listId=${listid}&id=${listname}&value=${listvalue}&type=${listtype}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        infoSaveSuccessfull(response);
        console.log(response);
    });
}

function addListToOrder(listid) {
    var listToOrder = new AjaxCall(`getReason=addListToOrder&listId=${listid}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
    listToOrder.makeAjaxCall(function (response) {
        console.log(response);
    });
}

if (document.readyState !== 'loading' ) {
    addListenListener();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        addListenListener();
    });
}

function addListenListener() {
    var lists = document.getElementsByClassName("listen");
    for (let list of lists) {
       var inputs = list.getElementsByTagName("input");
       for (let input of inputs) {
           input.addEventListener("change", function(e) {
               console.log(e.target.value + " " + e.target.name);

               var listid = list.id.slice(6),
                listname = e.target.name,
                listvalue = e.target.value;
                listtype = e.target.type;

               saveListData(listid, listname, listvalue, listtype);
           }, false);
       }
    }
}
