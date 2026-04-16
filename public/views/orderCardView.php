<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-6 mt-6">
    <?php foreach ($orders as $order): ?>
        <div class="group relative bg-white rounded-xl shadow-sm border border-gray-200 hover:shadow-md hover:border-blue-300 transition-all duration-200 flex flex-col">
            
            <div class="p-5 flex-1">
                <div class="flex justify-between items-start mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-gray-400">#<?= $order["id"] ?></span>
                    <?php if ($order["status"] === \Src\Classes\Project\OrderState::Archived): ?>
                        <span class="px-2 py-1 text-[10px] font-bold uppercase rounded bg-gray-100 text-gray-500 border border-gray-200">Archiviert</span>
                    <?php endif; ?>
                </div>

                <h3 class="text-lg font-bold text-gray-800 leading-tight group-hover:text-blue-600 transition-colors">
                    <?= $order["orderTitle"] ?>
                </h3>
                
                <p class="text-sm text-gray-500 mt-2 line-clamp-2 h-10" title="<?= $order["orderDescription"] ?>">
                    <?= $order["orderDescription"] ?>
                </p>

                <div class="grid grid-cols-2 gap-4 mt-5 pt-5 border-t border-gray-50">
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-400">Datum</p>
                        <p class="text-sm font-medium text-gray-700"><?= $order["date"] ?></p>
                    </div>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-400">Termin</p>
                        <p class="text-sm font-medium text-gray-400">
                            <?= $order["deadline"] ?: 'Keiner' ?>
                        </p>
                    </div>
                    <?php if ($order["invoice"] != 0): ?>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-400">Rechnung</p>
                        <p class="text-sm font-semibold text-gray-800">#<?= $order["invoice"] ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($order["summe"] != 0): ?>
                    <div>
                        <p class="text-[10px] uppercase font-bold text-gray-400">Summe</p>
                        <p class="text-sm font-bold text-blue-700"><?= $order["summe"] ?> €</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="px-5 py-3 bg-gray-50 rounded-b-xl border-t border-gray-100 flex justify-between items-center">
                <a href="<?= \Src\Classes\Link::getPageLink("auftrag") ?>?id=<?= $order["id"] ?>" 
                   class="text-sm font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                    Details öffnen 
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </a>
                
                <?php if ($order["status"] === \Src\Classes\Project\OrderState::Archived): ?>
                    <button data-binding="true" data-fun="rearchive" data-order-id="<?= $order['id'] ?>" 
                            class="text-gray-400 hover:text-orange-500 transition-colors border-0 shadow-sm" title="Wiederherstellen">
                        <?= \Src\Classes\Project\Icon::getDefault("iconArchive") ?>
                    </button>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>