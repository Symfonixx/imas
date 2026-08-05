import { createInertiaApp } from "@inertiajs/vue3";
import createServer from "@inertiajs/vue3/server";
import { renderToString } from "@vue/server-renderer";
import { createSSRApp, h } from "vue";
import { configureImasVueApp } from "./configureImasVueApp.js";
import { resolveInertiaPage } from "./resolveInertiaPage.js";

createServer((page) =>
    createInertiaApp({
        page,
        render: renderToString,
        resolve: resolveInertiaPage,
        title: (title) => {
            if (!title) {
                return page.props?.appName || "IMas";
            }
            return title;
        },
        setup({ App, props, plugin }) {
            const app = createSSRApp({ render: () => h(App, props) }).use(
                plugin,
            );

            configureImasVueApp(app, {
                ssr: true,
                ziggy: page.props?.ziggy ?? null,
            });

            return app;
        },
    }),
);
