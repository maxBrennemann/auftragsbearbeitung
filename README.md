# Auftragsbearbeitung

Das Projekt besteht aus einem Backoffice, in dem man Aufträge von Kunden anlegen und abarbeiten kann. Verschiedenen Mitarbeitern können Aufgaben zugewiesen werden. Es können Angebote und Rechnungen generiert werden.

In Entwicklung befindet sich außerdem ein Frontoffice, das wie ein klassischer Onlineshop funktionieren soll. Mit dabei ist ein CMS.

# Produkterstellung

attribute Tabelle:
    Beschreibt den Attributwert (gelb, Baumwolle)

attribute_group Tabelle:
    Gruppiert die Attribute (Farbe, Größe)

attribute_to_product Tabelle:
    Produkt-Attribut-Id mit Attribut-Id gemached

produkt_attribute Tabelle
    Produkt-Id mit weiteren Informationen über Produktvariante mit mehreren Attributen

produkt Tabelle
    Produkt allgemein mit Informationen
