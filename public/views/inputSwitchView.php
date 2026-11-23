<?php
/**
 * Schalter (an/aus). Struktur bitte beibehalten: Skripte finden die Beschriftung über
 * input.nextElementSibling.nextElementSibling (z. B. offeneRechnungen.ts).
 */
?>
<label class="inline-flex items-center cursor-pointer">
    <input type="checkbox" <?= isset($id) ? 'id="' . $id . '"' : '' ?> <?= isset($value) ? $value : "" ?> class="sr-only peer"
        <?= isset($binding) ? 'data-binding="true" data-fun="' . $binding . '"' : '' ?>
        <?= isset($write) ? 'data-write="true" data-fun="' . $write . '"' : '' ?>
    >
    <div class="relative w-10 h-[1.375rem] flex-none bg-line rounded-full transition-colors peer-focus-visible:ring-2 peer-focus-visible:ring-ink peer-focus-visible:ring-offset-2 peer-checked:bg-ink peer-checked:after:translate-x-[1.125rem] after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-[1.125rem] after:w-[1.125rem] after:shadow-sm after:transition-transform"></div>
    <span class="ml-2 text-sm font-medium"><?= $name ?></span>
</label>
