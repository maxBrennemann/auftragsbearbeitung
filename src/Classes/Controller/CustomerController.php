<?php

namespace Src\Classes\Controller;

use MaxBrennemann\PhpUtilities\DBAccess;
use MaxBrennemann\PhpUtilities\JSONResponseHandler;
use MaxBrennemann\PhpUtilities\Tools;
use Src\Classes\Project\CustomerPresenter;
use Src\Classes\Project\Kunde as Customer;
use Src\Classes\Project\SearchController;

class CustomerController
{

    public static function addAddress(): void
    {
        $customerId = (int) Tools::get("customerId");
        $zipCode = (int) Tools::get("zipCode");
        $city = Tools::get("city");
        $street = Tools::get("street");
        $houseNumber = Tools::get("houseNumber");
        $country = Tools::get("country");
        $addition = Tools::get("addition");

        Customer::addAddress($customerId, $street, $houseNumber, $zipCode, $city, $country, $addition);

        JSONResponseHandler::returnOK();
    }

    public static function getContacts(): void
    {
        $customerId = (int) Tools::get("customerId");

        $query = "SELECT
                Nummer AS id,
                Vorname AS firstName,
                Nachname AS lastName,
                Email AS email
            FROM ansprechpartner
            WHERE Kundennummer = :customerId;";
        $data = DBAccess::selectQuery($query, [
            "customerId" => $customerId,
        ]);

        JSONResponseHandler::sendResponse($data);
    }

    public static function searchCustomers(): void
    {
        $query = Tools::get("query");
        $customers = self::findCustomersByQuery($query, 10);

        JSONResponseHandler::sendResponse([
            "template" => CustomerPresenter::renderCards($customers),
            "count" => count($customers),
        ]);
    }

    public static function searchCustomersSimple(): void
    {
        $query = Tools::get("query");
        $customers = self::findCustomersByQuery($query, 10);

        JSONResponseHandler::sendResponse([
            "customers" => CustomerPresenter::toSimpleArray($customers),
            "count" => count($customers),
        ]);
    }

    /**
     * @param string $query
     * @param int $limit
     * @return Customer[]
     */
    private static function findCustomersByQuery(string $query, int $limit = 10): array
    {
        $results = SearchController::search("type:kunde $query", $limit);

        $customers = [];
        foreach ($results as $result) {
            $id = (int) $result["data"]["Kundennummer"];
            $customers[] = new Customer($id);
        }

        return $customers;
    }
}
