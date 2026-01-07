import { createPopup } from "../classes/helpers";

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
        setPopupHash(undefined);
    });
    addNavigation(eventEl);

    const imageId = innerImg.dataset.imageId;
    setPopupHash(imageId);
}

const setPopupHash = (imageId: string | undefined) => {
    const url = new URL(window.location.href);
    url.hash = imageId ? `#popup=${encodeURIComponent(imageId)}` : "";
    history.replaceState(null, "", url.toString());
}

const addNavigation = (container: HTMLElement) => {
    const galleryImages = getGalleryImages();
    if (galleryImages.length < 2) return;

    const btnAdd = document.createElement("button");
    btnAdd.classList.add("btn-primary", "ml-2");
    btnAdd.innerText = "⮕";

    const btnRemove = document.createElement("button");
    btnRemove.classList.add("btn-primary");
    btnRemove.innerText = "⬅";

    container.appendChild(btnAdd);
    container.appendChild(btnRemove);
}

const getGalleryImages = (): HTMLImageElement[] => {
    return Array.from(document.querySelectorAll<HTMLImageElement>('img[data-image-id], [data-image-id] img'))
        .filter(img => !!img.dataset.imageId);
}

export const checkAutoOpenPopup = () => {
    const hash = window.location.hash.startsWith("#") ? window.location.hash.slice(1) : "";
    const params = new URLSearchParams(hash);

    const imageId = params.get("popup");
    if (!imageId) return;

    const decodedId = decodeURIComponent(imageId);
    const image = document.querySelector<HTMLImageElement>(
        `[data-image-id="${CSS.escape(decodedId)}"] img, img[data-image-id="${CSS.escape(decodedId)}"]`
    );

    if (!image) return;

    if (!image.complete || image.naturalWidth === 0) {
        image.addEventListener("load", () => openImagePreview(image), { once: true });
    } else {
        openImagePreview(image);
    }
};
