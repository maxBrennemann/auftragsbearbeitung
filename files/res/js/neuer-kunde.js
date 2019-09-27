function addNextCustomerNumber() {
    var firstEl = document.getElementsByClassName("addingContentColumn")[0];
    var nextCustomerNumber = parseInt(firstEl.parentNode.previousSibling.firstChild.innerHTML);
    nextCustomerNumber++;
    firstEl.innerHTML = nextCustomerNumber;
    firstEl.id = "nextId";
}

function addSelection() {
    var secondEl = document.getElementsByClassName("addingContentColumn")[1];
    secondEl.innerHTML = `<select id="selectAnrede">
        <option value="0">Herr</option>
        <option value="1">Frau</option>
        <option value="2">Firma</option>
		</select>`;
}

function addAnsprechpartner() {
    var ansprechpartnerTable = document.getElementById("ansprechpartnerTable");
    ansprechpartnerTable.style.display = "inline";
}

function addDataToDB() {
    var tableCont = document.getElementsByClassName("ansprTableCont");
    var nextId = document.getElementById("nextId").innerHTML;
    var data = `getReason=insertAnspr&nextId=${nextId}&`;

    for (let i = 0; i < tableCont.length; i++) {
        data += tableCont[i].dataset.col + "=" + tableCont[i].innerHTML;
        i != tableCont.length - 1 ? data += "&" : 1;
    }

    let sendToDB = new AjaxCall(data, "POST", window.location.href);
    sendToDB.makeAjaxCall(function (res) {
        console.log(res);
    });
}

addNextCustomerNumber();
addSelection();
addAnsprechpartner();