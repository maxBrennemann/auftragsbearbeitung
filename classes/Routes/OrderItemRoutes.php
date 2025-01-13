<?php

namespace Classes\Routes;

use MaxBrennemann\PhpUtilities\Routes;

class OrderItemRoutes extends Routes
{

    /**
     *
     */
    protected static $getRoutes = [
        "/order-items/{id}/table" => []
    ];

    /**
     * @uses Classes\Project\Auftrag::getItemsOverview()
     */
    protected static $postRoutes = [
        "/order-items/{id}/overview" => [],
    ];

}


/*
case "reloadPostenListe":
				$auftragsId = $_POST['id'];
				$auftrag = new Auftrag($auftragsId);

				$data = [
					0 => $auftrag->getAuftragspostenAsTable(),
					1 => $auftrag->getInvoicePostenTable()
				];

				echo json_encode($data);
				break;
                */