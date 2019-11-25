<?php

require_once('DBAccess.php');
require_once('Mailer.php');

class Login {
	
	/* Code an https://www.php-einfach.de/experte/php-codebeispiele/loginscript/ angelehnt */
	function __construct() {
		
	}
	
	public static function manageRequest() {
		if(isset($_POST['info'])) {
			$info = $_POST['info'];
			
			if($info == 'register') {
				self::register();
			} else if($info == 'Einloggen') {
				self::login();
			} else if($info == 'logout') {
				$_SESSION = array();
				
				/* https://stackoverflow.com/questions/3512507/proper-way-to-logout-from-a-session-in-php*/
				if(ini_get("session.use_cookies")) {
					$params = session_get_cookie_params();
					setcookie(session_name(), '', time() - 42000,
						$params["path"], $params["domain"],
						$params["secure"], $params["httponly"]
					);
				}
				session_destroy();
			}
		}
	}
	
	private static function login() {
		if(!isset($_POST['loginData']) || !isset($_POST['password'])) {
			return false;
		}
		
		$loginData = $_POST['loginData'];
		$password = $_POST['password'];

		$user = DBAccess::selectQuery("SELECT * FROM `members` WHERE `email` = '$loginData' OR `username` = '$loginData'");
		if (empty($user)) return false;
		$user = $user[0];
		
		/* Überprüfung des Passworts */
		if($user !== false && password_verify($password, $user['password'])) {
			$_SESSION['userid'] = $user['id'];
			if($user['specialRole'] == 'admin') {
				$_SESSION['admin'] = $user['id'];
				//die('Login erfolgreich. Weiter zum <a href="https://max-website.tk/admin/">Admin-Bereich</a>');
			} else {
				//die('Login erfolgreich. Weiter zu <a href="geheim.php">internen Bereich</a>');
			}
			
			/*if() {
				echo "Sie müssen Ihre E-Mail Adresse noch bestätigen! Bestätigunsmail nochmal senden.";
			}*/
			
			$_SESSION['loggedIn'] = true;
		} else {
			echo "E-Mail / Benutzername oder Passwort war ungültig<br>";
			return false;
		}

		return true;
	}
	
	private static function register() {
		if(!isset($_POST['email']) || !isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['password2'])) {
			return false;
		}
		
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
			$user = DBAccess::selectQuery("SELECT * FROM members WHERE email = '$email'");
			
			if(!empty($user)) { //($user !== false) {
				echo 'Diese E-Mail-Adresse ist bereits vergeben<br>';
				$error = true;
			}
			
			$user = DBAccess::selectQuery("SELECT * FROM members WHERE username = '$username'");
			
			if(!empty($user)) { //$user !== false) {
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
			} else {
				echo 'Beim Abspeichern ist leider ein Fehler aufgetreten<br>';
			}
			
			self::generateMailKey($email);
		}
	}
	
	public static function generateMailKey($email) {
		$mailKey = md5(microtime().rand());
		
		while(DBAccess::selectQuery("SELECT id FROM members_validate_email WHERE mailKey = '$mailKey'") != null) {
			$mailKey = md5(microtime().rand());
		}
		
		$userId = intval(DBAccess::selectQuery("SELECT id FROM members WHERE email = '$email'"));
		DBAccess::insertQuery("INSERT INTO members_validate_email (memberId, email, mailKey) VALUES ($UserId, '$email', '$mailKey')");
		
		$mailLink = REWRITE_BASE . "?mailId=" . $mailKey;
		$mailText = '<a href="' . $mailLink . '">Hier</a> dem Link folgen!';
		Mailer::sendMail($email, "Bestätigen Sie Ihre E-Mail Adresse", $mailText, "no-reply@max-website.tk");
	}
	
	public static function registerEmail() {
		$mailKey = $_GET['mailId'];
		$memberId = DBAccess::selectQuery("SELECT memberId FROM members_validate_email WHERE mailKey = '$mailKey'");
		
		if($memberId != null) {
			$memberId = intval($memberId[0]['memberId']);
			DBAccess::updateQuery("UPDATE members SET isEmailValidated = 0 WHERE id = $memberId");
		}
	}
}

?>