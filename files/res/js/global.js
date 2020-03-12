document.addEventListener("click", function(event) {
	if (!event.target.matches('.showLog,.showLog *')) {
		if(document.getElementById("login")) {
			document.getElementById("login").style.display = "none";
		}
		if(document.getElementById("register")) {
			document.getElementById("register").style.display = "none";
		}
	}
})

function toggleHamList() {
	var hamlist = document.getElementById("hamlist"),
		hammen = document.getElementById("hammen");
	if(hammen.className.includes("hideHamlist")) {
		hammen.className = "showHamlist";
		hamlist.style.display = "inline";
	} else {
		hammen.className = "hideHamlist";
		hamlist.style.display = "none";
	}
}

function toggleVisibility(id) {
	if(document.getElementById(id).style.display == "none") {
		document.getElementById(id).style.display = "inline";
	} else {
		document.getElementById(id).style.display = "none";
	}
}

function logout() {
	ac = new AjaxCall("info=logout");
	ac.setUrl(window.location);
	ac.makeAjaxCall(function() {
		location.reload();
	});
}

function goToProfile() {
	document.getElementById("goToProfile").click();
}

window.onload = function() {
	var el = document.querySelector("input[type=email");
	if(el != null) {
		el.addEventListener("input", function() {
			if(validateEmail(el.value)) {
				el.style.color = "green";
			} else {
				el.style.color = "red";
			}
		}, false);
	}
}

function validateEmail(email) {
	var re = /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/i;
	return re.test(String(email).toLowerCase());
}

function getElement(id) {
	return document.getElementById(id);
}

function centerAbsoluteElement(div) {
	var divWidth = div.offsetWidth;
	var divHeight = div.offsetHeight;

	var pageWidth = window.innerWidth;
	var pageHeight = window.innerHeight;

	div.style.left = ((pageWidth - divWidth) / 2) + "px";
	div.style.top = ((pageHeight - divHeight) / 2) + "px";
}

var AjaxCall = function(paramString, ajaxType) {
	this.type = (ajaxType != null) ? ajaxType : "POST";
	this.paramString = (paramString != null) ? paramString : "";
	this.url;
}

AjaxCall.prototype.setType = function(type) {
	if(type != null) {
		this.type = type;
	} else {
		console.error("Ajax Type not defined");
	}
}

AjaxCall.prototype.setParamString = function(paramString) {
	if(paramString != null) {
		this.paramString = paramString;
	} else {
		console.warn("AjaxCall: no parameters given");
	}
}

AjaxCall.prototype.setUrl = function(url) {
	this.url = url;
}

AjaxCall.prototype.makeAjaxCall = function(dataCallback, ...args) {
	if(this.paramString == null) {
		console.warn("AjaxCall: no parameters given");
	}
	
	if(this.type == "POST") {
		var ajaxCall = new XMLHttpRequest();
		ajaxCall.onreadystatechange = function() {
			if(this.readyState == 4 && this.status == 200) {
				dataCallback(this.responseText, args);
			}
		}
		ajaxCall.open(this.type, this.url, true);
		ajaxCall.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		ajaxCall.send(this.paramString);
	} else if(this.type == "GET") {
		var ajaxCall = new XMLHttpRequest();
		ajaxCall.onreadystatechange = function() {
			if(this.readyState == 4 && this.status == 200) {
				dataCallback(this.responseText, args);
			}
		}
		ajaxCall.open("GET", this.url + this.paramString, true);
		ajaxCall.send();
	} else {
		console.error("AjaxCall: Ajax Type not defined");
	}
}