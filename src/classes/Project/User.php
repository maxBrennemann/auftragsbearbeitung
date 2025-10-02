<?php

namespace Src\Classes\Project;

use Src\Classes\Mailer;
use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;

class User
{
    private int $id;
    private string $username;
    private string $email;
    private string $prename;
    private string $lastname;

    private int $maxWorkingHours;
    private int $role;

    public function __construct(int $userId)
    {
        $query = "SELECT * FROM user WHERE id = :userId LIMIT 1;";
        $user = DBAccess::selectQuery($query, [
            "userId" => $userId,
        ]);

        if (empty($user)) {
            return;
        }

        $this->id = (int) $user[0]['id'];
        $this->username = $user[0]['username'];
        $this->email = $user[0]['email'];
        $this->prename = $user[0]['prename'];
        $this->lastname = $user[0]['lastname'];
        $this->maxWorkingHours = (int) $user[0]['max_working_hours'];
        $this->role = (int) $user[0]['role'];
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPrename(): string
    {
        return $this->prename;
    }

    public function getLastname(): string
    {
        return $this->lastname;
    }

    public function getMaxWorkingHours(): int
    {
        return $this->maxWorkingHours;
    }

    public function getRole(): int
    {
        return $this->role;
    }

    /**
     * checks if email is available and sets it
     */
    public function setEmail(string $email): void
    {
        if (self::checkEmailAvailable($email)) {
            $query = "UPDATE user SET email = :email WHERE id = :userId";
            DBAccess::updateQuery($query, [
                "email" => $email,
                "userId" => $this->id
            ]);
            $this->email = $email;
        }
    }

    public function setPrename(string $prename): void
    {
        $query = "UPDATE user SET prename = :prename WHERE id = :userId";
        DBAccess::updateQuery($query, [
            "prename" => $prename,
            "userId" => $this->id
        ]);
    }

    public function setLastname(string $lastname): void
    {
        $query = "UPDATE user SET lastname = :lastname WHERE id = :userId";
        $params = array(':lastname' => $lastname, ':userId' => $this->id);
        DBAccess::updateQuery($query, $params);
    }

    public function setMaxWorkingHours(int $maxWorkingHours): void
    {
        $query = "UPDATE user SET max_working_hours = :maxWorkingHours WHERE id = :userId";
        $params = array(':maxWorkingHours' => $maxWorkingHours, ':userId' => $this->id);
        DBAccess::updateQuery($query, $params);
    }

    public function setRole(mixed $role): void {}

    /**
     * returns a list of devices the user has logged in with
     * @return array<int, mixed>
     */
    public function getUserDeviceList(): array
    {
        $query = "SELECT device_type, user_device_name, DATE_FORMAT(last_usage, '%d.%m.%Y %H:%i:%s') as lastUsage, ip_address, browser, os 
            FROM user_devices 
            WHERE user_id = :userId
            ORDER BY last_usage DESC";
        $data = DBAccess::selectQuery($query, [
            "userId" => $this->id,
        ]);

        return $data;
    }

    public function getDeviceIcon(string $type, string $os): string
    {
        $icon = "";
        switch ($type) {
            case "mobile":
                switch ($os) {
                    case "Android":
                        $icon = "iconAndroid";
                        break;
                    case "iOS":
                        $icon = "iconApplePhone";
                        break;
                    default:
                        $icon = "iconPhone";
                        break;
                }
                break;
            case "tablet":
                $icon = "iconTablet";
                break;
            case "desktop":
                switch ($os) {
                    case "Mac OS":
                        $icon = "iconMac";
                        break;
                    case "Linux":
                        $icon = "iconLinux";
                        break;
                    case "Windows":
                    default:
                        $icon = "iconWindows";
                        break;
                }
                break;
            default:
                $icon = "iconUnrecognized";
                break;
        }

        $icon = Icon::get($icon, 35, 35, ["inline"]);
        return $icon;
    }

    public function getHistory(): string
    {
        $query = "SELECT history.orderid, history.id, history.insertstamp, history_type.name , CONCAT(COALESCE(history.alternative_text, ''), COALESCE(ids.descr, '')) AS `description`, history.state, user.username, user.prename
            FROM history
            LEFT JOIN (
                (SELECT CONCAT(fahrzeuge.Kennzeichen, ' ', fahrzeuge.Fahrzeug) AS `descr`, fahrzeuge_auftraege.id_fahrzeug AS id, 3 AS `type` FROM fahrzeuge, fahrzeuge_auftraege WHERE fahrzeuge.Nummer = fahrzeuge_auftraege.id_fahrzeug)
                UNION
                (SELECT notes.note AS `descr`, notes.id AS id, 7 AS `type` FROM notes)
            ) ids ON history.number = ids.id 
            AND history.type = ids.type
            LEFT JOIN history_type ON history_type.type_id = history.type
            LEFT JOIN user ON user.id = history.member_id
            WHERE history.member_id = :userId";

        $data = DBAccess::selectQuery($query, [
            "userId" => $this->id,
        ]);
        $header = [
            "columns" => [
                "orderid",
                "id",
                "insertstamp",
                "name",
                "description",
                "state",
                "username",
                "prename",
            ],
            "names" => [
                "Auftragsnummer",
                "Verlaufsnummer",
                "Datum",
                "Art",
                "Beschreibung",
                "Stand",
                "Username",
                "Vorname",
            ],
            "primaryKey" => "id",
        ];

        $options = [
            "hideOptions" => ["all"],
        ];
        $options["styles"]["table"]["className"] = [
            "table-auto", "overflow-x-scroll", "w-full"
        ];

        return TableGenerator::create($data, $options, $header);
    }

    public static function getUserOverview(): string
    {
        $data = DBAccess::selectQuery("SELECT * FROM user ORDER BY id ASC;");

        require_once "src/table-config.php";
        $header = getTableConfig()["user"];
        $options = [
            "link" => "/mitarbeiter?id=",
            "primaryKey" => "id",
            "hideOptions" => ["all"],
        ];
        $options["styles"]["table"]["className"] = [
            "table-auto", "overflow-x-scroll", "w-full"
        ];

        return TableGenerator::create($data, $options, $header);
    }

    public static function checkEmailAvailable(string $email): bool
    {
        $query = "SELECT id FROM user WHERE email = :email";
        $params = array(':email' => $email);
        $user = DBAccess::selectQuery($query, $params);

        if (empty($user)) {
            return true;
        }

        return false;
    }

    public static function checkUsernameAvailable(string $username): bool
    {
        $query = "SELECT id FROM user WHERE username = :username";
        $params = array(':username' => $username);
        $user = DBAccess::selectQuery($query, $params);

        if (empty($user)) {
            return true;
        }

        return false;
    }

    /**
     * adds a new user to the database,
     * if an error occurs, it returns -1,
     * if the user was added successfully, it returns the id of the user
     */
    public static function add(string $username, string $email, string $prename, string $lastname, string $password): int
    {
        if (!self::checkUsernameAvailable($username) || !self::checkEmailAvailable($email)) {
            return -1;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return -1;
        }

        if (!self::isPasswordSafe($password)) {
            return -1;
        }

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        $insert = "INSERT INTO user (username, prename, lastname, email, password, max_working_hours, role) VALUES (:username, :prename, :lastname, :email, :password, 0, 0)";
        $params = array(
            'username' => $username,
            'prename' => $prename,
            'lastname' => $lastname,
            'email' => $email,
            'password' => $password_hash
        );
        $result = (int) DBAccess::insertQuery($insert, $params);
        self::sendEmailVerification($result, $email);

        return $result;
    }

    public static function ajaxAdd(): void
    {
        $username = Tools::get("username");
        $email = Tools::get("email");
        $prename = Tools::get("prename");
        $lastname = Tools::get("lastname");
        $password = Tools::get("password");

        if ($username == null || $email == null || $prename == null || $lastname == null || $password == null)
        {
            JSONResponseHandler::sendErrorResponse(400, "All fields are required");
        }

        $userId = self::add($username, $email, $prename, $lastname, $password);

        JSONResponseHandler::sendResponse([
            "userId" => $userId,
        ]);
    }

    private static function sendEmailVerification(int $userId, string $email): void
    {
        $mailKey = md5(microtime() . rand());

        while (self::mailKeyExists($mailKey)) {
            $mailKey = md5(microtime() . rand());
        }

        DBAccess::insertQuery("INSERT INTO user_validate_mail (user_id, mail_key) VALUES (:userId, :mailKey)", array(
            ':userId' => $userId,
            ':mailKey' => $mailKey
        ));

        $mailLink = $_ENV["REWRITE_BASE"] . "/verify?id?" . $mailKey;
        $mailText = '<a href="' . $mailLink . '">Hier</a> dem Link folgen!';

        try {
            Mailer::sendMail($email, "Bestätigen Sie Ihre E-Mail Adresse", $mailText, "no-reply@organisierung.b-schriftung.de");
        } catch (\Exception $e) {
        }
    }

    private static function mailKeyExists(string $mailKey): bool
    {
        $query = "SELECT id FROM user_validate_mail WHERE mail_key = :mailKey";
        $params = array(':mailKey' => $mailKey);
        $result = DBAccess::selectQuery($query, $params);

        if (empty($result)) {
            return false;
        }

        return true;
    }

    /**
     * checks if the password is safe enough
     */
    private static function isPasswordSafe(string $password): bool
    {
        if (strlen($password) < 8) {
            return false;
        }

        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return false;
        }

        return true;
    }

    public static function getCurrentUserId(): int
    {
        if (isset($_SESSION["user_id"])) {
            return (int) $_SESSION["user_id"];
        }

        return 0;
    }

    public static function isAdmin(): bool
    {
        $userId = self::getCurrentUserId();

        if ($userId === -1) {
            return false;
        }

        $query = "SELECT user.id FROM user_roles 
            JOIN user
                ON user.role = user_roles.id 
            WHERE user.id = :userId 
                AND user_roles.role_name = 'admin'";
        $data = DBAccess::selectQuery($query, [
            "userId" => $userId,
        ]);

        if (empty($data)) {
            return false;
        }

        return true;
    }
}
