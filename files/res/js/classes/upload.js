import { ajax } from "./ajax.js";
import { addBindings } from "./bindings.js";

const fnNames = {};
let fileUploadInfo = {};

fnNames.write_fileUploader = async e => {
    const target = e.currentTarget;
    const type = target.dataset.type;
    const info = JSON.parse(JSON.stringify(fileUploadInfo[type] ?? []));

    const files = target.files;
    const location = info.location ?? "";
    delete info.location;

    const response = await ajax.uploadFiles(files, type, location, info);
    target.value = "";
}

export const initFileUploader = (data) => {
    fileUploadInfo = {
        ...fileUploadInfo,
        ...data,
    }
    addBindings(fnNames);
}
