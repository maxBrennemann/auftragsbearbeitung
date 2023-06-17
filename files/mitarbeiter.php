<?php
// TODO: show time tracking and order history
require_once("classes/project/User.php");

$user = null;
$showUserList = true;
if (isset($_GET["id"])) {
    $user = new User($_GET["id"]);
    $showUserList = false;
} else {
    $user = new User($_SESSION["userid"]);
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
                    <input type="text" placeholder="Neuer Benutzername" class="block rounded-sm m-1 ml-0 p-1 w-80" name="somename" autocomplete="off">
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
            <input type="text" value="<?=$user->getUsername()?>" class="block rounded-sm m-1 ml-0 p-1 w-80" id="username">
        </label>
        <label>
            <p>Vorname</p>
            <input type="text" value="<?=$user->getPrename()?>" class="block rounded-sm m-1 ml-0 p-1 w-80" id="prename">
        </label>
        <label>
            <p>Nachname</p>
            <input type="text" value="<?=$user->getLastname()?>" class="block rounded-sm m-1 ml-0 p-1 w-80" id="lastname">
        </label>
        <label>
            <p>Email</p>
            <input type="text" value="<?=$user->getEmail()?>" class="block rounded-sm m-1 ml-0 p-1 w-80" id="email">
        </label>
    </div>
    <div class="defCont">
        <p class="font-bold">Arbeitszeiten</p>
        <div>
            <p>Maximale Arbeitszeit</p>
            <input type="number" value="" class="block rounded-sm m-1 ml-0 p-1 w-80">
            <button class="btn-primary">Speichern</button>
        </div>
    </div>
    <div class="defCont">
        <p class="font-bold">Deine Geräte</p>
        <div>
            <?php foreach ($user->getUserDeviceList() as $devices): ?>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="defCont">
        <p class="font-bold">Verlauf</p>
        <?=$user->getHistory()?>
    </div>
<?php endif; ?>
