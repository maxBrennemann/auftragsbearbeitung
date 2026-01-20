<?php

namespace Src\Classes\Project;

use Src\Classes\Controller\TemplateController;
use Src\Classes\Project\Kunde as Customer;

final class CustomerPresenter
{
    public static function renderShortSummary(Customer $customer): string
    {
        return TemplateController::getTemplate("customerShortSummary", [
            "customer" => $customer,
        ]);
    }

    /**
     * @param array<Customer> $customers
     * @param bool $targetLink defines if the whole customer name is a link or not
     * @return string
     */
    public static function renderCards(array $customers, bool $targetLink = false): string
    {
        $html = "";
        foreach ($customers as $customer) {
            $html .= TemplateController::getTemplate("customerCardTemplate", [
                "customer" => $customer,
                "defaultLink" => $targetLink,
            ]);
        }

        return $html;
    }

    /**
     * @param array<Customer> $customers
     * @return array<int, array<string, mixed>>
     */
    public static function toSimpleArray(array $customers): array
    {
        return array_map(fn(Customer $c) => [
            "id" => $c->getKundennummer(),
            "name" => $c->getAlternativeName(),
            "email" => $c->getEmail(),
        ], $customers);
    }
}
