<?php
/*
 * Aufgaben: Klick führt nur zum Auftrag, sie bleiben stehen, bis der Schritt erledigt ist.
 * Neuigkeiten: Klick markiert als gelesen (data-notification-read) und führt zum Ziel.
 */
?>
<div class="p-2">
    <h3 class="font-bold">Benachrichtigungen und Aufgaben</h3>
    <div>
        <h4 class="font-semibold flex items-center mt-1">
            <span class="flex-auto">Meine Aufgaben (<?= $tasksCount ?>)</span>
        </h4>
        <div class="mt-2 max-h-64 overflow-auto">
            <?php if (empty($tasks)): ?>
                <p class="p-3 text-gray-500">Keine offenen Aufgaben.</p>
            <?php endif; ?>
            <?php foreach ($tasks as $n): ?>
                <a class="border-b last:border-none border-gray-600 taskList hover:bg-slate-100 flex items-center first:rounded-t-lg last:rounded-b-lg"
                    href="<?= htmlspecialchars($n["link"] !== "" ? $n["link"] : "#") ?>">
                    <span class="p-3"><span class="type-label"><?= htmlspecialchars($n["typeName"]) ?></span></span>
                    <span class="flex-1 pl-2 pr-4 py-3"><?= htmlspecialchars($n["content"]) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
    <div>
        <h4 class="font-semibold flex items-center mt-1">
            <span class="flex-auto">Benachrichtigungen und Neuigkeiten (<?= $newsCount ?>)</span>
        </h4>
        <div class="mt-2 max-h-64 overflow-auto relative">
            <?php if (empty($news)): ?>
                <p class="p-3 text-gray-500">Keine Neuigkeiten.</p>
            <?php endif; ?>
            <?php foreach ($news as $n): ?>
                <a class="border-b last:border-none border-gray-600 taskList hover:bg-slate-100 flex items-center first:rounded-t-lg last:rounded-b-lg"
                    href="<?= htmlspecialchars($n["link"]) ?>"
                    data-notification-read="<?= (int) $n["id"] ?>"
                    title="<?= $n["link"] !== "" ? "Öffnen und als gelesen markieren" : "Als gelesen markieren" ?>">
                    <span class="p-3"><span class="type-label"><?= htmlspecialchars($n["typeName"]) ?></span></span>
                    <span class="flex-1 pl-2 pr-4 py-3"><?= htmlspecialchars($n["content"]) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
