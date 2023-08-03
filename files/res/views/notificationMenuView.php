<div class="p-2">
    <h3 class="font-bold">Benachrichtigungen und Aufgaben</h3>
    <div style="display: block;">
        <h4 class="font-semibold my-3">Meine Aufgaben (<?=$tasksCount?>)<button onclick="updateNotifications()" class="floatRight noButton bg-gray-200 p-1 text-sm text-gray-700 hover:underline" title="Benachrichtigungen neu laden">⭮</button><button onclick="setRead()" class="floatRight noButton bg-gray-200 p-1 text-sm text-gray-700 hover:underline" title="Alles als gelesen markieren">✓</button></h4>
        <?php
            $count = 1;
            foreach ($tasks as $n): ?>
        <span class="block p-4 border-b border-gray-800 taskList cursor-pointer hover:bg-slate-100">
            <span><?=($count++)?></span>
            <button class="redButton"><?=$n["typeName"]?>: </button>
            <a href="<?=$n["link"]?>"><?=$n["content"]?></a>
        </span>
        <?php endforeach; ?>
    </div>
    <div style="display: block;">
        <h4 class="font-semibold my-3">Benachrichtigungen und Neuigkeiten (<?=$newsCount?>)<button onclick="updateNotifications()" class="floatRight noButton bg-gray-200 p-1 text-sm text-gray-700 hover:underline" title="Benachrichtigungen neu laden">⭮</button><button onclick="setRead()" class="floatRight noButton bg-gray-200 p-1 text-sm text-gray-700 hover:underline" title="Alles als gelesen markieren">✓</button></h4>
        <?php
            $count = 1;
            foreach ($news as $n): ?>
        <span class="block p-4 border-b border-gray-800 taskList cursor-pointer hover:bg-slate-100">
            <span><?=($count++)?></span>
            <button class="redButton"><?=$n["typeName"]?>: </button>
            <a href="<?=$n["link"]?>"><?=$n["content"]?></a>
        </span>
        <?php endforeach; ?>
    </div>
    <p class="mt-2"><a href="#">Ältere Benachrichtigungen anzeigen</a></p>
</div>