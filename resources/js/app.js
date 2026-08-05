import "toastr";

import { createInertiaApp, router } from "@inertiajs/vue3";
import { createSSRApp, h } from "vue";
import { configureImasVueApp } from "./configureImasVueApp.js";
import { resolveInertiaPage } from "./resolveInertiaPage.js";
import { killAllGsap, refreshScrollTrigger } from "./plugins/gsap.js";
import { syncZiggy } from "./utils/syncZiggy.js";

createInertiaApp({
    resolve: resolveInertiaPage,
    title: (title) => {
        if (!title) {
            return document.title || "IMas";
        }
        return title;
    },
    setup({ el, App, props, plugin }) {
        const app = createSSRApp({ render: () => h(App, props) }).use(plugin);

        configureImasVueApp(app);

        app.mount(el);

        return app;
    },
});

router.on("start", () => {
    killAllGsap();
});

router.on("success", (event) => {
    syncZiggy(event.detail.page?.props?.ziggy);
});

router.on("finish", () => {
    refreshScrollTrigger();
});
