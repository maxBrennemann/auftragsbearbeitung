<?php

namespace Src\Classes;

use Src\Classes\Controller\SessionController;
use Src\Classes\Project\User;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class Login
{
    private static int $loginKeyId = 0;

    /**
     * manual login via button, writes login into login protocol,
     * responds with json
     *
     * @return void
     */
    public static function handleLogin(): void
    {
        $user = self::getUser();
        $device = self::validateUser($user);
        $loginKey = "";

        $loginKey = self::getLoginKey($device["deviceId"]);
        JSONResponseHandler::sendResponse([
            "status" => "success",
            "deviceKey" => $device["deviceKey"],
            "loginKey" => $loginKey,
        ]);

        DBAccess::insertQuery("INSERT INTO login_history (`user_id`, `user_login_key_id`, `loginstamp`) VALUES (:id, :uloginkey, :loginstamp)", [
            "id" => $user["id"],
            "uloginkey" => self::$loginKeyId,
            "loginstamp" => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private static function getUser(): array
    {
        if (Tools::get("name") == null || Tools::get("password") == null) {
            JSONResponseHandler::throwError(401, [
                "status" => "error"
            ]);
        }

        $loginCredentials = Tools::get("name");

        /* check if user exists */
        $user = DBAccess::selectQuery("SELECT * FROM user WHERE `email` = :email OR `username` = :username LIMIT 1;", [
            "email" => $loginCredentials,
            "username" => $loginCredentials
        ]);

        if (empty($user)) {
            JSONResponseHandler::throwError(401, [
                "status" => "error"
            ]);
        }

        return $user[0];
    }

    /**
     * @param array<string, string> $user
     * @return array{deviceId: int, deviceKey: mixed}
     */
    private static function validateUser(array $user): array
    {
        $password = Tools::get("password");

        /* check password */
        if (password_verify($password, $user["password"])) {
            SessionController::login((int) $user["id"]);
            return self::getDeviceKey();
        }

        JSONResponseHandler::throwError(401, [
            "status" => "error"
        ]);
    }

    public static function handleLogout(): void
    {
        SessionController::logout();
    }

    private static function getLoginKey(int $deviceId): false|string
    {
        $autoLogin = Tools::get("setAutoLogin");
        if ($autoLogin == null || !$autoLogin) {
            return false;
        }

        /* get expiration date */
        $dateInTwoWeeks = new \DateTime();
        $dateInTwoWeeks->modify("+2 week");
        $dateInTwoWeeks = $dateInTwoWeeks->format("Y-m-d");

        /* generate login key */
        $loginKey = bin2hex(random_bytes(6));

        /* save login key */
        $id = DBAccess::insertQuery("INSERT INTO user_login_key (`user_id`, `login_key`, `expiration_date`, `user_device_id`) VALUES (:id, :loginkey, :expiration, :userDeviceId)", [
            "id" => User::getCurrentUserId(),
            "loginkey" => $loginKey,
            "expiration" => $dateInTwoWeeks,
            "userDeviceId" => $deviceId
        ]);
        self::$loginKeyId = $id;

        return $loginKey;
    }

    /**
     * @return array{deviceId: int, deviceKey: mixed}
     */
    private static function getDeviceKey(): array
    {
        $userAgent = Tools::get("userAgent");
        if ($userAgent == null) {
            $userAgent = "unknown";
        }

        $deviceKey = Tools::get("deviceKey");
        if ($deviceKey !== null && strlen($deviceKey) == 32) {
            $deviceId = self::getDeviceId($deviceKey);
            /* if deviceId is not null, return it */
            if ($deviceId !== 0) {
                return [
                    "deviceKey" => $deviceKey,
                    "deviceId" => $deviceId,
                ];
            }
        }

        $userAgentHash = self::generateDeviceKey($userAgent);

        $browser = Tools::get("browser");
        $os = Tools::get("os");
        $isMobile = Tools::get("isMobile") == "true";
        $isTablet = Tools::get("isTablet") == "true";
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
        if ($duplicateDevice !== false) {
            $userAgentHash = $duplicateDevice["md_hash"];
        }

        $id = self::saveDeviceKey($userAgentHash, $userAgent, $browser, $os, $deviceType);

        return [
            "deviceKey" => $userAgentHash,
            "deviceId" => $id
        ];
    }

    private static function generateDeviceKey(string $userAgent): string
    {
        $random_part = bin2hex(random_bytes(6));
        $userAgentHash = md5($userAgent . $random_part);
        return $userAgentHash;
    }

    private static function saveDeviceKey(string $key, string $userAgent, string $browser, string $os, string $deviceType): int
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

    /**
     * This function only gets used by manual login,
     * so if the user agent changes, this can be updated to the database
     * @param string $deviceKey
     * @return int
     */
    private static function getDeviceId(string $deviceKey): int
    {
        $query = "SELECT id, browser_agent FROM user_devices WHERE md_hash = :deviceKey;";
        $data = DBAccess::selectQuery($query, [
            "deviceKey" => $deviceKey
        ]);

        if (count($data) == 0) {
            return 0;
        }

        $userAgent = Tools::get("userAgent");
        $currentAgent = $data[0]["browser_agent"];

        $id = (int) $data[0]["id"];

        if ($userAgent != $currentAgent) {
            DBAccess::updateQuery("UPDATE user_devices SET browser_agent = :userAgent WHERE id = :id;", [
                "userAgent" => $userAgent,
                "id" => $id,
            ]);
        }

        return $id;
    }

    /**
     * @param array<int, mixed> $list
     * @return false|array<string, mixed>
     */
    private static function checkDuplicateDevices(array $list): false|array
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

    public static function autloginWrapper(): void
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
    private static function handleAutoLogin(): false|string
    {
        $userAgent = Tools::get("userAgent");
        if ($userAgent == null) {
            $userAgent = "unknown";
        }

        $browser = Tools::get("browser");
        $os = Tools::get("os");
        $isMobile = Tools::get("isMobile") == "true";
        $isTablet = Tools::get("isTablet") == "true";
        $deviceType = self::castDevice($isMobile, $isTablet);

        $data = DBAccess::selectQuery("SELECT id, browser_agent, browser, os, device_type FROM user_devices WHERE md_hash = :deviceKey", [
            "deviceKey" => Tools::get("deviceKey"),
        ]);

        if (count($data) == 0) {
            return false;
        }

        $deviceId = $data[0]["id"];

        if (
            $data[0]['browser_agent'] == $userAgent
            && $data[0]['browser'] == $browser
            && $data[0]['os'] == $os
            && $data[0]['device_type'] == $deviceType
        ) {
            $loginKey = Tools::get("loginKey");
            $query = "SELECT * 
				FROM user_login_key 
				WHERE login_key = :loginKey 
					AND user_device_id = :deviceId 
				ORDER BY id DESC
				LIMIT 1;";
            $data = DBAccess::selectQuery($query, [
                "loginKey" => $loginKey,
                "deviceId" => $deviceId,
            ]);

            if (count($data) == 0) {
                return false;
            } else {
                SessionController::login((int) $data[0]["user_id"]);
                return self::getLoginKey((int) $deviceId);
            }
        } else {
            return false;
        }
    }

    private static function castDevice(bool $isMobile, bool $isTablet): string
    {
        if ($isMobile) {
            return "mobile";
        }
        if ($isTablet) {
            return "tablet";
        }
        return "desktop";
    }
}
