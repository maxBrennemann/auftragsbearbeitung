function addTagListeners() {
    const tags = document.getElementsByTagName("dt");
    Array.from(tags).forEach(tag => {
        tag.addEventListener("click", showUsages);
    });
}

function showUsages(e: Event) {
    const tag = e.currentTarget;
    // TODO: implement tag usages
}

if (document.readyState !== 'loading' ) {
    addTagListeners();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        addTagListeners();
    });
}
