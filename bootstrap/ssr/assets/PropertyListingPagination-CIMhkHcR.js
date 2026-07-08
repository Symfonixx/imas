import { computed, mergeProps, useSSRContext, ref, onMounted, onBeforeUnmount, unref, withCtx, createVNode } from "vue";
import { ssrRenderAttrs, ssrRenderList, ssrRenderComponent } from "vue/server-renderer";
import { P as PropertyCard } from "../ssr.js";
import { usePage, Link } from "@inertiajs/vue3";
const _sfc_main$1 = {
  __name: "PropertyGridSection",
  __ssrInlineRender: true,
  props: {
    properties: { type: Object, required: true }
  },
  setup(__props) {
    const props = __props;
    const items = computed(() => {
      var _a;
      return ((_a = props.properties) == null ? void 0 : _a.data) ?? [];
    });
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(mergeProps({ class: "row" }, _attrs))}><!--[-->`);
      ssrRenderList(items.value, (item) => {
        _push(ssrRenderComponent(PropertyCard, {
          key: item.id,
          property: item,
          "column-class": "col-12 col-md-6 col-lg-6"
        }, null, _parent));
      });
      _push(`<!--]--></div>`);
    };
  }
};
const _sfc_setup$1 = _sfc_main$1.setup;
_sfc_main$1.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Property/resources/assets/js/components/PropertyGridSection.vue");
  return _sfc_setup$1 ? _sfc_setup$1(props, ctx) : void 0;
};
const COMPACT_MQ = "(max-width: 640px)";
const COMPACT_MAX_PAGE_BUTTONS = 4;
const _sfc_main = {
  __name: "PropertyListingPagination",
  __ssrInlineRender: true,
  props: {
    properties: { type: Object, required: true }
  },
  emits: ["navigate"],
  setup(__props, { emit: __emit }) {
    const props = __props;
    const emit = __emit;
    const page = usePage();
    const isCompact = ref(false);
    let compactMq = null;
    function trans(key) {
      return page.props.translations[key] ?? key;
    }
    function stripHtml(value) {
      return String(value ?? "").replace(/<[^>]*>/g, "").trim();
    }
    function isNumericPageLabel(label) {
      return /^\d+$/.test(stripHtml(label));
    }
    function withDisplayLabels(raw) {
      const n = raw.length;
      if (n < 2) {
        return raw.map((link) => ({ ...link, displayLabel: link.label }));
      }
      return raw.map((link, idx) => {
        let displayLabel = link.label;
        if (idx === 0) {
          displayLabel = trans("global.previous");
        } else if (idx === n - 1) {
          displayLabel = trans("global.next");
        }
        return { ...link, displayLabel };
      });
    }
    function compactPageLinks(links, maxPages = COMPACT_MAX_PAGE_BUTTONS) {
      const n = links.length;
      if (n < 2) {
        return links;
      }
      const prev = links[0];
      const next = links[n - 1];
      const pageLinks = links.slice(1, -1).filter((link) => isNumericPageLabel(link.label));
      if (pageLinks.length <= maxPages) {
        return links;
      }
      const activeLink = pageLinks.find((link) => link.active);
      const currentPage = activeLink ? parseInt(stripHtml(activeLink.label), 10) : 1;
      const totalPages = parseInt(
        stripHtml(pageLinks[pageLinks.length - 1].label),
        10
      );
      let start = Math.max(1, currentPage - Math.floor(maxPages / 2));
      let end = start + maxPages - 1;
      if (end > totalPages) {
        end = totalPages;
        start = Math.max(1, end - maxPages + 1);
      }
      const visiblePages = pageLinks.filter((link) => {
        const pageNum = parseInt(stripHtml(link.label), 10);
        return pageNum >= start && pageNum <= end;
      });
      return [prev, ...visiblePages, next];
    }
    function updateCompact() {
      isCompact.value = typeof window !== "undefined" && window.matchMedia(COMPACT_MQ).matches;
    }
    onMounted(() => {
      updateCompact();
      if (typeof window === "undefined" || !window.matchMedia) {
        return;
      }
      compactMq = window.matchMedia(COMPACT_MQ);
      compactMq.addEventListener("change", updateCompact);
    });
    onBeforeUnmount(() => {
      compactMq == null ? void 0 : compactMq.removeEventListener("change", updateCompact);
    });
    const displayLinks = computed(() => {
      var _a;
      const raw = ((_a = props.properties) == null ? void 0 : _a.links) ?? [];
      const labeled = withDisplayLabels(raw);
      return isCompact.value ? compactPageLinks(labeled) : labeled;
    });
    function onNavigate(event) {
      const btn = event.currentTarget;
      if (!btn || typeof btn.getBoundingClientRect !== "function") {
        emit("navigate");
        return;
      }
      const rect = btn.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      const ripple = document.createElement("span");
      ripple.className = "imas-blog-v2-pagination__ripple";
      ripple.style.width = `${size}px`;
      ripple.style.height = `${size}px`;
      ripple.style.left = `${event.clientX - rect.left - size / 2}px`;
      ripple.style.top = `${event.clientY - rect.top - size / 2}px`;
      btn.appendChild(ripple);
      setTimeout(() => ripple.remove(), 600);
      emit("navigate");
    }
    return (_ctx, _push, _parent, _attrs) => {
      if (displayLinks.value.length > 0) {
        _push(`<nav${ssrRenderAttrs(mergeProps({
          class: "imas-blog-v2-pagination",
          "aria-label": "Property listings pagination"
        }, _attrs))}><!--[-->`);
        ssrRenderList(displayLinks.value, (link, idx) => {
          _push(`<!--[-->`);
          if (link.url) {
            _push(ssrRenderComponent(unref(Link), {
              href: link.url,
              class: ["imas-blog-v2-pagination__btn", { "is-active": link.active }],
              "preserve-scroll": false,
              onClick: onNavigate
            }, {
              default: withCtx((_, _push2, _parent2, _scopeId) => {
                if (_push2) {
                  _push2(`<span${_scopeId}>${link.displayLabel ?? ""}</span>`);
                } else {
                  return [
                    createVNode("span", {
                      innerHTML: link.displayLabel
                    }, null, 8, ["innerHTML"])
                  ];
                }
              }),
              _: 2
            }, _parent));
          } else {
            _push(`<span class="imas-blog-v2-pagination__btn is-disabled" aria-disabled="true"><span>${link.displayLabel ?? ""}</span></span>`);
          }
          _push(`<!--]-->`);
        });
        _push(`<!--]--></nav>`);
      } else {
        _push(`<!---->`);
      }
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Property/resources/assets/js/components/PropertyListingPagination.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
export {
  _sfc_main$1 as _,
  _sfc_main as a
};
