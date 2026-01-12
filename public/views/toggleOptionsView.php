<button class="btn-options <?= $toggleClass ?>" id="<?= $toggleBtnId ?>">⋮</button>
<div class="hidden absolute right-0 top-0 bg-white rounded-lg drop-shadow-lg p-3 mt-5 <?= $toggleClass ?>" id="<?= $toggleId ?>">
    <?php foreach ($options as $option): ?>
        <?php 
            $show = $option["show"] ?? true;
            if ($show === false) continue; ?>
        <button 
            class="<?= $option["class"] ? $option["class"] : "btn-primary" ?>"
            data-binding="true"
            data-fun="<?= $option["function"] ?>">
            <?= $option["text"] ?>
        </button>
    <?php endforeach; ?>
</div>