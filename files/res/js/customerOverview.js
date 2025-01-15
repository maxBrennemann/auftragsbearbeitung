const init = () => {
    const searchInput = document.getElementById("search");
    searchInput.addEventListener("input", () => {
        const value = searchInput.value;
    
        if (value.length < 3) {
            return;
        }

        const url = new URL(window.location);
        url.searchParams.set("query", value);
        history.replaceState(null, "", url);

        ajax.get(``).then(r => {

        });
    });
}

if (document.readyState !== 'loading' ) {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
