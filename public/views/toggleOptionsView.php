<?php

use Src\Classes\Project\Icon;

/**
 * Menü hinter „⋮“ für seltene Aktionen. Das umgebende Element braucht position: relative.
 * Optionen: ["text", "function", "icon"?, "danger"? (bool), "dividerBefore"? (bool), "show"? (bool), "class"? (überschreibt)]
 * $bindToggle = true: Auf- und Zuklappen übernimmt js-classes (data-toggle), sonst eigener Code der Seite.
 */
$toggleAttributes = ($bindToggle ?? false) ? " data-toggle=\"true\" data-target=\"#{$toggleId}\"" : "";
?>
<button type="button" class="btn-options <?= $toggleClass ?>" id="<?= $toggleBtnId ?>"<?= $toggleAttributes ?> title="Weitere Aktionen" aria-label="Weitere Aktionen" aria-haspopup="menu"><?= Icon::get("ellipsis-vertical", 18) ?></button>
<div class="hidden absolute right-0 top-full z-20 mt-1 menu-panel <?= $toggleClass ?>" id="<?= $toggleId ?>" role="menu">
    <?php foreach ($options as $option): ?>
        <?php
        if (($option["show"] ?? true) === false) {
            continue;
        }
        $class = $option["class"] ?? ("menu-item" . (($option["danger"] ?? false) ? " menu-item-danger" : ""));
        ?>
        <?php if ($option["dividerBefore"] ?? false): ?>
            <div class="menu-divider" role="separator"></div>
        <?php endif; ?>
        <button type="button" role="menuitem" class="<?= $class ?>" data-binding="true" data-fun="<?= $option["function"] ?>">
            <?= isset($option["icon"]) ? Icon::get($option["icon"]) : "" ?>
            <span><?= $option["text"] ?></span>
        </button>
    <?php endforeach; ?>
</div>
