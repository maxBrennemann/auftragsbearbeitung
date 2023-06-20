export var AjaxCall = function(param, ajaxType, url) {
	this.type = (ajaxType != null) ? ajaxType : "POST";

	if (typeof param === 'string') {
		this.paramString = (param != null) ? param : "";
	} else if (typeof param === 'object') {
		let temp = "";
		for (let key in param) {
			let parameterEncoded = encodeURIComponent(param[key]);
			temp += key + "=" + parameterEncoded + "&";
		}

		this.paramString = temp.slice(0, -1);
	}
	this.url = url;
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
	if (this.paramString == null) {
		console.warn("AjaxCall: no parameters given");
	}
	
	if (this.type == "POST") {
		var ajaxCall = new XMLHttpRequest();
		ajaxCall.onreadystatechange = function() {
			if(this.readyState == 4 && this.status == 200) {
				dataCallback(this.responseText, args);
			}
		}
		ajaxCall.open(this.type, this.url, true);
		ajaxCall.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		ajaxCall.send(this.paramString);
	} else if (this.type == "GET") {
		var ajaxCall = new XMLHttpRequest();
		ajaxCall.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				dataCallback(this.responseText, args);
			}
		}
		ajaxCall.open("GET", this.url + this.paramString, true);
		ajaxCall.send();
	} else {
		console.error("AjaxCall: Ajax Type not defined");
	}
}

export const ajax = {
    async post(data, noJSON = false) {
        return this.request(data, "POST", noJSON);
    },

    async get(data, noJSON = false) {
		return this.request(data, "GET", noJSON);
    },

	async request(data, type, noJSON = false) {
		data.getReason = data.r;
        const param = Object.keys(data).map(key => {
            return `${key}=${encodeURIComponent(data[key])}`;
        });
        let response = await makeAsyncCall(type, param.join("&"), "").then(result => {
            return result;
        });
    
        if (noJSON) {
            return response;
        }

        let json = {};
        try {
            json = JSON.parse(response);
        } catch (e) {
            return {};
        }

        return json;
	}
}

export async function makeAsyncCall(type, params, location) {
	return new Promise((resolve, reject) => {
		if (params == null) {
			console.warn("AjaxCall: no parameters given");
		}
		
		if (type == "POST") {
			var ajaxCall = new XMLHttpRequest();
			ajaxCall.onload  = function() {
				if (this.readyState == 4 && this.status == 200) {
					resolve(this.responseText);
				} else {
					reject(this.responseText);
				}
			}
			ajaxCall.open("POST",  location, true);
			ajaxCall.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
			ajaxCall.send(params);
		} else if (type == "GET") {
			var ajaxCall = new XMLHttpRequest();
			ajaxCall.onload = function() {
				if (this.readyState == 4 && this.status == 200) {
					resolve(this.responseText);
				} else {
					reject();
				}
			}
			ajaxCall.open("GET", location + params, true);
			ajaxCall.send();
		} else {
			console.error("AjaxCall: Ajax Type not defined");
		}
	});
}
