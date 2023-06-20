<?php

require_once('settings.php');

class DBAccess {

	protected static $connection;
	protected static $statement;

	private static $host = HOST;
    private static $database = DATABASE;
    private static $username = USERNAME;
    private static $password = PASSWORD;
	
	function __construct() {
		
	}
	
	private static function createConnection() {
		try {
			$host = self::$host;
            $database = self::$database;
            $username = self::$username;
            $password = self::$password;

			self::$connection = new PDO("mysql:host=$host;dbname=$database;charset=utf8", $username, $password);
			self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
			die("Error connecting to database:<br>" . $e);
		}
	}
	
	public static function selectQuery($query, $params = NULL) {
		self::createConnection();
		
		self::$statement = self::$connection->prepare($query);
		
		self::bindParams($params);
		self::$statement->execute();
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
		$query = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME = '{$table}' AND TABLE_SCHEMA = '" . DATABASE . "'";
		if ($query == null)
			return null;
		return self::selectQuery($query);
	}
	
	public static function updateQuery($query, $params = NULL) {
		self::createConnection();

		self::$statement = self::$connection->prepare($query);

		self::bindParams($params);
		return self::$statement->execute();
	}

	/* exec for queries that don't return a result set */
	public static function updateQueryNonPrepared($query) {
		self::createConnection();
		
		return self::$connection->exec($query);
	}

	public static function deleteQuery($query, $params = NULL) {
		self::createConnection();
		
		self::$statement = self::$connection->prepare($query);

		self::bindParams($params);
		self::$statement->execute();
	}
	
	public static function insertQuery($query, $params = NULL) {
		self::createConnection();
		
		self::$statement = self::$connection->prepare($query);

		self::bindParams($params);
		self::$statement->execute();
		return self::$connection->lastInsertId();
	}

	public static function insertMultiple($query, $data) {
		foreach ($data as $rows) {
			$query_row = "(";
			foreach ($rows as $identifier => $item) {
				switch ($identifier) {
					case "int":
					case "integer":
						$query_row .= (int) $item . ", ";
						break;
					case "null":
						$query_row .= "NULL, ";
						break;
					case "string":
					default:
						$query_row .= "'" . $item . "', ";
						break;
				}
			}
			$query_row = substr($query_row, 0, -2);
			$query_row .= "),";
			$query .= $query_row;
		}

		$query = substr(($query), 0, -1);

		self::createConnection();
		self::$statement = self::$connection->prepare($query);
		self::$statement->execute();
		return self::$connection->lastInsertId();
	}

	private static function bindParams(&$params) {
		if ($params != NULL) {
			foreach($params as $key => &$val){
				$dataType = getType($val);
				switch($dataType) {
					case "integer":
						self::$statement->bindParam($key, $val, PDO::PARAM_INT);
						break;
					case "string":
						self::$statement->bindParam($key, $val, PDO::PARAM_STR);
						break;
					case "NULL":
						self::$statement->bindParam($key, $val, PDO::PARAM_NULL);
						break;
				}
			}
		}
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

}
