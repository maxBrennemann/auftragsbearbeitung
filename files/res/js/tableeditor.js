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

function addableTables() {
    var allowAddingContent = document.getElementsByClassName("allowAddingContent");

    tableName = allowAddingContent[0].dataset.type;
    sendTo = allowAddingContent[0].dataset.sendTo;
	
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
        let data = `getTable=${tableName}&getReason=${sendTo}&`;
        let isChecked = true;
		for(let i = 0; i < content.length; i++) {
            console.log("content: " + content[i].innerHTML);
            if (content[i].innerHTML == "") {
                content[i].style.backgroundColor = "#FF0000AA";
                content[i].style.opacity = "70%";
                isChecked = false;
            }
			data += tableHead[i].innerHTML + "=" + content[i].innerHTML;
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