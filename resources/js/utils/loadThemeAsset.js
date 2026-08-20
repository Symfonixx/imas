/**
 * Idempotent theme asset loaders (Find Houses scripts/styles under /theme/findhouses).
 */

const loadedScripts = new Map();
const loadedStyles = new Map();

/**
 * @param {string} src Absolute or root-relative URL
 * @returns {Promise<void>}
 */
export function loadScript(src) {
    if (typeof document === "undefined") {
        return Promise.resolve();
    }

    if (loadedScripts.has(src)) {
        return loadedScripts.get(src);
    }

    const existing = document.querySelector(`script[src="${src}"]`);
    if (existing) {
        const done = Promise.resolve();
        loadedScripts.set(src, done);
        return done;
    }

    const promise = new Promise((resolve, reject) => {
        const script = document.createElement("script");
        script.src = src;
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () =>
            reject(new Error(`Failed to load script: ${src}`));
        document.body.appendChild(script);
    });

    loadedScripts.set(src, promise);
    return promise;
}

/**
 * @param {string} href Absolute or root-relative URL
 * @returns {Promise<void>}
 */
export function loadStylesheet(href) {
    if (typeof document === "undefined") {
        return Promise.resolve();
    }

    if (loadedStyles.has(href)) {
        return loadedStyles.get(href);
    }

    const existing = document.querySelector(`link[href="${href}"]`);
    if (existing) {
        const done = Promise.resolve();
        loadedStyles.set(href, done);
        return done;
    }

    const promise = new Promise((resolve, reject) => {
        const link = document.createElement("link");
        link.rel = "stylesheet";
        link.href = href;
        link.onload = () => resolve();
        link.onerror = () =>
            reject(new Error(`Failed to load stylesheet: ${href}`));
        document.head.appendChild(link);
    });

    loadedStyles.set(href, promise);
    return promise;
}

/**
 * @param {string} [themeUrl='/theme/findhouses']
 * @returns {Promise<void>}
 */
export function loadOwlCarousel(themeUrl = "/theme/findhouses") {
    const base = themeUrl.replace(/\/$/, "");
    return loadStylesheet(`${base}/css/owl.carousel.min.css`).then(() =>
        loadScript(`${base}/js/owl.carousel.min.js`),
    );
}
