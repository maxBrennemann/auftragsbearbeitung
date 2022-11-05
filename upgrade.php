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
    <p>Aktuelle Version:<span id="actualVersion"></span></p>
    <button id="updateProject" data-binding="true">Update</button>
    <code></code>
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

        async function click_updateProject() {
            var print = await send({query:1}, "upgrade");
            print = JSON.parse(print);
            codeAppend(print.command, true);
            codeAppend(print.result);
            var sqlQuery = await send({query:2}, "upgrade");
            console.log(sqlQuery);
            sqlQuery = JSON.parse(sqlQuery);
            Object.entries(sqlQuery).forEach(
                ([key, value]) => {
                    codeAppend(value.command, true);
                    codeAppend(value.result);
                }
            );

            console.log(sqlQuery);
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

            var response = makeAsyncCall("POST", paramString, "http://localhost/auftragsbearbeitung/c/").then(result => {
                return result;
            });

            return response;
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