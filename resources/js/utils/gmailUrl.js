/**
 * Open Gmail compose with a pre-filled recipient (site contact email).
 *
 * @param {string} email Recipient address
 * @param {{ subject?: string, body?: string }} [options]
 * @returns {string}
 */
export function buildGmailComposeUrl(email, options = {}) {
    const to = String(email ?? "").trim();
    if (!to) {
        return "";
    }

    const params = new URLSearchParams({
        view: "cm",
        fs: "1",
        to,
    });

    const subject = String(options.subject ?? "").trim();
    const body = String(options.body ?? "").trim();

    if (subject) {
        params.set("su", subject);
    }
    if (body) {
        params.set("body", body);
    }

    return `https://mail.google.com/mail/?${params.toString()}`;
}
