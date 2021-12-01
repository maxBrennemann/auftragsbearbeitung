<?php 

require_once('classes/front/ProductController.php');

if (isset($_POST["delete"]) && isset($_SESSION["cart_Products"])) {
    $_SESSION["cart_Products"] = serialize([]);
}

$products = isset($_SESSION["cart_Products"]) ? unserialize($_SESSION["cart_Products"]) : [];

?>
<?php foreach ($products as $product): ?>
    <p><?=$product->getBezeichnung()?></p>
    <p><?=$product->getQuantity()?></p>
<?php endforeach; ?>
<button>Zur Kasse</button>
<form action="" method="post">
    <button type="submit" name="delete">Warenkorb leeren</button>
</form>