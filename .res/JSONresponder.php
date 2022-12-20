<?php

error_reporting(E_ALL);

require('../config/config.inc.php');
require_once("DBAccess.php");

$data = json_decode(file_get_contents('php://input'), true);

if (isset($_GET["upload"])) {
	$lastImg = count($_FILES["image"]);
	
	var_dump($_FILES["image"]);
	
	$originalname = basename($_FILES["image"]["name"]);
	$filename = $_GET["id"] . ".svg";
      
	if (move_uploaded_file($_FILES["image"]["tmp_name"], '../mainconf/img/svgFiles/' . $filename)) {
		echo "upload successfull";
	} else {
		echo "there was an error with $filename, originally: $originalname";
	}
	
	return;
}

/* form when image sent:
 * data[
 * 		"images" => ["image_url_encoded", "image_url_encoded"],
 * 		"id" => ["product_id"]
 * 	];
 */

/* https://www.prestashop.com/forums/topic/407476-how-to-add-image-during-programmatic-product-import/ */
if (isset($data["id"])) {
	$isCover = true;
    $imageIds = [];
	foreach ($data["images"] as $imageURL) {
		$productId = (int) $data["id"][0];
		$image = new Image();
		$image->id_product = $productId;
		$image->position = Image::getHighestPosition($productId) + 1;
		$image->cover = $isCover;
		if (($image->validateFields(false, true)) === true && ($image->validateFieldsLang(false, true)) === true && $image->add()) {
			if (!AdminImportController::copyImg($productId, $image->id, urldecode($imageURL), 'products', false)) {
				$image->delete();
			} else {
                array_push($imageIds, ["id" => $image->id, "url" => $imageUrl]);
            }
		}
		$isCover = false;
	}

    echo json_encode($imageIds);
} else if (isset($data["setQuantity"])) {
	ini_set('display_errors', '1');
	ini_set('display_startup_errors', '1');
	error_reporting(E_ALL);
	
	$id_product_attribute = (int) $data["id_product_attribute"];
	$id_product = (int) $data["id_product"];
	$quantity = (int) $data["quantity"];
	
	//StockAvailable::setQuantity($id_product, $id_product_attribute, $quantity);
	//return;
	
	
	$query = new DbQuery();
    $query->select('id_stock_available');
    $query->from('stock_available');
    $query->where('id_product = '.(int)$id_product);

    if ($id_product_attribute !== null) {
        $query->where('id_product_attribute = '.(int)$id_product_attribute);
    }
	$id_stock_available = (int)Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);

    if ($id_stock_available) {
		$stock_available = new StockAvailable($id_stock_available);
		$stock_available->quantity = (int)$quantity;
		$stock_available->update();
	}
	
	echo $id_stock_available;
	
	Cache::clean('StockAvailable::getQuantityAvailableByProduct_'.(int)$id_product.'*');
} else if(isset($data["uploadSVG"])) {
	echo $data;
} else if (isset($data["setDefault"])) {
	$id_product_attribute = (int) $data["id_product_attribute"][0];
	$id_product = (int) $data["id_product"][0];
	
	$product = new Product($id_product, true);
	$product->deleteDefaultAttributes();
	//$product->setDefaultAttribute($id_product_attribute);
	Product::updateDefaultAttribute($id_product);
	return;
	
	//var_dump($data); return;
	
	/* später vielleicht schöner machen */
	$query = "UPDATE prstshp_product_shop SET cache_default_attribute = $id_product_attribute WHERE id_product = $id_product";
	DBAccess::updateQuery($query);
	$query = "UPDATE prstshp_product SET cache_default_attribute = $id_product_attribute WHERE id_product = $id_product";
	DBAccess::updateQuery($query);
	$query = "UPDATE prstshp_product_attribute_shop SET default_on = 1 WHERE id_product_attribute = $id_product_attribute";
	DBAccess::updateQuery($query);
	$query = "UPDATE prstshp_product_attribute SET default_on = 1 WHERE id_product_attribute = $id_product_attribute";
	DBAccess::updateQuery($query);
} else if (isset($data["update"])) {
	$query = $data["query"];
	DBAccess::updateQuery($query);
} else {
	$query = $data["query"];
	echo json_encode(DBAccess::selectQuery($query));
}

?>