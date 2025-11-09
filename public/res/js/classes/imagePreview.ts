import { createPopup } from "../global.js";

const imagePreviewListeners = new WeakSet();

export const initImagePreviewListener = () => {
    const images = document.querySelectorAll<HTMLElement>("img, .img-prev");

    images.forEach(image => {
        if (imagePreviewListeners.has(image)) {
            return;
        }

        image.classList.add("cursor-pointer");
        image.addEventListener("click", (e) => {
            if (image !== e.target) return;
            openImagePreview(image);
        });

        imagePreviewListeners.add(image);
    });
}

const openImagePreview = (image: HTMLElement) => {
    const imageCopy = document.createElement("img");
    const innerImg = image instanceof HTMLImageElement ? (image as HTMLImageElement) : image.querySelector<HTMLImageElement>("img");

    if (!innerImg) return;

    imageCopy.src = innerImg.src;
    imageCopy.title = innerImg.title;
    imageCopy.width = innerImg.naturalWidth < 500 ? innerImg.naturalWidth : 500;

    const div = document.createElement("div");
    div.appendChild(imageCopy);

    const info = document.createElement("p");
    info.innerHTML = `Bild: ${innerImg.title ?? "kein Titel vorhanden"}, Maße: ${innerImg.naturalWidth}px x ${innerImg.naturalHeight}px`;
    info.classList.add("mt-2");
    div.appendChild(info);

    const eventEl = createPopup(div);
    eventEl.addEventListener("closePopup", () => {
        history.replaceState(null, "", window.location.pathname);
    });

    const imageId = innerImg.dataset.imageId;
    history.replaceState(null, "", `#popup=${imageId}`);
}

export const checkAutoOpenPopup = () => {
    const hash = window.location.hash;
    if (hash.startsWith("#popup=")) {
        const imageId = hash.replace("#popup=", "");
        const image = document.querySelector<HTMLImageElement>(`[data-image-id="${imageId}"] img, img[data-image-id="${imageId}"]`);

        if (!image) return;

        if (!image.complete || image.naturalWidth === 0) {
            image.addEventListener("load", () => {
                openImagePreview(image);
            }, { once: true });
        } else {
            openImagePreview(image);
        }
    }
}
