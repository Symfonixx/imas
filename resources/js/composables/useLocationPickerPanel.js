import { nextTick, onBeforeUnmount, onMounted, ref, toValue, watch } from "vue";
import { usePage } from "@inertiajs/vue3";

const MOBILE_PANEL_MQ = "(max-width: 991.98px)";
const DESKTOP_PANEL_WIDTH = 230;

/**
 * Shared panel positioning and open/close behavior for location pickers.
 *
 * @param {import('vue').MaybeRefOrGetter<'hero' | 'sidebar'>} layout
 */
export function useLocationPickerPanel(layout) {
    const page = usePage();
    const rootRef = ref(null);
    const triggerRef = ref(null);
    const panelRef = ref(null);
    const open = ref(false);
    const useMobilePanel = ref(false);
    const panelStyle = ref({});
    /**
     * Gate `<Teleport to="body">` until after mount (`v-if="mounted"`). Inertia's
     * Vue SSR does not emit teleport-to-body content into the server HTML, so
     * rendering the teleport during SSR / initial hydration causes a node
     * mismatch. Staying `false` through SSR and the first client render makes both
     * emit a single placeholder comment; the panel is created and teleported to
     * <body> only after hydration completes.
     */
    const mounted = ref(false);
    let mobileMq = null;

    function isRtlDocument() {
        return (
            document.documentElement.getAttribute("dir") === "rtl" ||
            page.props.text_direction === "rtl" ||
            page.props.locale === "ar"
        );
    }

    function syncMobilePanelMode() {
        useMobilePanel.value =
            typeof window !== "undefined" &&
            window.matchMedia(MOBILE_PANEL_MQ).matches;
        if (open.value) {
            schedulePanelPositionUpdate();
        }
    }

    function schedulePanelPositionUpdate() {
        nextTick(() => {
            updatePanelPosition();
            requestAnimationFrame(updatePanelPosition);
        });
    }

    function resolvePanelWidth(triggerWidth, viewportMargin) {
        const base = Math.round(triggerWidth);
        const widened = Math.max(Math.round(base * 1.4), base + 48);

        return Math.min(widened, window.innerWidth - viewportMargin, 352);
    }

    function resolveDesktopPanelWidth(triggerRect, viewportMargin) {
        if (toValue(layout) === "sidebar") {
            return Math.min(
                Math.round(triggerRect.width),
                window.innerWidth - viewportMargin,
            );
        }

        return Math.min(DESKTOP_PANEL_WIDTH, window.innerWidth - viewportMargin);
    }

    function updatePanelPosition() {
        if (!open.value || !triggerRef.value) {
            panelStyle.value = {};
            return;
        }

        const triggerRect = triggerRef.value.getBoundingClientRect();
        const margin = 12;
        const top = `${Math.round(triggerRect.bottom + 6)}px`;
        const viewportMargin =
            useMobilePanel.value && window.innerWidth <= 576 ? 24 : margin * 2;

        if (useMobilePanel.value) {
            const panelWidth = resolvePanelWidth(
                triggerRect.width,
                viewportMargin,
            );
            panelStyle.value = {
                position: "fixed",
                top,
                left: "50%",
                right: "auto",
                transform: "translateX(-50%)",
                width: `${panelWidth}px`,
                maxWidth: `calc(100vw - ${viewportMargin}px)`,
            };
            return;
        }

        const panelWidth = resolveDesktopPanelWidth(triggerRect, margin * 2);
        const isRtl = isRtlDocument();

        if (isRtl) {
            let right = window.innerWidth - triggerRect.right;
            const maxRight = window.innerWidth - panelWidth - margin;
            right = Math.min(Math.max(right, margin), maxRight);

            panelStyle.value = {
                position: "fixed",
                top,
                right: `${Math.round(right)}px`,
                left: "auto",
                width: `${panelWidth}px`,
                maxWidth: `${panelWidth}px`,
                transform: "none",
            };
            return;
        }

        let left = triggerRect.left;
        left = Math.max(
            margin,
            Math.min(left, window.innerWidth - panelWidth - margin),
        );

        panelStyle.value = {
            position: "fixed",
            top,
            left: `${Math.round(left)}px`,
            right: "auto",
            width: `${panelWidth}px`,
            maxWidth: `${panelWidth}px`,
            transform: "none",
        };
    }

    function onViewportChange() {
        syncMobilePanelMode();
        updatePanelPosition();
    }

    function onOutsideClick(event) {
        if (!open.value) {
            return;
        }
        const root = rootRef.value;
        const panel = panelRef.value;
        if (
            (root && root.contains(event.target)) ||
            (panel && panel.contains(event.target))
        ) {
            return;
        }
        open.value = false;
    }

    function onKeydown(event) {
        if (event.key === "Escape" && open.value) {
            open.value = false;
        }
    }

    function toggle(onOpen) {
        open.value = !open.value;
        if (open.value) {
            onOpen?.();
            schedulePanelPositionUpdate();
        } else {
            panelStyle.value = {};
        }
    }

    function close() {
        open.value = false;
        panelStyle.value = {};
    }

    onMounted(() => {
        mounted.value = true;

        document.addEventListener("click", onOutsideClick, true);
        document.addEventListener("keydown", onKeydown);
        window.addEventListener("resize", onViewportChange);
        window.addEventListener("scroll", onViewportChange, true);

        if (typeof window !== "undefined") {
            mobileMq = window.matchMedia(MOBILE_PANEL_MQ);
            syncMobilePanelMode();
            mobileMq.addEventListener("change", syncMobilePanelMode);
        }
    });

    onBeforeUnmount(() => {
        document.removeEventListener("click", onOutsideClick, true);
        document.removeEventListener("keydown", onKeydown);
        window.removeEventListener("resize", onViewportChange);
        window.removeEventListener("scroll", onViewportChange, true);
        mobileMq?.removeEventListener("change", syncMobilePanelMode);
    });

    watch(open, (isOpen) => {
        if (!isOpen) {
            panelStyle.value = {};
        }
    });

    return {
        rootRef,
        triggerRef,
        panelRef,
        open,
        useMobilePanel,
        panelStyle,
        mounted,
        toggle,
        close,
        schedulePanelPositionUpdate,
    };
}

export { MOBILE_PANEL_MQ, DESKTOP_PANEL_WIDTH };
