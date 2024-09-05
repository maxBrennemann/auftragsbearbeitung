<div class="grid grid-cols-3">
    <div class="defCont">
        <h1 class="font-bold">Import/ Export</h1>
        <div class="defCont hidden">
            <p class="font-bold">Motivexporte</p>
            <button id="createFbExport" data-binding="true" class="px-4 py-2 m-1 font-semibold text-sm bg-blue-200 text-slate-600 rounded-lg shadow-sm border-none">Facebook Export generieren</button>
        </div>
        <div class="productLoader" id="crawlAll">
            <div class="lds-ring" id="loaderCrawlAll"><div></div><div></div><div></div><div></div></div>
            <div>
                <progress max="1000" value="0" id="productProgress"></progress>
                <p><span id="currentProgress"></span> von <span id="maxProgress"></span></p>
                <p id="statusProgress"></p>
            </div>
        </div>
        <a href="#" class="link-primary" data-binding="true" data-fun="crawlAll">Alle Produtke vom Shop crawlen</a>
        <br>
        <a href="#" class="link-primary" data-binding="true" data-fun="crawlTags">Alle Tags vom Shop crawlen</a>
        <br>
        <button class="btn-primary" data-binding="true" data-fun="manageImports">Importe verwalten</button>
    </div>
    <div class="defCont">
        <h1 class="font-bold">Übersichten</h1>
        <div>
            <p><button class="showBox" id="yellow"></button> Diese Motivvariante ist im Shop, aber die Daten aus der Auftragsbearbeitung wurde nicht hochgeladen</p>
        </div>
        <div>
            <p><button class="showBox" id="green"></button> Diese Motivvariante ist im Shop und aktuell</p>
        </div>
        <div>
            <a href="<?=Link::getPageLink("sticker-images")?>" class="link-primary">Zur Bildübersicht</a>
        </div>
        <div>
            <button class="btn-primary" data-binding="true" data-fun="openTagOverview">Zur Tagübersicht</button>
        </div>
    </div>
    <div class="defCont">
        <h1 class="font-bold">Neues Motiv hinzufügen</h1>
        <div class="flex">
            <input type="text" id="newTitle" class="px-4 py-2 m-1 text-sm text-slate-600 rounded-lg">
            <button type="submit" data-binding="true" data-fun="createNewSticker" class="btn-primary">Hinzufügen</button>
        </div>
        <h1 class="font-bold">Altes Motiv aus Shop laden</h1>
        <div class="flex">
            <input type="text" id="oldLink" class="px-4 py-2 m-1 text-sm text-slate-600 rounded-lg">
            <button type="submit" data-binding="true" data-fun="loadSticker" class="btn-primary">Laden</button>
        </div>
    </div>
</div>
<div class="w-full overflow-x-scroll h-dvh overflow-y-scroll" id="stickerTable"></div>