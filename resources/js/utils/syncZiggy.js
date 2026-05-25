/**
 * Keep global Ziggy config in sync with the current Inertia page locale.
 * @routes in app.blade.php only runs on full document load; Inertia visits must merge fresh routes.
 */
export function syncZiggy(ziggy) {
    if (typeof window === 'undefined' || !ziggy || typeof ziggy !== 'object') {
        return;
    }

    if (!window.Ziggy) {
        window.Ziggy = ziggy;
        return;
    }

    if (ziggy.url) {
        window.Ziggy.url = ziggy.url;
    }

    if (ziggy.location) {
        window.Ziggy.location = ziggy.location;
    }

    if (ziggy.routes) {
        window.Ziggy.routes = ziggy.routes;
    }
}
