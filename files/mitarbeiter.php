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
    <?=User::getUserOverview()?>
    <button class="btn-primary" disabled>Neuen Benutzer anlegen</button>
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
        <p class="font-bold">Verlauf</p>
        <?=$user->getHistory()?>
    </div>
<?php endif; ?>
