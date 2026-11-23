<?php

/*
  Verlauf eines Auftrags oder Angebots als Zeitleiste (Richtlinie „Kontor“).
  Die neuesten $limit Einträge sind sichtbar, ältere liegen in einem <details>,
  damit „Mehr anzeigen“ ohne JS auf Auftrags- und Angebotsseite funktioniert.
*/

/** @var array<int, array<string, string>> $historyElement */

$limit = 8;

$verbs = [
    "added" => "hinzugefügt",
    "edited" => "bearbeitet",
    "finished" => "abgeschlossen",
    "deleted" => "gelöscht",
    "removed" => "entfernt",
    "payed" => "bezahlt",
    "unpaid" => "als unbezahlt markiert",
    "sent" => "gesendet",
    "accepted" => "angenommen",
    "rejected" => "abgelehnt",
    "expired" => "abgelaufen",
];

$renderEntry = function (array $history) use ($verbs): string {
    $state = (string) ($history["state"] ?? "");
    $verb = $verbs[$state] ?? $state;
    $date = date("d.m.Y, H:i", (int) strtotime((string) $history["insertstamp"]));
    $isoDate = date("c", (int) strtotime((string) $history["insertstamp"]));
    $name = htmlspecialchars((string) ($history["name"] ?? ""));
    $description = trim((string) ($history["Beschreibung"] ?? ""));
    $user = htmlspecialchars((string) ($history["prename"] ?? ""));

    $dotColor = match ($state) {
        "deleted", "removed", "rejected", "expired" => "bg-danger",
        "finished", "accepted", "payed" => "bg-ok",
        default => "bg-ink",
    };

    ob_start(); ?>
    <li class="relative pl-5 pb-3 last:pb-0">
        <span class="absolute -left-[5.5px] top-1.5 size-2.5 rounded-full ring-2 ring-white <?= $dotColor ?>" aria-hidden="true"></span>
        <div class="text-sm">
            <span class="font-semibold"><?= $name ?></span> <?= htmlspecialchars($verb) ?>
        </div>
        <?php if ($description !== "") : ?>
            <div class="text-sm break-words line-clamp-3" title="<?= htmlspecialchars($description) ?>"><?= htmlspecialchars($description) ?></div>
        <?php endif; ?>
        <div class="text-xs text-text-muted tabular-nums">
            <time datetime="<?= $isoDate ?>"><?= $date ?></time><?= $user !== "" ? " · " . $user : "" ?>
        </div>
    </li>
<?php
    return (string) ob_get_clean();
};

$visible = array_slice($historyElement, 0, $limit);
$older = array_slice($historyElement, $limit);
?>

<?php if (count($historyElement) === 0) : ?>
    <p class="text-sm text-text-muted">Noch keine Einträge.</p>
<?php else : ?>
    <ol class="relative ml-1 border-l border-line-soft">
        <?php foreach ($visible as $history) : ?>
            <?= $renderEntry($history) ?>
        <?php endforeach; ?>
    </ol>

    <?php if (count($older) > 0) : ?>
        <details class="group mt-3">
            <summary class="btn-ghost btn-sm cursor-pointer list-none w-fit">
                <span class="group-open:hidden">Ältere Einträge anzeigen (<?= count($older) ?>)</span>
                <span class="hidden group-open:inline">Ältere Einträge ausblenden</span>
            </summary>
            <ol class="relative ml-1 mt-3 border-l border-line-soft">
                <?php foreach ($older as $history) : ?>
                    <?= $renderEntry($history) ?>
                <?php endforeach; ?>
            </ol>
        </details>
    <?php endif; ?>
<?php endif; ?>
