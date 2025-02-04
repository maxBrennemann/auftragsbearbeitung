<?php

namespace Classes;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\Tools;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;

use Classes\Project\User;

class Login
{

	public static function handleLogin(): void
	{
		if (Tools::get("name") == null || Tools::get("password") == null) {
			JSONResponseHandler::sendResponse([
				"status" => "error"
			]);
		}

		$loginCredentials = Tools::get("name");
		$password = Tools::get("password");

		/* check if user exists */
		$user = DBAccess::selectQuery("SELECT * FROM user WHERE `email` = :email OR `username` = :username LIMIT 1;", [
			"email" => $loginCredentials,
			"username" => $loginCredentials
		]);

		if (empty($user)) {
			JSONResponseHandler::sendResponse([
				"status" => "error"
			]);
		}

		$user = $user[0];

		/* check password */
		if (password_verify($password, $user["password"])) {
			self::login($user["id"]);
			$device = self::getDeviceKey();
		} else {
			JSONResponseHandler::sendResponse([
				"status" => "error"
			]);
		}

		DBAccess::insertQuery("INSERT INTO login_history (`user_id`, `user_login_key_id`, `loginstamp`) VALUES (:id, :uloginkey, :loginstamp)", [
			"id" => $user["id"],
			"uloginkey" => 0,
			"loginstamp" => (new \DateTime())->format('Y-m-d H:i:s'),
		]);

		if (!$device) {
			JSONResponseHandler::sendResponse([
				"status" => "error"
			]);
		} else {
			JSONResponseHandler::sendResponse([
				"status" => "success",
				"deviceKey" => $device["deviceKey"],
				"loginKey" => self::getLoginKey($device["deviceId"]),
			]);
		}
	}

	private static function login($userId): void
	{
		$_SESSION["user_id"] = $userId;
		$_SESSION["loggedIn"] = true;
	}

	public static function handleLogout(): void
	{
		setcookie(session_name(), '', 100);
		session_unset();
		if (!session_destroy()) {
			/* reset session object */
			$_SESSION = [];
		}
	}

	private static function getLoginKey($deviceId): string
	{
		if (
			!Tools::get("setAutoLogin") == null
			|| Tools::get("setAutoLogin") == "false"
		) {
			return "";
		}

		/* get expiration date */
		$dateInTwoWeeks = new \DateTime();
		$dateInTwoWeeks->modify("+2 week");
		$dateInTwoWeeks = $dateInTwoWeeks->format("Y-m-d");

		/* generate login key */
		$loginKey = bin2hex(random_bytes(6));

		/* save login key */
		DBAccess::insertQuery("INSERT INTO user_login_key (`user_id`, `login_key`, `expiration_date`, `user_device_id`) VALUES (:id, :loginkey, :expiration, :userDeviceId)", [
			"id" => User::getCurrentUserId(),
			"loginkey" => $loginKey,
			"expiration" => $dateInTwoWeeks,
			"userDeviceId" => $deviceId
		]);

		return $loginKey;
	}

	private static function getDeviceKey()
	{
		$userAgent = Tools::get("userAgent");
		if ($userAgent == null) {
			$userAgent = "unknown";
		}

		if (
			Tools::get("deviceKey") !== null
			&& strlen(Tools::get("deviceKey")) == 32
		) {
			return [
				"deviceKey" => Tools::get("deviceKey"),
				"deviceId" => self::getDeviceId(Tools::get("deviceKey"))
			];
		}

		$userAgentHash = self::generateDeviceKey($userAgent);

		$browser = Tools::get("browser");
		$os = Tools::get("os");
		$isMobile = Tools::get("isMobile");
		$isTablet = Tools::get("isTablet");
		$deviceType = self::castDevice($isMobile, $isTablet);

		$query = "SELECT * 
			FROM user_devices 
			WHERE `browser_agent` = :userAgent 
				AND `os` = :os 
				AND `browser` = :browser 
				AND `device_type` = :deviceType 
				AND `user_id` = :userId;";
		$data = DBAccess::selectQuery($query, [
			"userAgent" => $userAgent,
			"os" => $os,
			"browser" => $browser,
			"deviceType" => $deviceType,
			"userId" => User::getCurrentUserId(),
		]);

		$duplicateDevice = self::checkDuplicateDevices($data);
		if ($duplicateDevice != false) {
			$userAgentHash = $duplicateDevice["md_hash"];
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
		$id = DBAccess::insertQuery($query, [
			"key" => $key,
			"os" => $os,
			"browser" => $browser,
			"deviceType" => $deviceType,
			"userId" => User::getCurrentUserId(),
			"browserAgent" => $userAgent,
			"ipAddress" => $_SERVER["REMOTE_ADDR"],
		]);

		return $id;
	}

	private static function getDeviceId($deviceKey): int
	{
		$query = "SELECT id FROM user_devices WHERE md_hash = :deviceKey;";
		$data = DBAccess::selectQuery($query, [
			"deviceKey" => $deviceKey
		]);

		if (count($data) == 0) {
			return 0;
		}

		return (int) $data[0]["id"];
	}

	private static function checkDuplicateDevices($list): string
	{
		if (count($list) == 0) {
			return false;
		}

		$ip = $_SERVER["REMOTE_ADDR"];

		foreach ($list as $device) {
			if ($device["ip_address"] == $ip) {
				return $device;
			}
		}

		return false;
	}

	public static function autloginWrapper()
	{
		$loginKey = self::handleAutoLogin();
		$status = $loginKey === false ? "failed" : "success";
		JSONResponseHandler::sendResponse([
			"status" => $status,
			"loginKey" => $loginKey,
		]);
	}

	/**
	 * the autologin hash is stored in the database and the cookie,
	 * the cookie, the browser type and os are used to identify the user
	 * the current ip adress is also stored in the database but is not crucial
	 */
	private static function handleAutoLogin(): bool|string
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
			"deviceKey" => Tools::get("deviceKey"),
		]);

		if (count($data) == 0) {
			return false;
		}

		$deviceId = $data[0]['id'];

		if (
			$data[0]['browser_agent'] == $userAgent
			&& $data[0]['browser'] == $browser
			&& $data[0]['os'] == $os
			&& $data[0]['device_type'] == $deviceType
		) {
			return false;
		} else {
			$loginKey = $_POST["loginKey"];
			$query = "SELECT * 
				FROM user_login_key 
				WHERE login_key = :loginKey 
					AND user_device_id = :deviceId 
				ORDER BY id ASC LIMIT 1;";
			$data = DBAccess::selectQuery($query, array(
				'loginKey' => $loginKey,
				'deviceId' => $deviceId,
			));

			if (count($data) == 0) {
				return false;
			} else {
				self::login($data[0]["user_id"]);
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
}
