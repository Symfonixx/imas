import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";

/**
 * Resolve an Inertia page component (core app + module namespaced pages).
 *
 * @param {string} name
 */
export function resolveInertiaPage(name) {
    const modules = name.split("::");

    if (modules.length > 1) {
        return resolvePageComponent(
            `../../Modules/${modules[0]}/resources/assets/js/Pages/${modules[1]}.vue`,
            import.meta.glob("../../Modules/**/resources/assets/js/Pages/**/*.vue"),
        );
    }

    return resolvePageComponent(
        `./Pages/${name}.vue`,
        import.meta.glob("./Pages/**/*.vue"),
    );
}
