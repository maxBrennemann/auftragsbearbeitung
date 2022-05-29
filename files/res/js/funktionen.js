if (document.readyState !== 'loading' ) {
    console.log( 'document is already ready, just execute code here' );
    startFunktionen();
} else {
    document.addEventListener('DOMContentLoaded', function () {
        console.log( 'document was not ready, place code here' );
        startFunktionen();
    });
}

function startFunktionen() {
    var clickable = document.getElementsByClassName("clickable");
    for (let item of clickable) {
        item.addEventListener("click", function(event) {
            let iframe = document.createElement("iframe");
            let src = "http://localhost/auftragsbearbeitung/c/neuer-kunde"; event.target.dataset.name;

            iframe.addEventListener("load", function() {
                var iframeDocument = this.contentDocument || this.contentWindow.document;
                iframeDocument.querySelector("header").style.display = "none";
                iframeDocument.querySelector("footer").style.display = "none";
                iframeDocument.querySelector("main").style.marginTop = "0";
              });

            iframe.src = src;

            iframe.style.width = "800px";
            iframe.style.height = "400px";

            iframe.style.background = "white";
            iframe.style.border = "1px solid grey";
            iframe.style.borderRadius = "6px";

            event.target.parentNode.insertBefore(iframe, event.target.nextSibling);
        }, false);
    }

    var links = document.getElementsByClassName("extLinks");
    for (let link of links) {
        link.addEventListener("click", function(e) {
            e.stopPropagation();
        }, false);
    }
}
