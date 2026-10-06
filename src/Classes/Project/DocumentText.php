<?php

namespace Src\Classes\Project;

use Src\Classes\Pdf\PDFTexts;
use MaxBrennemann\PhpUtilities\DBAccess;

/**
 * Toggle-bare Zusatztexte für Dokumente (Rechnungen, Angebote, ...), gescoped über
 * document_type + document_id statt einer dokumentspezifischen Tabelle. Ermöglicht
 * denselben Baustein-Text-Mechanismus für mehrere Dokumenttypen, ohne ihn je Typ
 * zu duplizieren.
 */
class DocumentText
{
    /**
     * @return array<int, array<string, mixed>>
     */
    public static function getTexts(string $documentType, int $documentId, ?string $defaultTextsType = null): array
    {
        $query = "SELECT * FROM document_text WHERE document_type = :documentType AND document_id = :documentId";
        $data = DBAccess::selectQuery($query, [
            "documentType" => $documentType,
            "documentId" => $documentId,
        ]);

        if ($defaultTextsType === null) {
            return $data;
        }

        /* fügt Vorschlagstexte hinzu, die noch nicht als eigene Zeile übernommen wurden */
        $defaultTexts = PDFTexts::get($defaultTextsType);
        foreach ($defaultTexts as $text) {
            $found = false;
            foreach ($data as $d) {
                if ($d["text"] == $text) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                $data[] = [
                    "id" => 0,
                    "document_type" => $documentType,
                    "document_id" => $documentId,
                    "text" => $text,
                    "active" => 0,
                ];
            }
        }

        return $data;
    }

    /**
     * Schaltet einen bestehenden Text um, oder übernimmt (bei textId == 0) einen
     * Vorschlagstext als neue, aktive Zeile. Gibt bei Neuanlage die neue id zurück, sonst 0.
     */
    public static function toggleText(string $documentType, int $documentId, int $textId, string $text): int
    {
        if ($textId == 0) {
            $query = "INSERT INTO document_text (document_type, document_id, `text`, active) VALUES (:documentType, :documentId, :text, 1);";
            DBAccess::insertQuery($query, [
                "documentType" => $documentType,
                "documentId" => $documentId,
                "text" => $text,
            ]);

            return (int) DBAccess::getLastInsertId();
        }

        $query = "UPDATE document_text SET active = IF(active = 0, 1, 0) WHERE id = :textId AND document_type = :documentType AND document_id = :documentId";
        DBAccess::updateQuery($query, [
            "textId" => $textId,
            "documentType" => $documentType,
            "documentId" => $documentId,
        ]);

        return 0;
    }

    public static function addText(string $documentType, int $documentId, string $text): int
    {
        $query = "INSERT INTO document_text (document_type, document_id, `text`, active) VALUES (:documentType, :documentId, :text, 1);";
        return (int) DBAccess::insertQuery($query, [
            "documentType" => $documentType,
            "documentId" => $documentId,
            "text" => $text,
        ]);
    }

    public static function editText(string $documentType, int $documentId, int $textId, string $text): void
    {
        $query = "UPDATE document_text SET `text` = :text WHERE id = :textId AND document_type = :documentType AND document_id = :documentId";
        DBAccess::updateQuery($query, [
            "text" => $text,
            "textId" => $textId,
            "documentType" => $documentType,
            "documentId" => $documentId,
        ]);
    }

    public static function deleteText(string $documentType, int $documentId, int $textId): void
    {
        $query = "DELETE FROM document_text WHERE id = :textId AND document_type = :documentType AND document_id = :documentId";
        DBAccess::deleteQuery($query, [
            "textId" => $textId,
            "documentType" => $documentType,
            "documentId" => $documentId,
        ]);
    }
}
