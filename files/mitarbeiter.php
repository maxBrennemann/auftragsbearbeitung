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
                    <input type="text" placeholder="Neuer Benutzername" class="block rounded-xs m-1 ml-0 p-1 w-80" name="somename" autocomplete="off">
                </label>
                <label>
                    <p>Vorname</p>
                    <input type="text" placeholder="Vorname" class="block rounded-xs m-1 ml-0 p-1 w-80" name="prename" autocomplete="off">
                </label>
                <label>
                    <p>Nachname</p>
                    <input type="text" placeholder="Nachname" class="block rounded-xs m-1 ml-0 p-1 w-80" name="lastname" autocomplete="off">
                </label>
                <label>
                    <p>Email</p>
                    <input type="email" placeholder="Email" class="block rounded-xs m-1 ml-0 p-1 w-80" name="email" autocomplete="off">
                </label>
                <label>
                    <p>Passwort</p>
                    <input type="password" class="block rounded-xs m-1 ml-0 p-1 w-80" name="newPassword" autocomplete="off">
                </label>
                <label>
                    <p>Passwort wiederholen</p>
                    <input type="password" class="block rounded-xs m-1 ml-0 p-1 w-80" name="newPasswordRepeat" autocomplete="off">
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
            <input type="text" value="<?=$user->getUsername()?>" class="block rounded-xs m-1 ml-0 p-1 w-80" id="username">
        </label>
        <label>
            <p>Vorname</p>
            <input type="text" value="<?=$user->getPrename()?>" class="block rounded-xs m-1 ml-0 p-1 w-80" id="prename">
        </label>
        <label>
            <p>Nachname</p>
            <input type="text" value="<?=$user->getLastname()?>" class="block rounded-xs m-1 ml-0 p-1 w-80" id="lastname">
        </label>
        <label>
            <p>Email</p>
            <input type="text" value="<?=$user->getEmail()?>" class="block rounded-xs m-1 ml-0 p-1 w-80" id="email">
        </label>
    </div>
    <div class="defCont">
        <p class="font-bold">Arbeitszeiten</p>
        <div>
            <p>Maximale Arbeitszeit</p>
            <input type="number" value="" class="block rounded-xs m-1 ml-0 p-1 w-80">
            <button class="btn-primary">Speichern</button>
        </div>
    </div>
    <div class="defCont">
        <p class="font-bold">Deine Geräte</p>
        <div>
            <?php foreach ($user->getUserDeviceList() as $device): ?>
                <div class="bg-white rounded-lg p-4 mt-2 grid grid-cols-3">
                    <div>
                        <?=$user->getDeviceIcon($device["device_type"], $device["os"])?>
                    </div>
                    <div class="col-span-2">
                        <p><?=$device["user_device_name"]?></p>
                        <p><?=$device["browser"]?> auf <?=$device["os"]?></p>
                        <p>IP-Adresse: <?=$device["ip_address"]?>, letze Verwendung: <?=$device["last_usage"]?></p>
                        <button class="btn-primary" disabled>Entfernen</button>
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
