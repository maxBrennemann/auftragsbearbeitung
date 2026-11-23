<?php

use Src\Classes\Project\Icon;

?>
<section class="defCont">
    <div class="section-head">
        <h2 class="section-title">Zeiterfassung</h2>
        <span class="section-meta">die laufende Zeit erscheint auch oben in der Kopfzeile, wenn in den Einstellungen aktiviert</span>
    </div>
    <div class="flex flex-wrap items-center gap-4">
        <label class="relative inline-flex items-center cursor-pointer">
            <input type="checkbox" value="" class="sr-only peer" data-fun="startStopTime" data-binding="true" id="startStopChecked">
            <div class="w-11 h-6 bg-line rounded-full peer peer-focus-visible:ring-2 peer-focus-visible:ring-ink peer-focus-visible:ring-offset-2 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-0.5 after:bg-white after:border-line after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-ink"></div>
            <span class="ms-3 font-semibold" id="statusTimeTracking">Zeiterfassung <span id="updateStartStopName">starten</span></span>
        </label>
        <div class="flex items-center gap-2">
            <button class="btn-secondary" id="pauseCurrentTracking" data-fun="pauseCurrentTracking" data-binding="true" disabled><?= Icon::get("pause") ?>Pausieren</button>
            <button class="btn-secondary" disabled data-fun="cancelCurrentTracking" data-binding="true" id="cancelCurrentTracking">Abbrechen</button>
        </div>
    </div>
</section>

<section class="defCont">
    <div class="section-head">
        <h2 class="section-title">Erfasste Zeiten</h2>
    </div>
    <div class="flex flex-wrap items-end gap-4 mb-4">
        <div class="btn-group" role="group" aria-label="Zeitraum">
            <button type="button" class="is-active" aria-pressed="true" data-fun="selectEntries" data-binding="true" data-value="all">Alle</button>
            <button type="button" aria-pressed="false" data-fun="selectEntries" data-binding="true" data-value="today">Heute</button>
            <button type="button" aria-pressed="false" data-fun="selectEntries" data-binding="true" data-value="week">Diese Woche</button>
            <button type="button" aria-pressed="false" data-fun="selectEntries" data-binding="true" data-value="month">Dieser Monat</button>
        </div>
        <div class="flex items-end gap-2">
            <label class="field">
                <span class="field-label">Von</span>
                <input type="date" class="input-primary timeDates" data-fun="selectDates" data-write="true">
            </label>
            <label class="field">
                <span class="field-label">Bis</span>
                <input type="date" class="input-primary timeDates" data-fun="selectDates" data-write="true">
            </label>
        </div>
    </div>
    <div class="relative">
        <div id="timeTrackingTable" class="overflow-auto"></div>
    </div>
</section>

<!-- Dialog beim Stoppen; timeTracking.ts schaltet hidden/flex um -->
<div class="hidden overflow-y-auto overflow-x-hidden fixed inset-0 z-50 justify-center items-center w-full max-h-full bg-white/75" id="askTask" role="dialog" aria-modal="true" aria-labelledby="askTaskTitle">
    <div class="relative w-[28rem] max-w-[calc(100%-2rem)] bg-white border border-line rounded-box shadow-xl">
        <div class="p-4">
            <p class="section-title" id="askTaskTitle">Was hast du gemacht?</p>
            <label class="field mt-3">
                <span class="field-label">Beschreibung der Tätigkeit</span>
                <input type="text" class="input-primary w-full" id="getTask">
            </label>
        </div>
        <div class="flex flex-row-reverse gap-2 px-4 py-3 border-t border-line-soft bg-[#f6f7f8] rounded-b-box">
            <button class="btn-primary" data-fun="sendTimeTracking" data-binding="true">Zeit speichern</button>
            <button class="btn-secondary" data-fun="cancelTimeTracking" data-binding="true">Abbrechen</button>
        </div>
    </div>
</div>
