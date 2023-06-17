<?php
require_once('classes/DBAccess.php');
require_once('classes/Link.php');
require_once('classes/Login.php');
require_once('classes/Articles.php');

$globalCSS =  Link::getGlobalCSS();
$globalJS =  Link::getGlobalJS();
$curr_Link = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$adminLink = Link::getAdminLink();

if (isset($_POST['info'])) {
	Login::handleLogin();
}

if (isset($_POST['article'])) {
	if($_POST['article'] == "add") {
		Articles::addArticle();
	}
}

$pageName = 'Admin';
if (isset($_GET['page']) && $_GET['page'] != null) {
	$pageName = $_GET['page'];
}

?>
<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8">
	<title>max-website.tk - <?=$pageName?></title>
	<meta name="Description" content="Admin">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="icon" href="https://max-website.tk/favicon.ico">
	<link rel="shortcut icon" type="image/png" href="https://max-website.tk/img/favicon.png">
	<link rel="stylesheet" href="<?=$globalCSS?>">
	<script src="<?=$globalJS?>"></script>
</head>
<body>
	<?php
		session_start();
		if (!isset($_SESSION['userid']) && !isset($_SESSION['admin'])) {
			die("Melde dich bitte an!");
		}
		
		$userId = $_SESSION['userid'];
		$user = DBAccess::selectQuery("SELECT * FROM user WHERE id = :id", array('id' => $userId));
		$user = $user[0];
	?>

	<div itemscope="itemscope" itemtype="http://www.schema.org/SiteNavigationElement" id="hammen">
		<div id="bar">
			<div class="navEl" onclick="toggleHamList();"></div>
			<div class="navEl" onclick="toggleHamList();"></div>
			<div class="navEl" onclick="toggleHamList();"></div>
		</div>
		<nav id="hamlist" style="display: none">
		<ul>
		<?php 
			$headers = DBAccess::selectQuery("SELECT * FROM header");

			foreach($headers as $header) {
				echo '<li><a itemprop="url" href="' . Link::getPageLink($header['src']) . '"><span itemprop="name">' . $header['name'] . '</span></a></li>';
			}
		?>
		</ul>
		</nav>
	</div>

	<h1 style="text-align: center; font-family: 'Libre Baskerville'"><?=$pageName?></h1>
	<span><?php echo "Hallo User: " . $user['username']; ?></span><br>
	<div class="navigationMenu" itemscope="itemscope" itemtype="http://www.schema.org/SiteNavigationElement">
		<nav>
		<ul>
			<?php 
				foreach($headers as $header) {
					echo '<li><a itemprop="url" href="' . Link::getPageLink($header['src']) . '"><span itemprop="name">' . $header['name'] . '</span></a></li>';
				}
			?>
		</ul>
		</nav>
	</div>
	<br>
	<div>
		<button class="btnDefault">Artikel hinzufügen</button>
		<button class="btnDefault">Artikel verwalten</button>
		<button class="btnDefault">Dateneintrag ändern</button>
	</div>
	<div>
		<form action method="post" id="articleUpload" enctype="multipart/form-data">
			<input type="hidden" name="article" value="add" />
			<p>Artikel</p>
			<input type="text" placeholder="Seitenname" name="pageName">
			<input type="text" placeholder="Angezeigte Url" name="src">
			<input type="file" name="srcfile">
			<input type="submit" value="Bestätigen">
		</form>
		<button id="addFiles">Weitere Dateien hinzufügen</button>
		<script>
			document.getElementById("addFiles").addEventListener("click", function() {
				var el = document.getElementById("articleUpload");
				var input = document.createElement("input");
				input.setAttribute("type", "file");
				input.setAttribute("name", "attachedFiles");
				input.setAttribute("multiple", "");
				el.appendChild(input);
			}, false);
		</script>
	</div>
	<br>
	<?php include('files/footer.php'); ?>