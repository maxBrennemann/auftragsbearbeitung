export const LABELS = {
    month: "Monat",
    year: "Jahr",
    ordertype: "Auftragstyp",
    customer: "Kunde",
    orderstate: "Auftragsstatus",
    volume: "Umsatz",
    profit: "Gewinn",
    startdate: "Startdatum",
    enddate: "Enddatum",
} as const;

export type DimensionKey = keyof typeof LABELS;

export const label = (key: string) =>
    (LABELS as Record<string, string>)[key] ?? key;
