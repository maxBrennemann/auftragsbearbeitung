<?php

$_SERVER["DOCUMENT_ROOT"] = "../../";

require_once('settings.php');
require_once('vendor/autoload.php');

use Classes\Project\Modules\Sticker\Exports\ExportFacebook;

$exportFacebook = new ExportFacebook();
$exportFacebook->generateCSV();
