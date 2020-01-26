function performSearchButton(e) {
	var query = e.target.previousSibling.value;
	console.log(query);
	ajaxSearch(query);
}

function performSearchEnter(e, query) {
    if (e.key === "Enter") {
        ajaxSearch(query);
    }
}

function ajaxSearch(query) {
    if (isNaN(query)) {
        var search = new AjaxCall(`getReason=search&query=${query}&stype=kunde&urlid=1&shortSummary=false`, "POST", window.location.href);
        search.makeAjaxCall(function (responseTable) {
            document.getElementById("searchResults").innerHTML = responseTable;
            addableTables();
        });
    } else {
        window.location.href = window.location.href + "?kdnr=" + query;
    }
}

function auftragHinzufuegen() {
    var bez = document.getElementById("bezeichnung").value;
    var bes = document.getElementById("beschreibung").value;
    var typ = document.getElementById("typ").value;
    var ter = document.getElementById("termin").value;
    var kdn = new URL(window.location.href).searchParams.get("kdnr") ||
                new URL(window.location.href).searchParams.get("id");

    var e = document.getElementById("selectMitarbeiter");
    var ang = e.options[e.selectedIndex].value;
    e = document.getElementById("selectAngenommen");
    var per = e.options[e.selectedIndex].value;
    e = document.getElementById("selectAnsprechpartner");
    var ans = 0;
    if (e != null) {
        ans = e.options[e.selectedIndex].value;
    }
    

    var paramString = new URLSearchParams();
    paramString.append("bez", bez);
    paramString.append("bes", bes);
    paramString.append("typ", typ);
    paramString.append("ter", ter);
    paramString.append("ang", ang);
    paramString.append("kdn", kdn);
    paramString.append("per", per);
    paramString.append("ans", ans);

    paramString = paramString.toString() + "&type=auftrag&getReason=createAuftrag";

    console.log(paramString);

    var createAuftrag = new AjaxCall(paramString, "POST", window.location.href);
    createAuftrag.makeAjaxCall(function (responseLink) {
        console.log(responseLink);

        document.getElementsById("absenden").disabled = true;
        document.getElementsById("showLinkToOrder").style.display = "inline";
        document.getElementsById("showLinkToOrder").innerHTML = `<p>Falls Sie nicht automatisch weitergeleitet werden, bitte <a href=\"${responseLink}\">hier klicken</a></p>`;

        window.location.href = responseLink;
    });
}
