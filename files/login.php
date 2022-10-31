<?php
    if (isset($_POST['info'])) {
        Login::manageRequest();
        header("Refresh:0");
    }
?>
<form method="post">
    <input type="text" name="loginData" autofocus placeholder="Nutzername oder Email">
    <br>
    <input type="password" name="password" placeholder="Passwort">
    <br>
    <input type="checkbox" name="setAutoLogin">
    <label for="setAutoLogin">Eingeloggt bleiben</label>
    <!-- lazy solution for now -->
    <input type="text" hidden name="browserInfo" id="userAgent">
    <br>
    <input type="submit" value="Einloggen" name="info">
</form>
<script>
    document.getElementById("userAgent").value = window.navigator.userAgent;
</script>
<style>
    form {
        border-radius: 6px;
        background-color: #eff0f1;
        padding: 10px;
        margin: 10px;
    }

    form input {
        margin: 7px;
    }

    form input::placeholder {
        color: #B2B2BE;
    }

    form input[type=text], form input[type=password] {
        padding: 5px;
        border: none;
        border-radius: 6px;
        background-color: white;
    }

    form input[type=submit] {
        border-radius: 6px;
        border: none;
        padding: 5px;
        background-color: #B2B2BE;
        color: white;
    }
</style>