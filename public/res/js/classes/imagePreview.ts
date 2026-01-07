import { createPopup } from "../classes/helpers";

const imagePreviewListeners = new WeakSet();
const popup = {

};

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
    imageCopy.alt = innerImg.alt;
    imageCopy.width = innerImg.naturalWidth < 500 ? innerImg.naturalWidth : 500;

    const div = document.createElement("div");
    div.appendChild(imageCopy);

    const info = document.createElement("p");
    info.innerHTML = `Bild: ${innerImg.alt ?? "kein Titel vorhanden"}, Maße: ${innerImg.naturalWidth}px x ${innerImg.naturalHeight}px`;
    info.classList.add("mt-2");
    div.appendChild(info);

    const imageId = innerImg.dataset.imageId;
    const eventEl = createPopup(div);

    eventEl.addEventListener("closePopup", () => {
        setPopupHash(undefined);
    });
    //addNavigation(eventEl, imageId);

    setPopupHash(imageId);
}

const setPopupHash = (imageId: string | undefined) => {
    const url = new URL(window.location.href);
    url.hash = imageId ? `#popup=${encodeURIComponent(imageId)}` : "";
    history.replaceState(null, "", url.toString());
}

const addNavigation = (container: HTMLElement, currentImageId?: string) => {
    const galleryImages = getGalleryImages();
    if (galleryImages.length < 2 || !currentImageId) return;

    const imageIndex = galleryImages.findIndex(el => {
        const img = el instanceof HTMLImageElement ? el : el.querySelector<HTMLImageElement>("img");
        return img?.dataset.imageId === currentImageId;
    });

    if (imageIndex === -1) return;

    const nextIndex = (imageIndex + 1) % galleryImages.length;
    const prevIndex = (imageIndex - 1 + galleryImages.length) % galleryImages.length;

    const btnNext = document.createElement("button");
    btnNext.classList.add("btn-primary", "ml-2");
    btnNext.innerText = "⮕";

    btnNext.addEventListener("click", () => {
        const nextImage = galleryImages[nextIndex];
        openImagePreview(nextImage);
    });

    const btnPrev = document.createElement("button");
    btnPrev.classList.add("btn-primary");
    btnPrev.innerText = "⬅";

    btnPrev.addEventListener("click", () => {
        const prevImage = galleryImages[prevIndex];
        openImagePreview(prevImage);
    });

    container.appendChild(btnNext);
    container.appendChild(btnPrev);
}

const getGalleryImages = (): HTMLElement[] => {
    return Array.from(document.querySelectorAll<HTMLElement>("img, .img-prev"));
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
