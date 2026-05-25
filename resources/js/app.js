import 'toastr';

import {createApp, h} from 'vue';
import {createInertiaApp, router} from '@inertiajs/vue3';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';

import PropertyCard from '../../Modules/Property/resources/assets/js/components/PropertyCard.vue';
import VideoLightbox from './components/Global/VideoLightbox.vue';
import gsapPlugin, {killAllGsap, refreshScrollTrigger} from './plugins/gsap';
import {syncZiggy} from './utils/syncZiggy.js';

createInertiaApp({
    resolve: (name) => {
        const modules = name.split("::");
        if (modules.length > 1) {
            return resolvePageComponent(
                `../../Modules/${modules[0]}/resources/assets/js/Pages/${modules[1]}.vue`,
                import.meta.glob('../../Modules/**/resources/assets/js/Pages/**/*.vue')
            );
        } else {
            return resolvePageComponent(
                `./Pages/${name}.vue`,
                import.meta.glob('./Pages/**/*.vue')
            );
        }
    },
    setup({el, App, props, plugin}) {
        createApp({render: () => h(App, props)})
            .use(plugin)
            .use(gsapPlugin)
            .mixin({methods: {route}})
            .component('PropertyCard', PropertyCard)
            .component('VideoLightbox', VideoLightbox)
            .mount(el);
    },
});

router.on('start', () => {
    killAllGsap();
});

router.on('success', (event) => {
    syncZiggy(event.detail.page?.props?.ziggy);
});

router.on('finish', () => {
    refreshScrollTrigger();
});
