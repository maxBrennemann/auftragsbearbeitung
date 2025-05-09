<?php

use Classes\Project\Icon;

?>
<div class="w-full">
    <div class="bg-gray-100 rounded-lg p-2">
        <h1>Zeiterfassung</h1>
        <div class="mt-2">   
            <label class="relative inline-flex items-center cursor-pointer">
                <input type="checkbox" value="" class="sr-only peer" data-fun="startStopTime" data-binding="true" id="startStopChecked">
                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-blue-600"></div>
                <span class="ms-3 text-sm font-medium text-gray-900 dark:text-gray-300" id="statusTimeTracking">Zeiterfassung <span id="updateStartStopName">starten</span></span>
            </label>
        </div>
        <div class="inline-flex items-center mt-2">
            <button class="btn-cancel inline-flex items-center" id="pauseCurrentTracking" data-fun="pauseCurrentTracking" data-binding="true" disabled><?=Icon::getDefault("iconPause")?><span class="ml-1">Pausieren</span></button>
            <button class="btn-cancel ml-2" data-fun="cancelCurrentTracking" data-binding="true">Abbrechen</button>
        </div>
    </div>
    <div class="mt-2">
        <div>
            <h2 class="font-bold">Zeitenübersicht</h2>
        </div>
        <div class="mt-2">
            <button class="btn-primary" data-fun="selectEntries" data-binding="true" data-value="all">Alle</button>
            <button class="btn-primary" data-fun="selectEntries" data-binding="true" data-value="today">Heute</button>
            <button class="btn-primary" data-fun="selectEntries" data-binding="true" data-value="week">Diese Woche</button>
            <button class="btn-primary" data-fun="selectEntries" data-binding="true" data-value="month">Dieser Monat</button>
        </div>
        <div class="relative">
            <div id="timeTrackingTable" class="mt-2 overflow-auto"></div>
        </div>
    </div>
    <div class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full bg-white/75" id="askTask">
		<div class="relative max-w-2xl max-h-full shadow-xl bg-white rounded-sm border border-solid border-gray-400 p-2">
            <p>Beschreibung der Tätigkeit:</p>
            <input type="text" class="block mt-2 input-primary w-full" id="getTask">
            <div class="mt-2">
                <button class="btn-cancel" data-fun="cancelTimeTracking" data-binding="true">Abbrechen</button>
                <button class="btn-primary" data-fun="sendTimeTracking" data-binding="true">Abschicken</button>
            </div>
		</div>
	</div>
</div>