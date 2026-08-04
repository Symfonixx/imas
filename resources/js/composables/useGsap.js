import { inject, onBeforeUnmount, shallowRef } from "vue";
import gsap from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";
import {
    createGsapContext,
    prefersReducedMotion,
    refreshScrollTrigger,
} from "@/plugins/gsap";

/**
 * Vue composable for GSAP in SFCs. Scopes animations to a template ref root
 * and reverts them on unmount (safe with Inertia client navigations).
 *
 * @example
 * const sectionRef = ref(null);
 * const { context } = useGsap();
 * onMounted(() => {
 *   context(() => {
 *     gsap.from(sectionRef.value, { opacity: 0, y: 24 });
 *   }, sectionRef);
 * });
 */
export function useGsap() {
    const gsapInstance = inject("gsap", gsap);
    const scrollTrigger = inject("ScrollTrigger", ScrollTrigger);
    const ctxRef = shallowRef(null);

    /**
     * @param {() => void} fn
     * @param {import('vue').Ref | import('vue').ComponentPublicInstance | Element | null | undefined} scope
     */
    function context(fn, scope) {
        ctxRef.value?.revert?.();

        const scopeEl = scope?.value ?? scope ?? undefined;
        ctxRef.value = createGsapContext(fn, scopeEl);

        return ctxRef.value;
    }

    onBeforeUnmount(() => {
        ctxRef.value?.revert?.();
        ctxRef.value = null;
    });

    return {
        gsap: gsapInstance,
        ScrollTrigger: scrollTrigger,
        context,
        prefersReducedMotion,
        refreshScrollTrigger,
    };
}
