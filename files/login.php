<?php
    if(isset($_POST['info'])) {
        Login::manageRequest();
        header("Refresh:0");
    }
?>
<form method="post">
    <p>Benutzername / Email: <input type="text" name="loginData"></p>
    <p>Passwort: <input type="password" name="password"></p>
    <input type="submit" value="Einloggen" name="info">
</form>