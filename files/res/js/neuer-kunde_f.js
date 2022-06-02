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

function switchOeffPriv(event) {
    var elements = document.getElementsByClassName("basicInfo");
    switch (event.value) {
        case "firma":
            elements[0].style.display = "inline";
            elements[1].style.display = "none";
            elements[2].style.display = "none";
            break;
        case "privat":
            elements[0].style.display = "none";
            elements[1].style.display = "inline";
            elements[2].style.display = "inline";
            break;
        default:
            console.log("error");
    }
}
