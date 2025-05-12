<div class="my-2">
    <?= Classes\Project\TemplateController::getTemplate("uploadFile", [
        "target" => $imageCategory,
    ]); ?>
</div>
<div>
    <div id="<?= $imageCategory ?>Table"></div>
    <div>
        <a class="float-right text-xs mt-1 cursor-pointer" data-binding="true" data-fun="showImageOptions" data-target="<?= $imageCategory ?>">Mehr</a>
    </div>
</div>