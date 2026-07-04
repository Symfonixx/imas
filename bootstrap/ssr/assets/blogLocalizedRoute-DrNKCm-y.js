import { o as localizedRoute } from "./App-BhEC2Bhd.js";
import { Link, usePage } from "@inertiajs/vue3";
import { createTextVNode, mergeProps, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderComponent, ssrRenderList } from "vue/server-renderer";
//#region Modules/Cms/resources/assets/js/Components/BlogListingSidebar.vue
var _sfc_main = {
	__name: "BlogListingSidebar",
	__ssrInlineRender: true,
	props: {
		/** When true, adds Bootstrap column classes (blog detail page row layout). */
		asColumn: {
			type: Boolean,
			default: false
		},
		searchAction: {
			type: String,
			default: ""
		},
		filters: {
			type: Object,
			default: () => ({})
		},
		categories: {
			type: Array,
			default: () => []
		},
		recentBlogs: {
			type: Array,
			default: () => []
		},
		categoryUrl: {
			type: Function,
			required: true
		},
		showSearch: {
			type: Boolean,
			default: true
		},
		showCategories: {
			type: Boolean,
			default: true
		},
		showRecentPosts: {
			type: Boolean,
			default: true
		}
	},
	setup(__props) {
		const page = usePage();
		function trans(key) {
			return page.props.translations[key] || key;
		}
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<aside${ssrRenderAttrs(mergeProps({ class: ["imas-blog-v2-sidebar", { "col-lg-3 col-md-12": __props.asColumn }] }, _attrs))}>`);
			if (__props.showSearch && __props.searchAction) {
				_push(`<div class="imas-blog-v2-sidebar__box"><h4 class="imas-blog-v2-sidebar__heading text-start">${ssrInterpolate(trans("blogs.search"))}</h4><form${ssrRenderAttr("action", __props.searchAction)} method="get" class="imas-blog-v2-sidebar__search">`);
				if (__props.filters.category_id) _push(`<input type="hidden" name="category_id"${ssrRenderAttr("value", __props.filters.category_id)}>`);
				else _push(`<!---->`);
				_push(`<input type="text" name="q" class="imas-blog-v2-sidebar__search-input"${ssrRenderAttr("placeholder", trans("blogs.search_placeholder"))}${ssrRenderAttr("value", __props.filters.q ?? "")} autocomplete="off"><button type="submit" class="imas-blog-v2-sidebar__search-btn"${ssrRenderAttr("aria-label", trans("blogs.search"))}><i class="fa fa-search" aria-hidden="true"></i></button></form></div>`);
			} else _push(`<!---->`);
			if (__props.showCategories) {
				_push(`<div class="imas-blog-v2-sidebar__box"><h4 class="imas-blog-v2-sidebar__heading text-start">${ssrInterpolate(trans("blogs.categories"))}</h4><ul class="imas-blog-v2-sidebar__cat-list"><li>`);
				_push(ssrRenderComponent(unref(Link), {
					href: __props.categoryUrl(null),
					class: ["imas-blog-v2-sidebar__cat-link", { "is-active": !__props.filters.category_id }]
				}, {
					default: withCtx((_, _push, _parent, _scopeId) => {
						if (_push) _push(`${ssrInterpolate(trans("blogs.all_categories"))}`);
						else return [createTextVNode(toDisplayString(trans("blogs.all_categories")), 1)];
					}),
					_: 1
				}, _parent));
				_push(`</li><!--[-->`);
				ssrRenderList(__props.categories, (c) => {
					_push(`<li>`);
					_push(ssrRenderComponent(unref(Link), {
						href: __props.categoryUrl(c.id),
						class: ["imas-blog-v2-sidebar__cat-link", { "is-active": __props.filters.category_id != null && Number(__props.filters.category_id) === c.id }]
					}, {
						default: withCtx((_, _push, _parent, _scopeId) => {
							if (_push) _push(`${ssrInterpolate(c.name)}`);
							else return [createTextVNode(toDisplayString(c.name), 1)];
						}),
						_: 2
					}, _parent));
					_push(`</li>`);
				});
				_push(`<!--]--></ul></div>`);
			} else _push(`<!---->`);
			if (__props.showRecentPosts) {
				_push(`<div class="imas-blog-v2-sidebar__box"><h4 class="imas-blog-v2-sidebar__heading text-start">${ssrInterpolate(trans("blogs.recent_posts"))}</h4><div class="imas-blog-v2-sidebar__recent"><!--[-->`);
				ssrRenderList(__props.recentBlogs, (r) => {
					_push(`<a${ssrRenderAttr("href", r.url)} class="imas-blog-v2-sidebar__recent-item"><img${ssrRenderAttr("src", r.image)}${ssrRenderAttr("alt", r.title)} loading="lazy"><div><div class="imas-blog-v2-sidebar__recent-title">${ssrInterpolate(r.title)}</div>`);
					if (r.date) _push(`<div class="imas-blog-v2-sidebar__recent-date text-dim text-start">${ssrInterpolate(r.date)}</div>`);
					else _push(`<!---->`);
					_push(`</div></a>`);
				});
				_push(`<!--]--></div></div>`);
			} else _push(`<!---->`);
			_push(`</aside>`);
		};
	}
};
var _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Cms/resources/assets/js/Components/BlogListingSidebar.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
//#endregion
//#region Modules/Cms/resources/assets/js/utils/blogLocalizedRoute.js
/**
* Blog index URL with locale prefix (GET forms / Inertia links must use this).
*/
function blogIndexLocalizedUrl(locale, params = {}) {
	return localizedRoute("blog.index", params, locale, "/blog");
}
//#endregion
export { _sfc_main as n, blogIndexLocalizedUrl as t };
