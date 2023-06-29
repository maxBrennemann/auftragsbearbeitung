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
			self::login($user['id']);
			$device = self::getDeviceKey();
		} else {
			return false;
		}

		DBAccess::insertQuery("INSERT INTO login_history (user_id, user_login_key_id, loginstamp) VALUES (:id, :uloginkey, :loginstamp)", array(
			':id' => $user['id'],
			':uloginkey' => 0,
			'loginstamp' => (new DateTime())->format('Y-m-d H:i:s'),
		));

		return $device;
	}

	private static function login($userId) {
		$_SESSION['userid'] = $userId;
		$_SESSION['loggedIn'] = true;
	}

	/**
	 * handles the logout process
	 */
	public static function handleLogout() {
		$key = $_POST["loginKey"];
		//DBAccess::deleteQuery("DELETE FROM `user_login` WHERE `login_key` = :key;", array(':key' => $key));

		setcookie(session_name(), '', 100);
		session_unset();
		session_destroy();
		$_SESSION = array();
	}

	public static function getLoginKey($deviceId) {
		if (!isset($_POST["setAutoLogin"]) || $_POST["setAutoLogin"] == "false") {
			return;
		}

		/* get expiration date */
		$dateInTwoWeeks = new DateTime();
		$dateInTwoWeeks->modify("+2 week");
		$dateInTwoWeeks = $dateInTwoWeeks->format("Y-m-d");

		/* generate login key */
		$loginKey = bin2hex(random_bytes(6));

		/* save login key */
		DBAccess::insertQuery("INSERT INTO user_login_key (user_id, login_key, expiration_date, user_device_id) VALUES (:id, :loginkey, :expiration, :userDeviceId)", array(
			':id' => $_SESSION['userid'],
			':loginkey' => $loginKey,
			':expiration' => $dateInTwoWeeks,
			':userDeviceId' => $deviceId
		));

		return $loginKey;
	}

	/**
	 * simple function to generate a unique device identifier
	 */
	private static function getDeviceKey() {
		$userAgent = getParameter("userAgent", "POST", "unknown");
		if (isset($_POST["deviceKey"]) && strlen($_POST["deviceKey"]) == 32) {

			return [
				"deviceKey" => $_POST["deviceKey"],
				"deviceId" => self::getDeviceId($_POST["deviceKey"])
			];
		}

		$userAgentHash = self::generateDeviceKey($userAgent);

		$browser = $_POST["browser"];
		$os = $_POST["os"];
		$isMobile = $_POST["isMobile"];
		$isTablet = $_POST["isTablet"];
		$deviceType = self::castDevice($isMobile, $isTablet);

		$query = "SELECT * FROM user_devices WHERE browser_agent = :userAgent AND os = :os AND browser = :browser AND device_type = :deviceType AND user_id = :userId;";
		$data = DBAccess::selectQuery($query, array(
			':userAgent' => $userAgent,
			':os' => $os,
			':browser' => $browser,
			':deviceType' => $deviceType,
			':userId' => $_SESSION['userid']
		));

		$duplicateDevice = self::checkDuplicateDevices($data);
		if ($duplicateDevice != false) {
			$userAgentHash = $duplicateDevice['md_hash'];
		} else {
			$id = self::saveDeviceKey($userAgentHash, $userAgent, $browser, $os, $deviceType);
		}

		return [
			"deviceKey" => $userAgentHash,
			"deviceId" => $id
		];
	}

	private static function generateDeviceKey($userAgent) {
		$random_part = bin2hex(random_bytes(6));
		$userAgentHash = md5($userAgent . $random_part);
		return $userAgentHash;
	}

	private static function saveDeviceKey($key, $userAgent, $browser, $os, $deviceType): int {
		$query = "INSERT INTO user_devices (md_hash, os, browser, device_type, user_id, browser_agent, ip_address) VALUES (:key, :os, :browser, :deviceType, :userId, :browserAgent, :ipAddress);";
		$id = DBAccess::insertQuery($query, array(
			':key' => $key,
			':os' => $os,
			':browser' => $browser,
			':deviceType' => $deviceType,
			':userId' => $_SESSION['userid'],
			':browserAgent' => $userAgent,
			':ipAddress' => $_SERVER['REMOTE_ADDR'],
		));

		return $id;
	}

	private static function getDeviceId($deviceKey): int {
		$query = "SELECT id FROM user_devices WHERE md_hash = :deviceKey;";
		$data = DBAccess::selectQuery($query, array(
			':deviceKey' => $deviceKey
		));

		if (count($data) == 0) {
			return false;
		}

		return (int) $data[0]['id'];
	}

	private static function checkDuplicateDevices($list) {
		if (count($list) == 0) {
			return false;
		}

		$ip = $_SERVER['REMOTE_ADDR'];

		foreach ($list as $device) {
			if ($device['ip_address'] == $ip) {
				return $device;
			}
		}

		return false;
	}

	/**
	 * the autologin hash is stored in the database and the cookie,
	 * the cookie, the browser type and os are used to identify the user
	 * the current ip adress is also stored in the database but is not crucial
	 */
	public static function handleAutoLogin() {
		$userAgent = getParameter("userAgent", "POST", "unknown");
		$browser = $_POST["browser"];
		$os = $_POST["os"];
		$isMobile = $_POST["isMobile"];
		$isTablet = $_POST["isTablet"];
		$deviceType = self::castDevice($isMobile, $isTablet);

		$deviceId = self::getDeviceId($_POST["deviceKey"]);
		$data = DBAccess::selectQuery("SELECT browser_agent, browser, os, device_type FROM user_devices WHERE id = :id", [
			':id' => $deviceId
		]);

		if ($data[0]['browser_agent'] != $userAgent || $data[0]['browser'] != $browser || $data[0]['os'] != $os || $data[0]['device_type'] != $deviceType) {
			// device has changed
			// expire all login keys
			return false;
		} else {
			$loginKey = $_POST["loginKey"];
			$query = "SELECT * FROM user_login_key WHERE login_key = :loginKey AND user_device_id = :deviceId ORDER BY id ASC LIMIT 1;";
			$data = DBAccess::selectQuery($query, array(
				'loginKey' => $loginKey,
				'deviceId' => $deviceId,
			));

			if (count($data) == 0) {
				// login key is not valid
				return false;
			} else {
				self::login($data[0]['user_id']);
				return self::getLoginKey($deviceId);
			}
		}
	}

	private static function castDevice($isMobile, $isTablet) {
		if ($isMobile == "true") {
			return "mobile";
		}
		if ($isTablet == "true") {
			return "tablet";
		}
		return "desktop";
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
