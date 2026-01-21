<?php

namespace Src\Classes\Controller;

use DateTime;
use MaxBrennemann\PhpUtilities\DBAccess;

class AccessTokenController
{

    /**
     * @return array<int, array<string, string>>
     */
    private static function getValidTokens(): array
    {
        $query = "SELECT id, `name`, `token_hash` FROM access_tokens WHERE is_active = 1";
        $data = DBAccess::selectQuery($query);

        return $data;
    }

    private static function updateLastUsed(int $id): void
    {
        $query = "UPDATE access_tokens SET last_used = :lastUsed WHERE id = :id;";
        DBAccess::updateQuery($query, [
            "lastUsed" => date('Y-m-d H:i:s'),
            "id" => $id,
        ]);
    }

    public static function isValid(?string $token): bool
    {
        if ($token == null) {
            return false;
        }

        $allToken = self::getValidTokens();
        foreach ($allToken as $t) {
            $tHash = $t["token_hash"];
            if (hash_equals($tHash, $token)) {
                self::updateLastUsed($t["id"]);
                return true;
            }
        }
       
        return false;
    }
}
