/**
 * Lowest unit-type price exposed as `start_price` on listing cards.
 *
 * @param {{ start_price?: unknown, price?: unknown }|null|undefined} property
 */
export function propertyStartPrice(property) {
    const start = Number(property?.start_price);
    if (Number.isFinite(start)) {
        return start;
    }

    const fallback = Number(property?.price);

    return Number.isFinite(fallback) ? fallback : null;
}
