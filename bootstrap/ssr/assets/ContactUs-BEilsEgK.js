import { u as _plugin_vue_export_helper_default } from "../ssr.js";
import { i as normalizeTurkishPhoneDigits, n as resolveWhatsAppContactHref, o as localizedRoute, r as formatTurkishPhone, t as _sfc_main$3 } from "./App-Tm_yWILr.js";
import { t as useScrollReveal } from "./useScrollReveal-DCquZn8b.js";
import { t as _sfc_main$4 } from "./InnerPageHeadingHero-CFmV_XXE.js";
import { t as ContactForm_default } from "./ContactForm-B4yFwPE1.js";
import { Head, usePage } from "@inertiajs/vue3";
import { computed, createBlock, createCommentVNode, createVNode, mergeProps, openBlock, ref, unref, useSSRContext, withCtx } from "vue";
import { ssrIncludeBooleanAttr, ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderClass, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
//#region Modules/Support/resources/assets/js/Components/ContactDetails.vue
var _sfc_main$2 = {
	__name: "ContactDetails",
	__ssrInlineRender: true,
	setup(__props) {
		const page = usePage();
		const globals = computed(() => page.props.globals ?? {});
		const settings = computed(() => page.props.settings ?? {});
		const contact = computed(() => globals.value.contact ?? {});
		const rawPhone = computed(() => String(contact.value.phone ?? "").trim());
		const phoneDisplay = computed(() => formatTurkishPhone(rawPhone.value) || rawPhone.value);
		const phoneHref = computed(() => {
			const raw = rawPhone.value;
			if (!raw) return "";
			const social = globals.value.social ?? {};
			const normalized = normalizeTurkishPhoneDigits(raw);
			const phoneForWhatsApp = normalized ? `+${normalized}` : raw;
			return resolveWhatsAppContactHref({
				whatsapp: social.whatsapp || settings.value.whatsapp,
				phone: phoneForWhatsApp
			});
		});
		const socialLinks = computed(() => {
			const s = globals.value.social ?? {};
			return [
				{
					key: "facebook",
					label: "Facebook",
					icon: "fa fa-facebook"
				},
				{
					key: "twitter",
					label: "Twitter",
					icon: "fa fa-twitter"
				},
				{
					key: "instagram",
					label: "Instagram",
					icon: "fab fa-instagram"
				},
				{
					key: "youtube",
					label: "YouTube",
					icon: "fa fa-youtube"
				},
				{
					key: "tiktok",
					label: "TikTok",
					icon: "fab fa-tiktok"
				},
				{
					key: "whatsapp",
					label: "WhatsApp",
					icon: "fa fa-whatsapp"
				}
			].map((d) => {
				const raw = String(s[d.key] ?? "").trim();
				if (!raw) return null;
				const href = d.key === "whatsapp" ? resolveWhatsAppContactHref({
					whatsapp: raw,
					phone: contact.value.phone
				}) : raw;
				if (!href) return null;
				return {
					...d,
					href
				};
			}).filter(Boolean);
		});
		const headOfficePrefix = computed(() => {
			const value = trans("contact_us.head_office_prefix");
			return value === "contact_us.head_office_prefix" ? "" : value.trim();
		});
		const headOfficeSuffix = computed(() => {
			const value = trans("contact_us.head_office_suffix");
			return value === "contact_us.head_office_suffix" ? "" : value.trim();
		});
		function trans(key) {
			return page.props.translations[key] || key;
		}
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "call-info imas-contact-page__details" }, _attrs))} data-v-8035b963><h3 class="imas-contact-page__heading text-xl font-semibold text-start" data-v-8035b963>${ssrInterpolate(trans("contact_us.contact_details"))}</h3><p class="imas-contact-page__intro text-card-excerpt text-dim mb-5 text-start" data-v-8035b963>${ssrInterpolate(trans("contact_us.Please_find_below_contact_details_and_contact_us_today"))}</p><div class="imas-contact-page__head-office mb-4 text-start" data-v-8035b963><p class="imas-contact-page__head-office-title mb-1" data-v-8035b963>`);
			if (headOfficePrefix.value) _push(`<!--[-->${ssrInterpolate(headOfficePrefix.value)}<!--]-->`);
			else _push(`<!---->`);
			_push(`<span class="imas-contact-page__head-office-brand" data-v-8035b963>IMAS GLOBAL</span>`);
			if (headOfficeSuffix.value) _push(`<!--[-->${ssrInterpolate(" ")}${ssrInterpolate(headOfficeSuffix.value)}<!--]-->`);
			else _push(`<!---->`);
			_push(`</p><p class="imas-contact-page__head-office-location mb-0" data-v-8035b963>${ssrInterpolate(trans("navBar.footer_location"))}</p></div><ul data-v-8035b963>`);
			if (contact.value.address) _push(`<li data-v-8035b963><div class="info text-start" data-v-8035b963><i class="fa fa-map-marker m-end" aria-hidden="true" data-v-8035b963></i><p class="in-p" data-v-8035b963>${ssrInterpolate(contact.value.address)}</p></div></li>`);
			else _push(`<!---->`);
			if (phoneDisplay.value) {
				_push(`<li class="imas-contact-phone" data-v-8035b963><div class="info" data-v-8035b963><i class="fa fa-phone m-end" aria-hidden="true" data-v-8035b963></i><p class="in-p in-p--phone" dir="ltr" data-v-8035b963>`);
				if (phoneHref.value) _push(`<a${ssrRenderAttr("href", phoneHref.value)} target="_blank" rel="noopener noreferrer" data-v-8035b963>${ssrInterpolate(phoneDisplay.value)}</a>`);
				else _push(`<!--[-->${ssrInterpolate(phoneDisplay.value)}<!--]-->`);
				_push(`</p></div></li>`);
			} else _push(`<!---->`);
			if (contact.value.email) _push(`<li class="imas-contact-email" data-v-8035b963><div class="info" data-v-8035b963><i class="fa fa-envelope m-end" aria-hidden="true" data-v-8035b963></i><p class="in-p ti" data-v-8035b963><a${ssrRenderAttr("href", "mailto:" + contact.value.email)} data-v-8035b963>${ssrInterpolate(contact.value.email)}</a></p></div></li>`);
			else _push(`<!---->`);
			_push(`</ul>`);
			if (socialLinks.value.length) {
				_push(`<!--[--><h4 class="imas-contact-page__social-title text-lg font-semibold mt-4 mb-3 text-start" data-v-8035b963>${ssrInterpolate(trans("contact_us.follow_us"))}</h4><ul class="netsocials d-flex flex-wrap" data-v-8035b963><!--[-->`);
				ssrRenderList(socialLinks.value, (item) => {
					_push(`<li data-v-8035b963><a${ssrRenderAttr("href", item.href)} target="_blank" rel="noopener noreferrer"${ssrRenderAttr("aria-label", item.label)} data-v-8035b963><i class="${ssrRenderClass(item.icon)}" aria-hidden="true" data-v-8035b963></i></a></li>`);
				});
				_push(`<!--]--></ul><!--]-->`);
			} else _push(`<!---->`);
			_push(`</div>`);
		};
	}
};
var _sfc_setup$2 = _sfc_main$2.setup;
_sfc_main$2.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Support/resources/assets/js/Components/ContactDetails.vue");
	return _sfc_setup$2 ? _sfc_setup$2(props, ctx) : void 0;
};
var ContactDetails_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main$2, [["__scopeId", "data-v-8035b963"]]);
//#endregion
//#region Modules/Support/resources/assets/js/Components/ContactFaq.vue
var _sfc_main$1 = {
	__name: "ContactFaq",
	__ssrInlineRender: true,
	setup(__props) {
		const page = usePage();
		const openIndex = ref(-1);
		function trans(key) {
			return page.props.translations[key] ?? key;
		}
		const title = computed(() => trans("contact_us.faq.title"));
		const subtitle = computed(() => {
			const value = trans("contact_us.faq.subtitle");
			return value === "contact_us.faq.subtitle" ? "" : value;
		});
		const items = computed(() => {
			const raw = page.props.translations["contact_us.faq.items"];
			if (!Array.isArray(raw)) return [];
			return raw.filter((item) => item && typeof item.question === "string" && item.question.trim() !== "" && typeof item.answer === "string" && item.answer.trim() !== "");
		});
		return (_ctx, _push, _parent, _attrs) => {
			if (items.value.length) {
				_push(`<section${ssrRenderAttrs(mergeProps({
					class: "imas-contact-faq imas-contact-page__panel imas-contact-page__panel--faq",
					"aria-labelledby": "imas-contact-faq-title"
				}, _attrs))}><header class="imas-contact-faq__header text-start"><h3 id="imas-contact-faq-title" class="imas-contact-page__heading imas-contact-faq__title text-xl font-semibold text-start">${ssrInterpolate(title.value)}</h3>`);
				if (subtitle.value) _push(`<p class="imas-contact-faq__subtitle text-card-excerpt text-dim text-start">${ssrInterpolate(subtitle.value)}</p>`);
				else _push(`<!---->`);
				_push(`</header><ul class="imas-contact-faq__list"><!--[-->`);
				ssrRenderList(items.value, (item, index) => {
					_push(`<li class="${ssrRenderClass([{ "imas-contact-faq__item--open": openIndex.value === index }, "imas-contact-faq__item"])}"><div class="imas-contact-faq__item-inner"><button type="button" class="imas-contact-faq__trigger"${ssrRenderAttr("aria-expanded", openIndex.value === index)}${ssrRenderAttr("aria-controls", `imas-contact-faq-panel-${index}`)}><span class="imas-contact-faq__question text-start">${ssrInterpolate(item.question)}</span><span class="imas-contact-faq__icon" aria-hidden="true"></span></button><div${ssrRenderAttr("id", `imas-contact-faq-panel-${index}`)} class="imas-contact-faq__content"${ssrIncludeBooleanAttr(openIndex.value !== index) ? " hidden" : ""}><p class="imas-contact-faq__answer text-card-excerpt text-dim text-start">${ssrInterpolate(item.answer)}</p></div></div></li>`);
				});
				_push(`<!--]--></ul></section>`);
			} else _push(`<!---->`);
		};
	}
};
var _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Support/resources/assets/js/Components/ContactFaq.vue");
	return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
//#endregion
//#region Modules/Support/resources/assets/js/Pages/ContactUs.vue
var _sfc_main = {
	__name: "ContactUs",
	__ssrInlineRender: true,
	props: { contactStoreUrl: {
		type: String,
		required: true
	} },
	setup(__props) {
		const page = usePage();
		const activeLocale = computed(() => page.props.locale || "en");
		const pageRef = ref(null);
		useScrollReveal(pageRef, { variant: "propertyListings" });
		const globals = computed(() => page.props.globals ?? {});
		const seo = computed(() => globals.value.seo ?? {});
		const media = computed(() => globals.value.media ?? {});
		function pickSeoString(...keys) {
			const s = seo.value;
			for (const key of keys) {
				const v = s[key];
				if (typeof v === "string" && v.trim() !== "") return v.trim();
			}
			return "";
		}
		const contactUsBannerUrl = computed(() => {
			const url = media.value.contact_us_banner;
			if (typeof url !== "string" || url.trim() === "") return "";
			const trimmed = url.trim();
			if (/\/default\.jpg(?:\?.*)?$/i.test(trimmed)) return "";
			return trimmed;
		});
		function trans(key) {
			return page.props.translations[key] || key;
		}
		const documentTitle = computed(() => `${trans("contact_us.title")} | ${page.props.appName}`);
		const metaDescription = computed(() => pickSeoString("site_meta_description", "website_desc"));
		const metaKeywords = computed(() => pickSeoString("site_meta_keywords", "website_keywords"));
		const ogTitle = computed(() => documentTitle.value);
		const ogDescription = computed(() => metaDescription.value);
		const ogImage = computed(() => {
			const banner = media.value.contact_us_banner;
			if (typeof banner === "string" && banner.trim() !== "") {
				const trimmed = banner.trim();
				if (!/\/default\.jpg(?:\?.*)?$/i.test(trimmed)) return trimmed;
			}
			const fallback = media.value.meta_img;
			return typeof fallback === "string" && fallback.trim() !== "" ? fallback.trim() : "";
		});
		const canonicalUrl = computed(() => localizedRoute("support.contact-us", {}, activeLocale.value, "/contact-us"));
		const ogUrl = computed(() => canonicalUrl.value);
		const twitterCard = computed(() => ogImage.value ? "summary_large_image" : "summary");
		const blogHeadingItems = computed(() => {
			const rows = [];
			try {
				if (typeof route === "function" && route().has?.("home")) rows.push({
					title: trans("navBar.Home"),
					href: route("home")
				});
			} catch {}
			rows.push({
				title: trans("navBar.Contact us"),
				href: null
			});
			return rows;
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: documentTitle.value }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						if (metaDescription.value) _push(`<meta head-key="description" name="description"${ssrRenderAttr("content", metaDescription.value)}${_scopeId}>`);
						else _push(`<!---->`);
						if (metaKeywords.value) _push(`<meta head-key="keywords" name="keywords"${ssrRenderAttr("content", metaKeywords.value)}${_scopeId}>`);
						else _push(`<!---->`);
						if (canonicalUrl.value) _push(`<link head-key="canonical" rel="canonical"${ssrRenderAttr("href", canonicalUrl.value)}${_scopeId}>`);
						else _push(`<!---->`);
						if (ogTitle.value) _push(`<meta head-key="og:title" property="og:title"${ssrRenderAttr("content", ogTitle.value)}${_scopeId}>`);
						else _push(`<!---->`);
						if (ogDescription.value) _push(`<meta head-key="og:description" property="og:description"${ssrRenderAttr("content", ogDescription.value)}${_scopeId}>`);
						else _push(`<!---->`);
						if (ogImage.value) _push(`<meta head-key="og:image" property="og:image"${ssrRenderAttr("content", ogImage.value)}${_scopeId}>`);
						else _push(`<!---->`);
						_push(`<meta head-key="og:type" property="og:type" content="website"${_scopeId}>`);
						if (ogUrl.value) _push(`<meta head-key="og:url" property="og:url"${ssrRenderAttr("content", ogUrl.value)}${_scopeId}>`);
						else _push(`<!---->`);
						_push(`<meta head-key="twitter:card" name="twitter:card"${ssrRenderAttr("content", twitterCard.value)}${_scopeId}>`);
						if (ogTitle.value) _push(`<meta head-key="twitter:title" name="twitter:title"${ssrRenderAttr("content", ogTitle.value)}${_scopeId}>`);
						else _push(`<!---->`);
						if (ogDescription.value) _push(`<meta head-key="twitter:description" name="twitter:description"${ssrRenderAttr("content", ogDescription.value)}${_scopeId}>`);
						else _push(`<!---->`);
						if (ogImage.value) _push(`<meta head-key="twitter:image" name="twitter:image"${ssrRenderAttr("content", ogImage.value)}${_scopeId}>`);
						else _push(`<!---->`);
					} else return [
						metaDescription.value ? (openBlock(), createBlock("meta", {
							key: 0,
							"head-key": "description",
							name: "description",
							content: metaDescription.value
						}, null, 8, ["content"])) : createCommentVNode("", true),
						metaKeywords.value ? (openBlock(), createBlock("meta", {
							key: 1,
							"head-key": "keywords",
							name: "keywords",
							content: metaKeywords.value
						}, null, 8, ["content"])) : createCommentVNode("", true),
						canonicalUrl.value ? (openBlock(), createBlock("link", {
							key: 2,
							"head-key": "canonical",
							rel: "canonical",
							href: canonicalUrl.value
						}, null, 8, ["href"])) : createCommentVNode("", true),
						ogTitle.value ? (openBlock(), createBlock("meta", {
							key: 3,
							"head-key": "og:title",
							property: "og:title",
							content: ogTitle.value
						}, null, 8, ["content"])) : createCommentVNode("", true),
						ogDescription.value ? (openBlock(), createBlock("meta", {
							key: 4,
							"head-key": "og:description",
							property: "og:description",
							content: ogDescription.value
						}, null, 8, ["content"])) : createCommentVNode("", true),
						ogImage.value ? (openBlock(), createBlock("meta", {
							key: 5,
							"head-key": "og:image",
							property: "og:image",
							content: ogImage.value
						}, null, 8, ["content"])) : createCommentVNode("", true),
						createVNode("meta", {
							"head-key": "og:type",
							property: "og:type",
							content: "website"
						}),
						ogUrl.value ? (openBlock(), createBlock("meta", {
							key: 6,
							"head-key": "og:url",
							property: "og:url",
							content: ogUrl.value
						}, null, 8, ["content"])) : createCommentVNode("", true),
						createVNode("meta", {
							"head-key": "twitter:card",
							name: "twitter:card",
							content: twitterCard.value
						}, null, 8, ["content"]),
						ogTitle.value ? (openBlock(), createBlock("meta", {
							key: 7,
							"head-key": "twitter:title",
							name: "twitter:title",
							content: ogTitle.value
						}, null, 8, ["content"])) : createCommentVNode("", true),
						ogDescription.value ? (openBlock(), createBlock("meta", {
							key: 8,
							"head-key": "twitter:description",
							name: "twitter:description",
							content: ogDescription.value
						}, null, 8, ["content"])) : createCommentVNode("", true),
						ogImage.value ? (openBlock(), createBlock("meta", {
							key: 9,
							"head-key": "twitter:image",
							name: "twitter:image",
							content: ogImage.value
						}, null, 8, ["content"])) : createCommentVNode("", true)
					];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(_sfc_main$3, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) {
						_push(`<div class="inner-pages imas-contact-page"${_scopeId}>`);
						_push(ssrRenderComponent(_sfc_main$4, {
							"page-title": trans("contact_us.title"),
							items: blogHeadingItems.value,
							"banner-image-url": contactUsBannerUrl.value
						}, null, _parent, _scopeId));
						_push(`<section class="contact-us imas-contact-page__section"${_scopeId}><div class="container"${_scopeId}><div class="row g-4"${_scopeId}><div class="col-lg-8 col-md-12"${_scopeId}><div class="imas-contact-page__panel imas-contact-page__panel--form"${_scopeId}>`);
						_push(ssrRenderComponent(ContactForm_default, {
							"contact-store-url": __props.contactStoreUrl,
							"source-page": trans("contact_us.title")
						}, null, _parent, _scopeId));
						_push(`</div></div><div class="col-lg-4 col-md-12"${_scopeId}><div class="imas-contact-page__panel imas-contact-page__panel--details"${_scopeId}>`);
						_push(ssrRenderComponent(ContactDetails_default, null, null, _parent, _scopeId));
						_push(`</div></div></div><div class="row g-4 imas-contact-page__faq-row"${_scopeId}><div class="col-12"${_scopeId}>`);
						_push(ssrRenderComponent(_sfc_main$1, null, null, _parent, _scopeId));
						_push(`</div></div></div></section></div>`);
					} else return [createVNode("div", {
						ref_key: "pageRef",
						ref: pageRef,
						class: "inner-pages imas-contact-page"
					}, [createVNode(_sfc_main$4, {
						"page-title": trans("contact_us.title"),
						items: blogHeadingItems.value,
						"banner-image-url": contactUsBannerUrl.value
					}, null, 8, [
						"page-title",
						"items",
						"banner-image-url"
					]), createVNode("section", { class: "contact-us imas-contact-page__section" }, [createVNode("div", { class: "container" }, [createVNode("div", { class: "row g-4" }, [createVNode("div", { class: "col-lg-8 col-md-12" }, [createVNode("div", { class: "imas-contact-page__panel imas-contact-page__panel--form" }, [createVNode(ContactForm_default, {
						"contact-store-url": __props.contactStoreUrl,
						"source-page": trans("contact_us.title")
					}, null, 8, ["contact-store-url", "source-page"])])]), createVNode("div", { class: "col-lg-4 col-md-12" }, [createVNode("div", { class: "imas-contact-page__panel imas-contact-page__panel--details" }, [createVNode(ContactDetails_default)])])]), createVNode("div", { class: "row g-4 imas-contact-page__faq-row" }, [createVNode("div", { class: "col-12" }, [createVNode(_sfc_main$1)])])])])], 512)];
				}),
				_: 1
			}, _parent));
			_push(`<!--]-->`);
		};
	}
};
var _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Support/resources/assets/js/Pages/ContactUs.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
//#endregion
export { _sfc_main as default };
