// @ts-ignore
import { ajax } from "js-classes/ajax.js";

/**
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

interface Parameter {
    currentName: string;
    currentIntent: string;
    iframeDocument: Document | null;
    manual: {
        current: number;
        items: HTMLElement[];
        texts: string[];
    };
    navigator: HTMLButtonElement | null;
}

const params: Parameter = {
    currentName : "",
    currentIntent : "",
    iframeDocument: null,
    manual: {
        current: 0,
        items: [],
        texts: []
    },
    navigator: null
};

function startFunktionen() {
    var clickable = document.querySelectorAll<HTMLElement>(".clickable");
    for (let item of clickable) {
        item.addEventListener("click", (event: MouseEvent) => {
            const target = event.target as HTMLElement;
            setupIframe(target);
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
function setupIframe(node: HTMLElement) {
    const iframe = document.createElement("iframe");
    const link = node.children[0] as HTMLAnchorElement;
    const src = link.href;

    params.currentIntent = node.dataset.intent!;
    params.currentName = node.dataset.name!;

    iframe.addEventListener("load", function() {
        const iframeDocument = (this.contentDocument || this.contentWindow?.document) as Document;
        if (iframeDocument == null) {
            return;
        }

        (iframeDocument.querySelector("header") as HTMLElement).style.display = "none";
        (iframeDocument.querySelector("footer") as HTMLElement).style.display = "none";
        (iframeDocument.querySelector("main") as HTMLElement).style.marginTop = "0";

        getManualData();
        setupNavigator(iframeDocument);
    });

    iframe.src = src;

    const main = document.getElementsByTagName("main")[0];
    const width = main.children[0].getBoundingClientRect().width - 20;
    iframe.style.width = String(width);
    iframe.style.height = "400px";

    iframe.style.marginLeft = "-30px";
    
    if (node.parentNode) {
        node.parentNode.insertBefore(iframe, node.nextSibling);
    }

    if (params.navigator == null) {
        const btns = document.querySelectorAll<HTMLButtonElement>(".manualNavigator")
        params.navigator = btns[0];
    }

    if (node.parentNode) {
        node.parentNode.insertBefore(params.navigator, iframe);
    }
}

function setupNavigator(iframeDocument: Document) {
    var navigator = params.navigator || document.querySelectorAll<HTMLButtonElement>(".manualNavigator")[0];
    navigator.style.display = "block";

    var manualItems = iframeDocument.getElementsByClassName("manual") as HTMLCollectionOf<HTMLElement>;
    params.manual.items = [];
    for (let item of manualItems) {
        if (item.dataset.intent == params.currentIntent) {
            params.manual.items.push(item);
        }
    }

    navigator.children[0].addEventListener("click", function() {iterateManual(-1)}, false);
    navigator.children[1].addEventListener("click", function() {iterateManual(1)}, false);
}

function getManualData() {
    ajax.get(`/api/v1/manual/${params.currentName}`, {
        "intent": params.currentIntent,
    }).then((r: Record<string, { info: string }>) => {
        for (let key in r) {
            params.manual.texts.push(r[key].info);
		}
    });
}

function iterateManual(direction: number) {
    let currentItem = params.manual.current;
    currentItem += direction;

    if (currentItem < 1 || currentItem > params.manual.items.length) return;

    let item = params.manual.items[currentItem - 1];
    item.classList.add("highlight");

    if (currentItem - 1 < params.manual.texts.length 
        && currentItem - 1 >= 0
        && params.navigator
    ) {
        params.navigator.children[2].innerHTML = params.manual.texts[currentItem - 1];
    }

    /* remove highlight from old current */
    let oldItem = params.manual.items[params.manual.current - 1];
    if (oldItem != undefined) {
        oldItem.classList.remove("highlight");
    }
    params.manual.current = currentItem;
}

if (document.readyState !== 'loading' ) {
    console.log( 'document is already ready, just execute code here' );
    startFunktionen();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        console.log( 'document was not ready, place code here' );
        startFunktionen();
    });
}
