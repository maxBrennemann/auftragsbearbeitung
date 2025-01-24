<?php

namespace Classes;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;

use Classes\Project\User;

class Login
{

	/**
	 * handles the login process
	 */
	public static function handleLogin()
	{
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

		if (empty($user)) {
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
			'loginstamp' => (new \DateTime())->format('Y-m-d H:i:s'),
		));

		if (!$device) {
			echo json_encode(["status" => "error"]);
		} else {
			echo json_encode([
				"status" => "success",
				"deviceKey" => $device["deviceKey"],
				"loginKey" => Login::getLoginKey($device["deviceId"]),
			]);
		}
	}

	private static function login($userId)
	{
		$_SESSION['user_id'] = $userId;
		$_SESSION['loggedIn'] = true;
	}

	/**
	 * handles the logout process
	 */
	public static function handleLogout()
	{
		setcookie(session_name(), '', 100);
		session_unset();
		session_destroy();
		$_SESSION = [];
	}

	public static function getLoginKey($deviceId)
	{
		if (!isset($_POST["setAutoLogin"]) || $_POST["setAutoLogin"] == "false") {
			return;
		}

		/* get expiration date */
		$dateInTwoWeeks = new \DateTime();
		$dateInTwoWeeks->modify("+2 week");
		$dateInTwoWeeks = $dateInTwoWeeks->format("Y-m-d");

		/* generate login key */
		$loginKey = bin2hex(random_bytes(6));

		/* save login key */
		DBAccess::insertQuery("INSERT INTO user_login_key (user_id, login_key, expiration_date, user_device_id) VALUES (:id, :loginkey, :expiration, :userDeviceId)", array(
			':id' => User::getCurrentUserId(),
			':loginkey' => $loginKey,
			':expiration' => $dateInTwoWeeks,
			':userDeviceId' => $deviceId
		));

		return $loginKey;
	}

	/**
	 * simple function to generate a unique device identifier
	 */
	private static function getDeviceKey()
	{
		$userAgent = Tools::get("userAgent");
		if ($userAgent == null) {
			$userAgent = "unknown";
		}

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
			':userId' => User::getCurrentUserId(),
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

	private static function generateDeviceKey($userAgent)
	{
		$random_part = bin2hex(random_bytes(6));
		$userAgentHash = md5($userAgent . $random_part);
		return $userAgentHash;
	}

	private static function saveDeviceKey($key, $userAgent, $browser, $os, $deviceType): int
	{
		$query = "INSERT INTO user_devices (md_hash, os, browser, device_type, user_id, browser_agent, ip_address) VALUES (:key, :os, :browser, :deviceType, :userId, :browserAgent, :ipAddress);";
		$id = DBAccess::insertQuery($query, array(
			':key' => $key,
			':os' => $os,
			':browser' => $browser,
			':deviceType' => $deviceType,
			':userId' => User::getCurrentUserId(),
			':browserAgent' => $userAgent,
			':ipAddress' => $_SERVER['REMOTE_ADDR'],
		));

		return $id;
	}

	private static function getDeviceId($deviceKey): int
	{
		$query = "SELECT id FROM user_devices WHERE md_hash = :deviceKey;";
		$data = DBAccess::selectQuery($query, array(
			':deviceKey' => $deviceKey
		));

		if (count($data) == 0) {
			return false;
		}

		return (int) $data[0]['id'];
	}

	private static function checkDuplicateDevices($list)
	{
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

	public static function autloginWrapper()
	{
		$loginKey = self::handleAutoLogin();
		$status = $loginKey === false ? "failed" : "success";
		echo json_encode([
			"status" => $status,
			"loginKey" => $loginKey,
		]);
	}

	/**
	 * the autologin hash is stored in the database and the cookie,
	 * the cookie, the browser type and os are used to identify the user
	 * the current ip adress is also stored in the database but is not crucial
	 */
	public static function handleAutoLogin()
	{
		$userAgent = Tools::get("userAgent");
		if ($userAgent == null) {
			$userAgent = "unknown";
		}

		$browser = Tools::get("browser");
		$os = Tools::get("os");
		$isMobile = Tools::get("isMobile");
		$isTablet = Tools::get("isTablet");
		$deviceType = self::castDevice($isMobile, $isTablet);

		$data = DBAccess::selectQuery("SELECT id, browser_agent, browser, os, device_type FROM user_devices WHERE md_hash = :deviceKey", [
			'deviceKey' => Tools::get("deviceKey")
		]);

		if (count($data) == 0) {
			return false;
		}

		$deviceId = $data[0]['id'];

		if ($data[0]['browser_agent'] == $userAgent && $data[0]['browser'] == $browser && $data[0]['os'] == $os && $data[0]['device_type'] == $deviceType) {
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

	private static function castDevice($isMobile, $isTablet)
	{
		if ($isMobile == "true") {
			return "mobile";
		}
		if ($isTablet == "true") {
			return "tablet";
		}
		return "desktop";
	}

	public static function getUserId()
	{
		if (isset($_SESSION['user_id'])) {
			$user = $_SESSION['user_id'];
			return $user;
		}
		return -1;
	}
}
