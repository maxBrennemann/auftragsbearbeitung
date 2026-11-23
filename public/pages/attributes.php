<?php

use Src\Classes\Controller\TemplateController;
use Src\Classes\Project\Icon;
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
            "attributes" => [],
        ];
    }

    /* Eigenschaften ohne Werte liefern durch den LEFT JOIN eine Zeile ohne Wert */
    if ($d["id"] !== null) {
        $attributeGroups[$d["attribute_group"]]["attributes"][] = [
            "id" => $d["id"],
            "value" => $d["value"],
        ];
    }
}

?>
<?= TemplateController::getTemplate("productNav", ["current" => "attributes"]) ?>

<section class="defCont">
    <div class="section-head">
        <h2 class="section-title">Eigenschaften und Werte</h2>
        <span class="section-meta">Werte per Ziehen am Griff sortieren</span>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-3">
        <?php foreach ($attributeGroups as $group) : ?>
            <!-- Reihenfolge der [data-id]-Elemente (Name, Beschreibung, Liste) wird in attributes.ts verwendet -->
            <div class="singleAttribute sheet flex flex-col gap-2">
                <label class="field">
                    <span class="field-label">Eigenschaft</span>
                    <input type="text" class="input-primary font-semibold" value="<?= $group["name"] ?>" data-id="<?= $group["id"] ?>">
                </label>
                <label class="field">
                    <span class="field-label">Beschreibung</span>
                    <input type="text" class="input-primary" value="<?= $group["descr"] ?>" data-id="<?= $group["id"] ?>">
                </label>
                <ul class="attributeValueGroups m-0 mt-2 space-y-1.5" id="attributeValues_<?= $group["id"] ?>" data-id="<?= $group["id"] ?>">
                    <?php foreach ($group["attributes"] as $a) : ?>
                        <li class="m-0 flex items-center gap-2 py-1.5 px-2.5 bg-[#f6f7f8] border border-line-soft rounded-control text-sm select-none transition-colors hover:border-line" draggable="true" data-id="<?= $a["id"] ?>">
                            <span class="flex-1"><?= $a["value"] ?></span>
                            <button type="button" class="btn-ghost btn-icon btn-sm cursor-grab" title="Zum Sortieren ziehen" aria-label="Zum Sortieren ziehen"><?= Icon::get("grip-vertical", 14) ?></button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endforeach; ?>
    </div>
</section>

<div class="grid grid-cols-1 lg:grid-cols-2">
    <section class="defCont addAttributeValue">
        <div class="section-head">
            <h2 class="section-title">Wert hinzufügen</h2>
        </div>
        <div class="field-grid">
            <label class="field">
                <span class="field-label">Wert</span>
                <input class="input-primary" id="newVal" placeholder="z. B. XL">
            </label>
            <label class="field">
                <span class="field-label">Zu Eigenschaft</span>
                <select class="input-primary" id="selectAttribute">
                    <?php foreach ($attributeGroups as $group) : ?>
                        <option value="<?= $group["id"] ?>"><?= $group["name"] ?></option>
                    <?php endforeach; ?>
                </select>
            </label>
        </div>
        <button id="btnAddValue" class="btn-primary mt-4" data-binding="true"><?= Icon::get("plus") ?>Wert hinzufügen</button>
    </section>

    <section class="defCont addAttribute">
        <div class="section-head">
            <h2 class="section-title">Neue Eigenschaft</h2>
        </div>
        <div class="field-grid">
            <label class="field">
                <span class="field-label">Name der Eigenschaft</span>
                <input class="input-primary" id="newName" placeholder="z. B. Größe">
            </label>
            <label class="field">
                <span class="field-label">Beschreibung</span>
                <input class="input-primary" id="descr">
            </label>
        </div>
        <div class="flex gap-2 mt-4">
            <button id="btnAddAttribute" class="btn-primary" data-binding="true"><?= Icon::get("plus") ?>Eigenschaft anlegen</button>
            <button id="btnAbortAttribute" class="btn-secondary" data-binding="true">Felder leeren</button>
        </div>
    </section>
</div>
