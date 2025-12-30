type QueryPayload = {
	dims: string[];
	filters: Array<{ field: string; op: string; value: any }>;
};

export const QueryBuilder = {
	toPayload(state: { dimensions: Set<string>; filters: any[] }): QueryPayload {
		return {
			dims: Array.from(state.dimensions),
			filters: state.filters
				.map(f => ({ field: f.field, op: f.op, value: f.value }))
				.filter(f => QueryBuilder.isFilterValid(f)),
		};
	},

	toSearchParams(payload: QueryPayload): URLSearchParams {
		const params = new URLSearchParams();

		if (payload.dims.length) {
			params.set("dims", payload.dims.join(","));
		}
		if (payload.filters.length) {
			params.set("filters", JSON.stringify(payload.filters));
		}

		return params;
	},

	toUrl(payload: QueryPayload, baseUrl = window.location.pathname): string {
		const params = QueryBuilder.toSearchParams(payload);
		const qs = params.toString();
		return qs ? `${baseUrl}?${qs}` : baseUrl;
	},

	fromUrl(search = window.location.search): QueryPayload {
		const params = new URLSearchParams(search);

		const dimsRaw = params.get("dims") ?? "";
		const dims = dimsRaw
			.split(",")
			.map(s => s.trim())
			.filter(Boolean);

		let filters: QueryPayload["filters"] = [];
		const filtersRaw = params.get("filters");
		if (filtersRaw) {
			try {
				const parsed = JSON.parse(filtersRaw);
				if (Array.isArray(parsed)) {
					filters = parsed.filter(f => QueryBuilder.isFilterValid(f));
				}
			} catch {}
		}

		return { dims, filters };
	},

	applyToState(payload: QueryPayload, state: any) {
		state.dimensions = new Set(payload.dims);
		state.filters = payload.filters.map(f => ({
			id: `f_${Math.random().toString(36).slice(2, 10)}`,
			field: f.field,
			op: f.op,
			value: f.value,
		}));
	},

	isFilterValid(f: any): boolean {
		if (!f || !f.field || !f.op) return false;

		if (f.op === "between") {
			return Array.isArray(f.value) && f.value.length === 2 && f.value[0] !== "" && f.value[1] !== "";
		}

		if (f.op === "in") {
			return Array.isArray(f.value) && f.value.length > 0;
		}

		if (typeof f.value === "number") return true;
		return f.value !== "" && f.value !== null && f.value !== undefined;
	},
};
