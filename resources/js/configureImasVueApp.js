import { route as ziggyRoute, ZiggyVue } from "../../vendor/tightenco/ziggy/dist/index.esm.js";
import PropertyCard from "../../Modules/Property/resources/assets/js/components/PropertyCard.vue";
import VideoLightbox from "./components/Global/VideoLightbox.vue";
import gsapPlugin from "./plugins/gsap.js";

/**
 * @param {object} ziggy
 */
function createRouteHelper(ziggy) {
    return (name, params, absolute) => ziggyRoute(name, params, absolute, ziggy);
}

/**
 * Register shared front-office Vue plugins and global components.
 *
 * @param {import('vue').App} app
 * @param {{ ssr?: boolean, ziggy?: object }} [options]
 */
export function configureImasVueApp(app, { ssr = false, ziggy = null } = {}) {
    app.component("PropertyCard", PropertyCard).component(
        "VideoLightbox",
        VideoLightbox,
    );

    if (ziggy && typeof ziggy === "object") {
        globalThis.Ziggy = ziggy;
        const routeFn = createRouteHelper(ziggy);

        if (ssr) {
            globalThis.route = routeFn;
        }

        app.use(ZiggyVue, ziggy);
        app.mixin({ methods: { route: routeFn } });
    } else if (!ssr && typeof route === "function") {
        app.mixin({ methods: { route } });
    }

    if (!ssr) {
        app.use(gsapPlugin);
    }

    return app;
}
