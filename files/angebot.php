<?php
	require_once('classes/Link.php');
	require_once('classes/project/FormGenerator.php');
	require_once('classes/project/Angebot.php');
	
	//$a = new Angebot();
	//$a->pdfGenerieren();

	if (isset($_GET['open'])) :
?>

<h2>Offene Angebote</h2>

<?php else : ?>
<div class="defCont">
	<a href="<?=Link::getPageLink("angebot")?>?open">Offene Angebote durchsehen</a>
	<p>Kundennummer: <input type="number" id="kdnr"><button onclick="neuesAngebot()">Bestätigen</button><br>Oder <a href="<?=Link::getPageLink("neuer-kunde")?>">hier</a> einen neuen Kunden anlegen.</p>
</div>
<div id="insTemp"></div>
<?php endif; ?>
<style>

	.adress {
		padding-right: 15px;
	}

	.fleft {
		float: left;
	}

	.fright {
		float: right;
	}

	.inlineC {
		display: inline-block;
	}

</style>