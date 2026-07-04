/**
 * Safe translation lookup for Inertia pages (SSR + client).
 *
 * @param {Record<string, string>|undefined|null} translations
 * @param {string} key
 */
export function inertiaTrans(translations, key) {
    if (translations && typeof translations === "object") {
        return translations[key] ?? key;
    }

    return key;
}

/**
 * @param {import('@inertiajs/vue3').Page} page
 */
export function transFromPage(page, key) {
    return inertiaTrans(page.props.translations, key);
}
