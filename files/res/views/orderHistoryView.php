<?php foreach ($historyElement as $history) : ?>
<?php switch ($history["state"]): ?>
<?php
    case "added": ?>
        <div class="showInMiddle"><?= $h['name'] ?>: <i>{$beschreibung}</i><br>hinzugefügt am {$datetime}<br>von {$person}</div><div class="verticalLine"></div>
<?php endswitch; ?>
<?php endforeach; ?>