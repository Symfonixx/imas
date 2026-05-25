import { onBeforeUnmount, watch } from "vue";

const YT_SCRIPT_ID = "imas-youtube-iframe-api";

function loadYoutubeIframeApi() {
    if (typeof window === "undefined") {
        return Promise.resolve(null);
    }

    if (window.YT?.Player) {
        return Promise.resolve(window.YT);
    }

    return new Promise((resolve) => {
        const onReady = () => resolve(window.YT ?? null);
        const previous = window.onYouTubeIframeAPIReady;
        window.onYouTubeIframeAPIReady = () => {
            previous?.();
            onReady();
        };

        if (!document.getElementById(YT_SCRIPT_ID)) {
            const tag = document.createElement("script");
            tag.id = YT_SCRIPT_ID;
            tag.src = "https://www.youtube.com/iframe_api";
            tag.async = true;
            document.head.appendChild(tag);
        }
    });
}

/**
 * Bind YouTube IFrame API to a hero background iframe: muted autoplay + restart on end.
 *
 * @param {import('vue').Ref<HTMLIFrameElement|null>} iframeRef
 * @param {import('vue').ComputedRef<boolean>|import('vue').Ref<boolean>} enabled
 */
export function useYoutubeHeroPlayer(iframeRef, enabled) {
    let player = null;
    let stopped = false;

    async function initPlayer() {
        if (stopped || !enabled.value || !iframeRef.value) {
            return;
        }

        const YT = await loadYoutubeIframeApi();
        if (stopped || !enabled.value || !iframeRef.value || !YT?.Player) {
            return;
        }

        destroyPlayer();

        player = new YT.Player(iframeRef.value, {
            events: {
                onReady(event) {
                    event.target.mute();
                    event.target.playVideo();
                },
                onStateChange(event) {
                    if (event.data === YT.PlayerState.ENDED) {
                        event.target.seekTo(0, true);
                        event.target.playVideo();
                    }
                },
            },
        });
    }

    function destroyPlayer() {
        try {
            player?.destroy?.();
        } catch {
            /* player may not be fully initialized */
        }
        player = null;
    }

    watch(
        () => enabled.value && iframeRef.value,
        (ready) => {
            if (!ready) {
                destroyPlayer();
                return;
            }
            initPlayer();
        },
        { flush: "post" },
    );

    onBeforeUnmount(() => {
        stopped = true;
        destroyPlayer();
    });
}
