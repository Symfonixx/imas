/**
 * Build a WhatsApp chat URL from admin settings (digits or full URL).
 *
 * @param {string} phoneOrUrl Raw phone, wa.me link, or api.whatsapp.com URL
 * @param {string} [text] Optional pre-filled message
 * @returns {string}
 */
export function buildWhatsAppContactUrl(phoneOrUrl, text = "") {
    const raw = String(phoneOrUrl ?? "").trim();
    if (!raw) {
        return "";
    }

    if (/^https?:\/\//i.test(raw)) {
        if (/api\.whatsapp\.com/i.test(raw)) {
            return raw;
        }
        const waMeFromUrl = raw.match(/wa\.me\/(\d+)/i);
        if (waMeFromUrl) {
            return buildWhatsAppContactUrl(waMeFromUrl[1], text);
        }
        return raw;
    }

    const digits = raw.replace(/\D/g, "");
    if (!digits) {
        return "";
    }

    const params = new URLSearchParams({ phone: digits });
    if (text) {
        params.set("text", text);
    }

    return `https://api.whatsapp.com/send/?${params.toString()}`;
}

/**
 * Prefer dedicated WhatsApp number/URL, then site contact phone.
 *
 * @param {{ whatsapp?: string, phone?: string }} sources
 * @returns {string}
 */
export function resolveWhatsAppContactHref({ whatsapp = "", phone = "" } = {}) {
    const dedicated = String(whatsapp).trim();
    if (dedicated) {
        return buildWhatsAppContactUrl(dedicated);
    }

    const contactPhone = String(phone).trim();
    if (contactPhone) {
        return buildWhatsAppContactUrl(contactPhone);
    }

    return "";
}
