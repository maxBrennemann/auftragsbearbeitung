<!DOCTYPE html>
<html>
<head>
	<title>Mein FTP Versuch</title>
	<!--<link rel="stylesheet" href="main_style.css">-->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body>
<?php

$directory = $_GET['dir'];
if($directory == NULL) {
	$directory = ".";
}

/* according to: http://php.net/manual/de/book.ftp.php#105868 */

class ftp {
	
    public $conn;

    public function __construct($url) {
        $this->conn = ftp_connect($url);
    } 
    
    public function __call($func, $a) {
        if(strstr($func, 'ftp_') !== false && function_exists($func)) {
            array_unshift($a, $this->conn); 
            return call_user_func_array($func, $a); 
        } else{ 
            die("Connection refused / error"); 
        }
    }
}

$ftp = new ftp('maxmietet.square7.ch'); 
$ftp->ftp_login('maxmietet_max', 'Aa123456789');

//var_dump($ftp->ftp_nlist("."));

$data = $ftp->ftp_nlist($directory);

echo 'Current Directory: ' . $ftp->ftp_pwd() . '<br><br>';

//echo createDirectoryPath();

foreach($data as $dat) {
	if($ftp->ftp_size($dat) == -1) {
		echo '<span class="dirLine"><i class="fa fa-folder" aria-hidden="true"></i>
				<a href="ftp.php?dir=' . $dat .'">' . $dat . '</a></span><br>';
	} else {
		echo '<span class="dirLine"><i class="fa fa-file" aria-hidden="true"></i>
				<a href="ftp.php?dir=' . $dat .'">' . $dat . '</a>
				<span class="rightSide">File size: ' . $ftp->ftp_size($dat) . ' Bytes</span></span><br>';
	}
}

$ftp->ftp_close();

function createDirectoryPath($dataArr) {
	$dirPath = "<span>";
	
	foreach($dataArr as $dat) {
		if($dat == '.') {
			$dirPath += "<a>Home</a>";
		} else {
			$dirPath += "<a>" . $dat . "</a>";
		}
	}
	
	$dirPath += "</span>";
	
	return $dirPath;
}

?>

<style>
	body {
		font-family: Segoe UI, sans-serif;
	}
	
	a {
		padding-left: 10px;
	}
	
	.dirLine {
		display: inline-block;
		width: 600px;
		height: 30px;
	}
	
	.dirLine:hover {
		background-color: lightcyan;
	}
	
	.rightSide {
		float: right;
	}
</style>
</body>
</html>