<?php

use Classes\Link;
use MaxBrennemann\PhpUtilities\DBAccess;

$data = DBAccess::selectQuery("SELECT ag.attribute_group, ag.descr, a.id, a.value, a.attribute_group_id, a.position 
    FROM attribute_group ag 
    LEFT JOIN attribute a 
        ON ag.id = a.attribute_group_id
    ORDER BY ag.position, a.position");

$attributeGroups = [];

foreach ($data as $d) {
    if (!array_key_exists($d["attribute_group"], $attributeGroups)) {
        $attributeGroups[$d["attribute_group"]] = [
            "descr" => $d["descr"],
            "id" => $d["attribute_group_id"],
            "name" => $d["attribute_group"],
        ];
    }

    $attributeGroups[$d["attribute_group"]]["attributes"][] = [
        "id" => $d["id"],
        "value" => $d["value"],
    ];
}

?>
<div class="mt-4">
    <a class="link-primary" href="<?= Link::getPageLink("neues-produkt") ?>">Zum Produktformular</a>
    <a class="link-primary ml-2" href="<?= Link::getPageLink("produkt") ?>">Zur Produktübersicht</a>
</div>

<div class="mt-2 defCont flex flex-row flex-wrap gap-2.5">
    <?php foreach ($attributeGroups as $group) : ?>
        <div class="singleAttribute bg-white rounded-lg p-3 flex-1">
            <div>
                <p>Name:</p>
                <input type="text" class="input-primary mt-1 font-bold" value="<?= $group["name"] ?>" data-id="<?= $group["id"] ?>">
            </div>
            <div class="mt-2">
                <p>Beschreibung:</p>
                <input type="text" class="input-primary mt-1 font-semibold" value="<?= $group["descr"] ?>" data-id="<?= $group["id"] ?>">
            </div>
            <ul class="attributeValueGroups mt-5 space-y-2" id="attributeValues_<?= $group["id"] ?>" data-id="<?= $group["id"] ?>">
                <?php foreach ($group["attributes"] as $a) : ?>
                    <li class="transition-all duration-150 ease-in-out bg-slate-100 rounded-md py-2 px-3 hover:bg-blue-300 cursor-pointer flex select-none" draggable="true" data-id="<?= $a["id"] ?>">
                        <span class="flex-1"><?= $a["value"] ?></span>
                        <div class="flex-none mr-1" title="Anordnen">
                            <button class="border-none" title="Anordnen"><?= Classes\Project\Icon::getDefault("iconMove") ?></button>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>
</div>

<div class="defCont addAttributeValue">
    <h3 class="font-semibold">Eigenschaftswert hinzufügen</h3>
    <div class="mt-2">
        <p>Attributname:</p>
        <input class="input-primary mt-1" id="newVal">
    </div>
    <div class="mt-2">
        <p>Eigenschaft:</p>
        <select class="input-primary mt-1" id="selectAttribute">
            <?php foreach ($attributeGroups as $group) : ?>
                <option value="<?= $group["id"] ?>"><?= $group["name"] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="mt-2">
        <button id="btnAddValue" class="btn-primary" data-binding="true">Hinzufügen</button>
    </div>
</div>

<div class="defCont addAttribute">
    <h3 class="font-semibold">Neue Eigenschaft hinzufügen</h3>
    <div clas="mt-2">
        <p>Eigenschaftsname:</p>
        <input class="input-primary mt-1" id="newName">
    </div>
    <div class="mt-2">
        <p>Beschreibung:</p>
        <input class="input-primary mt-1" id="descr">
    </div>
    <div class="mt-2">
        <button id="btnAddAttribute" class="btn-primary" data-binding="true">Hinzufügen</button>
        <button id="btnAbortAttribute" class="btn-cancel" data-binding="true">Leeren</button>
    </div>
</div>