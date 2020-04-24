<?php

if (isset($_POST['send'])) {
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];
    $db_name = $_POST['db_name'];

    unzip();
    createConfigFiles($db_user, $db_pass, $db_name);
}

function unzip() {
    $zip = new ZipArchive();
    $x = $zip->open("auftragsbearbeitung.zip");
    if ($x === true) {
        $zip->extractTo('files/');
        $zip->close();

        mkdir("upload/");
    }
}

function createConfigFiles($db_user, $db_pass, $db_name) {
    $settings = fopen("settings.php", "w");
    $txt = "<?php\r\ndefine('REWRITE_BASE', '/auftragsbearbeitung/');\r\ndefine('WEB_URL', '/auftragsbearbeitung');\r\ndefine('SUB_URL', '/content/');\r\n\r\n/* database connection */\r\ndefine('HOST', 'localhost');\r\ndefine('USERNAME', '$db_user');\r\ndefine('PASSWORD', '$db_pass');\r\ndefine('DATABASE', '$db_name');\r\n?>";
    fwrite($settings, $txt);
    fclose($settings);
}

if (isset($_POST['send'])) : ?>
<p>Bitte das Verzeichnis /install löschen</p>
<?php else: ?>
<div>
    <form id="sendConf" action method="post" id="articleUpload"></form>
    <label form="sendConf">Datenbankbenutzer</label>
    <input type="text" placeholder="user" name="db_user" form="sendConf"><br>
    <label form="sendConf">Passwort</label>
    <input type="text" placeholder="" name="db_pass" form="sendConf"><br>
    <label form="sendConf">Datenbankname</label>
    <input type="text" placeholder="auftragsbearbeitung" name="db_name" form="sendConf"><br>
    <input type="submit" value="Abschicken" form="sendConf" name="send">
</div>
<?php endif; ?>
<style>

    @import url('https://fonts.googleapis.com/css?family=Be+Vietnam|Raleway|Open+Sans&display=swap');

    body {
        font-family: 'Open Sans', Tahoma, Geneva, Verdana, sans-serif;
    }

    div {
        margin: auto;
        margin-left: auto;
        margin-right: auto;
        width: 200px;
        margin-top: 50px;
        border-radius: 6px;
        background: #eff0f1;
        border: none;
        padding: 20px;
    }

    div > * {
        margin: 2px;
    }

    input {
        padding: 5px;
    }

    input[type=submit] {
        background: white;
        border: none;
        border-radius: 6px;
        padding: 7px;
        margin-top: 7px;
    }

</style>