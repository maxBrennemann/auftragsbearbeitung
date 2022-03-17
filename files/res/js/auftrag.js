
/* global variables */
var globalData = {
    aufschlag: 0,
    vehicleId: 0,
    erledigendeSchritte : null,
    alleSchritte : null,
    auftragsId : parseInt(new URL(window.location.href).searchParams.get("id"))
}

if (document.readyState !== 'loading' ) {
    console.log( 'document is already ready, just execute code here' );
    initCode();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        console.log( 'document was not ready, place code here' );
        initCode();
    });
}

function initCode() {
    if (document.getElementById("selectVehicle") == null)
        return null;
    
    document.getElementById("selectVehicle").addEventListener("change", function(event) {
        if (event.target.value == "addNew") {
            document.getElementById("addVehicle").style.display = "inline-block";
        }
    });
}

/* get selection for adding a posten */
function getSelections() {
    var e = document.getElementById("selectPosten");
    var strUser = e.options[e.selectedIndex].value;

    if (strUser == "zeit") {
        document.getElementById("addPostenZeit").style.display = "block";
        document.getElementById("addPostenLeistung").style.display = "none";
        document.getElementById("addPostenProdukt").style.display = "none";
    } else if (strUser == "leistung") {
        document.getElementById("addPostenLeistung").style.display = "flex";
        document.getElementById("addPostenZeit").style.display = "none";
        document.getElementById("addPostenProdukt").style.display = "none";

        document.getElementById("ekp").addEventListener("input", function () {
            var startCalc = parseInt(document.getElementById("ekp").value);
            var price = startCalc * (1 + (globalData.aufschlag / 100));
            document.getElementById("pre").value = price;
        }, false);
    } else if (strUser == "produkt") {
        /*var showProducts = new AjaxCall(`getReason=createTable&type=product_compact&sendTo=`, "POST", window.location.href);
        showProducts.makeAjaxCall(function (responseTable) {
            document.getElementById("addPosten").innerHTML = responseTable;

            addableTables("product_compact");
        });*/
        document.getElementById("addPostenLeistung").style.display = "none";
        document.getElementById("addPostenZeit").style.display = "none";
        document.getElementById("addPostenProdukt").style.display = "block";
    }

    document.getElementById("showOhneBerechnung").style.display = "inline";
    document.getElementById("showDiscount").style.display = "inline";
}

function addTime() {
    let params = {
        getReason: "insTime",
        time: document.getElementById("time").value,
        wage: document.getElementById("wage").value,
        auftrag: globalData.auftragsId,
        descr: document.getElementById("descr").value,
        ohneBerechnung: getOhneBerechnung() ? 1 : 0,
        discount: document.getElementById("showDiscount").children[0].value
    };

    var add = new AjaxCall(params, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        updatePrice(response);
        reloadPostenListe();
        infoSaveSuccessfull("success");
        clearInputs(["time", "wage", "descr"]);
    });
}

function addLeistung() {
    var e = document.getElementById("selectLeistung");

    let params = {
        getReason: "insertLeistung",
        lei: e.options[e.selectedIndex].value,
        bes: document.getElementById("bes").value,
        ekp: document.getElementById("ekp").value,
        pre: document.getElementById("pre").value,
        meh: document.getElementById("meh").value,
        anz: document.getElementById("anz").value,
        auftrag: globalData.auftragsId,
        ohneBerechnung: getOhneBerechnung() ? 1 : 0,
        discount: document.getElementById("showDiscount").children[0].value
    };

    var add = new AjaxCall(params, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        updatePrice(response);
        reloadPostenListe();
        infoSaveSuccessfull("success");
        clearInputs(["bes", "ekp", "pre", "meh", "anz"]);
    });
}

