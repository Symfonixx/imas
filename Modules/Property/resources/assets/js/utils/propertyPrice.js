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

/**
 * Format a property price with a plain `$` symbol (not locale “US$” / “USD”).
 *
 * @param {unknown} amount
 * @param {string} [locale="en"]
 * @returns {string}
 */
export function formatPropertyMoney(amount, locale = "en") {
    const n = Number(amount);
    if (!Number.isFinite(n)) {
        return "—";
    }

    const formatted = new Intl.NumberFormat(locale, {
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(n);

    return `$${formatted}`;
}
