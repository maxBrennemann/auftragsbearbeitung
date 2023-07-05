import { initBindings } from "./classes/bindings.js";
import { click_mehListener } from "./auftrag/postenManager.js";
import { addColor, addSelectedColors, checkHexCode, removeColor } from "./auftrag/colorManager.js";
import { addBearbeitungsschritt, showBearbeitungsschritt, addNote, removeNote, addNewNote } from "./auftrag/noteStepManager.js";
import { setOrderFinished, updateDate, updateDeadline, setDeadlineState, initExtraOptions, editDescription, editOrderType, editTitle, archvieren } from "./auftrag/orderManager.js";
import { addExistingVehicle, addNewVehicle, selectVehicle } from "./auftrag/vehicleManager.js";
import { addProductCompactOld, addLeistung, addTime, addTimeInputs, selectLeistung, initPostenFilter, addProductCompact, calcTime, showPostenAdd } from "./auftrag/postenManager.js";
import "./auftrag/postenOrder.js";
import "./auftrag/calculateGas.js";

/* global variables */
window.globalData = {
    aufschlag: 0,
    vehicleId: 0,
    erledigendeSchritte : null,
    alleSchritte : null,
    auftragsId : parseInt(new URL(window.location.href).searchParams.get("id")),
    times : {
        0: "00:00",
        1: "00:00"
    },
}

const fnNames = {};
fnNames.click_mehListener = click_mehListener;
fnNames.click_changeContact = changeContact;

fnNames.click_addColor = addColor;
fnNames.click_removeColor = removeColor;
fnNames.click_addSelectedColors = addSelectedColors;
fnNames.write_checkHexCode = checkHexCode;

fnNames.click_addBearbeitungsschritt = addBearbeitungsschritt;
fnNames.click_showBearbeitungsschritt = showBearbeitungsschritt;
fnNames.click_addNote = addNote;
fnNames.click_removeNote = removeNote;
fnNames.click_addNewNote = addNewNote;

fnNames.click_setOrderFinished = setOrderFinished;
fnNames.write_updateDate = updateDate;
fnNames.write_updateDeadline = updateDeadline;
fnNames.write_editDescription = editDescription;
fnNames.write_editOrderType = editOrderType;
fnNames.write_editTitle = editTitle;
fnNames.click_setDeadlineState = setDeadlineState;
fnNames.click_archvieren = archvieren;

fnNames.click_addExistingVehicle = addExistingVehicle;
fnNames.click_addNewVehicle = addNewVehicle;
fnNames.write_selectVehicle = selectVehicle;

fnNames.click_showPostenAdd = showPostenAdd;
fnNames.click_addProductCompactOld = addProductCompactOld;
fnNames.click_addLeistung = addLeistung;
fnNames.click_addTime = addTime;
fnNames.click_addTimeInputs = addTimeInputs;
fnNames.click_addProductCompact = addProductCompact;
fnNames.write_selectLeistung = selectLeistung;

if (document.readyState !== 'loading' ) {
    initCode();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        initCode();
    });
}

function initCode() {
    initBindings(fnNames);

    /* auto sizes textareas on page load */
	var timeInputs = document.getElementsByClassName("timeInput");
	for (let t of timeInputs) {
		t.addEventListener("change", calcTime, false);
	}

    addSearchEventListeners();

    if (document.getElementById("selectVehicle") == null) {
        return;
    }
    
    document.getElementById("selectVehicle").addEventListener("change", function(event) {
        if (event.target.value == "addNew") {
            document.getElementById("addVehicle").style.display = "inline-block";
        }
    });

    initPostenFilter();
    initExtraOptions();
}

function addSearchEventListeners() {
    var data = document.getElementsByClassName("searchProductEvent");
    for (let i = 0; i < data.length; i++) {
        data[i].addEventListener("click", performProductSearch, false);
    }
    document.getElementById("productSearch").addEventListener("change", performProductSearch, false);
}

function performProductSearch() {
    var query = document.getElementById("productSearch").value;
    
    var params = {
        getReason: "searchProduct",
        query: query
    };
    
    var search = new AjaxCall(params, "POST", window.location.href);
    search.makeAjaxCall(function (response) {
        var element = document.getElementById("resultContainer");
        element.innerHTML = response;
    });
}

/* changes the contact person connected with the order */
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

window.performSearch = function(e) {
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
        close(div);
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

/* function starts deletion of the row */
window.deleteRow = function(key, type = "schritte", node) {
    let row = node.parentNode.parentNode;
    let header = row.parentNode.children[0];

    showDeleteMessage(row, header, key, type);
}

window.updateIsDone = function(key, event) {
    var update = new AjaxCall(`getReason=update&key=${key}&auftrag=${globalData.auftragsId}`, "POST", window.location.href);
    update.makeAjaxCall(function (response, args) {
        console.log(response);
        /* removes the row */
        let button = args[0];
        let row = button.parentNode.parentNode;
        row.parentNode.removeChild(row);
    }, event.target);
}

window.showAuftrag = function() {
    var url = window.location.href;
    url += "&show=t";
    window.location.href = url;
}

fnNames.click_showAuftragsverlauf = function() {}

window.chooseProduct = function(productId) {
    var amount = document.getElementById(productId + "_getAmount").value;
    var isFree = getOhneBerechnung() ? 1 : 0;
    var addToInvoice = getAddToInvoice() ? 1 : 0;
    var add = new AjaxCall(`getReason=insertProduct&product=${productId}&amount=${amount}&auftrag=${globalData.auftragsId}&ohneBerechnung=${isFree}&addToInvoice=${addToInvoice}`, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        console.log(response);
        reloadPostenListe();
    });
}

/* shows auftragsblatt, from: https://stackoverflow.com/questions/19851782/how-to-open-a-url-in-a-new-tab-using-javascript-or-jquery */
window.showPreview = function() {
    let link = document.getElementById("home_link").href + "pdf?type=auftrag&id=" + globalData.auftragsId;
    var win = window.open(link, '_blank');
    if (win) {
       win.focus();
    }
}

/* from https://www.w3schools.com/howto/tryit.asp?filename=tryhow_js_tabs and modified */
window.openTab = function(evt, id) {
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

/* performAction section of the table */
window.performAction = function(key, event) {
    /* centered upload div */
    var div = document.createElement("div");
    var form = document.createElement("form");
    var input = document.createElement("input");

    input.type = "file";
    input.name = "uploadedFile";
    input.setAttribute("form", "uploadFilesPosten");
    form.classList.add("fileUploader");
    form.name = "postenAttachment";
    form.dataset.target = "postenAttachment";
    form.id = "uploadFilesPosten";

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
    addActionButtonForDiv(div, "remove");

    /* add new file uploader */
    fileUploaders.push(new FileUploader(form));
    centerAbsoluteElement(div);
}
