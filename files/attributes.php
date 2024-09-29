<?php

$data = Classes\DBAccess::selectQuery("SELECT ag.attribute_group, ag.descr, a.id, a.value, a.attribute_group_id, a.position 
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
    <a class="link-button" href="<?= Classes\Link::getPageLink("produkt") ?>">Zu den Produkten</a>
    <a class="link-button" href="<?= Classes\Link::getPageLink("neues-produkt") ?>">Zum Produktformular</a>
</div>
<div class="mt-2 flex flex-row flex-wrap gap-2.5 m-auto">
    <?php foreach ($attributeGroups as $group) : ?>
        <div class="defCont singleAttribute">
            <h2 data-id="<?= $group['id'] ?>" class="underline"><?= $group["name"] ?></h2>
            <p><i><?= $group["descr"] ?></i></p>
            <ul class="attributeValueGroups mt-2" id="attributeValues_<?= $group["id"] ?>" data-id="<?= $group['id'] ?>">
                <?php foreach ($group["attributes"] as $a) : ?>
                    <li class="bg-white rounded-md p-1 pl-2 hover:bg-blue-300 cursor-pointer flex" draggable="true" data-id="<?= $a["id"] ?>">
                        <span class="flex-1"><?= $a["value"] ?></span>
                        <div class="flex-none mr-1"><button></button></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>
</div>
<div class="defCont addAttributeValue">
    <h3 class="mb-2 underline">Eigenschaftswert hinzufügen</h3>
    <input class="input-primary" id="newVal">
    <select class="input-primary" id="selectAttribute">
        <?php foreach ($attributeGroups as $group) : ?>
            <option value="<?= $group['id'] ?>"><?= $group['name'] ?></option>
        <?php endforeach; ?>
    </select>
    <button id="btnAddValue" class="btn-primary">Hinzufügen</button>
</div>
<div class="defCont addAttribute">
    <h3 class="mb-2 underline">Neue Eigenschaft hinzufügen</h3>
    <p class="p-2">Eigenschaftsname: <input class="input-primary" id="newName"></p>
    <p class="p-2">Beschreibung: <input class="input-primary" id="descr"></p>
    <button id="btnAddAttribute" class="btn-primary">Hinzufügen</button>
    <button id="btnAbortAttribute" class="btn-attention">Leeren</button>
</div>