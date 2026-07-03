/**
 * True when code runs in the browser (not during Inertia SSR in Node).
 */
export function isBrowser() {
    return typeof window !== "undefined" && typeof document !== "undefined";
}
