<?php

use Classes\Project\Produkt;

$product_id = isset($_GET["id"]) ? $_GET["id"] : 0;

if ($product_id != 0) {
    $product = new Produkt($product_id);

    $product_url = $_ENV["SHOPURL"]; //"https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    $product_price = $product->getPrice();
    $product_validUntil = "2022-12-01";
    $product_description = $product->getBeschreibung();
}

?>

<div class="product-container" itemscope itemtype="http://schema.org/Product">
    <aside>
        <h2><u><?=$title?></u></h2>
        <meta itemprop="name" content="<?=$title?>" />
        <meta itemprop="description" content="<?=$product_description?>" />
        <meta itemprop="sku" content="<?=$product->getProductId()?>" />
        <?php foreach ($product->getImages() as $i): ?>
        <link itemprop="image" href="http://localhost<?=$i->getImageURL()?>" />
        <?php endforeach; ?>
        <div>
            <img id="imagebig" src="<?=$product->getImages()[0]->getImageURL()?>">
        </div>
        <div class="product-images">
            <?php foreach ($product->getImages() as $i): ?>
            <div class="product-image-container" data-image-id="<?=$i->getImageId()?>">
                <img class="product-image" src="<?=$i->getImageURL()?>" alt="" height="auto">
            </div>
            <?php endforeach; ?>
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
    </aside>
    <aside>
        <p><?=$product->getBeschreibung()?></p>
        <span><?=number_format($product_price, 2, ",", ",")?> €</span>
        <button onclick="addToCart();">Zum Warenkorb hinzufügen</button>
    </aside>
</div>
<script>
    if (document.readyState !== 'loading' ) {
        console.log( 'document is already ready, just execute code here' );
        initCode();
    } else {
        document.addEventListener('DOMContentLoaded', function () {
            console.log( 'document was not ready, place code here' );
            initCode();
        });
    }

    var mainImageSrc = "";

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

    function initCode() {
        var images = document.getElementsByClassName("product-image");
        for (var i = 0; i < images.length; i++) {
            images[i].addEventListener("mouseover", function() {
                var toReplace = document.getElementById("imagebig");
                toReplace.src = this.src;
            }.bind(images[i]), false);
            images[i].addEventListener("mouseout", function(images) {
                var toReplace = document.getElementById("imagebig");
                toReplace.src = mainImageSrc;
            }, false);
        }

        mainImageSrc = document.getElementById("imagebig").src;
    }

</script>