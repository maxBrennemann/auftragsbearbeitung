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

/* https://stackoverflow.com/questions/17651207/mask-us-phone-number-string-with-javascript */
document.getElementById('telmobil').addEventListener('input', function (e) {
    var x = e.target.value.replace(/\D/g, '').match(/(\d{0,4})(\d{0,})/);
    e.target.value = !x[2] ? x[1] : x[1] + ' ' + x[2];
});
