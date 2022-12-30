<?php
    require_once('settings.php');
    require_once('classes/Link.php');
?>
<!DOCTYPE html>
<head>
    <title>Die Auftragsbearbeitung updaten</title>
    <script src="<?=Link::getGlobalJS()?>"></script>
</head>
<body>
    <h1>Auftragsbearbeitungsupdater</h1>
    <p>Aktuelle Version: <?=CURRENTVERSION?><span id="actualVersion"></span></p>
    <button id="updateProject" data-binding="true">Update</button>
    <button id="composerUpdate" data-binding="true">Composer Update</button>
    <button id="composerInstall" data-binding="true">Composer Install</button>
    <p>Composer Install, um alle Pakete aus composer.lock zu installieren</p>
    <p>Composer Update, um alle Pakete zu aktualisieren, Updates zu überprüfen, Pakete zu entfernen und um composer.lock neu zu generieren</p>
    <code></code>
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

        async function click_composerInstall() {
            var install = await send({
                'query': 4
            }, "upgrade");

            console.log(install);
            install = JSON.parse(install);
            codeAppend(install.command, true);
            codeAppend(install.result);
        }

        async function click_composerUpdate() {
            var update = await send({
                'query': 5
            }, "upgrade");

            console.log(update);
            update = JSON.parse(update);
            codeAppend(update.command, true);
            codeAppend(update.result);
        }

        async function click_updateProject() {
            /* git pull ausführen */
            var print = await send({
                'query': 1
            }, "upgrade");

            console.log(print);
            print = JSON.parse(print);
            codeAppend(print.command, true);
            codeAppend(print.result);

            /* db upgrade ausführen */
            var sqlQuery = await send({
                query: 2
            }, "upgrade");

            console.log(sqlQuery);
            sqlQuery = JSON.parse(sqlQuery);
            Object.entries(sqlQuery).forEach(
                ([key, value]) => {
                    codeAppend(value.command, true);
                    codeAppend(value.result);
                }
            );

            console.log(sqlQuery);

            /* minify neu ausführen */
            var minify = await send({
                'query': 3
            }, 'upgrade');
            console.log(minify);
            minify = JSON.parse(minify);
            codeAppend(minify.command, true);
            codeAppend(minify.result);
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

        function send(data, intent) {
            data.getReason = intent;

            /* temporarily copied here */
            let temp = "";
            for (let key in data) {
                temp += key + "=" + data[key] + "&";
            }

            paramString = temp.slice(0, -1);

            console.log(paramString);
            var response = makeAsyncCall("POST", paramString, '<?=WEB_URL . SUB_URL?>').then(result => {
                return result;
            });

            return response;
        }
    </script>
    <style>
        @font-face {
            font-family: 'Open Sans';
            src: url("../font/OpenSans-Regular.ttf") format('truetype');
            font-weight: normal;
        }

        body {
            font-family: 'Open Sans';
        }

        code {
            background-color: black;
            color: white;
            padding: 7px;
            white-space: pre-wrap;
            display: block;
            margin: 10px;
        }

        button {
            border:1px solid rgb(213, 213, 213);
            -webkit-box-shadow: 0 1px 2px 0 rgb(0 0 0 / 10%);
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 10%);
            border-radius: 12px;
            height: 30px;
            background: #fff;
            display: inline-block;
            box-sizing: border-box;
            padding: 0.5em 2em;
            outline: none;
            color: #1a1a1a;
            margin-top: 25px;
        }

        .styledCode {
            color: #bada55
        }
    </style>
</body>