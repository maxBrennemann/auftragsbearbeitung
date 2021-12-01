<?php

require_once('classes/project/Produkt.php');

$product_id = isset($_GET["id"]) ? $_GET["id"] : 0;

if ($product_id != 0) {
    $product = new Produkt($product_id);

    $product_url = "https://klebefux.de"; //"https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $product_price = $product->getPreis();
    $product_validUntil = "2022-12-01";
    $product_description = $product->getBeschreibung();
}

?>

<div itemscope itemtype="http://schema.org/Product">
    <h2><?=$title?></h2>
    <meta itemprop="name" content="<?=$title?>" />
    <meta itemprop="description" content="<?=$product_description?>" />
    <meta itemprop="sku" content="<?=$product->getProductId()?>" />
    <?php foreach ($product->getImages() as $i): ?>
    <link itemprop="image" href="http://localhost<?=$i->getImageURL()?>" />
    <?php endforeach; ?>
    <div class="image-container">
        [Images]
    </div>
    <div class="config-container">
    </div>
    <div itemprop="offers" itemtype="https://schema.org/Offer" itemscope>
        <link itemprop="url" href="<?=$product_url?>" />
        <meta itemprop="availability" content="https://schema.org/InStock" />
        <meta itemprop="priceCurrency" content="EUR" />
        <meta itemprop="itemCondition" content="https://schema.org/NewCondition" />
        <meta itemprop="price" content="<?=$product_price?>" />
        <meta itemprop="priceValidUntil" content="<?=$product_validUntil?>" />
    </div>
    <div itemprop="brand" itemtype="https://schema.org/Brand" itemscope>
        <meta itemprop="name" content="klebefux" />
    </div>
    <div itemprop="reviewRating" itemtype="https://schema.org/Rating" itemscope>
    </div>
    <button onclick="addToCart();">Add to Cart</button>
</div>
<script>
    function addToCart() {
        var ajaxCall = new XMLHttpRequest();
		ajaxCall.onreadystatechange = function() {
			if (this.readyState == 4 && this.status == 200) {
				console.log(this.responseText);
			}
		}
		ajaxCall.open("POST", window.location.href, true);
		ajaxCall.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		ajaxCall.send("getReason=frontAddToCart&productId=<?=$product_id?>");
    }
</script>