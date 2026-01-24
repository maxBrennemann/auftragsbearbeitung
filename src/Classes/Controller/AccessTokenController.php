<?php

namespace Src\Classes\Controller;

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

        $tokenHash = hash("sha256", $token);

        $allToken = self::getValidTokens();
        foreach ($allToken as $t) {
            $tHash = $t["token_hash"];
            if (hash_equals($tHash, $tokenHash)) {
                self::updateLastUsed((int) $t["id"]);
                return true;
            }
        }
       
        return false;
    }

    public static function create(string $name, bool $isActive = true): string
    {
        $plainToken = bin2hex(random_bytes(32));
        $tokenHash = hash("sha256", $plainToken);
        $name = strtoupper($name);

        $query = "INSERT INTO access_tokens (`name`, token_hash, is_active) VALUES (:name, :tokenHash, :isActive);";
        DBAccess::insertQuery($query, [
            "name" => $name,
            "tokenHash" => $tokenHash,
            "isActive" => (int) $isActive,
        ]);

        return $plainToken;
    }

    public static function deactivate(int $id): void
    {
        DBAccess::updateQuery("UPDATE access_tokens SET is_active = 0 WHERE id = :id;",
            [
                "id" => $id,
        ]);
    }
}
