<?php

use Src\Classes\Link;
use Src\Classes\Project\Icon;

/**
 * Übersicht der Funktionen mit eingebettetem Handbuch (funktionen.ts):
 * Ein Klick auf einen Eintrag mit .clickable öffnet die verlinkte Seite als Vorschau darunter.
 * Erwartete Struktur: <li class="clickable" data-name data-intent>Text <a class="extLinks" href>…</a></li>
 * (der Link muss das einzige Kind-Element sein).
 */
$demoLink = fn(string $page, string $query = "") => Link::getPageLink($page) . $query;
$ext = Icon::get("external-link", 14);

?>
<section class="defCont">
	<div class="section-head">
		<h2 class="section-title">Funktionen der Auftragsbearbeitung</h2>
		<span class="section-meta">Einträge mit Pfeil anklicken, um die Seite als Vorschau zu öffnen</span>
	</div>

	<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3">
		<div class="sheet">
			<span class="group-label mb-2">Kunden</span>
			<ul class="m-0 space-y-1 text-sm">
				<li class="clickable m-0 cursor-pointer hover:text-ink" data-name="neuerKunde" data-intent="create">Neuen Kunden anlegen <a class="link-primary extLinks ml-1 inline-flex align-[-0.15em]" href="<?= $demoLink("neuer-kunde") ?>" title="Seite öffnen"><?= $ext ?></a></li>
				<li class="clickable m-0 cursor-pointer hover:text-ink" data-name="kunde" data-intent="change-data">Kundendaten ändern <a class="link-primary extLinks ml-1 inline-flex align-[-0.15em]" href="<?= $demoLink("kunde", "?id=37") ?>" title="Seite öffnen"><?= $ext ?></a></li>
			</ul>
		</div>

		<div class="sheet">
			<span class="group-label mb-2">Aufträge</span>
			<ul class="m-0 space-y-1 text-sm">
				<li class="clickable m-0 cursor-pointer hover:text-ink" data-name="neuer-auftrag" data-intent="change-data">Neuen Auftrag anlegen <a class="link-primary extLinks ml-1 inline-flex align-[-0.15em]" href="<?= $demoLink("neuer-auftrag", "?id=37") ?>" title="Seite öffnen"><?= $ext ?></a></li>
				<li class="m-0 text-text-muted">Posten hinzufügen und bearbeiten</li>
				<li class="m-0 text-text-muted">Bearbeitungsschritte und Notizen</li>
				<li class="m-0 text-text-muted">Rechnungsstellung</li>
			</ul>
		</div>

		<div class="sheet">
			<span class="group-label mb-2">Angebote</span>
			<ul class="m-0 space-y-1 text-sm">
				<li class="clickable m-0 cursor-pointer hover:text-ink" data-name="angebot" data-intent="change-data">Neues Angebot anlegen <a class="link-primary extLinks ml-1 inline-flex align-[-0.15em]" href="<?= $demoLink("angebot", "?id=37") ?>" title="Seite öffnen"><?= $ext ?></a></li>
				<li class="m-0 text-text-muted">Posten hinzufügen</li>
				<li class="m-0 text-text-muted">Angebot in Auftrag übernehmen</li>
			</ul>
		</div>

		<div class="sheet md:col-span-2 xl:col-span-3">
			<span class="group-label mb-2">Weitere Bereiche</span>
			<div class="flex flex-wrap gap-2">
				<a class="btn-secondary btn-sm" href="<?= $demoLink("leistungen") ?>"><?= Icon::get("wrench", 14) ?>Leistungen</a>
				<a class="btn-secondary btn-sm" href="<?= $demoLink("produkt") ?>"><?= Icon::get("tag", 14) ?>Produkte</a>
				<a class="btn-secondary btn-sm" href="<?= $demoLink("diagramme") ?>"><?= Icon::get("chart-line", 14) ?>Analyse und Statistik</a>
				<a class="btn-secondary btn-sm" href="<?= $demoLink("payments") ?>"><?= Icon::get("wallet", 14) ?>Finanzen</a>
				<a class="btn-secondary btn-sm" href="<?= $demoLink("wiki") ?>"><?= Icon::get("book-open", 14) ?>Firmenwiki</a>
				<a class="btn-secondary btn-sm" href="<?= $demoLink("mitarbeiter") ?>"><?= Icon::get("users", 14) ?>Mitarbeiter</a>
			</div>
		</div>
	</div>
</section>

<!-- Navigation durch das Handbuch; funktionen.ts blendet sie ein, sobald eine Vorschau offen ist -->
<div class="manualNavigator mt-2 mb-2" style="display: none">
	<button type="button" class="btn-secondary btn-icon btn-sm" title="Vorheriger Hinweis" aria-label="Vorheriger Hinweis"><?= Icon::get("chevron-left") ?></button>
	<button type="button" class="btn-secondary btn-icon btn-sm" title="Nächster Hinweis" aria-label="Nächster Hinweis"><?= Icon::get("chevron-right") ?></button>
	<p class="inline-block ml-2 text-sm"></p>
</div>
