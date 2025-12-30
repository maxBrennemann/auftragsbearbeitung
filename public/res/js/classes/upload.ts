import { ajax } from "js-classes/ajax";
import { addBindings } from "js-classes/bindings";
import { notification } from "js-classes/notifications";
import { FunctionMap } from "../types/types";

const fnNames = {} as FunctionMap;
let fileUploadInfo = {} as Record<string, any>;

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

const uploadFiles = async (files: FileList, location: string, info: any, type: string, target: HTMLInputElement) => {
    await ajax.uploadFiles(files, location, info).then(r => {
        const event = new CustomEvent("fileUploaded", {
            "detail": { type, ...r.data },
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

export const initFileUploader = (data: any) => {
    fileUploadInfo = {
        ...fileUploadInfo,
        ...data,
    }
    addBindings(fnNames);
}
