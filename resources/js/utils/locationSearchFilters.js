function toIdSet(ids) {
    return new Set((ids ?? []).map((id) => String(id)));
}

/**
 * @param {Array<{ id: number|string, parent_id?: number|string|null }>} districts
 * @param {Array<string|number>} selectedCityIds
 */
export function filterDistrictsByCities(districts, selectedCityIds) {
    if (!selectedCityIds?.length) {
        return districts ?? [];
    }

    const citySet = toIdSet(selectedCityIds);

    return (districts ?? []).filter((district) =>
        citySet.has(String(district.parent_id)),
    );
}

/**
 * @param {Array<{ id: number|string, parent_id?: number|string|null }>} areas
 * @param {Array<{ id: number|string, parent_id?: number|string|null }>} districts
 * @param {Array<string|number>} selectedCityIds
 */
export function filterAreasByCities(areas, districts, selectedCityIds) {
    if (!selectedCityIds?.length) {
        return areas ?? [];
    }

    const visibleDistrictIds = toIdSet(
        filterDistrictsByCities(districts, selectedCityIds).map((d) => d.id),
    );

    return (areas ?? []).filter((area) =>
        visibleDistrictIds.has(String(area.parent_id)),
    );
}

/**
 * @param {Array<string|number>} ids
 * @param {Array<{ id: number|string }>} cities
 * @param {Array<{ id: number|string }>} districts
 * @param {Array<{ id: number|string }>} areas
 * @returns {{ cityIds: string[], districtAreaIds: string[] }}
 */
export function splitLocationIds(ids, cities, districts, areas) {
    const normalized = (ids ?? [])
        .filter((id) => id != null && id !== "")
        .map((id) => String(id));

    const cityIdSet = toIdSet((cities ?? []).map((c) => c.id));
    const districtAreaIdSet = toIdSet([
        ...(districts ?? []).map((d) => d.id),
        ...(areas ?? []).map((a) => a.id),
    ]);

    const cityIds = [];
    const districtAreaIds = [];

    for (const id of normalized) {
        if (cityIdSet.has(id)) {
            cityIds.push(id);
        } else if (districtAreaIdSet.has(id)) {
            districtAreaIds.push(id);
        }
    }

    return { cityIds, districtAreaIds };
}

/**
 * @param {Array<string|number>} ids
 * @param {Array<{ id: number|string }>} districts
 * @param {Array<{ id: number|string }>} areas
 */
export function pruneDistrictAreaIds(ids, districts, areas) {
    const allowed = toIdSet([
        ...(districts ?? []).map((d) => d.id),
        ...(areas ?? []).map((a) => a.id),
    ]);

    return (ids ?? [])
        .map((id) => String(id))
        .filter((id) => allowed.has(id));
}
