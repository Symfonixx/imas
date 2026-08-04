/** Dispatched on `document`; handled by `UserNavbar` to open `AuthModal`. */
export const IMAS_OPEN_AUTH_EVENT = "imas:open-auth";

/**
 * Open the front-office sign-in / register / forgot / reset modal from anywhere.
 * Requires `UserNavbar` (or another listener) to be mounted.
 *
 * @param {"login" | "register" | "forgot" | "reset"} [tab]
 */
export function useOpenAuthModal() {
    function openAuthModal(tab = "login") {
        if (typeof document === "undefined") {
            return;
        }

        const normalized =
            tab === "register" || tab === "reset" || tab === "forgot"
                ? tab
                : "login";

        document.dispatchEvent(
            new CustomEvent(IMAS_OPEN_AUTH_EVENT, {
                detail: { tab: normalized },
                bubbles: true,
            }),
        );
    }

    return { openAuthModal };
}
