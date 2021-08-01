<?php

$column_names = array(0 => array("COLUMN_NAME" => "Email"), 1 => array("COLUMN_NAME" => "Benutzername"));
$data = DBAccess::selectQuery("SELECT email AS Email, username AS Benutzername FROM members");

$t = new Table();
$t->createByData($data, $column_names);
$members_table = $t->getTable();


$column_names = array(0 => array("COLUMN_NAME" => "Vorname"), 1 => array("COLUMN_NAME" => "Nachname"), 2 => array("COLUMN_NAME" => "Email"));
$data = DBAccess::selectQuery("SELECT Email, Vorname, Nachname FROM mitarbeiter");

$t = new Table();
$t->createByData($data, $column_names);
$mitarbeiter_table = $t->getTable();

echo $members_table;
echo "<br><br>";
echo $mitarbeiter_table;

?>