function addProductCompact() {
    let params = {
        getReason: "insertProductCompact",
        menge: document.getElementById("posten_produkt_menge").value,
        marke: document.getElementById("posten_produkt_marke").value,
        ekpreis: document.getElementById("posten_produkt_ek").value,
        vkpreis: document.getElementById("posten_produkt_vk").value,
        name: document.getElementById("posten_produkt_name").value,
        beschreibung: document.getElementById("posten_produkt_besch").value,
        auftrag: globalData.auftragsId,
        ohneBerechnung: getOhneBerechnung() ? 1 : 0,
        discount: document.getElementById("showDiscount").children[0].value
    };

    var add = new AjaxCall(params, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        console.log(response);
        reloadPostenListe();
        clearInputs(["posten_produkt_menge", "posten_produkt_marke", "posten_produkt_ek", "posten_produkt_vk", "posten_produkt_name", "posten_produkt_besch"]);
    });
}

function reloadPostenListe() {
    var reload = new AjaxCall(`getReason=reloadPostenListe&id=${globalData.auftragsId}`, "POST", window.location.href);
    reload.makeAjaxCall(function (response) {
        document.getElementById("auftragsPostenTable").innerHTML = response;
    });
}

function updatePrice(newPrice) {
    document.getElementById("gesamtpreis").innerText = new Intl.NumberFormat('de-DE', { style: 'currency', currency: 'EUR' }).format(newPrice);
}

class PostenListe {
    constructor() {

    }
}

function getOhneBerechnung() {
    return document.getElementById("ohneBerechnung").checked;
}

function showSelection(element) {
    document.getElementById('newPosten').style.display = 'inline';
    element.style.display = 'none';

    var getGeneralPosten = new AjaxCall("getReason=createTable&type=custom", "POST", window.location.href);
    getGeneralPosten.makeAjaxCall(function (responseTable) {
        document.getElementById("generalPosten").innerHTML = responseTable;
    });
}

/* addes bearbeitungsschritte */
function addBearbeitungsschritte() {
    var bearbeitungsschritte = document.getElementById("bearbeitungsschritte");
    bearbeitungsschritte.style.display = "block";

    if (document.getElementById("sendStepToServer") == null) {
        var btn = document.createElement("button");
        btn.id = "sendStepToServer";
        btn.innerHTML = "Hinzufügen";
        btn.addEventListener("click", function () {
            var tableData = document.getElementsByClassName("bearbeitungsschrittInput");
            var steps = [];
            for (var i = 0; i < tableData.length; i++) {
                steps.push(tableData[i].value);
            }

            if (steps[1] == "") {
                steps[1] = 0;
            }

            var el = document.getElementsByName("isAlreadyDone")[0];
            var radio = el.elements["isDone"];
            var hide;
            for (var i = 0; i < radio.length; i++) {
                if (radio[i].checked) {
                    hide = radio[i].value;
                    break;
                }
            }
            
            /* 0 = hide, 1 = show */
            hide = hide == "hide" ? 0 : 1;

            /* check for assigned task */
            let assigned = document.querySelector('input[name="assignTo"]');
            let assignedTo = "none";
            if (assigned.checked == true) {
                let e = document.getElementById("selectMitarbeiter");
                assignedTo = e.options[e.selectedIndex].value;
            }

            /* ajax parameter */
            let params = {
                getReason: "insertStep",
                bez: steps[0],
                datum: steps[1],
                auftrag: globalData.auftragsId,
                hide: hide,
                prio: steps[2],
                assignedTo: assignedTo
            };

            var add = new AjaxCall(params, "POST", window.location.href);
            add.makeAjaxCall(function (response) {
                console.log(response);
                document.getElementById("stepTable").innerHTML = response;

                var tableData = document.getElementsByClassName("bearbeitungsschrittInput");
                for (var i = 0; i < tableData.length; i++) {
                    tableData[i].value = "";
                }

                document.getElementById("bearbeitungsschritte").removeChild(document.getElementById("sendStepToServer"));
                document.getElementById("bearbeitungsschritte").style.display = "none";
            }.bind(this), false);
        }, false);

        bearbeitungsschritte.appendChild(btn);
    }
}

