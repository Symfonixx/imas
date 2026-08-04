import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";

gsap.registerPlugin(ScrollTrigger);

/** Shared defaults for IMas front-office animations. */
gsap.defaults({
    ease: "power2.out",
    duration: 1,
});

/**
 * @returns {boolean}
 */
export function prefersReducedMotion() {
    return (
        typeof window !== "undefined" &&
        window.matchMedia?.("(prefers-reduced-motion: reduce)")?.matches ===
            true
    );
}

/**
 * Stop tweens and scroll triggers before Inertia navigations or teardown.
 */
export function killAllGsap() {
    ScrollTrigger.getAll().forEach((trigger) => trigger.kill());
    gsap.globalTimeline.clear();
}

/**
 * Run `fn` inside a scoped GSAP context (auto-reverted on `revert()`).
 * Skips animation when the user prefers reduced motion.
 *
 * @param {() => void} fn
 * @param {import('vue').ComponentPublicInstance | Element | string | null | undefined} scope
 * @returns {import('gsap').Context | { revert: () => void }}
 */
export function createGsapContext(fn, scope) {
    if (prefersReducedMotion()) {
        return { revert() {} };
    }

    return gsap.context(fn, scope ?? undefined);
}

/**
 * Refresh ScrollTrigger after layout changes (images, fonts, Inertia page swap).
 */
export function refreshScrollTrigger() {
    if (prefersReducedMotion()) {
        return;
    }

    requestAnimationFrame(() => {
        ScrollTrigger.refresh();
    });
}

export { gsap, ScrollTrigger };

export default {
    install(app) {
        app.config.globalProperties.$gsap = gsap;
        app.config.globalProperties.$ScrollTrigger = ScrollTrigger;
        app.provide("gsap", gsap);
        app.provide("ScrollTrigger", ScrollTrigger);
    },
};
