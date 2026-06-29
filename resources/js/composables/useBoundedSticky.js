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

/**
 * Pins `targetRef` while scrolling through `boundaryRef`, then releases at the boundary bottom.
 */
export function useBoundedSticky({
    boundaryRef,
    columnRef,
    targetRef,
    minWidth = 992,
}) {
    let raf = 0;
    let resizeObserver = null;

    function resetTargetStyles(target) {
        target.style.position = "";
        target.style.top = "";
        target.style.bottom = "";
        target.style.left = "";
        target.style.width = "";
        target.style.zIndex = "";
    }

    function update() {
        const boundary = boundaryRef.value;
        const column = columnRef.value;
        const target = targetRef.value;

        if (!boundary || !column || !target) {
            return;
        }

        if (window.innerWidth < minWidth) {
            resetTargetStyles(target);
            return;
        }

        const stick = getStickOffset();
        const boundaryRect = boundary.getBoundingClientRect();
        const columnRect = column.getBoundingClientRect();
        const targetHeight = target.offsetHeight;

        if (columnRect.top > stick) {
            resetTargetStyles(target);
            return;
        }

        if (boundaryRect.bottom <= stick + targetHeight) {
            resetTargetStyles(target);
            target.style.position = "absolute";
            target.style.bottom = "0";
            target.style.width = "100%";
            target.style.zIndex = "5";
            return;
        }

        target.style.position = "fixed";
        target.style.top = `${stick}px`;
        target.style.left = `${columnRect.left}px`;
        target.style.width = `${columnRect.width}px`;
        target.style.zIndex = "5";
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
            if (boundaryRef.value) {
                resizeObserver.observe(boundaryRef.value);
            }
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
            resetTargetStyles(target);
        }
    });
}
