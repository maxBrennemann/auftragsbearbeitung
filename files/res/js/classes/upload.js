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

    await uploadFiles(files, location, info, type, target);
}

fnNames.drop_fileUploader = async e => {
    e.preventDefault();

    if (!e.dataTransfer.files) {
        return;
    }

    const files = e.dataTransfer.files;
    const target = e.currentTarget.querySelector("input");
    const type = target.dataset.type;
    const info = JSON.parse(JSON.stringify(fileUploadInfo[type] ?? []));
    const location = info.location ?? "";
    delete info.location;

    await uploadFiles(files, location, info, type, target);
}

fnNames.dragover_fileUploader = async e => {
    e.preventDefault();
}

const uploadFiles = async (files, location, info, type, target) => {
    await ajax.uploadFiles(files, location, info).then(r => {
        const event = new CustomEvent("fileUploaded", {
            "detail": { type, ...r },
            "bubbles": true,
        });
        target.dispatchEvent(event);
        notification("", "success");
    }).catch((error) => {
        const event = new CustomEvent("fileUploaded", {
            "detail": { type, ...error },
            "bubbles": true,
        });
        target.dispatchEvent(event);
        notification("", "failure");
        console.error(error);
    });

    target.value = "";
}

export const initFileUploader = (data) => {
    fileUploadInfo = {
        ...fileUploadInfo,
        ...data,
    }
    addBindings(fnNames);
}
