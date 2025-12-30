export type FilterField =
  | "startdate"
  | "enddate"
  | "ordertype"
  | "orderstate"
  | "customer"
  | "volume"
  | "profit";

export type FilterOp = "eq" | "neq" | "in" | "gt" | "gte" | "lt" | "lte" | "between";

export type FilterValue =
  | string
  | number
  | (string | number)[]
  | [number, number]
  | [string, string];

export type Filter = {
  id: string;
  field: FilterField;
  op: FilterOp;
  value: FilterValue;
};