/* adds a note to the order */
function addNote() {
    var noteNode = document.querySelector(".noteInput");
    if (noteNode == undefined)
        return null;

    note = noteNode.value;
    noteNode.value = "";

    /* ajax parameter */
    let params = {
        getReason: "addNoteOrder",
        auftrag: globalData.auftragsId,
        note: note
    };

    var add = new AjaxCall(params, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        document.getElementById("noteContainer").innerHTML = response;
        infoSaveSuccessfull("success");
    }.bind(this), false);
}

/* function creates a popup window that asks the user whether he wants the note to be deleted or not */
function removeNote(event) {
    let note = event.target.parentNode.children[1].innerHTML;

    let number = indexInClass(event.target.parentNode);

    var div = document.createElement("div");
    let textnode = document.createTextNode(`Willst Du die Notiz "${note}" wirklich löschen?`);

    let btn_yes = document.createElement("button");
    btn_yes.innerHTML = "Ja";
    let btn_no = document.createElement("button");
    btn_no.innerHTML = "Nein";

    /* inner function to delete the node */
    function delNode(number, div) {
        div.parentNode.removeChild(div);

        console.log(number);

        /* ajax call to delete note from db, note is then removed from webpage */
        var del = new AjaxCall(`getReason=deleteNote&number=${number}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
        del.makeAjaxCall(function (response) {
            document.getElementById("noteContainer").innerHTML = response;
        });
    }

    /* inner function for node button to remove the div */
    function close(div) {
        div.parentNode.removeChild(div);
    }

    /* event listeners */
    btn_yes.addEventListener("click", function() {
        delNode(number, div);
    }, false);

    btn_no.addEventListener("click", function() {
        close(div);
    }, false);

    div.appendChild(textnode);
    div.appendChild(document.createElement("br"));
    div.appendChild(btn_yes);
    div.appendChild(btn_no);
    document.body.appendChild(div);

    addActionButtonForDiv(div, "remove");
    centerAbsoluteElement(div);
}

/* changes the contact */
async function changeContact() {
    var div = document.createElement("div");

    var kdnr = document.getElementById("kundennummer").innerHTML;
    var response = await makeAsyncCall("POST", `getReason=getAnspr&id=${kdnr}`, window.location.href)
        .then(function (response) {
            return response;
        })
        .catch(function (err) {
            console.error('Augh, there was an error!', err);
    });

    div.innerHTML = response;
    document.body.appendChild(div);

    var btn = div.getElementsByTagName("button");
    btn[0].addEventListener("click", function(event) {
        var checkedId = document.querySelector('input[name="anspr"]:checked');
        if (checkedId == null)
            return;
        checkedId = checkedId.dataset.ansprid;
        var sendAnsprId = new AjaxCall(`getReason=setAnspr&order=${globalData.auftragsId}&ansprId=${checkedId}`, "POST", window.location.href);
        sendAnsprId.makeAjaxCall(function (ansprResponse) {
            var data = JSON.parse(ansprResponse);
            var error = data[0];
            if (error == "ok") {
                infoSaveSuccessfull("success");
                document.getElementById("showAnspr").innerHTML = data[1];
            } else {
                alert(response);
                infoSaveSuccessfull();
            }
        });

        /* accesses the first childnode of the parent container, this child contains a close button */
        event.target.parentNode.children[0].click();
    }, false);

    addActionButtonForDiv(div, "hide");
    centerAbsoluteElement(div);
}

function performSearch(e) {
    var query = e.target.previousSibling.value;
    console.log(query);

    var search = new AjaxCall(`getReason=search&query=${query}&stype=produkt&shortSummary=true`, "POST", window.location.href);
    search.makeAjaxCall(function (responseTable) {
        var div = document.createElement("div");
        div.innerHTML = responseTable;
        div.id = "prodcutSearchContainer";
        document.body.appendChild(div);
        centerAbsoluteElement(div);
        addActionButtonForDiv(div, 'remove');

        addableTables();
    });
}

function addFahrzeug(param) {
    var kfz, fahrzeug;
    if (param != null && param) {
        if (globalData.vehicleId != 0) {
            var ajax = new AjaxCall(`getReason=attachCar&auftrag=${globalData.auftragsId}&fahrzeug=${globalData.vehicleId}`);
            ajax.makeAjaxCall(function (response) {
                document.getElementById("fahrzeugTable").innerHTML = response;
            });
        }
    } else {
        kfz = document.getElementById("kfz").value;
        fahrzeug = document.getElementById("fahrzeug").value;

        var kundennummer = document.getElementById("kundennummer").innerText;
        var add = new AjaxCall(`getReason=insertCar&kfz=${kfz}&fahrzeug=${fahrzeug}&kdnr=${kundennummer}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
        add.makeAjaxCall(function (response) {
            /* table is in the div after the addVehicle form */
            let el = document.getElementById("addVehicle").nextSibling;
            el.innerHTML = response;
        });
    }
}

function selectVehicle(event) {
    globalData.vehicleId = event.target.value;
}

function showDeleteMessage(row, header, key, type) {
    var div = document.createElement("div");

    /* creates table */
    let table = document.createElement("table");
    tbody = document.createElement("tbody");
    table.appendChild(tbody);
    tbody.appendChild(header.cloneNode(true));
    tbody.appendChild(row.cloneNode(true));

    /* creates inner text of deletion verification */
    let content = `Willst Du diese Zeile wirklich löschen?:<br>`;
    let contentNode = document.createElement("p");
    contentNode.innerHTML = content;
    contentNode.appendChild(table);

    /* creates the yes and no buttons */
    let btn_yes = document.createElement("button");
    btn_yes.innerHTML = "Ja";
    let btn_no = document.createElement("button");
    btn_no.innerHTML = "Nein";

    /* inner function to delete the node
     * type => type of data to be deleted
     * key => the key for the server so that the correct data is deleted
     * row => row for the frontend to be deleted 
     */
    function delNode(type, key, row) {
        var del = new AjaxCall(`getReason=delete&type=${type}&key=${key}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
        del.makeAjaxCall(function (response) {
            console.log(response);
        });

        while (row.nodeName != "TR") {
            row = row.parentNode;
        }

        row.parentNode.removeChild(row);
    }

    /* inner function for node button to remove the div */
    function close(div) {
        div.parentNode.removeChild(div);
    }

    /* event listeners */
    btn_yes.addEventListener("click", function() {
        delNode(type, key, row);
    }, false);

    btn_no.addEventListener("click", function() {
        close(div);
    }, false);

    /* adds all relevant nodes to the div */
    div.appendChild(contentNode);
    div.appendChild(document.createElement("br"));
    div.appendChild(btn_yes);
    div.appendChild(btn_no);
    document.body.appendChild(div);

    addActionButtonForDiv(div, "remove");
    centerAbsoluteElement(div);
}

function editRow(key, element) {
    /* create div with addPosten content */
    var div = document.createElement("div");

    /* copy posten input data into new div */
    var postenType = element.parentNode.parentNode.dataset.type; //"addPostenLeistung";
    var movePostenInput = document.getElementById(postenType);
    var moveOhneBerechnung = document.getElementById("showOhneBerechnung");
    var moveDiscount = document.getElementById("showDiscount");

    movePostenInput.style.display = "block";
    moveOhneBerechnung.style.display = "block";
    moveDiscount.style.display = "block";

    div.appendChild(movePostenInput);
    div.appendChild(moveOhneBerechnung);
    div.appendChild(moveDiscount);

    document.body.appendChild(div);

    addActionButtonForDiv(div, "hide");
    centerAbsoluteElement(div);

    /* sends token to server to overwrite a posten */
    var table = document.getElementById("auftragsPostenTable").children[0].dataset.key;
    var postenId = key;
    var update = new AjaxCall(`getReason=overwritePosten&postenId=${postenId}&table=${table}`, "POST", window.location.href);
    update.makeAjaxCall(function (response) {
        console.log(response);
    });

    /* moves back the addPosten content */
    let closeButton = div.querySelector(".closeButton");
    closeButton.addEventListener("click", function(event) {
        let div = event.target.parentNode;

        let addPosten = document.getElementById("addPosten");
        /* moves postenData to first place */
        div.children[1].style.display = "none";
        addPosten.insertBefore(div.children[1], addPosten.children[0]);
        /* remove last child, its a br tag */
        addPosten.removeChild(addPosten.children[addPosten.children.length - 1]);
        /* add OhneBerechnung */
        div.children[1].style.display = "none";
        addPosten.appendChild(div.children[1]);
        /* add br tag */
        addPosten.appendChild(document.createElement("br"));
        /* add Discount */
        div.children[1].style.display = "none";
        addPosten.appendChild(div.children[1]);

        /* its always position 1 because the elements are moved away and the list gets shorter */
    }, false);
}

/* function starts deletion of the row */
function deleteRow(key, type = "schritte", node) {
    let row = node.parentNode.parentNode;
    let header = row.parentNode.children[0];

    showDeleteMessage(row, header, key, type);
}

function updateIsDone(key, event) {
    var update = new AjaxCall(`getReason=update&key=${key}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
    update.makeAjaxCall(function (response, args) {
        console.log(response);
        /* removes the row */
        let button = args[0];
        let row = button.parentNode.parentNode;
        row.parentNode.removeChild(row);
    }, event.target);
}

function radio(val) {
    var stepTable = document.getElementById("stepTable");
    var params = "", data;
    if (val == "show") {
        params = `getReason=getAllSteps&auftrag=${globalData.auftragsId}`;
        data = globalData.alleSchritte;
    } else if (val == "hide") {
        params = `getReason=getOpenSteps&auftrag=${globalData.auftragsId}`;
        data = globalData.erledigendeSchritte;
    }
    
    if (data == null) {
        var add = new AjaxCall(params, "POST", window.location.href);
        add.makeAjaxCall(function (response, data) {
            stepTable.innerHTML = response;
            switch (data[0]) {
                case "show":
                    globalData.alleSchritte = response;
                break;
                case "hide":
                    globalData.erledigendeSchritte = response;
                break;
            }
        }, val);
    } else {
        stepTable.innerHTML = data;
    }
}

function selectLeistung(e) {
    globalData.aufschlag = parseInt(e.target.options[e.target.selectedIndex].dataset.aufschlag);
}

function addColor() {
    var div = document.getElementById("farbe");
    div.style.display = "block";

    addActionButtonForDiv(div, "hide");
    centerAbsoluteElement(div);

    var c = div.querySelector("canvas");
    c.style.margin = "auto";

    c.addEventListener("mouseup", function() {
        var element = document.querySelector("input.colorInput.jscolor");
        element.value = cp.color.toUpperCase();
        checkHexCode(element);
    }, false);
}

function removeColor(colorId) {
    console.log("removing color : " + colorId);

    var arch = new AjaxCall(`getReason=removeColor&auftrag=${globalData.auftragsId}&colorId=${colorId}`);
    arch.makeAjaxCall(function (colorHTML) {
        var showColors = document.getElementById("showColors");
        //var data = JSON.parse(colorHTML);
        showColors.innerHTML = colorHTML; //data.farben;
    });
}

/*
 * toggles the colorpicker div
 */
function toggleCP() {
    document.getElementById("cpContainer").style.display = "block";
    centerAbsoluteElement(document.getElementById("farbe"));
}

/*
 * you can select multiple existing colors, which are added to this variable via the function
 * beneath;
 * all colors are highlighted via the colorElementUnderline class
 */
var addToOrderColors = [];
function toggleCS() {
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
 * adds all selected colors to the server;
 */
function addSelectedColors() {
    var addcolors = new AjaxCall(`getReason=existingColors&auftrag=${globalData.auftragsId}&ids=${JSON.stringify(addToOrderColors)}`);
    addcolors.makeAjaxCall(function (colorHTML) {
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
 * sends the newly created color to the server;
 * then resets the form and shows the newly added color
 */
function sendColor() {
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
    
    var sendC = new AjaxCall(`getReason=newColor&auftrag=${globalData.auftragsId}&farbname=${data[0]}&farbwert=${data[3]}&bezeichnung=${data[1]}&hersteller=${data[2]}`);
    sendC.makeAjaxCall(function (colorHTML) {
        var showColors = document.getElementById("showColors");
        var data = JSON.parse(colorHTML);
        showColors.innerHTML = data.farben;
        
        var elements = document.getElementsByClassName("colorInput");
        for (let i = 0; i < elements.length; i++) {
            elements[i].value = "";
        }
    });
}

function showAuftrag() {
    var url = window.location.href;
    url += "&show=t";
    window.location.href = url;
}

function checkHexCode(el) {
    if (/^[0-9a-fA-F]{6}$/.test(el.value)) {
        el.parentNode.classList.add("validInput");
        el.parentNode.classList.remove("invalidInput");
        return null;
    }
    el.parentNode.classList.add("invalidInput");
    el.parentNode.classList.remove("validInput");
}

function showAuftragsverlauf() {
    var container = document.createElement("div");
}

function archvieren() {
    var arch = new AjaxCall(`getReason=archivieren&auftrag=${globalData.auftragsId}`);
    arch.makeAjaxCall(function () {
       var div = document.createElement("div");
       var a = document.createElement("a");
       a.href = document.getElementById("home_link").href;
       a.innerText = "Zurück zur Startseite";
       div.appendChild(a);
       centerAbsoluteElement(div);
       addActionButtonForDiv(div, 'remove');
       document.body.appendChild(div);
    });
}

/* product section */

function chooseProduct(productId) {
    var amount = document.getElementById(productId + "_getAmount").value;
    var isFree = getOhneBerechnung() ? 1 : 0;
    var add = new AjaxCall(`getReason=insertProduct&product=${productId}&amount=${amount}&auftrag=${globalData.auftragsId}&ohneBerechnung=${isFree}`, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        console.log(response);
        reloadPostenListe();
    });
}

/* edit title */
function editTitle(event) {
    var text = document.getElementById("orderTitle");
    text.contentEditable = true;
    text.classList.add("descriptionEditable");
    
    if (event.target.innerText == "✔") {
        var saveDescription = new AjaxCall(`getReason=saveTitle&text=${text.innerText}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
        saveDescription.makeAjaxCall(function (response) {
            if (response == "saved") {
                var text = document.getElementById("orderTitle");
                text.classList.remove("descriptionEditable");
                text.contentEditable = false;

                var btn = text.nextElementSibling;
                btn.innerText = "✎";

                infoSaveSuccessfull("success");
            } else
                console.log("not saved");
        });
    } else {
        event.target.innerText = "✔";
    }
}

/* edit description */
function editDescription(event) {
    var text = document.getElementById("orderDescription");
    text.contentEditable = true;
    text.classList.add("descriptionEditable");
    
    if (event.target.innerText == "Speichern") {
        var saveDescription = new AjaxCall(`getReason=saveDescription&text=${text.innerText}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
        saveDescription.makeAjaxCall(function (response) {
            if (response == "saved") {
                var text = document.getElementById("orderDescription");
                text.classList.remove("descriptionEditable");
                text.contentEditable = false;

                var btn = text.nextElementSibling;
                btn.innerText = "Bearbeiten";

                infoSaveSuccessfull("success");
            } else
                console.log("not saved");
        });
    } else {
        event.target.innerText = "Speichern";
    }
}

/* shows auftragsblatt, from: https://stackoverflow.com/questions/19851782/how-to-open-a-url-in-a-new-tab-using-javascript-or-jquery */
function showPreview() {
    let link = document.getElementById("home_link").href + "pdf?type=auftrag&id=" + globalData.auftragsId;
    var win = window.open(link, '_blank');
    if (win) {
       win.focus();
    }
}

/*
 * changes the text node that shows the date into an input field and adds an event listener to send
 * the new date to the server
 */
function changeDate(type, e) {
    let dateNode = document.getElementById("changeDate-" + type);

    let newInput = document.createElement("input");
    newInput.type = "date";

    dateNode.innerHTML = "";
    dateNode.appendChild(newInput);

    let sendToServer = function(newInput, type, target) {
        let date = newInput.value;
        var send = new AjaxCall(`getReason=updateDate&auftrag=${globalData.auftragsId}&date=${date}&type=${type}`);
        send.makeAjaxCall(function (response, args) {
            infoSaveSuccessfull(response);
            args[0].parentNode.innerHTML = args[0].value;
            args[1].onclick = function() {changeDate(args[2], event)};
            args[1].innerText = "✎";
        }, newInput, target, type);
    }

    e.target.innerHTML = "✔";
    e.target.onclick = function() {
        sendToServer(newInput, type, e.target)
    };
}

/* from https://www.w3schools.com/howto/tryit.asp?filename=tryhow_js_tabs and modified */
function openTab(evt, id) {
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" activetab", "");
    }
    document.getElementsByClassName("tabcontent")[id].style.display = "block";
    evt.currentTarget.className += " activetab";
}

function showPostenAdd() {
    document.getElementById("showPostenAdd").style.display = "";
}

/* performAction section of the table */
function performAction(key, event) {
    /* centered upload div */
    var div = document.createElement("div");
    var form = document.createElement("form");
    var input = document.createElement("input");

    input.type = "file";
    input.name = "uploadedFile";
    form.classList.add("fileUploader");
    form.name = "postenAttachment";
    form.dataset.target = "postenAttachment";

    form.appendChild(input);
    div.appendChild(form);

    /* hidden key input form */
    let hidden = document.createElement("input");
    hidden.name = "key";
    hidden.hidden = true;
    hidden.type = "text";
    hidden.value = key;
    form.appendChild(hidden);

    /* hidden key input form */
    let tableKey = document.createElement("input");
    tableKey.name = "tableKey";
    tableKey.hidden = true;
    tableKey.type = "text";
    tableKey.value = event.target.parentNode.parentNode.parentNode.parentNode.dataset.key;
	form.appendChild(tableKey);

    document.body.appendChild(div);
    centerAbsoluteElement(div);
    addActionButtonForDiv(div, "remove");

    /* add new file uploader */
    fileUploaders.push(new FileUploader(form));
}

/*
 * adds files to vehicles
 */
function addFileVehicle(key, event) {
    var form = document.getElementById("fileVehicle");
    form.style.display = "";

    /* hidden key input form */
    let hidden = document.createElement("input");
    hidden.name = "key";
    hidden.hidden = true;
    hidden.type = "text";
    hidden.value = key;
    form.appendChild(hidden);

    /* hidden key input form */
    let tableKey = document.createElement("input");
    tableKey.name = "tableKey";
    tableKey.hidden = true;
    tableKey.type = "text";
    tableKey.value = event.target.parentNode.parentNode.parentNode.parentNode.dataset.key;
    form.appendChild(tableKey);
}

/*
 * asks if an order should be set to be finished
 */
function setOrderFinished() {
    if (confirm('Möchtest Du den Auftrag als "Erledigt" markieren?')) {
        /* Erledigt */
        var send = new AjaxCall(`getReason=setOrderFinished&auftrag=${globalData.auftragsId}`);
        send.makeAjaxCall(function () {});
        document.getElementById("home_link").click();
    } else {
        /* Abbruch */
    }
}
