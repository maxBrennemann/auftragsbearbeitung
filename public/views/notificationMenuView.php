<div class="p-2">
    <h3 class="font-bold">Benachrichtigungen und Aufgaben</h3>
    <div>
        <h4 class="font-semibold flex items-center mt-1">
            <span class="flex-auto">Meine Aufgaben (<?= $tasksCount ?>)</span>
        </h4>
        <div class="mt-2 max-h-64 overflow-auto">
            <?php foreach ($tasks as $n): ?>
                <div class="border-b last:border-none border-gray-600 taskList cursor-pointer hover:bg-slate-100 flex items-center first:rounded-t-lg last:rounded-b-lg">
                    <div class="p-3">
                        <button class="btn-primary-small"><?= $n["typeName"] ?> </button>
                    </div>
                    <a class="block flex-1 pl-2 pr-4 py-3" href="<?= $n["link"] ?>"><?= $n["content"] ?></a>
                    <div class="p-3 ml-auto">
                        <button class="btn-active-small" data-fun="" data-binding="true">Erledigt</button>
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
            <?php foreach ($news as $n): ?>
                <div class="border-b last:border-none border-gray-600 taskList cursor-pointer hover:bg-slate-100 flex items-center first:rounded-t-lg last:rounded-b-lg">
                    <div class="p-3">
                        <button class="btn-primary-small"><?= $n["typeName"] ?> </button>
                    </div>
                    <a class="block flex-1 pl-2 pr-4 py-3" href="<?= $n["link"] ?>"><?= $n["content"] ?></a>
                    <div class="p-3">
                        <button class="btn-active-small ml-auto" data-fun="" data-binding="true">Als gelesen markieren</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>