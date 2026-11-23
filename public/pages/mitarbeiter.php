<?php
// TODO: show time tracking and order history

use Src\Classes\Project\Icon;
use Src\Classes\Project\User;
use MaxBrennemann\PhpUtilities\Tools;

$user;
$showUserList = true;
$userId = Tools::get("id");

if ($userId !== null) {
    $user = new User($userId);
    $showUserList = false;
} else {
    $userId = User::getCurrentUserId();
    $user = new User($userId);
}

if ($showUserList) : ?>
    <section class="defCont">
        <div class="section-head">
            <h2 class="section-title">Mitarbeiter</h2>
            <button class="btn-secondary btn-sm" data-target="#addNewUserForm,#showNewUserBtn" data-toggle="true" id="showNewUserBtn"><?= Icon::get("user-plus", 14) ?>Neuen Benutzer anlegen</button>
        </div>
        <div class="overflow-x-auto"><?= User::getUserOverview() ?></div>
    </section>

    <section class="defCont hidden" id="addNewUserForm">
        <div class="section-head">
            <h2 class="section-title">Neuen Benutzer anlegen</h2>
            <span class="section-meta">alle Felder sind Pflicht, Passwort mindestens 8 Zeichen</span>
        </div>
        <form class="field-grid max-w-3xl">
            <!-- verhindert, dass der Browser gespeicherte Zugangsdaten einträgt: https://stackoverflow.com/a/23234498/7113688 -->
            <input autocomplete="false" name="hidden" type="text" class="hidden">
            <input type="text" name="username" style="display:none">
            <input type="password" style="display:none">

            <label class="field">
                <span class="field-label">Benutzername</span>
                <input type="text" class="input-primary" id="newUsername" autocomplete="off" aria-autocomplete="none">
            </label>
            <label class="field">
                <span class="field-label">E-Mail</span>
                <input type="email" class="input-primary" id="newEmail" autocomplete="off">
            </label>
            <label class="field">
                <span class="field-label">Vorname</span>
                <input type="text" class="input-primary" id="newPrename" autocomplete="off">
            </label>
            <label class="field">
                <span class="field-label">Nachname</span>
                <input type="text" class="input-primary" id="newLastname" autocomplete="off">
            </label>
            <label class="field">
                <span class="field-label">Passwort</span>
                <input type="password" class="input-primary" id="newPassword" autocomplete="new-password">
            </label>
            <label class="field">
                <span class="field-label">Passwort wiederholen</span>
                <input type="password" class="input-primary" id="newPasswordRepeat" autocomplete="new-password">
            </label>
        </form>
        <p id="addUserWarnings" class="field-hint is-error mt-3 min-h-4" role="alert"></p>
        <div class="flex gap-2 mt-3">
            <button class="btn-primary" data-fun="addNewUser" data-binding="true"><?= Icon::get("user-plus") ?>Benutzer anlegen</button>
            <button class="btn-secondary" data-toggle="true" data-target="#addNewUserForm,#showNewUserBtn">Abbrechen</button>
        </div>
    </section>
<?php else: ?>
    <?php
    /* Bearbeiten darf nur der Benutzer selbst oder ein Admin, siehe User::getEditableUser() */
    $canEdit = $user->getId() === User::getCurrentUserId() || User::isAdmin();
    ?>
    <div class="mx-2.5 mt-2 mb-4">
        <span class="group-label text-text-muted">Mitarbeiter</span>
        <h2 class="page-title"><?= htmlspecialchars(trim($user->getPrename() . " " . $user->getLastname()) ?: $user->getUsername()) ?></h2>
    </div>

    <section class="defCont" id="userData" data-user-id="<?= $user->getId() ?>">
        <div class="section-head">
            <h2 class="section-title">Benutzerdaten</h2>
        </div>
        <div class="field-grid">
            <label class="field">
                <span class="field-label">Benutzername</span>
                <input type="text" value="<?= htmlspecialchars($user->getUsername()) ?>" class="input-primary" id="username" readonly aria-describedby="usernameHint">
                <span class="field-hint" id="usernameHint">Anmeldename, kann nicht geändert werden</span>
            </label>
            <label class="field">
                <span class="field-label">E-Mail</span>
                <input type="email" value="<?= htmlspecialchars($user->getEmail()) ?>" class="input-primary" id="email" autocomplete="off" aria-autocomplete="none">
            </label>
            <label class="field">
                <span class="field-label">Vorname</span>
                <input type="text" value="<?= htmlspecialchars($user->getPrename()) ?>" class="input-primary" id="prename" autocomplete="off" aria-autocomplete="none">
            </label>
            <label class="field">
                <span class="field-label">Nachname</span>
                <input type="text" value="<?= htmlspecialchars($user->getLastname()) ?>" class="input-primary" id="lastname" autocomplete="off" aria-autocomplete="none">
            </label>
            <label class="field">
                <span class="field-label">Maximale Arbeitszeit [Stunden]</span>
                <input type="number" min="0" max="168" step="1" value="<?= $user->getMaxWorkingHours() ?: "" ?>" class="input-primary" id="maxWorkingHours" placeholder="nicht festgelegt">
            </label>
        </div>
        <p id="userDataWarnings" class="field-hint is-error mt-3 min-h-4" role="alert"></p>
        <div class="flex items-center gap-2 mt-2">
            <button class="btn-primary" data-fun="saveUser" data-binding="true" <?= $canEdit ? "" : "disabled" ?>><?= Icon::get("save") ?>Speichern</button>
            <?php if (!$canEdit) : ?>
                <span class="field-hint">Nur der Benutzer selbst oder ein Admin kann diese Daten ändern.</span>
            <?php endif; ?>
        </div>
    </section>

    <section class="defCont">
        <div class="section-head">
            <h2 class="section-title">Geräte</h2>
            <span class="section-meta">Geräte, auf denen dieser Benutzer angemeldet war</span>
        </div>
        <div class="flex flex-col gap-2">
            <?php foreach ($user->getUserDeviceList() as $device): ?>
                <div class="sheet flex flex-wrap items-center gap-4" data-device>
                    <span class="flex-none grid place-items-center w-12 h-12 rounded-control bg-ink-soft text-ink">
                        <?= $user->getDeviceIcon($device["device_type"], $device["os"]) ?>
                    </span>
                    <div class="flex-1 min-w-48">
                        <?php if (!empty($device["user_device_name"])) : ?>
                            <p class="font-semibold"><?= htmlspecialchars($device["user_device_name"]) ?></p>
                        <?php endif; ?>
                        <p class="text-sm"><?= htmlspecialchars($device["browser"]) ?> auf <?= htmlspecialchars($device["os"]) ?></p>
                        <p class="text-xs text-text-muted">IP-Adresse <?= htmlspecialchars($device["ip_address"]) ?> · zuletzt verwendet <?= $device["lastUsage"] ?></p>
                    </div>
                    <button class="btn-danger btn-sm" data-fun="removeDevice" data-binding="true" data-device-id="<?= (int) $device["id"] ?>" <?= $canEdit ? "" : "disabled" ?> title="Gerät entfernen; die automatische Anmeldung auf diesem Gerät endet"><?= Icon::get("trash-2", 14) ?>Entfernen</button>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="defCont">
        <div class="section-head">
            <h2 class="section-title">Verlauf</h2>
        </div>
        <?= $user->getHistory() ?>
    </section>
<?php endif; ?>
