<?php 

    $idUser = $_SESSION['userid'];
    $currDate = date('Y-m-1 H:i:s');

    $query = "SELECT started_at AS Beginn, stopped_at AS Ende, duration_ms AS Zeit, task AS Aufgabe, edit_log AS Bearbeitungsnotiz FROM user_timetracking WHERE `user_id` = $idUser AND `started_at` > '$currDate'";
    $column_names = array(
        0 => array("COLUMN_NAME" => "Beginn"),
        1 => array("COLUMN_NAME" => "Ende"),
        2 => array("COLUMN_NAME" => "Zeit"),
        3 => array("COLUMN_NAME" => "Aufgabe"),
        4 => array("COLUMN_NAME" => "Bearbeitungsnotiz"),);
    $data = DBAccess::selectQuery($query);
    $count = 0;
    for ($i = 0; $i < sizeof($data); $i++) {
        $d = $data[$i];
        /* https://stackoverflow.com/questions/2754765/how-to-reformat-date-in-php */
        $startedAtFormatted = DateTime::createFromFormat('Y-m-d H:i:s', $d['Beginn']);
        $data[$i]['Beginn'] = $startedAtFormatted->format("d.m.Y H:i:s");
        $stoppedAtFormatted = DateTime::createFromFormat('Y-m-d H:i:s', $d['Ende']);
        $data[$i]['Ende'] = $stoppedAtFormatted->format("d.m.Y H:i:s");

        /* https://stackoverflow.com/questions/3856293/how-to-convert-seconds-to-time-format */
        $seconds = (int) $d["Zeit"] / 1000;
        $hours = floor($seconds / 3600);
        $mins = floor($seconds / 60 % 60);
        $secs = floor($seconds % 60);

        $data[$i]["Zeit"] = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);

        /* unschöne Lösung */
        if ($count < 5) {
            $d["editable"] = "true";
        } else {
            $d["editable"] = "false";
        }
        $count++;
    }
    $t = new Table();
    $t->createByData($data, $column_names);
    $table = $t->getTable();

?>
<div class="defCont">
    <div class="zeit">
        <label class="switch">
            <input type="checkbox" id="startStopChecked">
            <span class="slider round" id="startStopTime" data-binding="true"></span>
        </label>
        <p>Zeiterfassung <span id="updateStartStopName" data-update="startStopTime">starten</span></p>
        <span id="timer"></span>
    </div>
    <div id="askTask">
        <input type="text" id="getTask">
        <p>Was hast Du gemacht?</p>
        <button id="sendTimeTracking" data-binding="true">Abschicken</button>
    </div>
    <div id="showTaskTable"><?=$table?></div>
</div>
<script>
    var started = false;
    var interval = null;
    var startTime = null;
    var stopTime = null;

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

        if (localStorage.getItem("startTime")) {
            started = true;
            interval = setInterval(countTime, 1000);
            document.getElementById("updateStartStopName").innerHTML = "stoppen";
            document.getElementById("startStopChecked").checked = true;
        }
    }

    function click_startStopTime() {
        started = !started;
        switch (started) {
            case true:
                storeTimestamp("startTime");
                interval = setInterval(countTime, 1000);
                document.getElementById("updateStartStopName").innerHTML = "stoppen";
            break;
            case false:
                clearInterval(interval);
                document.getElementById("updateStartStopName").innerHTML = "starten";
                document.getElementById("askTask").style.display = "inline";
            break;
        }
    }

    async function click_sendTimeTracking() {
        var task = document.getElementById("getTask").value;
        var table = await send({task:task}, "sendTimeTracking");
        document.getElementById("showTaskTable").innerHTML = table;
        localStorage.clear("startTime");
    }

    function countTime() {
        let curr = new Date().getTime().toString();
        let startTime = parseInt(localStorage.getItem("startTime"));

        let diff = curr - startTime;

        let sec = Math.floor(diff / 1000);
        let hou = Math.floor(sec / 60 / 60);
        sec = sec - hou * 60 * 60;
        let min = Math.floor(sec / 60);
        sec = sec - min * 60;

        document.getElementById("timer").innerHTML = `${this.pad(hou)}:${this.pad(min)}:${this.pad(sec)}`;
    }

    function storeTimestamp(stamp) {
        let time = new Date().getTime().toString();
        localStorage.setItem(stamp, time);
    }

    function pad(num) {
        return ('00' + num).slice(-2);
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
    /* https://www.w3schools.com/howto/howto_css_switch.asp */
    /* The switch - the box around the slider */
    .switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
    }

    /* Hide default HTML checkbox */
    .switch input {
    opacity: 0;
    width: 0;
    height: 0;
    }

    /* The slider */
    .slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    -webkit-transition: .4s;
    transition: .4s;
    }

    .slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    -webkit-transition: .4s;
    transition: .4s;
    }

    input:checked + .slider {
    background-color: #2196F3;
    }

    input:focus + .slider {
    box-shadow: 0 0 1px #2196F3;
    }

    input:checked + .slider:before {
    -webkit-transform: translateX(26px);
    -ms-transform: translateX(26px);
    transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
    border-radius: 34px;
    }

    .slider.round:before {
    border-radius: 50%;
    }

    /* style vom rest */
    .zeit > * {
        display: inline-block;
        vertical-align: middle;
    }

    #askTask {
        display: none;
    }
</style>