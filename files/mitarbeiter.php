<?php
// TODO: show time tracking and order history

use Classes\Project\User;

$user = null;
$showUserList = true;
if (isset($_GET["id"])) {
    $user = new User($_GET["id"]);
    $showUserList = false;
} else {
    $user = new User($_SESSION["user_id"]);
}

if ($showUserList) : ?>
    <div class="defCont">
        <?=User::getUserOverview()?>
    </div>
    <div class="defCont">
        <div class="hidden" id="addNewUserForm">
            <form autocomplete="off">
                <!-- https://stackoverflow.com/a/23234498/7113688 -->
                <input autocomplete="false" name="hidden" type="text" class="hidden">
                <input type="text" name="username" style="display:none">
                <input type="password" style="display:none">
                <label>
                    <p>Nutzername</p>
                    <input type="text" placeholder="Neuer Benutzername" class="block rounded-sm m-1 ml-0 p-1 w-80" name="somename" autocomplete="off" aria-autocomplete="none">
                </label>
                <label>
                    <p>Vorname</p>
                    <input type="text" placeholder="Vorname" class="block rounded-sm m-1 ml-0 p-1 w-80" name="prename" autocomplete="off">
                </label>
                <label>
                    <p>Nachname</p>
                    <input type="text" placeholder="Nachname" class="block rounded-sm m-1 ml-0 p-1 w-80" name="lastname" autocomplete="off">
                </label>
                <label>
                    <p>Email</p>
                    <input type="email" placeholder="Email" class="block rounded-sm m-1 ml-0 p-1 w-80" name="email" autocomplete="off">
                </label>
                <label>
                    <p>Passwort</p>
                    <input type="password" class="block rounded-sm m-1 ml-0 p-1 w-80" name="newPassword" autocomplete="off">
                </label>
                <label>
                    <p>Passwort wiederholen</p>
                    <input type="password" class="block rounded-sm m-1 ml-0 p-1 w-80" name="newPasswordRepeat" autocomplete="off">
                </label>
                <button class="btn-primary">Abschicken</button>
            </form>
        </div>
        <button class="btn-primary" id="addNewUserBtn">Neuen Benutzer anlegen</button>
    </div>
<?php else: ?>
    <div class="defCont">
        <p class="font-bold">Benutzerdaten</p>
        <label>
            <p>Nutzername</p>
            <input type="text" value="<?=$user->getUsername()?>" class="input-primary-new w-80" id="username" autocomplete="off" aria-autocomplete="none">
        </label>
        <label>
            <p>Vorname</p>
            <input type="text" value="<?=$user->getPrename()?>" class="input-primary-new w-80" id="prename" autocomplete="off" aria-autocomplete="none">
        </label>
        <label>
            <p>Nachname</p>
            <input type="text" value="<?=$user->getLastname()?>" class="input-primary-new w-80" id="lastname" autocomplete="off" aria-autocomplete="none">
        </label>
        <label>
            <p>Email</p>
            <input type="text" value="<?=$user->getEmail()?>" class="input-primary-new w-80" id="email" autocomplete="off" aria-autocomplete="none">
        </label>
        <div class="mt-2">
            <button class="btn-cancel">Abbrechen</button>
            <button class="btn-primary-new">Speichern</button>
        </div>
    </div>
    <div class="defCont">
        <p class="font-bold">Arbeitszeiten</p>
        <div>
            <p>Maximale Arbeitszeit</p>
            <input type="number" value="" class="input-primary-new">
            <button class="btn-primary-new">Speichern</button>
        </div>
    </div>
    <div class="defCont">
        <p class="font-bold">Deine Geräte</p>
        <div>
            <?php foreach ($user->getUserDeviceList() as $device): ?>
                <div class="bg-white rounded-lg p-4 mt-2 grid grid-cols-3 hover:bg-gray-50">
                    <div class="inline-flex items-center pl-5">
                        <?=$user->getDeviceIcon($device["device_type"], $device["os"])?>
                    </div>
                    <div>
                        <p class="font-semibold"><?=$device["user_device_name"]?></p>
                        <p class="font-semibold"><?=$device["browser"]?> auf <?=$device["os"]?></p>
                        <p>IP-Adresse: <?=$device["ip_address"]?>, letze Verwendung: <?=$device["lastUsage"]?></p>
                    </div>
                    <div class="inline-flex items-center justify-center">
                        <button class="btn-delete">Entfernen</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="defCont">
        <p class="font-bold">Verlauf</p>
        <?=$user->getHistory()?>
    </div>
<?php endif; ?>
