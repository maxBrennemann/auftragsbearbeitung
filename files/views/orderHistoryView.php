<?php

$count = 0;
$limit = 7;

foreach ($historyElement as $history) : ?>
<?php $history["insertstamp"] = date('d.m.Y H:i', strtotime($history["insertstamp"])); ?>

<?php switch ($history["state"]): ?>
<?php
    case "added": ?>
        <div class="<?= $count > $limit ? "hidden" : "" ?>">
            <div class="bg-white rounded-lg p-1 text-center">
                <?= $history["name"] ?>: 
                <i><?= $history["Beschreibung"] ?></i>
                <br>hinzugefügt am <?= $history["insertstamp"] ?>
                <br>von <?= $history["prename"] ?>
            </div>
            <div class="w-0.5 h-7 m-auto border-l-2 border-l-black"></div>
        </div>
        <? break; ?>
<?php
    case "edited": ?>
<?php
    case "finished": ?>
        <div class="<?= $count > $limit ? "hidden" : "" ?>">
            <div class="bg-white rounded-lg p-1 text-center">
                <?= $history["name"] ?>: 
                <i><?= $history["Beschreibung"] ?></i>
                <br>abgeschlossen am <?= $history["insertstamp"] ?>
                <br>von <?= $history["prename"] ?>
            </div>
            <div class="w-0.5 h-7 m-auto border-l-2 border-l-black"></div>
        </div>
        <? break; ?>
<?php
    case "deleted": ?>
        <div class="<?= $count > $limit ? "hidden" : "" ?>">
            <div class="bg-white rounded-lg p-1 text-center">
                <?= $history["name"] ?>: 
                <i><?= $history["Beschreibung"] ?></i>
                <br>gelöscht am <?= $history["insertstamp"] ?>
                <br>von <?= $history["prename"] ?>
            </div>
            <div class="w-0.5 h-7 m-auto border-l-2 border-l-black"></div>
        </div>
        <? break; ?>
<?php endswitch; ?>
<?php 

$count++;
endforeach; ?>

<div class="text-center<?= $count > $limit ? " " : " hidden" ?>">
    <button class="btn-primary" data-fun="showMoreOrderHistory" data-binding="true">Mehr anzeigen</button>
</div>
<div class="bg-white rounded-lg p-1 text-center<?= $count > $limit ? " hidden" : "" ?>">Keine (weiteren) Einträge</div>