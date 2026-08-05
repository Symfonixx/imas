import { computed, ref, unref, withCtx, openBlock, createBlock, createCommentVNode, createVNode, toDisplayString, createTextVNode, useSSRContext } from "vue";
import { ssrRenderComponent, ssrRenderAttr, ssrInterpolate } from "vue/server-renderer";
import { usePage, Head } from "@inertiajs/vue3";
import { l as localizedRoute, _ as _sfc_main$2 } from "./App-DYVlVBS1.js";
import { d as buildArticleSchema, _ as _sfc_main$1 } from "./structuredData-Cakj6_Zn.js";
import { b as blogIndexLocalizedUrl, _ as _sfc_main$4 } from "./blogLocalizedRoute-D9Gu4BjL.js";
import { _ as _sfc_main$3 } from "./InnerPageHeadingHero-CEyYr1UI.js";
import { u as useScrollReveal } from "./useScrollReveal-DuFSzefs.js";
import "gsap";
import "gsap/ScrollTrigger";
import "../ssr.js";
import "@inertiajs/vue3/server";
import "@vue/server-renderer";
const _sfc_main = {
  __name: "Show",
  __ssrInlineRender: true,
  props: {
    title: { type: String, required: true },
    blog: { type: Object, required: true },
    recentBlogs: { type: Array, default: () => [] },
    categories: { type: Array, default: () => [] },
    filters: { type: Object, required: true }
  },
  setup(__props) {
    const props = __props;
    const page = usePage();
    const activeLocale = computed(() => page.props.locale || "en");
    const articleTextRef = ref(null);
    useScrollReveal(articleTextRef, { variant: "blogShowArticle" });
    const globals = computed(() => page.props.globals ?? {});
    const media = computed(() => globals.value.media ?? {});
    const blogShowBannerUrl = computed(() => {
      const url = media.value.blog_show_banner;
      if (typeof url !== "string" || url.trim() === "") {
        return "";
      }
      const trimmed = url.trim();
      if (/\/default\.jpg(?:\?.*)?$/i.test(trimmed)) {
        return "";
      }
      return trimmed;
    });
    function trans(key) {
      return page.props.translations[key] || key;
    }
    function plainText(value) {
      if (typeof value !== "string") {
        return "";
      }
      const s = value.replace(/<[^>]*>/g, " ").replace(/\s+/g, " ").trim();
      return s;
    }
    const blogHeadingItems = computed(() => {
      var _a, _b, _c, _d;
      const rows = [];
      try {
        if (typeof route === "function" && ((_b = (_a = route()).has) == null ? void 0 : _b.call(_a, "home"))) {
          rows.push({
            title: trans("navBar.Home"),
            href: localizedRoute("home", {}, activeLocale.value, "/")
          });
        }
        if (typeof route === "function" && ((_d = (_c = route()).has) == null ? void 0 : _d.call(_c, "blog.index"))) {
          rows.push({
            title: trans("navBar.Blogs"),
            href: blogIndexLocalizedUrl(activeLocale.value)
          });
        }
      } catch {
      }
      rows.push({
        title: props.blog.title,
        href: null
      });
      return rows;
    });
    const blogIndexUrl = computed(
      () => blogIndexLocalizedUrl(activeLocale.value)
    );
    function categoryIndexUrl(categoryId) {
      const params = {};
      if (props.filters.q) {
        params.q = props.filters.q;
      }
      if (categoryId != null) {
        params.category_id = categoryId;
      }
      return blogIndexLocalizedUrl(activeLocale.value, params);
    }
    const meta = computed(() => props.blog.meta ?? {});
    const documentTitle = computed(
      () => `${plainText(String(meta.value.title || props.title))} | ${page.props.appName}`
    );
    const metaDescription = computed(() => {
      const d = meta.value.description;
      return typeof d === "string" && d.trim() !== "" ? plainText(d) : "";
    });
    const metaKeywords = computed(() => {
      const k = meta.value.keywords;
      if (Array.isArray(k)) {
        const s = k.filter(Boolean).join(", ").trim();
        return s !== "" ? s : "";
      }
      if (typeof k === "string" && k.trim() !== "") {
        return k.trim();
      }
      return "";
    });
    const canonicalUrl = computed(() => {
      const u = meta.value.canonical_url;
      return typeof u === "string" && u.trim() !== "" ? u.trim() : "";
    });
    const ogTitle = computed(() => documentTitle.value);
    const ogDescription = computed(() => metaDescription.value);
    const ogImage = computed(() => {
      const u = meta.value.image;
      if (typeof u === "string" && u.trim() !== "") {
        return u.trim();
      }
      const fallback = props.blog.image;
      if (typeof fallback === "string" && fallback.trim() !== "") {
        return fallback.trim();
      }
      const siteFallback = media.value.meta_img;
      return typeof siteFallback === "string" && siteFallback.trim() !== "" ? siteFallback.trim() : "";
    });
    const ogUrl = computed(() => canonicalUrl.value);
    const twitterCard = computed(
      () => ogImage.value ? "summary_large_image" : "summary"
    );
    const articleSchema = computed(() => {
      const publisherLogo = media.value.white_logo || media.value.black_logo || media.value.meta_img || "";
      return buildArticleSchema({
        headline: plainText(String(meta.value.title || props.blog.title)),
        description: metaDescription.value,
        image: ogImage.value,
        datePublished: props.blog.created_at,
        url: canonicalUrl.value,
        publisherName: page.props.appName,
        publisherLogo
      });
    });
    const articleJsonLd = computed(() => {
      const schema = articleSchema.value;
      return schema ? JSON.stringify(schema) : "";
    });
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<!--[-->`);
      _push(ssrRenderComponent(unref(Head), { title: documentTitle.value }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            if (metaDescription.value) {
              _push2(`<meta head-key="description" name="description"${ssrRenderAttr("content", metaDescription.value)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            if (metaKeywords.value) {
              _push2(`<meta head-key="keywords" name="keywords"${ssrRenderAttr("content", metaKeywords.value)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            if (canonicalUrl.value) {
              _push2(`<link head-key="canonical" rel="canonical"${ssrRenderAttr("href", canonicalUrl.value)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            if (ogTitle.value) {
              _push2(`<meta head-key="og:title" property="og:title"${ssrRenderAttr("content", ogTitle.value)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            if (ogDescription.value) {
              _push2(`<meta head-key="og:description" property="og:description"${ssrRenderAttr("content", ogDescription.value)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            if (ogImage.value) {
              _push2(`<meta head-key="og:image" property="og:image"${ssrRenderAttr("content", ogImage.value)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<meta head-key="og:type" property="og:type" content="article"${_scopeId}>`);
            if (ogUrl.value) {
              _push2(`<meta head-key="og:url" property="og:url"${ssrRenderAttr("content", ogUrl.value)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<meta head-key="twitter:card" name="twitter:card"${ssrRenderAttr("content", twitterCard.value)}${_scopeId}>`);
            if (ogTitle.value) {
              _push2(`<meta head-key="twitter:title" name="twitter:title"${ssrRenderAttr("content", ogTitle.value)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            if (ogDescription.value) {
              _push2(`<meta head-key="twitter:description" name="twitter:description"${ssrRenderAttr("content", ogDescription.value)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            if (ogImage.value) {
              _push2(`<meta head-key="twitter:image" name="twitter:image"${ssrRenderAttr("content", ogImage.value)}${_scopeId}>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(ssrRenderComponent(_sfc_main$1, {
              "head-key": "jsonld-article",
              content: articleJsonLd.value
            }, null, _parent2, _scopeId));
          } else {
            return [
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
                content: "article"
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
              }, null, 8, ["content"])) : createCommentVNode("", true),
              createVNode(_sfc_main$1, {
                "head-key": "jsonld-article",
                content: articleJsonLd.value
              }, null, 8, ["content"])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_sfc_main$2, null, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="imas-blog-v2 imas-blog-section-anchor"${_scopeId}>`);
            _push2(ssrRenderComponent(_sfc_main$3, {
              "page-title": trans("blogs.blog_details"),
              items: blogHeadingItems.value,
              "banner-image-url": blogShowBannerUrl.value
            }, null, _parent2, _scopeId));
            _push2(`<main class="imas-blog-v2__page container"${_scopeId}><section class="imas-blog-v2__main"${_scopeId}><article class="imas-blog-show"${_scopeId}>`);
            if (__props.blog.image) {
              _push2(`<div class="imas-blog-show__media"${_scopeId}><img${ssrRenderAttr("src", __props.blog.image)}${ssrRenderAttr("alt", __props.blog.title)} loading="eager"${_scopeId}></div>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`<div class="imas-blog-show__content"${_scopeId}><header class="imas-blog-show-article-text__header"${_scopeId}><h1 class="imas-blog-show__title text-2xl font-bold text-start"${_scopeId}>${ssrInterpolate(__props.blog.title)}</h1><div class="imas-blog-show__meta text-md text-dim"${_scopeId}>`);
            if (__props.blog.date) {
              _push2(`<span class="imas-blog-show__date"${_scopeId}>${ssrInterpolate(__props.blog.date)}</span>`);
            } else {
              _push2(`<!---->`);
            }
            if (__props.blog.date && __props.blog.visits != null) {
              _push2(`<span class="imas-blog-show__meta-sep" aria-hidden="true"${_scopeId}>/</span>`);
            } else {
              _push2(`<!---->`);
            }
            if (__props.blog.visits != null) {
              _push2(`<span class="imas-blog-show__views"${_scopeId}><i class="fa fa-eye" aria-hidden="true"${_scopeId}></i> ${ssrInterpolate(__props.blog.visits)}</span>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`</div>`);
            if (__props.blog.category) {
              _push2(`<span class="imas-blog-show__category-label mb-4"${_scopeId}>${ssrInterpolate(__props.blog.category.name)}</span>`);
            } else {
              _push2(`<!---->`);
            }
            _push2(`</header><div class="imas-blog-show-body text-base text-start"${_scopeId}>${__props.blog.content ?? ""}</div></div></article></section>`);
            _push2(ssrRenderComponent(_sfc_main$4, {
              "search-action": blogIndexUrl.value,
              filters: __props.filters,
              categories: __props.categories,
              "recent-blogs": __props.recentBlogs,
              "category-url": categoryIndexUrl
            }, null, _parent2, _scopeId));
            _push2(`</main></div>`);
          } else {
            return [
              createVNode("div", { class: "imas-blog-v2 imas-blog-section-anchor" }, [
                createVNode(_sfc_main$3, {
                  "page-title": trans("blogs.blog_details"),
                  items: blogHeadingItems.value,
                  "banner-image-url": blogShowBannerUrl.value
                }, null, 8, ["page-title", "items", "banner-image-url"]),
                createVNode("main", { class: "imas-blog-v2__page container" }, [
                  createVNode("section", { class: "imas-blog-v2__main" }, [
                    createVNode("article", {
                      ref_key: "articleTextRef",
                      ref: articleTextRef,
                      class: "imas-blog-show"
                    }, [
                      __props.blog.image ? (openBlock(), createBlock("div", {
                        key: 0,
                        class: "imas-blog-show__media"
                      }, [
                        createVNode("img", {
                          src: __props.blog.image,
                          alt: __props.blog.title,
                          loading: "eager"
                        }, null, 8, ["src", "alt"])
                      ])) : createCommentVNode("", true),
                      createVNode("div", { class: "imas-blog-show__content" }, [
                        createVNode("header", { class: "imas-blog-show-article-text__header" }, [
                          createVNode("h1", { class: "imas-blog-show__title text-2xl font-bold text-start" }, toDisplayString(__props.blog.title), 1),
                          createVNode("div", { class: "imas-blog-show__meta text-md text-dim" }, [
                            __props.blog.date ? (openBlock(), createBlock("span", {
                              key: 0,
                              class: "imas-blog-show__date"
                            }, toDisplayString(__props.blog.date), 1)) : createCommentVNode("", true),
                            __props.blog.date && __props.blog.visits != null ? (openBlock(), createBlock("span", {
                              key: 1,
                              class: "imas-blog-show__meta-sep",
                              "aria-hidden": "true"
                            }, "/")) : createCommentVNode("", true),
                            __props.blog.visits != null ? (openBlock(), createBlock("span", {
                              key: 2,
                              class: "imas-blog-show__views"
                            }, [
                              createVNode("i", {
                                class: "fa fa-eye",
                                "aria-hidden": "true"
                              }),
                              createTextVNode(" " + toDisplayString(__props.blog.visits), 1)
                            ])) : createCommentVNode("", true)
                          ]),
                          __props.blog.category ? (openBlock(), createBlock("span", {
                            key: 0,
                            class: "imas-blog-show__category-label mb-4"
                          }, toDisplayString(__props.blog.category.name), 1)) : createCommentVNode("", true)
                        ]),
                        createVNode("div", {
                          class: "imas-blog-show-body text-base text-start",
                          innerHTML: __props.blog.content
                        }, null, 8, ["innerHTML"])
                      ])
                    ], 512)
                  ]),
                  createVNode(_sfc_main$4, {
                    "search-action": blogIndexUrl.value,
                    filters: __props.filters,
                    categories: __props.categories,
                    "recent-blogs": __props.recentBlogs,
                    "category-url": categoryIndexUrl
                  }, null, 8, ["search-action", "filters", "categories", "recent-blogs"])
                ])
              ])
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(`<!--]-->`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Cms/resources/assets/js/Pages/Show.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main as default
};
