/** Turkish mobile display: +90 536 910 46 89 (3-3-2-2 after country code). */
const TR_COUNTRY_CODE = "90";
const TR_NATIONAL_LENGTH = 10;

/**
 * Normalize to E.164 digits without "+" (e.g. "905369104689").
 *
 * Accepts +90…, 90…, 0…, or 10-digit national numbers.
 *
 * @param {string} phone
 * @returns {string}
 */
export function normalizeTurkishPhoneDigits(phone) {
    const digits = String(phone ?? "").replace(/\D/g, "");
    if (!digits) {
        return "";
    }

    let national = digits;

    if (national.startsWith(TR_COUNTRY_CODE)) {
        national = national.slice(TR_COUNTRY_CODE.length);
    }

    if (national.startsWith("0")) {
        national = national.slice(1);
    }

    if (national.length !== TR_NATIONAL_LENGTH) {
        return "";
    }

    return `${TR_COUNTRY_CODE}${national}`;
}

/**
 * Format a Turkish phone for display: "+90 536 910 46 89".
 *
 * Non-Turkish or invalid input is returned trimmed unchanged.
 *
 * @param {string} phone
 * @returns {string}
 */
export function formatTurkishPhone(phone) {
    const raw = String(phone ?? "").trim();
    if (!raw) {
        return "";
    }

    const e164 = normalizeTurkishPhoneDigits(raw);
    if (!e164) {
        return raw;
    }

    const national = e164.slice(TR_COUNTRY_CODE.length);
    return `+${TR_COUNTRY_CODE} ${national.slice(0, 3)} ${national.slice(3, 6)} ${national.slice(6, 8)} ${national.slice(8, 10)}`;
}

/**
 * Build a tel: href for a Turkish number (+905369104689).
 *
 * @param {string} phone
 * @returns {string}
 */
export function buildTurkishTelHref(phone) {
    const e164 = normalizeTurkishPhoneDigits(phone);
    return e164 ? `tel:+${e164}` : "";
}
