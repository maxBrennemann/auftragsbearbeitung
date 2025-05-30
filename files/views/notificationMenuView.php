<div class="p-2">
    <h3 class="font-bold">Benachrichtigungen und Aufgaben</h3>
    <div>
        <h4 class="font-semibold flex items-center">
            <span class="flex-auto">Meine Aufgaben (<?= $tasksCount ?>)</span>
        </h4>
        <div class="mt-2">
            <?php
            $count = 1;
            foreach ($tasks as $n): ?>
                <span class="p-4 border-b last:border-none border-gray-800 taskList cursor-pointer hover:bg-slate-100 flex items-center">
                    <span><?= ($count++) ?></span>
                    <button class="btn-primary-small"><?= $n["typeName"] ?> </button>
                    <a class="ml-2" href="<?= $n["link"] ?>"><?= $n["content"] ?></a>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
    <div>
        <h4 class="font-semibold flex items-center">
            <span class="flex-auto">Benachrichtigungen und Neuigkeiten (<?= $newsCount ?>)</span>
        </h4>
        <div class="mt-2">
            <?php
            $count = 1;
            foreach ($news as $n): ?>
                <span class="p-4 border-b border-gray-800 taskList cursor-pointer hover:bg-slate-100 flex items-center">
                    <span><?= ($count++) ?></span>
                    <button class="btn-primary-small"><?= $n["typeName"] ?> </button>
                    <a class="ml-2" href="<?= $n["link"] ?>"><?= $n["content"] ?></a>
                </span>
            <?php endforeach; ?>
        </div>
    </div>
    <p class="mt-3 hidden"><a href="<?= \Classes\Link::getPageLink("") ?>" class="link-primary">Ältere Benachrichtigungen anzeigen</a></p>
</div>