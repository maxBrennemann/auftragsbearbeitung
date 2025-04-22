import {} from "./listcreator.js";

var l;

/* creation function */

function createNewList() {
    var name = document.getElementById("newListName").value;
    l = new Liste(name);

    document.getElementById("newListenpunktName").disabled = false;
    document.getElementById("createNewListenpunkt").disabled = false;
}

function createNewListenpunkt() {
    var name = document.getElementById("newListenpunktName").value;
    var checked = getCheckedValue();
    var lp = new Listenpunkt(name, checked);
    if (l != null) {
        l.addListenpunkt(lp);
        lp.showListenpunkt();
    }

    document.getElementById("newAuswahlName").disabled = false;
    document.getElementById("createNewListenauswahl").disabled = false;
}

function createNewAuswahl() {
    var name = document.getElementById("newAuswahlName").value;
    var lp = l.getCurrentLp();
    var aus = new Auswahl(name, lp, lp.type);
    lp.addAuswahl(aus);
    document.getElementById("newAuswahlName").value = "";
}

function getCheckedValue() {
    var type1 = document.getElementById("listenpunktOption1").checked;
    if (type1)
        return 1;
    
    var type2 = document.getElementById("listenpunktOption2").checked;
    if (type2)
        return 2;
    else
        return 3;
}

function saveList() {
    var list = {
        name : l.name,
        listenpunkte : {}
    }

    for (var i = 0; i < l.listenpunkte.length; i++) {
        var currLp = l.listenpunkte[i];

        var listenpunktObj = {};

        listenpunktObj.id = i;
        listenpunktObj.text = currLp.bezeichnung;
        listenpunktObj.type = currLp.type;
        listenpunktObj.auswahl = {}

        for (var n = 0; n < currLp.auswahl.length; n++) {
            var currAuswahl = currLp.auswahl[n];

            var auswahlObj = {};

            auswahlObj.text = currAuswahl.bezeichnung;
            auswahlObj.ordnung = n;

            listenpunktObj.auswahl["auswahl" + n] = auswahlObj;
        }

        list.listenpunkte[i] = listenpunktObj;
    }

    var json = JSON.stringify(list);

    var sendListData = new AjaxCall(`getReason=saveList&data=${json}`, "POST", window.location.href);
    sendListData.makeAjaxCall(function (response) {
        console.log(response);
        location.href = response;
    })
}

/* load lists */

function loadList() {

}

/* Liste */

var Liste = function(name) {
    this.name = name;
    this.listenpunkte = [];
    this.anchor = document.getElementById("listpreview");

    this.oncreate = function() {
        var title = document.createElement("h2");
        title.innerHTML = this.name;
        this.anchor.appendChild(title);
    }

    this.oncreate();
}

Liste.prototype.addListenpunkt = function (lp) {
    this.listenpunkte.push(lp);
    lp.setId(this.listenpunkte.length);
}

Liste.prototype.showListenpunkt = function() {

}

Liste.prototype.getCurrentLp = function() {
    return this.listenpunkte[this.listenpunkte.length - 1];
}

/* Listenpunkt */

var Listenpunkt = function(bezeichnung, type) {
    this.anchor = document.getElementById("listpreview");
    this.container = document.createElement("div");
    this.bezeichnung = bezeichnung;
    this.type = type;
    this.id;
    this.auswahl = [];
}

Listenpunkt.prototype.showListenpunkt = function() {
    var title = document.createElement("h4");
    title.innerHTML = this.bezeichnung;
    this.container.appendChild(title);
    this.anchor.appendChild(this.container);
}

Listenpunkt.prototype.addAuswahl = function(auswahl) {
    this.auswahl.push(auswahl);
}

Listenpunkt.prototype.setId = function(id) {
    this.id = id;
}

Listenpunkt.prototype.getId = function() {
    return this.bezeichnung + this.id;
}

/* Auswahl */

var Auswahl = function(bezeichnung, anchor, type) {
    this.bezeichnung = bezeichnung;
    this.anchor = anchor;
    this.container = document.createElement("div");

    this.oncreate = function() {
        var option = document.createElement("input");
        var label = document.createElement("label");
        option.name = this.anchor.getId();
        option.value = this.bezeichnung;
        label.for = this.bezeichnung;
        label.innerHTML = this.bezeichnung;

        switch (type) {
            case 1:
                option.type = "radio";
            break;
            case 2:
                option.type = "checkbox";
            break;
            case 3:
                option.type = "text";
                option.value = "";
            break;
        }

        if (type == 3) {
            this.container.appendChild(label);
            this.container.appendChild(option);
        } else {
            this.container.appendChild(option);
            this.container.appendChild(label);
        }
       
        this.anchor.container.appendChild(this.container);
    }

    this.oncreate();
}
