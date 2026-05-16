/**
 * @param {number|string|null|undefined} value
 */
function formatAreaNumber(value) {
    const n = Number(value);
    if (!Number.isFinite(n)) {
        return null;
    }

    return new Intl.NumberFormat(undefined, {
        maximumFractionDigits: 0,
    }).format(n);
}

/**
 * @param {number|string|null|undefined} min
 * @param {number|string|null|undefined} max
 */
export function unitTypeAreaRange(min, max) {
    const minS = formatAreaNumber(min);
    const maxS = formatAreaNumber(max);

    if (minS && maxS && minS !== maxS) {
        const lo = Math.min(Number(min), Number(max));
        const hi = Math.max(Number(min), Number(max));

        return `${formatAreaNumber(lo)} - ${formatAreaNumber(hi)} m²`;
    }

    const single = minS ?? maxS;

    return single ? `${single} m²` : "";
}

/**
 * @param {{ name?: string, min_area?: unknown, max_area?: unknown }|null|undefined} unitType
 * @return {{ name: string, area: string }}
 */
export function unitTypeDisplayParts(unitType) {
    if (!unitType) {
        return { name: "—", area: "" };
    }

    const name = String(unitType.name ?? "").trim() || "—";
    const area = unitTypeAreaRange(unitType.min_area, unitType.max_area);

    return { name, area };
}

/**
 * @param {{ name?: string, min_area?: unknown, max_area?: unknown }|null|undefined} unitType
 * @deprecated Prefer {@see unitTypeDisplayParts} in templates for RTL-safe layout.
 */
export function unitTypeDisplayLine(unitType) {
    const { name, area } = unitTypeDisplayParts(unitType);

    if (!area) {
        return name;
    }

    return `${name} → ${area}`;
}
