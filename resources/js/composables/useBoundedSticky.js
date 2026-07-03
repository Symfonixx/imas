import { onBeforeUnmount, onMounted } from "vue";

function getStickOffset(extraGap = 16) {
    const header = document.getElementById("header");
    if (!header) {
        return extraGap;
    }

    const rect = header.getBoundingClientRect();
    const pinned = header.classList.contains("imas-scroll-pinned--in");

    if (pinned || rect.top <= 0) {
        return Math.round(rect.height + extraGap);
    }

    return Math.round(rect.bottom + extraGap);
}

function clearInlinePosition(target) {
    target.style.position = "";
    target.style.top = "";
    target.style.bottom = "";
    target.style.left = "";
    target.style.right = "";
    target.style.width = "";
    target.style.maxWidth = "";
    target.style.zIndex = "";
    target.style.transform = "";
    target.style.insetInlineStart = "";
}

/**
 * Keeps a sidebar contact panel stuck while scrolling its parent column.
 * Uses CSS `position: sticky` (direction-safe for RTL/LTR) and only updates
 * `--imas-sticky-top` for the pinned header offset.
 */
export function useBoundedSticky({
    boundaryRef: _boundaryRef,
    columnRef: _columnRef,
    targetRef,
    minWidth = 992,
}) {
    let raf = 0;
    let resizeObserver = null;

    function update() {
        const target = targetRef.value;

        if (!target) {
            return;
        }

        if (window.innerWidth < minWidth) {
            target.style.removeProperty("--imas-sticky-top");
            clearInlinePosition(target);
            return;
        }

        target.style.setProperty("--imas-sticky-top", `${getStickOffset()}px`);
        clearInlinePosition(target);
    }

    function scheduleUpdate() {
        cancelAnimationFrame(raf);
        raf = requestAnimationFrame(update);
    }

    onMounted(() => {
        window.addEventListener("scroll", scheduleUpdate, { passive: true });
        window.addEventListener("resize", scheduleUpdate);

        if (typeof ResizeObserver !== "undefined") {
            resizeObserver = new ResizeObserver(scheduleUpdate);
            if (targetRef.value) {
                resizeObserver.observe(targetRef.value);
            }
        }

        scheduleUpdate();
        window.setTimeout(scheduleUpdate, 100);
        window.setTimeout(scheduleUpdate, 500);
    });

    onBeforeUnmount(() => {
        cancelAnimationFrame(raf);
        window.removeEventListener("scroll", scheduleUpdate);
        window.removeEventListener("resize", scheduleUpdate);
        resizeObserver?.disconnect();

        const target = targetRef.value;
        if (target) {
            target.style.removeProperty("--imas-sticky-top");
            clearInlinePosition(target);
        }
    });
}
