/** Dispatched on `document`; handled by `UserNavbar` to open `AuthModal`. */
export const IMAS_OPEN_AUTH_EVENT = "imas:open-auth";

/**
 * Open the front-office sign-in / register modal from anywhere in the app.
 * Requires `UserNavbar` (or another listener) to be mounted.
 *
 * @param {"login" | "register" | "reset"} [tab]
 */
export function useOpenAuthModal() {
    function openAuthModal(tab = "login") {
        if (typeof document === "undefined") {
            return;
        }

        const normalized =
            tab === "register" || tab === "reset" ? tab : "login";

        document.dispatchEvent(
            new CustomEvent(IMAS_OPEN_AUTH_EVENT, {
                detail: { tab: normalized },
                bubbles: true,
            }),
        );
    }

    return { openAuthModal };
}
