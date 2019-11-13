<?php
	/* Code an https://www.php-einfach.de/experte/php-codebeispiele/loginscript/ angelehnt */
	
	require_once('DBAccess.php');
	session_start();
	
	if(isset($_GET['info']) && $_GET['info'] == 'register') {
		$error = false;
		$email = $_POST['email'];
		$username = $_POST['username'];
		$password = $_POST['password'];
		$password2 = $_POST['password2'];
		
		if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			echo 'Bitte eine gültige E-Mail-Adresse eingeben<br>';
			$error = true;
		}
		
		if(strlen($password) == 0) {
			echo 'Bitte ein Passwort angeben<br>';
			$error = true;
		}
		
		if($password != $password2) {
			echo 'Die Passwörter müssen übereinstimmen<br>';
			$error = true;
		}
		
		/* Überprüfe, dass die E-Mail-Adresse noch nicht registriert wurde */
		if(!$error) { 
			$result = DBAccess::selectQuery("SELECT * FROM members WHERE email = $email");
			$user = $statement->fetch();
			
			if($user !== false) {
				echo 'Diese E-Mail-Adresse ist bereits vergeben<br>';
				$error = true;
			}
			
			$result = DBAccess::selectQuery("SELECT * FROM members WHERE username = $username");
			$user = $statement->fetch();
			
			if($user !== false) {
				echo 'Dieser Benutzername ist bereits vergeben<br>';
				$error = true;
			}
		}
		
		/* Keine Fehler, wir können den Nutzer registrieren */
		if(!$error) {    
			$password_hash = password_hash($password, PASSWORD_DEFAULT);
			
			$params = array('username' => $username, 'email' => $email, 'password' => $password_hash);
			$insert = "INSERT INTO members (username, email, password) VALUES (:username, :email, :password)";
			$result = DBAccess::insertQuery($insert, $params);
			
			if($result) {        
				echo 'Du wurdest erfolgreich registriert. <a href="login.php">Zum Login</a>';
				$showFormular = false;
			} else {
				echo 'Beim Abspeichern ist leider ein Fehler aufgetreten<br>';
			}
		}
	} else if(isset($_GET['info']) && $_GET['info'] == 'login') {
		$loginData = $_POST['loginData'];
		$password = $_POST['password'];
		
		$user = DBAccess::selectQuery("SELECT * FROM members WHERE email = $loginData OR username = $loginData");
		
		//Überprüfung des Passworts
		if ($user !== false && password_verify($password, $user['password'])) {
			$_SESSION['userid'] = $user['id'];
			
			if($user['specialRole'] == 'admin') {
				$_SESSION['admin'] = $user['id'];
				die('Login erfolgreich. Weiter zum <a href="https://max-website.tk/admin/">Admin-Bereich</a>');
			} else {
				die('Login erfolgreich. Weiter zu <a href="geheim.php">internen Bereich</a>');
			}
		} else {
			echo "E-Mail / Benutzername oder Passwort war ungültig<br>";
		}
	}
?>