/* https://stackoverflow.com/questions/14226803/wait-5-seconds-before-executing-next-line */
const delay = ms => new Promise(res => setTimeout(res, ms));

/* function shows an info text about the update status of an ajax query */
export function infoSaveSuccessfull(status = "failiure", errorMessage = "") {
	var statusClass = "";
	var text = "";
	
	switch (status) {
		case "success":
			statusClass = "showSuccess";
			text = "Speichern erfolgreich!";
			break;
		case "failiure":
		default:
			statusClass = "showFailure";
			text = "Speichern hat nicht geklappt!";
			break;
	}

	let div = document.createElement("div");
    div.innerHTML = text;
    div.classList.add(statusClass);
    document.body.appendChild(div);

    setTimeout(function () {
        div.classList.add("hidden");
    }, 1000);

    setTimeout(function () {
        div.parentNode.removeChild(div);
    }, 2000);
}

class StatusInfoBox {

  constructor(type, message = "") {
    this.type = type;
    this.status = StatusInfoHandler.STATUS_INIT;

    this.errorMessage = "";
    this.message = "Wird gespeichert";
    if (message != "") {
      this.message = message;
    }

    this.#createInfoBox();
  }

  /**
   * creates the info box with the default message,
   * this box will later be updated
   */
  #createInfoBox() {
    let div = document.createElement("div");
    div.innerHTML = this.message;
    div.classList.add("showDefault");
    document.body.appendChild(div);
    this.domContent = div;

    /* types can be extended here */
    switch (this.type) {
      case StatusInfoHandler.TYPE_LOADER:
        this.#addLoader();
        break;
    }
  }

  setType(type) {
    this.type = type;
  }

  statusUpdate(status, message, errorMessage = "") {
    this.status = status;
    this.message = message;
    this.errorMessage = errorMessage;

    this.#updateInfo();
  }

  /**
   * updates the infoBox after the ajax request is finished,
   * can show error messages with error copy
   */
   #updateInfo() {
    let statusClass = "";
    let infoText = this.message;

    switch (this.status) {
      case StatusInfoHandler.STATUS_SUCCESS:
        statusClass = "showSuccess";
        if (infoText == "") {
          infoText = "Speichern erfolgreich!";
        }
        break;
      case StatusInfoHandler.STATUS_FAILURE:
      default:
        statusClass = "showFailure";
        if (infoText == "") {
          infoText = "Speichern hat nicht geklappt!";
        }
        break;
    }

    this.domContent.innerHTML = infoText;
    this.domContent.classList.remove("showDefault");
    this.domContent.classList.add(statusClass);

    if (this.type == StatusInfoHandler.TYPE_ERRORCOPY && this.status == StatusInfoHandler.STATUS_FAILURE) {
      this.#addCopyError();
    } else {
      this.#hideout();
    }
  }

  #hideout() {
    /* after a given period of time the info box disappears */
    setTimeout(() => {
      this.domContent.classList.add("hidden");
    }, 1000);

    /* after a given period of time the info box is removed from the DOM */
    setTimeout(() => {
      this.domContent.parentNode.removeChild(this.domContent);
    }, 2000);
  }

  #addCopyError() {
    const infoText = document.createElement("div");
    infoText.classList.add("inline");
    infoText.innerHTML = "Ein Fehler ist aufgetreten.";

    const removeBtn = document.createElement("button");
    removeBtn.addEventListener("click", e => {
      this.domContent.parentNode.removeChild(this.domContent);
    });
    removeBtn.innerHTML = "x";
    removeBtn.classList.add("font-bold", "text-gray-700", "rounded-full", "bg-white", "inline-flex", "items-center", "justify-center", "font-mono", "mr-5", "removeFailureMessage");

    const copyContent = document.createElement("input");
    copyContent.value = this.errorMessage;
    copyContent.style.display = "none";

    const copyBtn = document.createElement("button");
    copyBtn.addEventListener("click", () => {
      copyContent.select();
      copyContent.setSelectionRange(0, 99999);
      navigator.clipboard.writeText(copyContent.value);
    });
    copyBtn.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="15px" height="15px" fill="white"><title>content-copy</title><path d="M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z" /></svg>`;
    copyBtn.classList.add("copyBtn");

    this.domContent.innerHTML = "";

    this.domContent.appendChild(removeBtn);
    this.domContent.appendChild(copyContent);
    this.domContent.appendChild(copyBtn);
    this.domContent.appendChild(infoText);
  }

  /* shows a spinning loader till the ajax request is finished */
  #addLoader() {
    let infoText = document.createElement("div");
    infoText.classList.add("inline");
    infoText.innerHTML = this.message;

    let loader = document.createElement("div");
    loader.classList.add("inline");
    loader.classList.add("loaderSettings");
    loader.innerHTML = `<div class="loaderOrSymbol">
		<div class="lds-ring"><div></div><div></div><div></div><div></div></div>`;

    this.domContent.innerHTML = "";
    this.domContent.appendChild(loader);
    this.domContent.appendChild(infoText);
  }

}

/**
 * StatusInfoHandler is used to show the user the status of ajax requests,
 * class was generated by ChatGPT by giving it my requirements and the old statusInfo code
 */
export class StatusInfoHandler {

  static globalStatusInfoHandler;

  static STATUS_FAILURE = 0;
  static STATUS_SUCCESS = 1;
  static STATUS_INIT = 2;

  static TYPE_LOADER = 0;
  static TYPE_DEFAULT = 1;
  static TYPE_ERRORCOPY = 2;

  constructor() {
    this.globalStatusInfoHandler = this;
    this.#initHTML();
    this.infoMessages = [];
  }

  #initHTML() {
    const div = document.createElement("div");
    div.classList.add("inline");
    document.body.appendChild(div);
  }

  /* shorthand for most usecases, currently not used */
  static shortInfoStatus(status, customMessage) {
    const infoBox = new StatusInfoBox();
    infoBox.showInfo(status, customMessage);
    this.globalStatusInfoHandler.infoMessages.push(infoBox);
  }

  addInfoBox(type, customMessage = "") {
    const infoBox = new StatusInfoBox(type, customMessage);
    this.infoMessages.push(infoBox);
    return infoBox;
  }

}

function testInfo() {
    const infoHandler = new StatusInfoHandler();
    const infoBox = infoHandler.addInfoBox(StatusInfoHandler.TYPE_ERRORCOPY, "wird übertragen");

    ajax.post({
      r: "testString",
    }).then(response => {
      infoBox.statusUpdate(StatusInfoHandler.STATUS_FAILURE, "custom message", "doofer fehler");
    }).catch(error => {
      infoBox.statusUpdate(StatusInfoHandler.STATUS_FAILURE, "error"); 
    });
}
