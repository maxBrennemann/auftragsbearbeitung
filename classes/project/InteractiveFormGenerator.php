<?php

require_once('classes/DBAccess.php');
error_reporting(E_ALL);

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * Klasse generiert Tabellen für Formulare, erbt von FormGenerator
 *
 * @access public
 * @author Max Brennemann, maxgoogelt@gmail.com
 */
class InteractiveFormGenerator extends FormGenerator {
	
	private $type;
	private $isOrderedBy;
	private $whereCondition;
	private $tableData;
	private $dataTypes = null;

	function __construct($type, $isOrderedBy, $whereCondition) {
		super($type, $isOrderedBy, $whereCondition);
	}

}

?>