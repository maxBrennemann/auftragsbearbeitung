// AUTO-GENERATED FILE
// Do not edit manually.
// Generated via: php ./console autoUpgrade --skip-migration
export const tableConfig = {
    "address": {
        "primaryKey": "id",
        "columns": [
            {
                "key": "id",
                "label": "Id"
            },
            {
                "key": "id_customer",
                "label": "Kundennummer"
            },
            {
                "key": "strasse",
                "label": "Straße"
            },
            {
                "key": "hausnr",
                "label": "Hausnummer"
            },
            {
                "key": "plz",
                "label": "PLZ"
            },
            {
                "key": "ort",
                "label": "Ort"
            },
            {
                "key": "zusatz",
                "label": "Zusatz"
            },
            {
                "key": "country",
                "label": "Land"
            },
            {
                "key": "art",
                "label": "Art der Adresse"
            }
        ]
    },
    "ansprechpartner": {
        "primaryKey": "Nummer",
        "columns": [
            {
                "key": "Nummer",
                "label": "Nummer"
            },
            {
                "key": "Kundennummer",
                "label": "Kundennummer"
            },
            {
                "key": "Vorname",
                "label": "Vorname"
            },
            {
                "key": "Nachname",
                "label": "Nachname"
            },
            {
                "key": "Email",
                "label": "Email"
            },
            {
                "key": "Durchwahl",
                "label": "Durchwahl"
            },
            {
                "key": "Mobiltelefonnummer",
                "label": "Mobiltelefonnummer"
            }
        ]
    },
    "auftrag": {
        "primaryKey": "Auftragsnummer",
        "columns": [
            {
                "key": "Auftragsnummer",
                "label": "Auftragsnummer"
            },
            {
                "key": "Kundennummer",
                "label": "Kundennummer"
            },
            {
                "key": "Auftragsbezeichnung",
                "label": "Auftragsbezeichnung"
            },
            {
                "key": "Auftragsbeschreibung",
                "label": "Auftragsbeschreibung"
            },
            {
                "key": "Auftragstyp",
                "label": "Auftragstyp"
            },
            {
                "key": "Datum",
                "label": "Datum"
            },
            {
                "key": "Termin",
                "label": "Termin"
            },
            {
                "key": "Fertigstellung",
                "label": "Fertigstellung"
            },
            {
                "key": "AngenommenDurch",
                "label": "AngenommenDurch"
            },
            {
                "key": "AngenommenPer",
                "label": "AngenommenPer"
            },
            {
                "key": "Ansprechpartner",
                "label": "Ansprechpartner"
            },
            {
                "key": "Rechnungsnummer",
                "label": "Rechnungsnummer"
            },
            {
                "key": "Bezahlt",
                "label": "Bezahlt"
            },
            {
                "key": "status",
                "label": "status"
            }
        ]
    },
    "auftragstyp": {
        "primaryKey": "id",
        "columns": [
            {
                "key": "id",
                "label": "Nummer"
            },
            {
                "key": "Auftragstyp",
                "label": "Auftragstyp"
            }
        ]
    },
    "color": {
        "primaryKey": "id",
        "columns": [
            {
                "key": "id",
                "label": "Id"
            },
            {
                "key": "color_name",
                "label": "Farbname"
            },
            {
                "key": "hex_value",
                "label": "Hexwert"
            },
            {
                "key": "short_name",
                "label": "Kurzbezeichnung"
            },
            {
                "key": "producer",
                "label": "Hersteller"
            }
        ]
    },
    "dateien": {
        "primaryKey": "id",
        "columns": [
            {
                "key": "dateiname",
                "label": "Gespeicherter Name"
            },
            {
                "key": "originalname",
                "label": "Name"
            },
            {
                "key": "date",
                "label": "Datum"
            },
            {
                "key": "typ",
                "label": "Dateityp"
            }
        ]
    },
    "einkauf": {
        "primaryKey": "id",
        "columns": [
            {
                "key": "id",
                "label": "Nummer"
            },
            {
                "key": "name",
                "label": "Name"
            },
            {
                "key": "description",
                "label": "Beschreibung"
            }
        ]
    },
    "fahrzeuge": {
        "primaryKey": "Nummer",
        "columns": [
            {
                "key": "Nummer",
                "label": "Nummer"
            },
            {
                "key": "Kundennummer",
                "label": "Kundennummer"
            },
            {
                "key": "Kennzeichen",
                "label": "Kennzeichen"
            },
            {
                "key": "Fahrzeug",
                "label": "Fahrzeug"
            }
        ]
    },
    "invoice": {
        "primaryKey": "id",
        "columns": [
            {
                "key": "id",
                "label": "id"
            },
            {
                "key": "invoice_number",
                "label": "invoice_number"
            },
            {
                "key": "order_id",
                "label": "order_id"
            },
            {
                "key": "contact_id",
                "label": "contact_id"
            },
            {
                "key": "address_id",
                "label": "address_id"
            },
            {
                "key": "status",
                "label": "status"
            },
            {
                "key": "creation_date",
                "label": "creation_date"
            },
            {
                "key": "performance_date",
                "label": "performance_date"
            },
            {
                "key": "payment_date",
                "label": "payment_date"
            },
            {
                "key": "finalized_date",
                "label": "finalized_date"
            },
            {
                "key": "amount",
                "label": "amount"
            },
            {
                "key": "payment_type",
                "label": "payment_type"
            },
            {
                "key": "created_at",
                "label": "created_at"
            },
            {
                "key": "updated_at",
                "label": "updated_at"
            }
        ]
    },
    "leistung": {
        "primaryKey": "Nummer",
        "columns": [
            {
                "key": "Nummer",
                "label": "Nummer"
            },
            {
                "key": "Bezeichnung",
                "label": "Bezeichnung"
            },
            {
                "key": "Beschreibung",
                "label": "Beschreibung"
            },
            {
                "key": "Quelle",
                "label": "Quelle"
            },
            {
                "key": "Aufschlag",
                "label": "Aufschlag"
            }
        ]
    },
    "module_sticker_image": {
        "primaryKey": "id_datei",
        "columns": [
            {
                "key": "id_datei",
                "label": "Dateinummer"
            },
            {
                "key": "id_motiv",
                "label": "Motivnummer"
            },
            {
                "key": "image_sort",
                "label": "Bildtyp"
            },
            {
                "key": "id_product",
                "label": "Produktnummer"
            },
            {
                "key": "id_image_shop",
                "label": "Bildnummer im Shop"
            },
            {
                "key": "description",
                "label": "Beschreibung"
            },
            {
                "key": "image_order",
                "label": "Position"
            }
        ]
    },
    "module_sticker_sticker_data": {
        "primaryKey": "id",
        "columns": [
            {
                "key": "id",
                "label": "Nummer"
            },
            {
                "key": "category",
                "label": "Kategorie"
            },
            {
                "key": "name",
                "label": "Name"
            },
            {
                "key": "is_plotted",
                "label": "geplottet"
            },
            {
                "key": "is_short_time",
                "label": "Werbeaufkleber"
            },
            {
                "key": "is_long_time",
                "label": "Hochleistungsfolie"
            },
            {
                "key": "is_walldecal",
                "label": "Wandtattoo"
            },
            {
                "key": "is_multipart",
                "label": "Mehrteilig"
            },
            {
                "key": "is_shirtcollection",
                "label": "Textil"
            },
            {
                "key": "is_colorable",
                "label": "Einfärbbar"
            },
            {
                "key": "is_customizable",
                "label": "Personalisierbar"
            },
            {
                "key": "is_for_configurator",
                "label": "Für Konfigurator"
            },
            {
                "key": "price_class",
                "label": "Preisklasse"
            },
            {
                "key": "size_summary",
                "label": "Größen"
            },
            {
                "key": "creation_date",
                "label": "Erstelldatum"
            },
            {
                "key": "directory_name",
                "label": "Verzeichnis"
            },
            {
                "key": "is_revised",
                "label": "Überarbeitet"
            },
            {
                "key": "is_marked",
                "label": "Gemerkt"
            },
            {
                "key": "additional_info",
                "label": "Zusatzinfo"
            },
            {
                "key": "additional_data",
                "label": "Erweiterte Infos"
            }
        ]
    },
    "pdf_texts": {
        "primaryKey": "id",
        "columns": [
            {
                "key": "id",
                "label": "Nummer"
            },
            {
                "key": "type",
                "label": "Art"
            },
            {
                "key": "status",
                "label": "Status"
            },
            {
                "key": "text",
                "label": "Text"
            }
        ]
    },
    "produkt": {
        "primaryKey": "Nummer",
        "columns": [
            {
                "key": "Nummer",
                "label": "Nummer"
            },
            {
                "key": "Marke",
                "label": "Marke"
            },
            {
                "key": "Preis",
                "label": "Preis"
            },
            {
                "key": "Einkaufspreis",
                "label": "Einkaufspreis"
            },
            {
                "key": "Bezeichnung",
                "label": "Bezeichnung"
            },
            {
                "key": "Beschreibung",
                "label": "Beschreibung"
            }
        ]
    },
    "schritte": {
        "primaryKey": "Schrittnummer",
        "columns": [
            {
                "key": "Schrittnummer",
                "label": "Nummer"
            },
            {
                "key": "Auftragsnummer",
                "label": "Auftrag"
            },
            {
                "key": "assignedTo",
                "label": "allgemein"
            },
            {
                "key": "Bezeichnung",
                "label": "Bezeichnung"
            },
            {
                "key": "Datum",
                "label": "Datum"
            },
            {
                "key": "Priority",
                "label": "Priorität"
            },
            {
                "key": "finishingDate",
                "label": "Erledigt am"
            },
            {
                "key": "istErledigt",
                "label": "Status"
            }
        ]
    },
    "user": {
        "primaryKey": "id",
        "columns": [
            {
                "key": "id",
                "label": "Nummer"
            },
            {
                "key": "lastname",
                "label": "Nachname"
            },
            {
                "key": "prename",
                "label": "Vorname"
            },
            {
                "key": "username",
                "label": "Username"
            },
            {
                "key": "email",
                "label": "Email"
            },
            {
                "key": "role",
                "label": "Rolle"
            },
            {
                "key": "max_working_hours",
                "label": "Arbeitsstunden"
            }
        ]
    },
    "user_timetracking": {
        "primaryKey": "id",
        "columns": [
            {
                "key": "id",
                "label": "id"
            },
            {
                "key": "user_id",
                "label": "user_id"
            },
            {
                "key": "started_at",
                "label": "started_at"
            },
            {
                "key": "stopped_at",
                "label": "stopped_at"
            },
            {
                "key": "is_pending",
                "label": "is_pending"
            },
            {
                "key": "duration_ms",
                "label": "duration_ms"
            },
            {
                "key": "task",
                "label": "task"
            },
            {
                "key": "edit_log",
                "label": "edit_log"
            }
        ]
    }
};
