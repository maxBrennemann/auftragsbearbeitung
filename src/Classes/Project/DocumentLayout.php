<?php

namespace Src\Classes\Project;

use Exception;
use MaxBrennemann\PhpUtilities\DBAccess;

/**
 * Manuelles Drag-Reorder von Posten/Texten/etc. für Dokumente (Rechnungen, Angebote, ...),
 * gescoped über document_type + document_id statt einer dokumentspezifischen Tabelle.
 */
class DocumentLayout
{
    private string $documentType;
    private int $documentId;
    /** @var array<int, array<string, string>> */
    private array $layout;

    public function __construct(string $documentType, int $documentId)
    {
        $this->documentType = $documentType;
        $this->documentId = $documentId;
        $this->getLayoutData();
    }

    private function getLayoutData(): void
    {
        $query = "SELECT * FROM document_layout WHERE document_type = :documentType AND document_id = :documentId ORDER BY position;";
        $data = DBAccess::selectQuery($query, [
            "documentType" => $this->documentType,
            "documentId" => $this->documentId,
        ]);

        $this->layout = $data;
    }

    /**
     * Merged die übergebenen Inhalts-Einträge anhand der gespeicherten Reihenfolge; neue
     * Einträge (noch nicht in der Reihenfolge enthalten) werden nach Typ angehängt.
     *
     * @param array<int, array<string, mixed>> $items Einträge mit ["id", "type", "content"]
     * @param array<string, mixed>|null $trailingEntry wird unabhängig von der gespeicherten
     *   Reihenfolge immer als letzter Eintrag angehängt (z. B. das Leistungsdatum bei Rechnungen)
     * @return array<int, array<mixed>>
     */
    public function getOrderedContent(array $items, ?array $trailingEntry = null): array
    {
        $allMap = [];
        foreach ($items as $entry) {
            $key = "{$entry['type']}-{$entry['id']}";
            $allMap[$key] = $entry;
        }

        $result = [];
        $usedKeys = [];

        foreach ($this->layout as $layoutEntry) {
            $key = "{$layoutEntry['content_type']}-{$layoutEntry['content_id']}";
            if (isset($allMap[$key])) {
                $result[] = $allMap[$key];
                $usedKeys[$key] = true;
            }
        }

        $defaultOrder = ['item', 'text', 'vehicle'];
        foreach ($defaultOrder as $type) {
            foreach ($allMap as $key => $entry) {
                if ($entry['type'] === $type && !isset($usedKeys[$key])) {
                    $result[] = $entry;
                }
            }
        }

        if ($trailingEntry !== null) {
            $result[] = $trailingEntry;
        }

        return $result;
    }

    /**
     * @param array<int, mixed> $positions
     */
    public function writeItemsOrder(array $positions): bool
    {
        $query = "INSERT INTO document_layout (document_type, document_id, position, content_type, content_id) VALUES (:documentType, :documentId, :position, :type, :id) ON DUPLICATE KEY UPDATE position = VALUES(position)";

        foreach ($positions as $entry) {
            try {
                DBAccess::insertQuery($query, [
                    "documentType" => $this->documentType,
                    "documentId" => $this->documentId,
                    "position" => $entry["position"],
                    "type" => $entry["type"],
                    "id" => $entry["id"],
                ]);
            } catch (Exception $e) {
                return false;
            }
        }

        return true;
    }
}
