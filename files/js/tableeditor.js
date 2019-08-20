var Table = function(columns) {
	this.columns = columns;
}

var currTable;

function init() {
	console.log("initializing...");
	
	addableTables();
}

function addableTables() {
	var allowAddingContent = document.getElementsByClassName("allowAddingContent");
	
	if(allowAddingContent.length != 0) {
		var btn = document.createElement("button");
		btn.addEventListener("click", addContent, false);
		btn.innerHTML = "Add";
		allowAddingContent[0].parentNode.appendChild(btn);
		currTable = new Table(0);
	}
}

function addContent() {
	var content = document.getElementsByClassName("addingContentColumn");
	var tableHead = document.getElementsByClassName("tableHead");
	
	if(content.length != 0) {
		let data = "getTable=kunde&getReason=neuerKunde&";
		for(let i = 0; i < content.length; i++) {
			console.log("content: " + content[i].innerHTML);
			data += tableHead[i].innerHTML + "=" + content[i].innerHTML;
			i != content.length - 1 ? data += "&" : 1;
		}
        let sendToDB = new AjaxCall(data, "POST", "http://localhost/auftragsbearbeitung/content/neuer-kunde");
        sendToDB.makeAjaxCall(function (responseTable) {
            var tableContainer = document.getElementById("tableContainer");
            tableContainer.innerHTML = responseTable;
            addableTables();
        });
	} else {
		alert("leere");
	}
}

window.onload = function() {
	init();
}