import { s as useGsap } from "./App-7O-qCwwD.js";
import { nextTick, onMounted, watch } from "vue";
//#region resources/js/composables/useScrollReveal.js
/** Shared scroll-reveal timings (slower, more visible motion). */
var PRESETS = {
	default: {
		ease: "power2.out",
		y: 56,
		x: 56,
		duration: 1.15,
		titleDuration: 1,
		itemDuration: .95,
		footerDuration: .85,
		panelDuration: 1.35,
		stagger: .18,
		staggerTight: .14
	},
	/** Home page: longer reveals and wider stagger between items. */
	home: {
		ease: "power2.out",
		y: 72,
		x: 64,
		duration: 1.55,
		titleDuration: 1.35,
		itemDuration: 1.25,
		footerDuration: 1.1,
		panelDuration: 1.85,
		stagger: .26,
		staggerTight: .22
	}
};
/**
* @param {object} options
* @param {'default' | 'home'} [options.preset]
*/
function resolveTiming(options) {
	return {
		...PRESETS[options.preset] ?? PRESETS.default,
		...options.y != null ? { y: options.y } : {},
		...options.duration != null ? { duration: options.duration } : {}
	};
}
/**
* Scroll-triggered reveal (plays once when the section enters the viewport).
*
* @param {import('vue').Ref<HTMLElement | null>} sectionRef
* @param {object} [options]
* @param {import('vue').Ref<boolean> | import('vue').ComputedRef<boolean> | (() => boolean)} [options.when] - Re-run when this becomes true (e.g. v-if sections).
* @param {'default' | 'home'} [options.preset] - Timing preset (`home` is slower).
* @param {'section' | 'panel' | 'cards' | 'carousel' | 'propertyListings' | 'blogShowArticle' | 'sections'} [options.variant]
* @param {string} [options.start] - ScrollTrigger start position.
* @param {string} [options.sectionSelector] - For `sections` variant (default `[data-imas-reveal]`).
* @param {number} [options.y]
* @param {number} [options.duration]
*/
function useScrollReveal(sectionRef, options = {}) {
	const { gsap, context, prefersReducedMotion, refreshScrollTrigger } = useGsap();
	const timing = resolveTiming(options);
	const { when = null, variant = "section", start = "top 88%", sectionSelector = "[data-imas-reveal]" } = options;
	const { y, duration } = timing;
	let hasPlayed = false;
	function buildAnimation(root) {
		const T = timing;
		const scrollTrigger = {
			trigger: root,
			start,
			once: true,
			toggleActions: "play none none none"
		};
		if (variant === "panel") {
			const panel = root.querySelector(".imas-tc-overview__panel, .imas-about-overview__panel") ?? root;
			gsap.from(panel, {
				opacity: 0,
				y: 64,
				scale: .94,
				duration: T.panelDuration,
				ease: T.ease,
				scrollTrigger
			});
			return;
		}
		if (variant === "cards") {
			const tl = gsap.timeline({
				scrollTrigger,
				defaults: { ease: T.ease }
			});
			const title = root.querySelector(".sec-title");
			const items = root.querySelectorAll(".portfolio-items > *, .imas-featured-slide, .imas-articles-slide, .service-1 .serv, .news-wrap .row > [class*='col-'], .imas-popular-slide");
			const footer = root.querySelector(".bg-all");
			if (title) tl.from(title, {
				opacity: 0,
				y: 40,
				duration: T.titleDuration
			}, 0);
			if (items.length) tl.from(items, {
				opacity: 0,
				y: 48,
				duration: T.itemDuration,
				stagger: T.stagger
			}, title ? "-=0.4" : 0);
			if (footer) tl.from(footer, {
				opacity: 0,
				y: 32,
				duration: T.footerDuration
			}, items.length ? "-=0.25" : title ? "-=0.15" : 0);
			if (!title && !items.length && !footer) gsap.from(root, {
				opacity: 0,
				y,
				duration,
				scrollTrigger
			});
			return;
		}
		if (variant === "propertyListings") {
			const tl = gsap.timeline({
				scrollTrigger,
				defaults: { ease: T.ease }
			});
			const heading = root.querySelector(".imas-property-listings-heading, .imas-inner-page-heading-hero");
			const toolbar = root.querySelector(".blog-pots .headings-2, .imas-property-listings-toolbar");
			const cards = root.querySelectorAll(".blog-pots .row > *:not(.imas-blog-show-article-col), .imas-property-listings__grid .row > *, .imas-tc-page .imas-tc-page-content, .imas-contact-page .contact-us .row > [class*='col-']");
			const sidebarBlocks = [...root.querySelectorAll("aside.car > *, aside.imas-blog-listing-sidebar > *, aside.imas-blog-v2-sidebar > *")].filter((el) => !el.closest(".imas-about-page"));
			const pagination = root.querySelector("nav.agents, nav.imas-blog-pagination, nav.imas-blog-v2-pagination");
			if (heading) tl.from(heading, {
				opacity: 0,
				y: 40,
				duration: T.titleDuration
			}, 0);
			if (toolbar) tl.from(toolbar, {
				opacity: 0,
				y: 36,
				duration: T.itemDuration
			}, heading ? "-=0.35" : 0);
			if (cards.length) tl.from(cards, {
				opacity: 0,
				y: 48,
				duration: T.itemDuration,
				stagger: T.staggerTight
			}, heading || toolbar ? "-=0.3" : 0);
			if (sidebarBlocks.length) tl.from(sidebarBlocks, {
				opacity: 0,
				y: 44,
				duration: T.itemDuration,
				stagger: T.stagger
			}, cards.length ? "-=0.5" : heading ? "-=0.35" : 0);
			if (pagination) tl.from(pagination, {
				opacity: 0,
				y: 32,
				duration: T.footerDuration
			}, "-=0.25");
			if (!heading && !toolbar && !cards.length && !sidebarBlocks.length && !pagination) gsap.from(root, {
				opacity: 0,
				y,
				duration,
				scrollTrigger
			});
			return;
		}
		if (variant === "blogShowArticle") {
			const tl = gsap.timeline({
				scrollTrigger: {
					trigger: root,
					start,
					once: true,
					toggleActions: "play none none none"
				},
				defaults: { ease: T.ease }
			});
			const header = root.querySelector(".imas-blog-show-article-text__header");
			const body = root.querySelector(".imas-blog-show-body");
			if (header) tl.from(header, {
				opacity: 0,
				y: 40,
				duration: T.titleDuration
			}, 0);
			if (body) tl.from(body, {
				opacity: 0,
				y: 48,
				duration: T.duration
			}, header ? "+=0.12" : 0);
			if (!header && !body) gsap.from(root, {
				opacity: 0,
				y,
				duration,
				ease: T.ease,
				scrollTrigger: {
					trigger: root,
					start,
					once: true,
					toggleActions: "play none none none"
				}
			});
			return;
		}
		if (variant === "sections") {
			const blocks = root.querySelectorAll(sectionSelector);
			if (!blocks.length) {
				gsap.from(root, {
					opacity: 0,
					y,
					duration,
					ease: T.ease,
					scrollTrigger
				});
				return;
			}
			blocks.forEach((el) => {
				const mode = el.getAttribute("data-imas-reveal") || "up";
				const fromVars = {
					opacity: 0,
					duration: T.duration,
					ease: T.ease,
					scrollTrigger: {
						trigger: el,
						start,
						once: true,
						toggleActions: "play none none none"
					}
				};
				if (mode === "aside") fromVars.x = T.x;
				else fromVars.y = T.y;
				gsap.from(el, fromVars);
			});
			return;
		}
		if (variant === "carousel") {
			const tl = gsap.timeline({
				scrollTrigger,
				defaults: { ease: T.ease }
			});
			const title = root.querySelector(".sec-title, .imas-custom-heading");
			const track = root.querySelector(".owl-carousel, .imas-popular-rail");
			if (title) tl.from(title, {
				opacity: 0,
				y: 40,
				duration: T.titleDuration
			}, 0);
			if (track) tl.from(track, {
				opacity: 0,
				y: 52,
				duration: T.duration
			}, title ? "-=0.35" : 0);
			else if (!title) gsap.from(root, {
				opacity: 0,
				y,
				duration,
				scrollTrigger
			});
			return;
		}
		gsap.from(root, {
			opacity: 0,
			y,
			duration,
			ease: T.ease,
			scrollTrigger
		});
	}
	function setup() {
		const root = sectionRef.value;
		if (!root || hasPlayed) return;
		if (when != null) {
			if (!(typeof when === "function" ? when() : when.value)) return;
		}
		if (prefersReducedMotion()) {
			hasPlayed = true;
			return;
		}
		context(() => {
			buildAnimation(root);
		}, sectionRef);
		hasPlayed = true;
		refreshScrollTrigger();
	}
	function scheduleSetup() {
		nextTick(() => {
			nextTick(setup);
		});
	}
	onMounted(scheduleSetup);
	if (when != null) watch(when, (active) => {
		if (active && !hasPlayed) scheduleSetup();
	});
}
//#endregion
export { useScrollReveal as t };
