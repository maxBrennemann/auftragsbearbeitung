<?php

$_SERVER["DOCUMENT_ROOT"] = "../../";

require_once('settings.php');
require_once('vendor/autoload.php');
require_once('classes/DBAccess.php');

require_once("classes/MinifyFiles.php");
require_once("upgrade/UpgradeManager.php");

require_once("classes/project/modules/sticker/export/ExportFacebook.php");

$exportFacebook = new ExportFacebook();
$exportFacebook->generateCSV();
