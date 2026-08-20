//#region resources/js/utils/loadThemeAsset.js
/**
* Idempotent theme asset loaders (Find Houses scripts/styles under /theme/findhouses).
*/
var loadedScripts = /* @__PURE__ */ new Map();
var loadedStyles = /* @__PURE__ */ new Map();
/**
* @param {string} src Absolute or root-relative URL
* @returns {Promise<void>}
*/
function loadScript(src) {
	if (typeof document === "undefined") return Promise.resolve();
	if (loadedScripts.has(src)) return loadedScripts.get(src);
	if (document.querySelector(`script[src="${src}"]`)) {
		const done = Promise.resolve();
		loadedScripts.set(src, done);
		return done;
	}
	const promise = new Promise((resolve, reject) => {
		const script = document.createElement("script");
		script.src = src;
		script.async = true;
		script.onload = () => resolve();
		script.onerror = () => reject(/* @__PURE__ */ new Error(`Failed to load script: ${src}`));
		document.body.appendChild(script);
	});
	loadedScripts.set(src, promise);
	return promise;
}
/**
* @param {string} href Absolute or root-relative URL
* @returns {Promise<void>}
*/
function loadStylesheet(href) {
	if (typeof document === "undefined") return Promise.resolve();
	if (loadedStyles.has(href)) return loadedStyles.get(href);
	if (document.querySelector(`link[href="${href}"]`)) {
		const done = Promise.resolve();
		loadedStyles.set(href, done);
		return done;
	}
	const promise = new Promise((resolve, reject) => {
		const link = document.createElement("link");
		link.rel = "stylesheet";
		link.href = href;
		link.onload = () => resolve();
		link.onerror = () => reject(/* @__PURE__ */ new Error(`Failed to load stylesheet: ${href}`));
		document.head.appendChild(link);
	});
	loadedStyles.set(href, promise);
	return promise;
}
/**
* @param {string} [themeUrl='/theme/findhouses']
* @returns {Promise<void>}
*/
function loadOwlCarousel(themeUrl = "/theme/findhouses") {
	const base = themeUrl.replace(/\/$/, "");
	return loadStylesheet(`${base}/css/owl.carousel.min.css`).then(() => loadScript(`${base}/js/owl.carousel.min.js`));
}
//#endregion
export { loadOwlCarousel, loadScript, loadStylesheet };
