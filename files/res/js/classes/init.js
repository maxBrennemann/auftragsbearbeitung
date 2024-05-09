
const initEvent = new Event('init');

function init() {

}

if (document.readyState !== 'loading' ) {
    init();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        init();
    });
}
