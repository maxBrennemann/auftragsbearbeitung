<?php

require_once('classes/Mailer.php');

class User {

    private $id;
    private $username;
    private $email;
    private $prename;
    private $lastname;

    private $maxWorkingHours;
    private $role;

    function __construct($userId) {
        $query = "SELECT * FROM user WHERE id = :userId LIMIT 1;";
        $params = array(':userId' => $userId);
        $user = DBAccess::selectQuery($query, $params);

        if (empty($user)) {
            return false;
        }

        $this->id = $user[0]['id'];
        $this->username = $user[0]['username'];
        $this->email = $user[0]['email'];
        $this->prename = $user[0]['prename'];
        $this->lastname = $user[0]['lastname'];
        $this->maxWorkingHours = $user[0]['max_working_hours'];
        $this->role = $user[0]['role'];
    }

    public function getId() {
        return $this->id;
    }

    public function getUsername() {
        return $this->username;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getPrename() {
        return $this->prename;
    }

    public function getLastname() {
        return $this->lastname;
    }

    public function getMaxWorkingHours() {
        return $this->maxWorkingHours;
    }

    public function getRole() {
        return $this->role;
    }

    /**
     * checks if email is available and sets it
     */
    public function setEmail($email) {
        if (self::checkEmailAvailable($email)) {
            $query = "UPDATE user SET email = :email WHERE id = :userId";
            $params = array(':email' => $email, ':userId' => $this->id);
            DBAccess::updateQuery($query, $params);
            $this->email = $email;
        }
    }

    public function setPrename($prename) {
        $query = "UPDATE user SET prename = :prename WHERE id = :userId";
        $params = array(':prename' => $prename, ':userId' => $this->id);
        DBAccess::updateQuery($query, $params);
    }

    public function setLastname($lastname) {
        $query = "UPDATE user SET lastname = :lastname WHERE id = :userId";
        $params = array(':lastname' => $lastname, ':userId' => $this->id);
        DBAccess::updateQuery($query, $params);
    }

    public function setMaxWorkingHours($maxWorkingHours) {
        $query = "UPDATE user SET max_working_hours = :maxWorkingHours WHERE id = :userId";
        $params = array(':maxWorkingHours' => $maxWorkingHours, ':userId' => $this->id);
        DBAccess::updateQuery($query, $params);
    }

    public function setRole($role) {
        
    }

    /**
     * returns a list of devices the user has logged in with
     */
    public function getUserDeviceList() {
        $query = "SELECT device_type, user_device_name, last_usage, ip_address, browser, os FROM user_devices WHERE user_id = :userId";
        $data = DBAccess::selectQuery($query, [
            "userId" => $this->id,
        ]);

        return $data;
    }

    public function getDeviceIcon($type, $os) {
        $icon = "";
        switch ($type) {
            case "mobile":
                switch ($os) {
                    case "Android":
                        $icon = Icon::getDefault("iconAndroid");
                        break;
                    case "iOS":
                        $icon = Icon::getDefault("iconApplePhone");
                        break;
                    default:
                        $icon = Icon::getDefault("iconPhone");
                        break;
                }
                break;
            case "tablet":
                $icon = Icon::getDefault("iconTablet");
                break;
            case "desktop":
                switch ($os) {
                    case "Mac OS":
                        $icon = Icon::getDefault("iconMac");
                        break;
                    case "Linux":
                        $icon = Icon::getDefault("iconLinux");
                        break;
                    case "Windows":
                    default:
                        $icon = Icon::getDefault("iconWindows");
                        break;
                }
                break;
            default:
                $icon = Icon::getDefault("iconUnrecognized");
                break;
        }

        return $icon;
    }

    public function getHistory() {
        $query = "SELECT history.orderid, history.id, history.insertstamp, history_type.name , CONCAT(COALESCE(history.alternative_text, ''), COALESCE(ids.descr, '')) AS Beschreibung, history.state, user.username, user.prename
            FROM history
            LEFT JOIN (
                (SELECT CONCAT(fahrzeuge.Kennzeichen, ' ', fahrzeuge.Fahrzeug) AS `descr`, fahrzeuge_auftraege.id_fahrzeug AS id, 3 AS `type` FROM fahrzeuge, fahrzeuge_auftraege WHERE fahrzeuge.Nummer = fahrzeuge_auftraege.id_fahrzeug)
                UNION
                (SELECT notizen.Notiz AS `descr`, notizen.Nummer AS id, 7 AS `type` FROM notizen)
            ) ids ON history.number = ids.id 
            AND history.type = ids.type
            LEFT JOIN history_type ON history_type.type_id = history.type
            LEFT JOIN user ON user.id = history.member_id
            WHERE history.member_id = :userId";

        $data = DBAccess::selectQuery($query, [
            "userId" => $this->id,
        ]);
        $column_names = [
            0 => [
                "COLUMN_NAME" => "orderid",
                "ALT" => "Auftragsnummer",
            ],
            1 => [
                "COLUMN_NAME" => "id",
                "ALT" => "Verlaufsnummer",
            ],
            2 => [
                "COLUMN_NAME" => "insertstamp",
                "ALT" => "Datum",
            ],
            3 => [
                "COLUMN_NAME" => "name",
                "ALT" => "Art",
            ],
            4 => [
                "COLUMN_NAME" => "Beschreibung"
            ],
            5 => [
                "COLUMN_NAME" => "state",
                "ALT" => "Stand",
            ],
            6 => [
                "COLUMN_NAME" => "username",
                "ALT" => "Benutzername",
            ],
            7 => [
                "COLUMN_NAME" => "prename",
                "ALT" => "Vorname",
            ],
        ];

        $t = new Table();
		$t->createByData($data, $column_names);
		return $t->getTable();
    }

    public static function getUserOverview() {
        $column_names = [
            0 => [
                "COLUMN_NAME" => "id",
                "ALT" => "Nummer",
            ],
            1 => [
                "COLUMN_NAME" => "username",
                "ALT" => "Benutzername",
            ],
            2 => [
                "COLUMN_NAME" => "email",
                "ALT" => "E-Mail",
            ],
            3 => [
                "COLUMN_NAME" => "prename",
                "ALT" => "Vorname",
            ],
            4 => [
                "COLUMN_NAME" => "lastname",
                "ALT" => "Nachname",
            ],
            5 => [
                "COLUMN_NAME" => "max_working_hours",
                "ALT" => "Max. Arbeitsstunden",
            ],
            6 => [
                "COLUMN_NAME" => "role",
                "ALT" => "Rolle",
            ],
        ];
        $data = DBAccess::selectQuery("SELECT * FROM user ORDER BY id ASC;");
		
		$t = new Table();
		$t->createByData($data, $column_names);
        $link = new Link();
		$link->addBaseLink("mitarbeiter");
		$link->setIterator("id", $data, "id");
		$t->addLink($link);
		return $t->getTable();
    }

    public static function checkEmailAvailable($email) {
        $query = "SELECT id FROM user WHERE email = :email";
        $params = array(':email' => $email);
        $user = DBAccess::selectQuery($query, $params);

        if (empty($user)) {
            return true;
        }

        return false;
    }

    public static function checkUsernameAvailable($username) {
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
    public static function add($username, $email, $prename, $lastname, $password) {
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
        $result = DBAccess::insertQuery($insert, $params);
        self::sendEmailVerification($result, $email);

        return $result;
    }

    private static function sendEmailVerification($userId, $email) {
        $mailKey = md5(microtime().rand());

        while (self::mailKeyExists($mailKey)) {
            $mailKey = md5(microtime().rand());
        }

		DBAccess::insertQuery("INSERT INTO user_validate_mail (user_id, mail_key) VALUES (:userId, :mailKey)", array(
            ':userId' => $userId,
            ':mailKey' => $mailKey
        ));
		
		$mailLink = $_ENV["REWRITE_BASE"] . "/verify?id?" . $mailKey;
		$mailText = '<a href="' . $mailLink . '">Hier</a> dem Link folgen!';

        try {
            Mailer::sendMail($email, "Bestätigen Sie Ihre E-Mail Adresse", $mailText, "no-reply@organisierung.b-schriftung.de");
        } catch (Exception $e) {
           
        }
    }

    private static function mailKeyExists($mailKey) {
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
    private static function isPasswordSafe($password) {
        if (strlen($password) < 8) {
            return false;
        }

        if (!preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            return false;
        }

        return true;
    }

    public static function getCurrentUserId() {
        if (isset($_SESSION['user_id'])) {
            return $_SESSION['user_id'];
        }

        return -1;
    }

}
