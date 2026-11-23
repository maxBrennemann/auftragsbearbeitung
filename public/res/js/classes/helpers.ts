export const createPopup = (content: HTMLElement, containerClasses: string[] = []) => {
    const container = document.createElement("div");
    container.classList.add("overlay-container");
    const contentContainer = document.createElement("div");
    contentContainer.classList.add("overlay-container__content", ...containerClasses);
    const optionsContainer = document.createElement("div");
    optionsContainer.classList.add("overlay-container__content__options");

    const closePopup = () => {
        container.classList.remove("overlay-container--visible");
        document.body.classList.remove("overflow-hidden");
        document.documentElement.classList.remove("overflow-hidden");

        container.addEventListener("transitionend", () => {
            container.remove();
            const event = new CustomEvent("closePopup", {
                bubbles: true,
            });
            optionsContainer.dispatchEvent(event);
        }, { once: true });
    }

    const button = document.createElement("button");
    button.type = "button";
    button.classList.add("btn-secondary", "ml-2");
    button.dataset.popupClose = "true";
    button.innerHTML = "Abbrechen";
    button.addEventListener("click", closePopup);
    optionsContainer.appendChild(button);

    content.classList.add("p-3");
    contentContainer.appendChild(content);
    contentContainer.appendChild(optionsContainer);
    container.appendChild(contentContainer);
    document.body.appendChild(container);

    container.addEventListener("click", e => {
        if (e.target === container) closePopup();
    });

    requestAnimationFrame(() => {
        container.classList.add("overlay-container--visible");
    });

    document.body.classList.add("overflow-hidden");
    document.documentElement.classList.add("overflow-hidden");

    return optionsContainer;
}

/**
 * Schließt ein mit createPopup() geöffnetes Popup. Erwartet den von createPopup() zurückgegebenen Container
 * (oder ein Element darin) und löst denselben Ablauf aus wie ein Klick auf „Abbrechen“.
 */
export const closePopupFor = (popupElement: Element) => {
    const container = popupElement.closest(".overlay-container") ?? popupElement;
    container.querySelector<HTMLButtonElement>("[data-popup-close]")?.click();
}

/**
 * Bindet [data-toggle]-Elemente in nachgeladenem HTML (z. B. per Ajax eingefügte Templates).
 * js-classes bindet data-toggle nur einmal beim Start, spätere addBindings()-Aufrufe erfassen es nicht.
 * Das Verhalten ist dasselbe: Klick schaltet `hidden` an allen Elementen aus data-target um.
 */
export const bindToggles = (root: ParentNode = document) => {
    root.querySelectorAll<HTMLElement>("[data-toggle]:not([data-toggle-bound])").forEach(el => {
        el.dataset.toggleBound = "true";
        el.addEventListener("click", () => {
            const target = el.dataset.target;
            if (!target) return;
            document.querySelectorAll(target).forEach(element => element.classList.toggle("hidden"));
        });
    });
}

export const loader =(init: () => void) => {
    if (document.readyState !== 'loading') {
        init();
    } else {
        document.addEventListener('DOMContentLoaded', function () {
            init();
        });
    }
}

export const clickOutside = (elementSelector: string, callback: () => void) => {
    window.addEventListener("click", (e) => {
        const target = e.target as HTMLElement | null;
        if (!target) return;

        if (!target.closest(elementSelector)) {
            callback();
        }
    });
}
