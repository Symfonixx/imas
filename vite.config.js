import { defineConfig } from "vite";
import { createHtmlPlugin } from "vite-plugin-html";
import vueDevTools from "vite-plugin-vue-devtools";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";
import path from "path";

export default defineConfig(({ mode }) => ({
    server: {
        host: "127.0.0.1",
        cors: true,
        hmr: {
            host: "127.0.0.1",
        },
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            ssr: "resources/js/ssr.js",
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        // Devtools must never ship in production — it adds large unused JS (PSI).
        ...(mode === "development" ? [vueDevTools()] : []),
        createHtmlPlugin({}),
    ],
    resolve: {
        alias: {
            "@": path.resolve(__dirname, "resources/js"),
        },
    },
    build: {
        // Split heavy libs so the initial route can defer unused chunks.
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes("node_modules/gsap")) {
                        return "gsap";
                    }
                    if (id.includes("node_modules/flag-icons")) {
                        return "flag-icons";
                    }
                    if (id.includes("node_modules/toastr")) {
                        return "toastr";
                    }
                },
            },
        },
    },
}));
