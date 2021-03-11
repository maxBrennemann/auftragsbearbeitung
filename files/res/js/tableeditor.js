var Table = function(columns) {
	this.columns = columns;
}

var currTable;
var tableName;
var sendTo;

function init() {
	console.log("initializing...");
	
	addableTables();
}

function addableTables(tname) {
    tableName = tname;
    var allowAddingContent = document.getElementsByClassName("allowAddingContent");
    var addingContentColumn = document.getElementsByClassName("addingContentColumn");

    if (allowAddingContent.length != 0) {
        sendTo = allowAddingContent[0].dataset.sendTo;

        if (allowAddingContent.length != 0) {
            var btn = document.createElement("button");
            btn.addEventListener("click", addContent, false);
            btn.innerHTML = "Hinzufügen";
            allowAddingContent[0].parentNode.appendChild(btn);
            currTable = new Table(0);
        }
    }

    if (addingContentColumn.length != 0) {
        for (var i = 0; i < addingContentColumn.length; i++) {
            var type = addingContentColumn[i].dataset.datatype;
            if (type == "number") {
                addingContentColumn[i].addEventListener("keyup", function (event) {
                    if (isNaN(event.key) && event.key.length == 1) {
                        var text = event.target.innerText;
                        text = text.replace(/\D/g, '');
                        event.target.innerText = text;
                    }
                }, false);
            } else if (!isNaN(type)) {
                addingContentColumn[i].addEventListener("keydown", function (event) {
                    type = event.target.dataset.datatype;
                    if (event.target.innerText.length > parseInt(type) && event.key.length == 1) {
                        event.target.innerText = event.target.innerText.slice(0, parseInt(type));
                    };
                }, false);
            }
        }
    }
}

function addContent() {
	var content = document.getElementsByClassName("addingContentColumn");
	var tableHead = document.getElementsByClassName("tableHead");
	
	if(content.length != 0) {
        let data = `getTable=${tableName}&getReason=${sendTo}&`;
        let isChecked = true;
		for(let i = 0; i < content.length; i++) {
            console.log("content: " + content[i].innerHTML);
            if (content[i].innerHTML == "") {
                content[i].style.backgroundColor = "#FF0000AA";
                content[i].style.opacity = "70%";
                isChecked = false;
            }
            /*
             * Ausnahme für Anrede
             */
            if (tableHead[i].innerHTML == "Anrede") {
                var e = document.getElementById("selectAnrede");
                var anrede = e.options[e.selectedIndex].value;
                data += tableHead[i].innerHTML + "=" + anrede;
            } else {
                data += tableHead[i].innerHTML + "=" + content[i].innerHTML;
            }
            i != content.length - 1 ? data += "&" : 1;
        }
        if (isChecked) {
            let sendToDB = new AjaxCall(data, "POST", window.location.href);
            sendToDB.makeAjaxCall(function (responseTable) {
                var tableContainer = document.getElementById("tableContainer");
                tableContainer.innerHTML = responseTable;
                addableTables();
            });
        }
	} else {
		alert("leere");
    }
}

window.onload = function() {
	init();
}

/*class Table {
    constructor(html_table) {
        this.html_table = html_table;
        this.rows = html_table.rows;

        var temp = [];
        for (var i = 0; i < this.rows; i++) {
            temp.push(this.rows[i]);
        }
        this.rows = temp;
    }

    sortByRow(rowId) {
        this.rows.sort((a, b) => a[rowId] - b[rowId]);
        //this.html_table.rows = 
    }
}*/

/* adds a new line to a table to be sent to the server */
function tableAddnewLine() {
    let btn = event.target;

    /* addes new empty line which is set to be contenteditable */
    var identifier = btn.dataset.table;
    var table = document.querySelector(`[data-key="${identifier}"]`);
    var newRow = table.insertRow();
    for (let i = 0; i < table.children[0].children[0].cells.length; i++) {
        let cell = newRow.insertCell();
        //cell.innerText = "...";
        cell.contentEditable = "true";
    }

    /* redraw table */
    let tableDisplay = table.style.display;
    table.style.display = "none";
    setTimeout(function() {
        table.style.display = tableDisplay;
    }, 20);
    
    /* sets button value to be checkable to send the data */
    btn.innerText = "✔";
    btn.onclick = function() {
        tableSendnewLine()
    };
}

function tableSendnewLine() {
    /* resets button */
    let btn = event.target;
    btn.innerText = "+";
    btn.onclick = function() {
        tableAddnewLine()
    };

    var identifier = btn.dataset.table;
    var table = document.querySelector(`[data-key="${identifier}"]`).children[0];
    var lastRow = table.children[table.children.length - 1];

    var data = {};
    for (let i = 0; i < lastRow.children.length; i++) {
        data[i] = lastRow.children[i].innerText;
    }

    data = JSON.stringify(data);

    /* ajax parameter */
    let params = {
        getReason: "addNewLine",
        data: data,
        key : identifier
    };

    var add = new AjaxCall(params, "POST", window.location.href);
    add.makeAjaxCall(function (response) {
        if (response == "ok") {
            console.log("data sent to server");
        }
    });
}
