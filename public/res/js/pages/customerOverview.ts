export {};

const init = (): void => {
    const searchInput = document.getElementById("search") as HTMLInputElement | null;

    if (!searchInput) {
        console.warn("Search input not found.");
        return;
    }

    const searchIcon = searchInput.nextElementSibling as HTMLElement | null;

    const triggerSearch = () => {
        const value = searchInput.value.trim();

        if (value.length < 3) {
            return;
        }

        const url = new URL(window.location.href);
        url.searchParams.set("query", encodeURIComponent(value));
        history.replaceState(null, "", url);

        window.location.reload();
    }

    searchInput.addEventListener("keydown", (e: KeyboardEvent) => {
        if (e.key === "Enter") {
            triggerSearch();
        }
    });

    if (searchIcon) {
        searchIcon.addEventListener("click", triggerSearch);
        searchIcon.style.cursor = "pointer";
    }
}

if (document.readyState !== 'loading') {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
