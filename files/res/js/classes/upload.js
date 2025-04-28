import { ajax } from "./ajax.js";
import { addBindings } from "./bindings.js";
import { notification } from "./notifications.js";

const fnNames = {};
let fileUploadInfo = {};

fnNames.write_fileUploader = async e => {
    const target = e.currentTarget;
    const type = target.dataset.type;
    const info = JSON.parse(JSON.stringify(fileUploadInfo[type] ?? []));

    const files = target.files;
    const location = info.location ?? "";
    delete info.location;

    await ajax.uploadFiles(files, location, info).then(() => {
        notification("", "success");
    }).catch((error) => {
        notification("", "failure");
        console.error(error);
    });

    target.value = "";
    const event = new CustomEvent("fileUploaded", {
        detail: { type },
        bubbles: true,
    });
    target.dispatchEvent(event);
}

export const initFileUploader = (data) => {
    fileUploadInfo = {
        ...fileUploadInfo,
        ...data,
    }
    addBindings(fnNames);
}
