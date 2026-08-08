import { f as _plugin_vue_export_helper_default, h as useOpenAuthModal } from "../ssr.js";
import { i as normalizeTurkishPhoneDigits, n as resolveWhatsAppContactHref, r as formatTurkishPhone } from "./App-DkOZMeWI.js";
import { t as ContactForm_default } from "./ContactForm-Ba_oo6of.js";
import { usePage } from "@inertiajs/vue3";
import { computed, mergeProps, onBeforeUnmount, onMounted, ref, useSSRContext, watch } from "vue";
import { ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderClass, ssrRenderComponent, ssrRenderList, ssrRenderStyle } from "vue/server-renderer";
//#region Modules/Property/resources/assets/js/components/PropertyShowContactSidebar.vue
var _sfc_main = {
	__name: "PropertyShowContactSidebar",
	__ssrInlineRender: true,
	props: {
		contactStoreUrl: {
			type: String,
			required: true
		},
		defaultSubject: {
			type: String,
			default: ""
		},
		defaultMessage: {
			type: String,
			default: ""
		},
		sourcePage: {
			type: String,
			default: ""
		},
		hideFormSubject: {
			type: Boolean,
			default: false
		},
		propertyId: {
			type: Number,
			default: null
		},
		isFavorited: {
			type: Boolean,
			default: false
		},
		isSoldOut: {
			type: Boolean,
			default: false
		}
	},
	setup(__props) {
		const props = __props;
		const canToggleFavorite = computed(() => props.propertyId != null && !props.isSoldOut);
		const effectiveDefaultSubject = computed(() => {
			if (!props.hideFormSubject) return props.defaultSubject;
			return shareUrl.value || props.defaultSubject;
		});
		const effectiveSourcePage = computed(() => {
			if (props.sourcePage) return props.sourcePage;
			if (!props.hideFormSubject) return props.defaultSubject;
			return "";
		});
		const page = usePage();
		const { openAuthModal } = useOpenAuthModal();
		computed(() => page.props.auth != null);
		const localFavorited = ref(Boolean(props.isFavorited));
		watch(() => props.isFavorited, (v) => {
			localFavorited.value = Boolean(v);
		});
		const favoriteAriaLabel = computed(() => localFavorited.value ? trans("properties.remove_favorite") : trans("properties.add_favorite"));
		const shareMenuRef = ref(null);
		const shareOpen = ref(false);
		const linkCopied = ref(false);
		const shareUrl = ref("");
		const isRtl = computed(() => {
			const dir = page.props.text_direction;
			if (dir === "rtl" || dir === "ltr") return dir === "rtl";
			return (page.props.locale || "en") === "ar";
		});
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
		const contactItems = computed(() => {
			const c = contact.value;
			const items = [];
			if (phoneDisplay.value) items.push({
				key: "phone",
				icon: "fa-phone",
				iconClass: "la-phone",
				text: phoneDisplay.value,
				href: phoneHref.value || null,
				external: Boolean(phoneHref.value)
			});
			if (c.email) items.push({
				key: "email",
				icon: "fa-envelope",
				iconClass: "la-envelope-o",
				text: c.email,
				href: `mailto:${c.email}`
			});
			return items;
		});
		function trans(key) {
			return page.props.translations[key] || key;
		}
		const shareLinks = computed(() => {
			const url = shareUrl.value;
			if (!url) return [];
			const encoded = encodeURIComponent(url);
			const title = encodeURIComponent(typeof document !== "undefined" ? document.title : "");
			return [
				{
					key: "facebook",
					label: "Facebook",
					icon: "fa fa-facebook",
					href: `https://www.facebook.com/sharer/sharer.php?u=${encoded}`
				},
				{
					key: "twitter",
					label: "X (Twitter)",
					icon: "fa fa-twitter",
					href: `https://twitter.com/intent/tweet?url=${encoded}&text=${title}`
				},
				{
					key: "linkedin",
					label: "LinkedIn",
					icon: "fa fa-linkedin",
					href: `https://www.linkedin.com/sharing/share-offsite/?url=${encoded}`
				},
				{
					key: "whatsapp",
					label: "WhatsApp",
					icon: "fa fa-whatsapp",
					href: `https://wa.me/?text=${encoded}`
				},
				{
					key: "email",
					label: trans("property_show.share_email"),
					icon: "fa fa-envelope",
					href: `mailto:?subject=${title}&body=${encoded}`
				}
			];
		});
		function closeShareMenu() {
			shareOpen.value = false;
			linkCopied.value = false;
		}
		function onDocumentClick(event) {
			const root = shareMenuRef.value;
			if (!root || !(event.target instanceof Node)) return;
			if (!root.contains(event.target)) closeShareMenu();
		}
		onMounted(() => {
			if (typeof window !== "undefined") shareUrl.value = window.location.href;
			document.addEventListener("click", onDocumentClick);
		});
		onBeforeUnmount(() => {
			document.removeEventListener("click", onDocumentClick);
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<div${ssrRenderAttrs(mergeProps({ class: "imas-blog-v2-sidebar__box imas-property-show-contact mt-33" }, _attrs))} data-v-d2c62f70><div class="imas-contact-sidebar-header" data-v-d2c62f70><h4 class="imas-blog-v2-sidebar__heading text-start mb-0" data-v-d2c62f70>${ssrInterpolate(trans("navBar.Contact us"))}</h4><div class="imas-contact-sidebar-actions" data-v-d2c62f70>`);
			if (canToggleFavorite.value) _push(`<button type="button" class="${ssrRenderClass([{ "is-favorited": localFavorited.value }, "imas-contact-favorite__toggle"])}"${ssrRenderAttr("aria-label", favoriteAriaLabel.value)}${ssrRenderAttr("aria-pressed", localFavorited.value)} data-v-d2c62f70><i class="${ssrRenderClass([localFavorited.value ? "fa-heart" : "fa-heart-o", "fa favorite-icon"])}" aria-hidden="true" data-v-d2c62f70></i></button>`);
			else _push(`<!---->`);
			_push(`<div class="imas-contact-share" data-v-d2c62f70><button type="button" class="imas-contact-share__toggle"${ssrRenderAttr("aria-label", trans("property_show.share_page"))}${ssrRenderAttr("aria-expanded", shareOpen.value ? "true" : "false")} aria-haspopup="true" data-v-d2c62f70><i class="fa fa-share-alt" aria-hidden="true" data-v-d2c62f70></i></button><div class="imas-contact-share__menu" role="menu" style="${ssrRenderStyle(shareOpen.value ? null : { display: "none" })}" data-v-d2c62f70><!--[-->`);
			ssrRenderList(shareLinks.value, (item) => {
				_push(`<a${ssrRenderAttr("href", item.href)} class="imas-contact-share__item" role="menuitem" target="_blank" rel="noopener noreferrer" data-v-d2c62f70><i class="${ssrRenderClass(item.icon)}" aria-hidden="true" data-v-d2c62f70></i><span data-v-d2c62f70>${ssrInterpolate(item.label)}</span></a>`);
			});
			_push(`<!--]--><button type="button" class="imas-contact-share__item imas-contact-share__item--button" role="menuitem" data-v-d2c62f70><i class="fa fa-link" aria-hidden="true" data-v-d2c62f70></i><span data-v-d2c62f70>${ssrInterpolate(linkCopied.value ? trans("property_show.link_copied") : trans("property_show.copy_link"))}</span></button></div></div></div></div><div class="imas-property-show-contact__body" data-v-d2c62f70><div class="sidebar-widget author-widget2" data-v-d2c62f70>`);
			if (contactItems.value.length) {
				_push(`<ul class="author__contact imas-contact-list" data-v-d2c62f70><!--[-->`);
				ssrRenderList(contactItems.value, (item) => {
					_push(`<li class="${ssrRenderClass([{ "imas-contact-list__item--rtl": isRtl.value }, "imas-contact-list__item"])}" data-v-d2c62f70>`);
					if (isRtl.value) {
						_push(`<!--[--><span class="imas-contact-list__label" data-v-d2c62f70>`);
						if (item.href) _push(`<a class="imas-contact-list__link"${ssrRenderAttr("href", item.href)}${ssrRenderAttr("target", item.external ? "_blank" : void 0)}${ssrRenderAttr("rel", item.external ? "noopener noreferrer" : void 0)} data-v-d2c62f70>${ssrInterpolate(item.text)}</a>`);
						else _push(`<!--[-->${ssrInterpolate(item.text)}<!--]-->`);
						_push(`</span><span class="${ssrRenderClass([item.iconClass, "imas-contact-list__icon la"])}" data-v-d2c62f70><i class="${ssrRenderClass(["fa", item.icon])}" aria-hidden="true" data-v-d2c62f70></i></span><!--]-->`);
					} else {
						_push(`<!--[--><span class="${ssrRenderClass([item.iconClass, "imas-contact-list__icon la"])}" data-v-d2c62f70><i class="${ssrRenderClass(["fa", item.icon])}" aria-hidden="true" data-v-d2c62f70></i></span><span class="imas-contact-list__label" data-v-d2c62f70>`);
						if (item.href) _push(`<a class="imas-contact-list__link"${ssrRenderAttr("href", item.href)}${ssrRenderAttr("target", item.external ? "_blank" : void 0)}${ssrRenderAttr("rel", item.external ? "noopener noreferrer" : void 0)} data-v-d2c62f70>${ssrInterpolate(item.text)}</a>`);
						else _push(`<!--[-->${ssrInterpolate(item.text)}<!--]-->`);
						_push(`</span><!--]-->`);
					}
					_push(`</li>`);
				});
				_push(`<!--]--></ul>`);
			} else _push(`<!---->`);
			_push(`<div class="agent-contact-form-sidebar imas-property-show-contact__form" data-v-d2c62f70><h4 class="imas-property-show-contact__form-title text-start" data-v-d2c62f70>${ssrInterpolate(trans("property_show.request_inquiry"))}</h4>`);
			_push(ssrRenderComponent(ContactForm_default, {
				"contact-store-url": __props.contactStoreUrl,
				variant: "sidebar",
				"hide-title": "",
				"hide-subject": __props.hideFormSubject,
				"default-subject": effectiveDefaultSubject.value,
				"default-message": __props.defaultMessage,
				"source-page": effectiveSourcePage.value
			}, null, _parent));
			_push(`</div></div></div></div>`);
		};
	}
};
var _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Property/resources/assets/js/components/PropertyShowContactSidebar.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var PropertyShowContactSidebar_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main, [["__scopeId", "data-v-d2c62f70"]]);
//#endregion
export { PropertyShowContactSidebar_default as t };
