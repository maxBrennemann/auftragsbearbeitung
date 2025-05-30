<div class="grid grid-cols-3">
    <div class="defCont">
        <h1 class="font-semibold">Import/ Export</h1>
        <div class="defCont hidden">
            <p class="font-semibold">Motivexporte</p>
            <button id="createFbExport" data-binding="true" class="btn-primary">Facebook Export generieren</button>
        </div>
        <a href="#" class="link-primary" data-binding="true" data-fun="crawlAll">Alle Produtke vom Shop crawlen</a>
        <br>
        <a href="#" class="link-primary" data-binding="true" data-fun="crawlTags">Alle Tags vom Shop crawlen</a>
        <br>
        <button class="btn-primary" data-binding="true" data-fun="manageImports">Importe verwalten</button>
    </div>
    <div class="defCont">
        <h1 class="font-semibold">Übersichten</h1>
        <div class="inline-flex gap-2 items-center bg-white rounded-md p-2">
            <button class="align-middle w-24 border-0 flex-none h-10 bg-lime-500"></button>
            <p>Diese Motivvariante ist im Shop, aber die Daten aus der Auftragsbearbeitung wurde nicht hochgeladen</p>
        </div>
        <div class="mt-3 inline-flex gap-2 items-center bg-white rounded-md p-2">
            <button class="align-middle w-24 border-0 flex-none h-10 bg-amber-500"></button>
            <p>Diese Motivvariante ist im Shop und aktuell</p>
        </div>
        <div>
            <a href="<?= Classes\Link::getPageLink("sticker-images") ?>" class="link-primary">Zur Bildübersicht</a>
        </div>
        <div>
            <button class="btn-primary" data-binding="true" data-fun="openTagOverview">Zur Tagübersicht</button>
        </div>
    </div>
    <div class="defCont">
        <h1 class="font-semibold">Neues Motiv hinzufügen</h1>
        <div class="flex flex-wrap">
            <input type="text" id="newTitle" class="input-primary">
            <button type="submit" data-binding="true" data-fun="createNewSticker" class="btn-primary ml-2">Hinzufügen</button>
        </div>
        <h1 class="font-semibold">Altes Motiv aus Shop laden</h1>
        <div class="flex flex-wrap">
            <input type="text" id="oldLink" class="input-primary">
            <button type="submit" data-binding="true" data-fun="loadSticker" class="btn-primary ml-2">Laden</button>
        </div>
    </div>
</div>
<div class="w-full overflow-x-auto h-dvh overflow-y-scroll mt-2" id="stickerTable"></div>