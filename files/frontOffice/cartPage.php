<?php 

require_once('classes/front/ProductController.php');
$products = isset($_SESSION["cart_Products"]) ? unserialize($_SESSION["cart_Products"]) : [];

?>
<?php foreach ($products as $product): ?>
    <p><?=$product->getBezeichnung()?></p>
<?php endforeach; ?>