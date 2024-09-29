<?php

$_SERVER["DOCUMENT_ROOT"] = "../../";

require_once('settings.php');
require_once('vendor/autoload.php');

use Classes\Project\Modules\Sticker\Imports\ImportGoogleSearchConsole;

ImportGoogleSearchConsole::import();
