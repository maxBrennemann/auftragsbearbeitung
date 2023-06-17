<?php

require_once('Mailer.php');

class Login {

	/**
	 * handles the login process
	 */
	public static function handleLogin() {
		if (!isset($_POST['name']) || !isset($_POST['password'])) {
			return false;
		}
		
		$loginCredentials = $_POST['name'];
		$password = $_POST['password'];

		/* check if user exists */
		$user = DBAccess::selectQuery("SELECT * FROM user WHERE `email` = :email OR `username` = :username LIMIT 1;", array(
			':email' => $loginCredentials, 
			':username' => $loginCredentials
		));

		if (empty($user))  {
			return false;
		}

		$user = $user[0];
		
		/* check password */
		if ($user !== false && password_verify($password, $user['password'])) {
			$_SESSION['userid'] = $user['id'];
			$_SESSION['loggedIn'] = true;

			$deviceId = self::generateDeviceKey(getParameter("userAgent", "unknown"));
		} else {
			return false;
		}

		DBAccess::insertQuery("INSERT INTO login_history (user_id, user_login_key_id) VALUES (:id, :uloginkey)",
			array(
				':id' => $user['id'],
				':uloginkey' => 0,
			));

		return $deviceId;
	}

	/**
	 * handles the logout process
	 */
	public static function handleLogout() {
		$key = $_POST["loginkey"];
		//DBAccess::deleteQuery("DELETE FROM `user_login` WHERE `loginkey` = :key;", array(':key' => $key));

		setcookie(session_name(), '', 100);
		session_unset();
		session_destroy();
		$_SESSION = array();
	}

	public static function getLoginKey($deviceKey) {
		if (!isset($_POST["setAutoLogin"]) || $_POST["setAutoLogin"] == false) {
			return;
		}

		/* get expiration date */
		$dateInTwoWeeks = new DateTime();
		$dateInTwoWeeks->modify("+2 week");
		$dateInTwoWeeks = $dateInTwoWeeks->format("Y-m-d");
	}

	/**
	 * simple function to generate a unique device identifier
	 */
	private static function generateDeviceKey($userAgent) {
		if (isset($_POST["deviceKey"])) {
			return $_POST["deviceKey"];
		}

		$random_part = bin2hex(random_bytes(6));
		$userAgentHash = md5($userAgent . $random_part);

		$browser = $_POST["browser"];
		$os = $_POST["os"];
		$isMobile = $_POST["isMobile"];
		$isTablet = $_POST["isTablet"];

		$query = "SELECT * FROM user_devices WHERE md_hash = :userAgentHash AND os = :os AND browser = :browser AND device_type = :deviceType AND user_id = :userId;";
		$data = DBAccess::selectQuery($query, array(
			':userAgentHash' => $userAgentHash,
			':os' => $os,
			':browser' => $browser,
			':deviceType' => $isMobile ? "mobile" : ($isTablet ? "tablet" : "desktop"),
			':userId' => $_SESSION['userid']
		));

		$duplicateDevice = self::checkDuplicateDevices($data);
		if ($duplicateDevice != false) {
			$userAgentHash = $duplicateDevice['md_hash'];
		} else {
			$id = self::saveDeviceKey($userAgentHash, $userAgent, $browser, $os, $isMobile, $isTablet);
		}

		return $userAgentHash;
	}

	private function saveDeviceKey($key, $userAgent, $browser, $os, $isMobile, $isTablet) {
		$query = "INSERT INTO user_devices (md_hash, os, browser, device_type, user_id) VALUES (:key, :os, :browser, :deviceType, :userId);";
		$id = DBAccess::insertQuery($query, array(
			':key' => $key,
			':os' => $os,
			':browser' => $browser,
			':deviceType' => $isMobile ? "mobile" : ($isTablet ? "tablet" : "desktop"),
			':userId' => $_SESSION['userid']
		));

		return $id;
	}

	private function checkDuplicateDevices($list) {
		if (count($list) == 0) {
			return false;
		}

		$ip = $_SERVER['REMOTE_ADDR'];

		foreach ($list as $device) {
			if ($device['ip_adress'] == $ip) {
				return $device;
			}
		}

		return false;
	}

	public function test() {
		$userAgent = $_POST["userAgent"];
		$loginKey = $_POST["loginkey"];

		$hash = md5($userAgent . $loginKey);
		$query = "SELECT * FROM user_devices ud LEFT JOIN user_login_key ul ON ud.id = ul.user_device_id WHERE md_hash = '$hash' AND expiration_date > CURDATE() LIMIT 1";
		$data = DBAccess::selectQuery($query);

		if ($data != null) {
			$user = $data[0]["user_id"];
			$_SESSION['userid'] = $user;
	
			/* special role check must be added later for autologin */
			$_SESSION['loggedIn'] = true;
			Login::handleAutoLogin();
			echo "success";
		} else {
			echo "failed";
		}
	}

	/**
	 * the autologin hash is stored in the database and the cookie,
	 * the cookie, the browser type and os are used to identify the user
	 * the current ip adress is also stored in the database but is not crucial
	 */
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

			$query = "INSERT INTO user_login (`user_id`, md_hash, expiration_date, device_name, ip_adress, loginkey) VALUES (:user_id, :hash, :date, :device_name, :ip_adress, :loginkey)";

			echo json_encode([$jsData, $random_part]);
			DBAccess::insertQuery($query, [
				':user_id' => $user_id,
				':hash' => $hash,
				':date' => $dateInTwoWeeks,
				':device_name' => $browser,
				':ip_adress' => $ip,
				':loginkey' => $random_part
			]);
		}
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
		curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.7.12) Gecko/20050915 Firefox/1.0.7");
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
