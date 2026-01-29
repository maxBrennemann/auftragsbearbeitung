<?php

namespace Src\Classes\Project;

use Src\Classes\Link;

class CompanyProfile
{

    /**
     * Summary of getCompanyDetails
     * @return array{
     * companyAddress: string|null,
     * companyBank: string|null,
     * companyBic: string|null,
     * companyCity: string|null,
     * companyCountry: string|null,
     * companyEmail: string|null,
     * companyIban: string|null,
     * companyImprint: string|null,
     * companyName: string|null,
     * companyPhone: string|null,
     * companyUstIdNr: string|null,
     * companyWebsite: string|null,
     * companyZip: string|null}
     */
    public static function get(): array
    {
        return [
            "companyName" => Settings::get("company.name"),
            "companyAddress" => Settings::get("company.address"),
            "companyZip" => Settings::get("company.zip"),
            "companyCity" => Settings::get("company.city"),
            "companyCountry" => Settings::get("company.country"),
            "companyPhone" => Settings::get("company.phone"),
            "companyEmail" => Settings::get("company.email"),
            "companyWebsite" => Settings::get("company.website"),
            "companyImprint" => Settings::get("company.imprint"),
            "companyBank" => Settings::get("company.bank"),
            "companyIban" => Settings::get("company.IBAN"),
            "companyBic" => Settings::get("company.BIC"),
            "companyUstIdNr" => Settings::get("company.UstIdNr"),
        ];
    }

    public static function getLogo(): string
    {
        $image = Image::getLogo();
        if ($image == "") {
            return ROOT . "public/assets/img/default_image.png";
        } else {
            return Link::getFilePath($image, "upload");
        }
    }
}
