<?php

require_once('settings.php');

class DBAccess {

	protected static $connection;
	protected static $statement;
	
	function __construct() {
		
	}
	
	private static function createConnection() {
		if (self::$connection != null)
			return;

		try {
			$host = $_ENV["HOST"];
            $database = $_ENV["DATABASE"];
            $username = $_ENV["USERNAME"];
            $password = $_ENV["PASSWORD"];

			self::$connection = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
			self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
			echo "Error connecting to the database.";
    		error_log($e->getMessage());
		}
	}
	
	public static function selectQuery($query, $params = NULL) {
		self::createConnection();
		self::$statement = self::$connection->prepare($query);
		self::$statement->execute($params);
		$result = self::$statement->fetchAll(PDO::FETCH_ASSOC);
		
		return $result;
	}

	public static function selectAll($table) {
		return self::selectQuery("SELECT * FROM $table");
	}

	public static function selectAllByCondition($table, $condName, $condParam) {
		return self::selectQuery("SELECT * FROM $table WHERE $condName = $condParam");
	}

	public static function selectColumnNames($table) {
		$query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$table}' AND TABLE_SCHEMA = '" . $_ENV["DATABASE"] . "'";
		if ($query == null)
			return null;
		return self::selectQuery($query);
	}
	
	public static function updateQuery($query, $params = NULL) {
		self::createConnection();
		self::$statement = self::$connection->prepare($query);
		return self::$statement->execute($params);
	}

	/* exec for queries that don't return a result set */
	public static function updateQueryNonPrepared($query) {
		self::createConnection();
		
		return self::$connection->exec($query);
	}

	public static function deleteQuery($query, $params = NULL) {
		self::createConnection();
		self::$statement = self::$connection->prepare($query);
		self::$statement->execute($params);
	}
	
	public static function insertQuery($query, $params = NULL) {
		self::createConnection();
		self::$statement = self::$connection->prepare($query);
		self::$statement->execute($params);
		return self::$connection->lastInsertId();
	}

	/**
	 * https://stackoverflow.com/questions/1176352/pdo-prepared-inserts-multiple-rows-in-single-query
	 * Switched to prepared statements to avoid SQL injection and escape errors
	 * 
	 * @param string $queryPart has to look like "INSER INTO tbl (col1, ...) VALUES 
	 * @param array $data
	 * 
	 * @return int
	 */
	public static function insertMultiple($queryPart, $data) {
		$values = str_repeat('?,', count($data[0]) - 1) . '?';
		$sql = $queryPart .
			str_repeat("($values),", count($data) - 1) . "($values)";

		self::createConnection();
		self::$statement = self::$connection->prepare($sql);
		self::$statement->execute(array_merge(...$data));
		return self::$connection->lastInsertId();
	}

	public static function executeQuery($query) {
		self::createConnection();
		
		self::$statement = self::$connection->prepare($query);
		self::$statement->execute();
		
		self::$statement->execute();
	}

	/* exec for queries, that don't return result sets, otherwise the general mySQL error 2014 can occur */
	public static function execQuery($query) {
		self::createConnection();
		
		self::$statement = self::$connection->prepare($query);
		self::$statement->exec();
	}

	/**
	 * returns the number of affected rows by the last INSERT, UPDATE, DELETE query
	 * @return int
	 */
	public static function getAffectedRows(): int {
		return self::$statement->rowCount();
	}

	/* 
	##### EXAMPLE #####
	EXPORT_DATABASE("localhost","user","pass","db_name" ); 
	
	##### Notes #####
		* (optional) 5th parameter: to backup specific tables only,like: array("mytable1","mytable2",...)   
		* (optional) 6th parameter: backup filename (otherwise, it creates random name)
		* IMPORTANT NOTE ! Many people replaces strings in SQL file, which is not recommended. READ THIS:  http://puvox.software/tools/wordpress-migrator
		* If you need, you can check "import.php" too
	*/

	// by https://github.com/ttodua/useful-php-scripts //
	public static function EXPORT_DATABASE($host,$user,$pass,$name,       $tables=false, $backup_name=false) { 
		set_time_limit(3000); $mysqli = new mysqli($host,$user,$pass,$name); $mysqli->select_db($name); $mysqli->query("SET NAMES 'utf8'");
		$queryTables = $mysqli->query('SHOW TABLES'); while($row = $queryTables->fetch_row()) { $target_tables[] = $row[0]; }	if($tables !== false) { $target_tables = array_intersect( $target_tables, $tables); } 
		$content = "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\r\nSET time_zone = \"+00:00\";\r\n\r\n\r\n/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;\r\n/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;\r\n/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;\r\n/*!40101 SET NAMES utf8 */;\r\n--\r\n-- Database: `".$name."`\r\n--\r\n\r\n\r\n";
		foreach($target_tables as $table){
			if (empty($table)){ continue; } 
			$result	= $mysqli->query('SELECT * FROM `'.$table.'`');  	$fields_amount=$result->field_count;  $rows_num=$mysqli->affected_rows; 	$res = $mysqli->query('SHOW CREATE TABLE '.$table);	$TableMLine=$res->fetch_row(); 
			$content .= "\n\n".$TableMLine[1].";\n\n";   $TableMLine[1]=str_ireplace('CREATE TABLE `','CREATE TABLE IF NOT EXISTS `',$TableMLine[1]);
			for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0) {
				while($row = $result->fetch_row())	{ //when started (and every after 100 command cycle):
					if ($st_counter%100 == 0 || $st_counter == 0 )	{$content .= "\nINSERT INTO ".$table." VALUES";}
						$content .= "\n(";    for($j=0; $j<$fields_amount; $j++){ $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) ); if (isset($row[$j])){$content .= '"'.$row[$j].'"' ;}  else{$content .= '""';}	   if ($j<($fields_amount-1)){$content.= ',';}   }        $content .=")";
					//every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
					if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num) {$content .= ";";} else {$content .= ",";}	$st_counter=$st_counter+1;
				}
			} $content .="\n\n\n";
		}
		$content .= "\r\n\r\n/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;\r\n/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;\r\n/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;";
		$backup_name = $backup_name ? $backup_name : $name.'___('.date('H-i-s').'_'.date('d-m-Y').').sql';
		ob_get_clean(); header('Content-Type: application/octet-stream');  header("Content-Transfer-Encoding: Binary");  header('Content-Length: '. (function_exists('mb_strlen') ? mb_strlen($content, '8bit'): strlen($content)) );    header("Content-disposition: attachment; filename=\"".$backup_name."\""); 
		return $content;
	}

	private static function closeStatement() {
		self::$statement = null;
	}
	
	private static function closeConnection() {
		self::$connection = null;
	}

	public static function close() {
		self::closeStatement();
		self::closeConnection();
	}

}
