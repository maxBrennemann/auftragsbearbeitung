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
    <script>
        if (document.readyState !== 'loading' ) {
            init();
        } else {
            document.addEventListener('DOMContentLoaded', function () {
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
            var print = await send({getReason:null}, "testDummy");
            console.log(print);
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
</body>