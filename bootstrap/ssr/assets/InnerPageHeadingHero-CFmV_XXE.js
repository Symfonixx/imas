import { d as resolveYoutubeHeroBackgroundSrc } from "../ssr.js";
import { Link, usePage } from "@inertiajs/vue3";
import { computed, createTextVNode, mergeProps, onBeforeUnmount, onMounted, ref, toDisplayString, unref, useSSRContext, watch, withCtx } from "vue";
import { ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderClass, ssrRenderComponent, ssrRenderList, ssrRenderStyle } from "vue/server-renderer";
//#region resources/js/composables/useYoutubeHeroPlayer.js
var YT_SCRIPT_ID = "imas-youtube-iframe-api";
function loadYoutubeIframeApi() {
	if (typeof window === "undefined") return Promise.resolve(null);
	if (window.YT?.Player) return Promise.resolve(window.YT);
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
function useYoutubeHeroPlayer(iframeRef, enabled) {
	let player = null;
	let stopped = false;
	async function initPlayer() {
		if (stopped || !enabled.value || !iframeRef.value) return;
		const YT = await loadYoutubeIframeApi();
		if (stopped || !enabled.value || !iframeRef.value || !YT?.Player) return;
		destroyPlayer();
		player = new YT.Player(iframeRef.value, { events: {
			onReady(event) {
				event.target.mute();
				event.target.playVideo();
			},
			onStateChange(event) {
				if (event.data === YT.PlayerState.ENDED) {
					event.target.seekTo(0, true);
					event.target.playVideo();
				}
			}
		} });
	}
	function destroyPlayer() {
		try {
			player?.destroy?.();
		} catch {}
		player = null;
	}
	watch(() => enabled.value && iframeRef.value, (ready) => {
		if (!ready) {
			destroyPlayer();
			return;
		}
		initPlayer();
	}, { flush: "post" });
	onBeforeUnmount(() => {
		stopped = true;
		destroyPlayer();
	});
}
//#endregion
//#region resources/js/components/global/InnerPageHeadingHero.vue
/** Arabic and related scripts need cursive joining — do not split per character. */
var _sfc_main = {
	__name: "InnerPageHeadingHero",
	__ssrInlineRender: true,
	props: {
		pageTitle: {
			type: String,
			required: true
		},
		/** @type {InnerPageHeadingCrumb[]} */
		items: {
			type: Array,
			default: () => []
		},
		bannerImageUrl: {
			type: String,
			default: ""
		},
		/** YouTube iframe HTML, watch URL, or embed URL for a muted autoplay hero background. */
		bannerVideoEmbed: {
			type: String,
			default: ""
		},
		/** Title casing for Latin letter-by-letter / compact hero (connected scripts stay unchanged). */
		capitalizeTitle: {
			type: Boolean,
			default: true
		}
	},
	setup(__props) {
		const CONNECTED_SCRIPT_RE = /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB50-\uFDFF\uFE70-\uFEFF]/u;
		/**
		* @typedef {{ title: string, href?: string | null }} InnerPageHeadingCrumb
		*/
		const props = __props;
		const page = usePage();
		const heroBgRef = ref(null);
		const heroVideoIframeRef = ref(null);
		const prefersCompactHeroTitle = ref(false);
		let compactTitleMq = null;
		function capitalizeHeroTitle(text) {
			const locale = String(page.props.locale ?? "en");
			return String(text || "").toLocaleLowerCase(locale).replace(/(\p{L})(\p{L}*)/gu, (_, first, rest) => first.toLocaleUpperCase(locale) + rest);
		}
		function usesConnectedScript(text) {
			return CONNECTED_SCRIPT_RE.test(String(text || ""));
		}
		const titleUsesConnectedScript = computed(() => {
			if (usesConnectedScript(props.pageTitle)) return true;
			const locale = String(page.props.locale ?? "");
			const dir = String(page.props.text_direction ?? "");
			return locale === "ar" || dir === "rtl";
		});
		/** Narrow viewports: avoid per-letter flex wrap (e.g. "PROPERTY LIS" / "TINGS"). */
		const titleUsesConnectedTitle = computed(() => titleUsesConnectedScript.value || prefersCompactHeroTitle.value);
		const displayTitle = computed(() => {
			const raw = String(props.pageTitle || "");
			if (titleUsesConnectedTitle.value) return titleUsesConnectedScript.value ? raw : props.capitalizeTitle ? capitalizeHeroTitle(raw) : raw;
			return props.capitalizeTitle ? capitalizeHeroTitle(raw) : raw;
		});
		const titleLetters = computed(() => displayTitle.value.split(""));
		const heroVideoSrc = computed(() => resolveYoutubeHeroBackgroundSrc(props.bannerVideoEmbed));
		const hasVideoBackground = computed(() => Boolean(heroVideoSrc.value));
		useYoutubeHeroPlayer(heroVideoIframeRef, hasVideoBackground);
		const bgStyle = computed(() => {
			if (hasVideoBackground.value) return;
			const url = typeof props.bannerImageUrl === "string" ? props.bannerImageUrl.trim() : "";
			if (!url || /\/default\.jpg(?:\?.*)?$/i.test(url)) return;
			return {
				backgroundImage: `linear-gradient(
            color-mix(in srgb, var(--brand-navy-hover) 72%, transparent),
            color-mix(in srgb, var(--bg) 88%, transparent)
        ), url("${url}")`,
				backgroundSize: "cover",
				backgroundPosition: "center"
			};
		});
		function syncCompactHeroTitle() {
			if (typeof window === "undefined" || !window.matchMedia) {
				prefersCompactHeroTitle.value = false;
				return;
			}
			prefersCompactHeroTitle.value = window.matchMedia("(max-width: 640px)").matches;
		}
		function onScroll() {
			if (prefersCompactHeroTitle.value || hasVideoBackground.value) {
				if (heroBgRef.value) heroBgRef.value.style.transform = "";
				return;
			}
			const y = window.scrollY;
			if (heroBgRef.value && y < 700) heroBgRef.value.style.transform = `translateY(${y * .35}px)`;
		}
		function onCompactTitleMqChange() {
			syncCompactHeroTitle();
			onScroll();
		}
		onMounted(() => {
			syncCompactHeroTitle();
			if (typeof window !== "undefined" && window.matchMedia) {
				compactTitleMq = window.matchMedia("(max-width: 640px)");
				compactTitleMq.addEventListener("change", onCompactTitleMqChange);
			}
			window.addEventListener("scroll", onScroll, { passive: true });
			onScroll();
		});
		onBeforeUnmount(() => {
			compactTitleMq?.removeEventListener("change", onCompactTitleMqChange);
			window.removeEventListener("scroll", onScroll);
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<header${ssrRenderAttrs(mergeProps({ class: ["imas-inner-page-heading-hero", { "imas-inner-page-heading-hero--video": hasVideoBackground.value }] }, _attrs))}><div class="${ssrRenderClass([{ "imas-inner-page-heading-hero__bg--video": hasVideoBackground.value }, "imas-inner-page-heading-hero__bg"])}" style="${ssrRenderStyle(bgStyle.value)}">`);
			if (heroVideoSrc.value) _push(`<iframe class="imas-inner-page-heading-hero__video"${ssrRenderAttr("src", heroVideoSrc.value)} title="" tabindex="-1" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" aria-hidden="true"></iframe>`);
			else _push(`<!---->`);
			_push(`</div><div class="imas-inner-page-heading-hero__inner"><h1 class="${ssrRenderClass([{ "imas-inner-page-heading-hero__title--connected": titleUsesConnectedTitle.value }, "imas-inner-page-heading-hero__title"])}"${ssrRenderAttr("aria-label", __props.pageTitle)}>`);
			if (titleUsesConnectedTitle.value) _push(`<span class="imas-inner-page-heading-hero__title-text">${ssrInterpolate(displayTitle.value)}</span>`);
			else {
				_push(`<!--[-->`);
				ssrRenderList(titleLetters.value, (ch, i) => {
					_push(`<span class="imas-inner-page-heading-hero__letter" style="${ssrRenderStyle({ animationDelay: `${120 + i * 90}ms` })}">${ssrInterpolate(ch === " " ? "\xA0" : ch)}</span>`);
				});
				_push(`<!--]-->`);
			}
			_push(`</h1>`);
			if (__props.items.length) {
				_push(`<nav class="imas-inner-page-heading-hero__crumbs" aria-label="Breadcrumb"><!--[-->`);
				ssrRenderList(__props.items, (item, idx) => {
					_push(`<!--[-->`);
					if (item.href) _push(ssrRenderComponent(unref(Link), {
						href: item.href,
						class: "imas-inner-page-heading-hero__crumb-link"
					}, {
						default: withCtx((_, _push, _parent, _scopeId) => {
							if (_push) _push(`${ssrInterpolate(item.title)}`);
							else return [createTextVNode(toDisplayString(item.title), 1)];
						}),
						_: 2
					}, _parent));
					else _push(`<span class="imas-inner-page-heading-hero__crumb-active">${ssrInterpolate(item.title)}</span>`);
					if (idx < __props.items.length - 1) _push(`<span class="imas-inner-page-heading-hero__crumb-sep" aria-hidden="true">/</span>`);
					else _push(`<!---->`);
					_push(`<!--]-->`);
				});
				_push(`<!--]--></nav>`);
			} else _push(`<!---->`);
			_push(`</div></header>`);
		};
	}
};
var _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("resources/js/components/global/InnerPageHeadingHero.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
//#endregion
export { _sfc_main as t };
