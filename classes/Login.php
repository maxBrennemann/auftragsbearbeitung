<?php

require_once('Mailer.php');

class Login {

	public static function handleLogout() {
		$key = $_POST["loginkey"];
		//DBAccess::deleteQuery("DELETE FROM `user_login` WHERE `loginkey` = :key;", array(':key' => $key));

		setcookie(session_name(), '', 100);
		session_unset();
		session_destroy();
		$_SESSION = array();
	}
	
	public static function manageRequest() {
		if (isset($_POST['info'])) {
			$info = $_POST['info'];
			
			if ($info == 'register') {
				self::register();
			} else if ($info == 'Einloggen') {
				self::login();
			} else if ($info == 'logout') {
				$_SESSION = array();
				
				/* https://stackoverflow.com/questions/3512507/proper-way-to-logout-from-a-session-in-php*/
				if (ini_get("session.use_cookies")) {
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
		if (!isset($_POST['loginData']) || !isset($_POST['password'])) {
			return false;
		}
		
		$loginData = $_POST['loginData'];
		$password = $_POST['password'];

		$user = DBAccess::selectQuery("SELECT * FROM user WHERE `email` = :email OR `username` = :username LIMIT 1;", array(':email' => $loginData, ':username' => $loginData));

		if (empty($user))  {
			return false;
		}

		$user = $user[0];
		
		/* Überprüfung des Passworts */
		if ($user !== false && password_verify($password, $user['password'])) {
			$_SESSION['userid'] = $user['id'];

			if ($user['specialRole'] == 'admin') {
				$_SESSION['admin'] = $user['id'];
			}
			
			$_SESSION['loggedIn'] = true;
			self::handleAutoLogin();
		} else {
			echo "E-Mail / Benutzername oder Passwort war ungültig<br>";
			return false;
		}

		DBAccess::insertQuery("INSERT INTO login_history (user_id, user_login_key) VALUES (:id, :uloginkey)",
			array(
				':id' => $user['id'],
				':uloginkey' => 0,
			));

		return true;
	}

	public static function handleAutoLogin() {
		if (isset($_POST["setAutoLogin"])) {
			
			$status = $_POST["setAutoLogin"];
			// store autologin hash and set expire date, set autologin checkbox to true as default value
			// TODO: my devices
			
			$jsData = $_POST["browserInfo"];

			$ip = $_SERVER['REMOTE_ADDR'];
			$browser = $_SERVER['HTTP_USER_AGENT'];
			$dateInTwoWeeks = new DateTime();
			$dateInTwoWeeks->modify("+2 week");
			$dateInTwoWeeks = $dateInTwoWeeks->format("Y-m-d");
			$user_id = $_SESSION['userid'];
			$random_part = bin2hex(random_bytes(6));
			$hash = md5($jsData . $random_part);
			/* browser agent stays empty for now */

			//$query = "INSERT INTO user_login (`user_id`, md_hash, expiration_date, device_name, ip_adress, loginkey) VALUES (:user_id, :hash, :date, :device_name, :ip_adress, :loginkey)";

			echo json_encode([$jsData, $random_part]);
			/*DBAccess::insertQuery($query, [
				':user_id' => $user_id,
				':hash' => $hash,
				':date' => $dateInTwoWeeks,
				':device_name' => $browser,
				':ip_adress' => $ip,
				':loginkey' => $random_part
			]);*/
		}
	}
	
	private static function register() {
		if (!isset($_POST['email']) || !isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['password2'])) {
			return false;
		}
		
		$error = false;
		$email = $_POST['email'];
		$prename = $_POST['prename'];
		$lastname = $_POST['lastname'];
		$username = $_POST['username'];
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
		
		/* Überprüfe, dass die E-Mail-Adresse noch nicht registriert wurde */
		if (!$error) { 
			$user = DBAccess::selectQuery("SELECT id FROM user WHERE email = :email", array(':email' => $email));
			
			if (!empty($user)) {
				echo 'Diese E-Mail-Adresse ist bereits vergeben<br>';
				$error = true;
			}
			
			$user = DBAccess::selectQuery("SELECT id FROM user WHERE username = :username", array(':username' => $username));
			
			if (!empty($user)) {
				echo 'Dieser Benutzername ist bereits vergeben<br>';
				$error = true;
			}
		}
		
		/* Keine Fehler, wir können den Nutzer registrieren */
		if (!$error) {    
			$password_hash = password_hash($password, PASSWORD_DEFAULT);
			$insert = "INSERT INTO user (username, prename, lastname, email, password, max_working_hours, role) VALUES (:username, :prename, :lastname, :email, :password, 0, 0)";
			$params = array(
				'username' => $username,
				'prename' => $prename,
				'lastname' => $lastname,
				'email' => $email,
				'password' => $password_hash
			);
			$result = DBAccess::insertQuery($insert, $params);
			
			if ($result) {        
				echo 'Du wurdest erfolgreich registriert. <a href="login.php">Zum Login</a>';
			} else {
				echo 'Beim Abspeichern ist leider ein Fehler aufgetreten<br>';
			}
			
			self::generateMailKey($email);
		}
	}
	
	/**
	 * members_validate_email doesn't exist yet
	 */
	public static function generateMailKey($email) {
		$mailKey = md5(microtime().rand());
		
		while(DBAccess::selectQuery("SELECT id FROM members_validate_email WHERE mailKey = '$mailKey'") != null) {
			$mailKey = md5(microtime().rand());
		}
		
		$userId = intval(DBAccess::selectQuery("SELECT id FROM members WHERE email = '$email'"));
		DBAccess::insertQuery("INSERT INTO members_validate_email (memberId, email, mailKey) VALUES ($userId, '$email', '$mailKey')");
		
		$mailLink = REWRITE_BASE . "?mailId=" . $mailKey;
		$mailText = '<a href="' . $mailLink . '">Hier</a> dem Link folgen!';
		Mailer::sendMail($email, "Bestätigen Sie Ihre E-Mail Adresse", $mailText, "no-reply@max-website.tk");
	}
	
	/**
	 * members_validate_email doesn't exist
	 */
	public static function registerEmail() {
		$mailKey = $_GET['mailId'];
		$memberId = DBAccess::selectQuery("SELECT memberId FROM members_validate_email WHERE mailKey = '$mailKey'");
		
		if($memberId != null) {
			$memberId = intval($memberId[0]['memberId']);
			DBAccess::updateQuery("UPDATE members SET isEmailValidated = 0 WHERE id = $memberId");
		}
	}

	public static function getUserId() {
		if (isset($_SESSION['userid'])) {
			$user = $_SESSION['userid'];
			return $user;
		}
		return -1;
	}

	/* 
	 * https://stackoverflow.com/questions/1082302/file-get-contents-from-url-that-is-only-accessible-after-log-in-to-website
	 * https://stackoverflow.com/questions/3008817/login-to-remote-site-with-php-curl
	 */
	public static function curlLogin($page) {
		$loginUrl = 'http://' . $_SERVER['HTTP_HOST'] . Link::getPageLink("login"); //action from the login form
		$username = CURL_USERNAME;
		$password = CURL_PASSWORD;
		$loginFields = "info=Einloggen&loginData=" . $username. "&password=" . $password;
		$remotePageUrl = 'http://' . $_SERVER['HTTP_HOST'] . Link::getPageLink($page); //url of the page you want to save  

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_NOBODY, false);
		curl_setopt($ch, CURLOPT_URL, $loginUrl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

		curl_setopt($ch, CURLOPT_COOKIEJAR, "cookies/cookies.txt");
		//set the cookie the site has for certain features, this is optional
		curl_setopt($ch, CURLOPT_COOKIE, "cookiename=0");
		curl_setopt($ch, CURLOPT_USERAGENT,
			"Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_REFERER, $_SERVER['REQUEST_URI']);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $loginFields);
		curl_exec($ch);

		//page with the content I want to grab
		curl_setopt($ch, CURLOPT_URL, $remotePageUrl);
		//do stuff with the info with DomDocument() etc
		$html = curl_exec($ch);
		curl_close($ch);

		return $html;
	}

}

?>