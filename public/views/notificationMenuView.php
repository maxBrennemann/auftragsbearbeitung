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
                <div class="border-b last:border-none border-gray-600 taskList cursor-pointer hover:bg-slate-100 flex items-center first:rounded-t-lg last:rounded-b-lg">
                    <div class="p-3">
                        <span class="type-label"><?= htmlspecialchars($n["typeName"]) ?></span>
                    </div>
                    <a class="block flex-1 pl-2 pr-4 py-3" href="<?= htmlspecialchars($n["link"]) ?>"><?= htmlspecialchars($n["content"]) ?></a>
                    <div class="p-3 ml-auto">
                        <button class="btn-secondary btn-sm" data-notification-read="<?= (int) $n["id"] ?>">Erledigt</button>
                    </div>
                </div>
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
                <div class="border-b last:border-none border-gray-600 taskList cursor-pointer hover:bg-slate-100 flex items-center first:rounded-t-lg last:rounded-b-lg">
                    <div class="p-3">
                        <span class="type-label"><?= htmlspecialchars($n["typeName"]) ?></span>
                    </div>
                    <a class="block flex-1 pl-2 pr-4 py-3" href="<?= htmlspecialchars($n["link"]) ?>"><?= htmlspecialchars($n["content"]) ?></a>
                    <div class="p-3">
                        <button class="btn-secondary btn-sm ml-auto" data-notification-read="<?= (int) $n["id"] ?>">Als gelesen markieren</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
