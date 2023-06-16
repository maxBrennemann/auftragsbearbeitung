<?php
/* Code an https://www.php-einfach.de/experte/php-codebeispiele/loginscript/ angelehnt */

require_once('DBAccess.php');
session_start();

if (isset($_GET['info']) && $_GET['info'] == 'register') {
	$error = false;
	$email = $_POST['email'];
	$username = $_POST['username'];
	$prename = $_POST['prename'];
	$lastname = $_POST['lastname'];
	$password = $_POST['password'];
	$password2 = $_POST['password2'];
	
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		echo 'Bitte eine gültige E-Mail-Adresse eingeben<br>';
		$error = true;
	}
	
	if (strlen($password) == 0) {
		echo 'Bitte ein Passwort angeben<br>';
		$error = true;
	}
	
	if ($password != $password2) {
		echo 'Die Passwörter müssen übereinstimmen<br>';
		$error = true;
	}
	
	/* Überprüfe, dass die E-Mail-Adresse und Username noch nicht registriert wurden */
	if (!$error) { 
		$query = "SELECT id FROM user WHERE email = :email;";
		$result = DBAccess::selectQuery($query, array('email' => $email));
		
		if (count($result) > 0) {
			echo 'Diese E-Mail-Adresse ist bereits vergeben<br>';
			$error = true;
		}
		
		$query = "SELECT id FROM user WHERE username = :username";
		$result = DBAccess::selectQuery($query, array('username' => $username));
		
		if (count($result) > 0) {
			echo 'Dieser Benutzername ist bereits vergeben<br>';
			$error = true;
		}
	}
	
	/* Keine Fehler, wir können den Nutzer registrieren */
	if (!$error) {    
		$password_hash = password_hash($password, PASSWORD_DEFAULT);
		$query = "INSERT INTO user (username, prename, lastname, email, password, max_working_hours, role) VALUES (:username, :prename, :lastname, :email, :password, 0, 0)";
		$params = array(
			'username' => $username,
			'prename' => $prename,
			'lastname' => $lastname,
			'email' => $email,
			'password' => $password_hash
		);
		$result = DBAccess::insertQuery($query, $params);
		
		if ($result) {        
			echo 'Du wurdest erfolgreich registriert. <a href="login.php">Zum Login</a>';
			$showFormular = false;
		} else {
			echo 'Beim Abspeichern ist leider ein Fehler aufgetreten<br>';
		}
	}
} else if (isset($_GET['info']) && $_GET['info'] == 'login') {
	$loginData = $_POST['loginData'];
	$password = $_POST['password'];
	
	$user = DBAccess::selectQuery("SELECT * FROM user WHERE email = :loginData OR username = :password", array('loginData' => $loginData, 'password' => $password));
	
	if ($user !== false && password_verify($password, $user['password'])) {
		$_SESSION['userid'] = $user['id'];
		
		if ($user['specialRole'] == 'admin') {
			$_SESSION['admin'] = $user['id'];
			die('Login erfolgreich. Weiter zum <a href="https://max-website.tk/admin/">Admin-Bereich</a>');
		} else {
			die('Login erfolgreich. Weiter zu <a href="geheim.php">internen Bereich</a>');
		}
	} else {
		echo "E-Mail / Benutzername oder Passwort war ungültig<br>";
	}
}
