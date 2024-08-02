import { ajax } from "./ajax.js";

export const createFileUpload = (anchor, config = null) => {
    const form = document.createElement("form");
    form.classList.add("fileUploader", "mt-2");
    form.method = "post";
    form.enctype = "multipart/form-data";

    form.dataset.target = config ? config.target : "";
    form.name = config ? config.name : "";

    const label = document.createElement("label");
    label.classList.add("btn-primary", "my-2", "inline-block", "cursor-pointer");
    label.textContent = "Dateien hinzufügen";
    label.addEventListener("click", openFileDialog);
    form.appendChild(label);

    const input = document.createElement("input");
    input.type = "file";
    input.name = "uploadedFile";
    input.multiple = true;
    input.classList.add("hidden");
    input.addEventListener("change", () => {
        uploadFiles(form, config);
    });
    form.appendChild(input);

    const hidden = document.createElement("input");
    hidden.name = config ? config.target : "";
    hidden.value = anchor.dataset.id;
    hidden.hidden = true;
    form.appendChild(hidden);

    anchor.appendChild(form);
}

const openFileDialog = (event) => {
    const input = event.target.nextElementSibling;
    input.click();
}

const uploadFiles = (form, config = null) => {
    const files = form.querySelector("input[type=file]").files;
    const target = form.dataset.target;
    const additionalInfo = {
        target: target
    };

    ajax.uploadFiles(files, target, additionalInfo).then((response) => {
        console.log(response);
        if (config && config.callback) {
            config.callback(response);
        }
    }).catch((error) => {
        console.error(error);
        if (config && config.onError) {
            config.onError(error);
        }
    });
}
