<?php

namespace Src\Classes\Project;

use Src\Classes\Link;

class CompanyProfile
{

    /**
     * Summary of getCompanyDetails
     * @return array{companyAddress: string|null, companyBank: string|null, companyBic: string|null, companyCity: string|null, companyCountry: string|null, companyEmail: string|null, companyIban: string|null, companyImprint: string|null, companyName: string|null, companyPhone: string|null, companyUstIdNr: string|null, companyWebsite: string|null, companyZip: string|null}
     */
    public static function get(): array
    {
        return [
            "companyName" => Settings::get("companyName"),
            "companyAddress" => Settings::get("companyAddress"),
            "companyZip" => Settings::get("companyZip"),
            "companyCity" => Settings::get("companyCity"),
            "companyCountry" => Settings::get("companyCountry"),
            "companyPhone" => Settings::get("companyPhone"),
            "companyEmail" => Settings::get("companyEmail"),
            "companyWebsite" => Settings::get("companyWebsite"),
            "companyImprint" => Settings::get("companyImprint"),
            "companyBank" => Settings::get("companyBank"),
            "companyIban" => Settings::get("companyIban"),
            "companyBic" => Settings::get("companyBic"),
            "companyUstIdNr" => Settings::get("companyUstIdNr"),
        ];
    }

    public static function getLogo(): string
    {
        $image = ClientSettings::getLogo();
        if ($image == "") {
            return ROOT . "public/assets/img/default_image.png";
        } else {
            return Link::getFilePath($image, "upload");
        }
    }
}
