<?php

use Classes\Link;

$tailwindCSS = Link::getTW();
?>
<!DOCTYPE html>
<head>
    <title>Die Auftragsbearbeitung updaten</title>
    <link rel="stylesheet" href="<?=$tailwindCSS?>">
    <script type="module" src="<?=Link::getGlobalJS()?>"></script>
</head>
<body>
    <div class="mx-auto w-4/5">
        <h1 class="mt-2 font-bold">Auftragsbearbeitungsupdater</h1>
        <p>Aktuelle Version: <span id="actualVersion" class="font-mono"><?=getCurrentVersion()?></span></p>
        <button id="updateProject" class="border-solid border-2 rounded-md px-2 py-1 bg-amber-400 border-transparent" data-binding="true">Update</button>
        <button id="composerUpdate" class="border-solid border-2 rounded-md px-2 py-1 bg-amber-400 border-transparent" data-binding="true">Composer Update</button>
        <button id="composerInstall" class="border-solid border-2 rounded-md px-2 py-1 bg-amber-400 border-transparent" data-binding="true">Composer Install</button>
        <p>Composer Install, um alle Pakete aus composer.lock zu installieren</p>
        <p>Composer Update, um alle Pakete zu aktualisieren, Updates zu überprüfen, Pakete zu entfernen und um composer.lock neu zu generieren</p>
        <code></code>
    </div>
    <!-- https://stackoverflow.com/questions/33052195/what-are-the-differences-between-composer-update-and-composer-install -->
    <script>
        var code;
        if (document.readyState !== 'loading' ) {
            code = document.querySelector("code");
            init();
        } else {
            document.addEventListener('DOMContentLoaded', function () {
                code = document.querySelector("code");
                init();
            });
        }

        function init() {
            let bindings = document.querySelectorAll('[data-binding]');
            [].forEach.call(bindings, function(el) {
                var fun_name = "click_" + el.id;
                el.addEventListener("click", function() {
                    var fun = window[fun_name];
                    if (typeof fun === "function") {
                        fun();
                    }
                }.bind(fun_name), false);
            });
        }

        function click_composerInstall() {
            ajax.post({
                query: 4,
                r: "upgrade",
            }).then(install => {
                console.log(install);
                codeAppend(install.command, true);
                codeAppend(install.result);
            });
        }

        function click_composerUpdate() {
            ajax.post({
                query: 5,
                r: "upgrade",
            }).then(update => {
                console.log(update);
                codeAppend(update.command, true);
                codeAppend(update.result);
            });
        }

        function click_updateProject() {
            /* git pull ausführen */
            ajax.post({
                query: 1,
                r: "upgrade",
            }).then(print => {
                console.log(print);
                codeAppend(print.command, true);
                codeAppend(print.result);
            });

            /* db upgrade ausführen */
            ajax.post({
                query: 2,
                r: "upgrade",
            }).then(sqlQuery => {
                console.log(sqlQuery);
                
                Object.entries(sqlQuery).forEach(
                    ([key, value]) => {
                        codeAppend(value.command, true);
                        codeAppend(value.result);
                    }
                );

                console.log(sqlQuery);
            });

            /* minify neu ausführen */
            ajax.post({
                query: 3,
                r: "upgrade",
            }).then(minify => {
                console.log(minify);
                codeAppend(minify.command, true);
                codeAppend(minify.result);
            });
        }

        function codeAppend(text, style = false) {
            var p = document.createElement("p");
            p.innerHTML = text;
            if (style) {
                p.innerHTML += ":";
                p.classList.add("styledCode");
            }
            code.appendChild(p);
        }
    </script>
    <style>
        code {
            background-color: black;
            color: white;
            padding: 7px;
            white-space: pre-wrap;
            display: block;
            margin: 10px;
        }

        .styledCode {
            color: #bada55
        }
    </style>
</body>