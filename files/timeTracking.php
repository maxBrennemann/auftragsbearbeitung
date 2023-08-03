<?php
require_once("classes/project/TimeTracking.php");

$idUser = $_SESSION['userid'];
$timeTables = TimeTracking::getTimeTables((int) $idUser);

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
    <div class="mt-4" id="askTask">
        <input class="input-primary" type="text" id="getTask">
        <p>Was hast Du gemacht?</p>
        <button class="btn-primary" id="sendTimeTracking" data-binding="true">Abschicken</button>
    </div>
</div>
<div class="defCont mt-3">
    <div>
        <select class="input-primary">
            <option>Datum</option>
            <option>Dauer</option>
            <option>Aufgabe</option>
        </select>
        <?php foreach ($timeTables as $month => $table): ?>
            <div>
                <p class="monthHeading mt-2 font-semibold"><?=$month?></p>
                <?=$table?>
            </div>
        <?php endforeach; ?>
        <div id="showTaskTable"></div>
    </div>
</div>