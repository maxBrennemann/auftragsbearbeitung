<?php

require_once('settings.php');

class DBAccess {
	private static $connection;
	private static $statement;
	
	function __construct() {
		
	}
	
	private static function createConnection() {
		try {
			self::$connection = new PDO("mysql:host=" . HOST . ";dbname=" . DATABASE . ";charset=utf8", USERNAME, PASSWORD);
			self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
			die("Error connecting to database:<br>" . $e);
		}
	}
	
	public static function selectQuery($query) {
		self::createConnection();
		
		self::$statement = self::$connection->prepare($query);
		self::$statement->execute();
		$result = self::$statement->fetchAll(PDO::FETCH_ASSOC);
		
		return $result;
	}
	
	public static function updateQuery($query) {
		self::createConnection();
		
		self::$statement = self::$connection->prepare($query);
		self::$statement->execute();
		
		return self::$statement->execute();
	}
	
	public static function insertQuery($query, $params = NULL) {
		self::createConnection();
		
		self::$statement = self::$connection->prepare($query);

		if($params != NULL) {
			foreach($params as $key => &$val){
				$dataType = getType($val);
				switch($dataType) {
					case "integer":
						self::$statement->bindParam($key, $val, PDO::PARAM_INT);
						break;
					case "string":
						self::$statement->bindParam($key, $val, PDO::PARAM_STR);
						break;
				}
			}
		}
		
		return self::$statement->execute();
	}
}

?>