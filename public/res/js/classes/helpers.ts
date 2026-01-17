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
    button.classList.add("btn-cancel", "ml-2");
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

export const loader = (init: () => void) => {
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
