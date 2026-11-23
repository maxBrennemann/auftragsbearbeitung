<?php

use Src\Classes\Link;
use Src\Classes\Project\Icon;
use Src\Classes\Project\OrderState;

/**
 * Auftragskarten (Kundenseite, Auftragsübersicht).
 * JS-Haken für „Wiederherstellen“ (kunde.ts click_rearchive): .orderCard, .orderOptions, .orderDisabled
 */
?>
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-3">
    <?php foreach ($orders as $order): ?>
        <?php
        $isArchived = $order["status"] === OrderState::Archived;
        $deadline = in_array($order["deadline"], ["", "-", null], true) ? "" : $order["deadline"];
        ?>
        <article class="orderCard sheet flex flex-col p-0 transition-colors hover:border-ink">
            <div class="flex-1 p-4">
                <div class="flex items-center justify-between gap-2 mb-1.5">
                    <span class="kpi-label">Auftrag <?= $order["id"] ?></span>
                    <?php if ($isArchived): ?>
                        <span class="orderDisabled stamp stamp-muted">Archiviert</span>
                    <?php endif; ?>
                </div>

                <h3 class="font-heading font-bold text-ink leading-snug"><?= $order["orderTitle"] ?></h3>

                <p class="text-sm text-text-muted mt-1 line-clamp-2 min-h-10" title="<?= $order["orderDescription"] ?>">
                    <?= $order["orderDescription"] ?>
                </p>

                <dl class="grid grid-cols-2 gap-x-4 gap-y-2 mt-3 pt-3 border-t border-line-soft text-sm">
                    <div>
                        <dt class="kpi-label">Eingang</dt>
                        <dd class="tabular-nums"><?= $order["date"] ?></dd>
                    </div>
                    <div>
                        <dt class="kpi-label">Termin</dt>
                        <dd class="tabular-nums <?= $deadline ? "" : "text-text-muted" ?>"><?= $deadline ?: "keiner" ?></dd>
                    </div>
                    <?php if ($order["invoice"] != 0): ?>
                        <div>
                            <dt class="kpi-label">Rechnung</dt>
                            <dd class="tabular-nums">Nr. <?= $order["invoice"] ?></dd>
                        </div>
                    <?php endif; ?>
                    <?php if ($order["summe"] != 0): ?>
                        <div>
                            <dt class="kpi-label">Summe</dt>
                            <dd class="tabular-nums font-semibold"><?= is_numeric($order["summe"]) ? number_format((float) $order["summe"], 2, ',', '.') : $order["summe"] ?> €</dd>
                        </div>
                    <?php endif; ?>
                </dl>
            </div>

            <div class="flex items-center justify-between gap-2 px-4 py-2 border-t border-line-soft bg-[#f6f7f8] rounded-b-[inherit]">
                <a href="<?= Link::getPageLink("auftrag") ?>?id=<?= $order["id"] ?>" class="link-primary inline-flex items-center gap-1 text-sm">
                    Auftrag öffnen <?= Icon::get("chevron-right", 14) ?>
                </a>

                <?php if ($isArchived): ?>
                    <button type="button" class="orderOptions btn-ghost btn-icon btn-sm" data-binding="true" data-fun="rearchive" data-order-id="<?= $order['id'] ?>" title="Aus dem Archiv holen" aria-label="Aus dem Archiv holen">
                        <?= Icon::get("archive-restore") ?>
                    </button>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>
