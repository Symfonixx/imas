/**
 * Resolve a front-office video URL into an embeddable iframe src or direct file URL.
 *
 * @param {string|null|undefined} raw
 * @returns {{ type: 'iframe', src: string }|{ type: 'file', src: string }|null}
 */
export function resolveVideoPlayback(raw) {
    if (typeof raw !== "string") {
        return null;
    }

    const trimmed = raw.trim();
    if (trimmed === "") {
        return null;
    }

    const iframeSrc = extractIframeSrc(trimmed);
    if (iframeSrc) {
        return { type: "iframe", src: normalizeEmbedUrl(iframeSrc) };
    }

    if (isEmbeddableUrl(trimmed)) {
        return { type: "iframe", src: normalizeEmbedUrl(trimmed) };
    }

    const youtubeId = extractYoutubeId(trimmed);
    if (youtubeId) {
        return {
            type: "iframe",
            src: `https://www.youtube.com/embed/${youtubeId}`,
        };
    }

    const vimeoId = extractVimeoId(trimmed);
    if (vimeoId) {
        return {
            type: "iframe",
            src: `https://player.vimeo.com/video/${vimeoId}`,
        };
    }

    if (/\.(mp4|webm|ogg)(\?.*)?$/i.test(trimmed)) {
        return { type: "file", src: trimmed };
    }

    return null;
}

/**
 * @param {string} embedSrc
 * @param {{ autoplay?: boolean }} [options]
 */
export function withVideoAutoplay(embedSrc, options = {}) {
    const autoplay = options.autoplay !== false;

    if (!autoplay || typeof embedSrc !== "string" || embedSrc === "") {
        return embedSrc;
    }

    try {
        const url = new URL(embedSrc, window.location.origin);
        url.searchParams.set("autoplay", "1");
        url.searchParams.set("rel", "0");
        if (url.hostname.includes("youtube.com")) {
            url.searchParams.set("modestbranding", "1");
        }
        return url.toString();
    } catch {
        const joiner = embedSrc.includes("?") ? "&" : "?";
        return `${embedSrc}${joiner}autoplay=1`;
    }
}

function extractIframeSrc(value) {
    const match = value.match(/<iframe[^>]+src=["']([^"']+)["']/i);
    return match?.[1]?.trim() ?? "";
}

function isEmbeddableUrl(value) {
    return (
        /youtube\.com\/embed\//i.test(value) ||
        /player\.vimeo\.com\/video\//i.test(value)
    );
}

function extractYoutubeId(value) {
    const patterns = [
        /youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/i,
        /youtube\.com\/watch\?[^#]*v=([a-zA-Z0-9_-]{11})/i,
        /youtu\.be\/([a-zA-Z0-9_-]{11})/i,
        /youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/i,
    ];

    for (const pattern of patterns) {
        const match = value.match(pattern);
        if (match?.[1]) {
            return match[1];
        }
    }

    return null;
}

function extractVimeoId(value) {
    const match = value.match(/vimeo\.com\/(?:video\/)?(\d+)/i);
    return match?.[1] ?? null;
}

function normalizeEmbedUrl(src) {
    if (src.startsWith("//")) {
        return `https:${src}`;
    }

    return src;
}
