if (document.readyState !== 'loading' ) {
    console.log( 'document is already ready, just execute code here' );
    startFunktionen();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        console.log( 'document was not ready, place code here' );
        startFunktionen();
    });
}

/*
 * Aufbau der Anleitung:
 * funktionen.php -> class clickable
 *      data-name: Name der Seite
 *      data-intent: Element der Seite (falls gesetzt, sonst gesamter main-content)
 * 
 * einzelne Seiten -> class manual
 *      data-intent: Element der Seite, was zu dem entsprechenden intent gehört
 *      data-id: Reihenfolge, über die der Hilfstext angezeigt wird
 *      data-action: um vordefinierte Aktionen auszuführen (vorerst zweitranging)
 * 
 * dazu werden die nötigen Hilfstexte aus der DB geladen und in einem Kästchen angezeigt.
 */

var params = {
    currentName : "",
    currentIntent : "create",
    iframeDocument: null,
    manual: {
        current: 0,
        items: []
    },
    navigator: null
};

function startFunktionen() {
    var clickable = document.getElementsByClassName("clickable");
    for (let item of clickable) {
        item.addEventListener("click", function(event) {
            setupIframe(event.target);
        }, false);
    }

    var links = document.getElementsByClassName("extLinks");
    for (let link of links) {
        link.addEventListener("click", function(e) {
            e.stopPropagation();
        }, false);
    }
}

/* called, when event listener is triggered */
function setupIframe(node) {
    let iframe = document.createElement("iframe");
    let src = node.children[0].href;

    iframe.addEventListener("load", function() {
        var iframeDocument = this.contentDocument || this.contentWindow.document;
        iframeDocument.querySelector("header").style.display = "none";
        iframeDocument.querySelector("footer").style.display = "none";
        iframeDocument.querySelector("main").style.marginTop = "0";

        setupNavigator(iframeDocument);
    });

    iframe.src = src;

    iframe.style.width = "800px";
    iframe.style.height = "400px";

    iframe.style.background = "white";
    iframe.style.border = "1px solid grey";
    iframe.style.borderRadius = "6px";

    node.parentNode.insertBefore(iframe, node.nextSibling);
    if (params.navigator == null)
        params.navigator = document.getElementsByClassName("manualNavigator")[0];
    node.parentNode.insertBefore(params.navigator, iframe);
}

function setupNavigator(iframeDocument) {
    var navigator = params.navigator || document.getElementsByClassName("manualNavigator")[0];
    navigator.style.display = "block";

    var manualItems = iframeDocument.getElementsByClassName("manual");
    for (let item of manualItems) {
        if (item.dataset.intent == params.currentIntent)
            params.manual.items.push(item);
    }

    navigator.children[0].addEventListener("click", function() {iterateManual(-1)}, false);
    navigator.children[1].addEventListener("click", function() {iterateManual(1)}, false);
}

function iterateManual(direction) {
    var currentItem = params.manual.current;
    currentItem += direction;
    if (currentItem < 1 || currentItem > params.manual.items.length)
        return;
    var item = params.manual.items[currentItem - 1];
    item.classList.add("highlight");
}
