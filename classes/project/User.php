<?php

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
        $query = "SELECT id FROM user WHERE email = :email";
        $params = array(':email' => $email);
        $user = DBAccess::selectQuery($query, $params);

        if (empty($user)) {
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

    public function getUserDeviceList() {

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

}
