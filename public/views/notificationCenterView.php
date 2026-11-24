<?php
$categoryLabels = [
    "step" => "Aufgaben",
    "order" => "Aufträge",
    "invoice" => "Rechnungen",
    "customer" => "Kunden",
    "other" => "Sonstiges",
];

$categoryCounts = [];
foreach ($notifications as $notification) {
    $category = $notification["category"];
    $categoryCounts[$category] = ($categoryCounts[$category] ?? 0) + 1;
}
?>
<section class="defCont">
    <div class="section-head">
        <h2 class="section-title">Benachrichtigungen</h2>
        <div class="flex flex-wrap items-center gap-3 text-sm">
            <span class="section-meta"><span id="notificationVisibleCount"><?= count($notifications) ?></span> Einträge</span>
            <button class="btn-secondary btn-sm" data-binding="true" data-fun="markAllRead"
                <?= $unreadCount > 0 ? "" : "disabled" ?>>Alle als gelesen markieren</button>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-2 mb-3">
        <span class="group-label">Status</span>
        <button type="button" class="btn-primary btn-sm" data-binding="true" data-fun="filterStatus"
            data-filter-status="all" aria-pressed="true">Alle</button>
        <button type="button" class="btn-secondary btn-sm" data-binding="true" data-fun="filterStatus"
            data-filter-status="unread" aria-pressed="false">Ungelesen (<span id="notificationUnreadCount"><?= (int) $unreadCount ?></span>)</button>

        <span class="group-label ml-2">Typ</span>
        <button type="button" class="btn-primary btn-sm" data-binding="true" data-fun="filterCategory"
            data-filter-category="all" aria-pressed="true">Alle</button>
        <?php foreach ($categoryLabels as $category => $label): ?>
            <?php if (empty($categoryCounts[$category])): continue; endif; ?>
            <button type="button" class="btn-secondary btn-sm" data-binding="true" data-fun="filterCategory"
                data-filter-category="<?= $category ?>" aria-pressed="false"><?= $label ?> (<?= $categoryCounts[$category] ?>)</button>
        <?php endforeach; ?>
    </div>

    <?php if (empty($notifications)): ?>
        <p class="text-gray-500">Keine Benachrichtigungen vorhanden.</p>
    <?php else: ?>
        <div id="notificationList" class="flex flex-col">
            <?php foreach ($notifications as $n): ?>
                <div class="border-b last:border-none border-gray-600 flex items-center gap-3 py-2 <?= $n["ischecked"] ? "opacity-60" : "" ?>"
                    data-notification-row="<?= (int) $n["id"] ?>"
                    data-status="<?= $n["ischecked"] ? "read" : "unread" ?>"
                    data-category="<?= htmlspecialchars($n["category"]) ?>">
                    <span class="type-label"><?= htmlspecialchars($n["typeName"]) ?></span>
                    <div class="flex-1 min-w-0">
                        <?php if ($n["link"] !== ""): ?>
                            <a class="link-primary" href="<?= htmlspecialchars($n["link"]) ?>"
                                <?php if ($n["category"] !== "step"): ?>data-open-notification="<?= (int) $n["id"] ?>"<?php endif; ?>><?= htmlspecialchars($n["content"]) ?></a>
                        <?php else: ?>
                            <span><?= htmlspecialchars($n["content"]) ?></span>
                        <?php endif; ?>
                        <div class="section-meta">
                            <?= date("d.m.Y H:i", strtotime($n["created_at"])) ?>
                            <?php if (!empty($n["initiatorName"])): ?>
                                &middot; von <?= htmlspecialchars($n["initiatorName"]) ?>
                            <?php endif; ?>
                            <?php if (!$n["ischecked"]): ?>
                                &middot; <span class="font-semibold" data-unread-marker>ungelesen</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if (!$n["ischecked"]): ?>
                        <button class="btn-secondary btn-sm" data-binding="true" data-fun="markRead"
                            data-id="<?= (int) $n["id"] ?>">Als gelesen markieren</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
        <p id="notificationEmptyFilter" class="text-gray-500 hidden">Keine Einträge für diesen Filter.</p>
        <?php if (count($notifications) >= $limit): ?>
            <p class="section-meta mt-2">Es werden die neuesten <?= (int) $limit ?> Benachrichtigungen angezeigt.</p>
        <?php endif; ?>
    <?php endif; ?>
</section>
