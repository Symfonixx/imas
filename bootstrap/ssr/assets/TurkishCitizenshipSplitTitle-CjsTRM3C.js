import { ref, computed, onMounted, nextTick, mergeProps, useSSRContext } from "vue";
import { ssrRenderAttrs, ssrInterpolate } from "vue/server-renderer";
import { usePage } from "@inertiajs/vue3";
import { u as useGsap } from "./App-DYVlVBS1.js";
import { _ as _export_sfc } from "../ssr.js";
const _sfc_main = {
  __name: "TurkishCitizenshipSplitTitle",
  __ssrInlineRender: true,
  props: {
    primary: {
      type: String,
      required: true
    },
    accent: {
      type: String,
      required: true
    },
    showDivider: {
      type: Boolean,
      default: true
    },
    align: {
      type: String,
      default: "start",
      validator: (value) => value === "center" || value === "start"
    },
    /** Slide-in reveal for primary then accent (Turkish Citizenship page only). */
    reveal: {
      type: Boolean,
      default: false
    }
  },
  setup(__props) {
    const props = __props;
    const page = usePage();
    const rootRef = ref(null);
    const { gsap, context, prefersReducedMotion, refreshScrollTrigger } = useGsap();
    const isRtl = computed(() => {
      const dir = page.props.text_direction;
      if (dir === "rtl" || dir === "ltr") {
        return dir === "rtl";
      }
      return (page.props.locale || "en") === "ar";
    });
    const revealFromX = computed(() => isRtl.value ? 56 : -56);
    let hasRevealed = false;
    function setupReveal() {
      const root = rootRef.value;
      if (!root || !props.reveal || hasRevealed) {
        return;
      }
      if (prefersReducedMotion()) {
        hasRevealed = true;
        return;
      }
      const primary = root.querySelector(".imas-tc-split-title__primary");
      const accent = root.querySelector(".imas-tc-split-title__accent");
      const divider = root.querySelector(".imas-tc-split-title__divider");
      const fromX = revealFromX.value;
      context(() => {
        const hidden = [primary, accent, divider].filter(Boolean);
        gsap.set(hidden, { opacity: 0, x: fromX });
        const tl = gsap.timeline({
          scrollTrigger: {
            trigger: root,
            start: "top 88%",
            once: true,
            toggleActions: "play none none none"
          },
          defaults: { ease: "power2.out" }
        });
        if (primary) {
          tl.to(primary, { opacity: 1, x: 0, duration: 0.9 }, 0);
        }
        if (accent) {
          tl.to(
            accent,
            { opacity: 1, x: 0, duration: 0.9 },
            primary ? 0.14 : 0
          );
        }
        if (divider) {
          tl.to(
            divider,
            { opacity: 1, x: 0, duration: 0.55 },
            accent ? 0.1 : primary ? 0.12 : 0
          );
        }
      }, rootRef);
      hasRevealed = true;
      refreshScrollTrigger();
    }
    onMounted(() => {
      if (!props.reveal) {
        return;
      }
      nextTick(() => {
        nextTick(setupReveal);
      });
    });
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<header${ssrRenderAttrs(mergeProps({
        ref_key: "rootRef",
        ref: rootRef,
        class: ["imas-tc-split-title", {
          "imas-tc-split-title--center": __props.align === "center",
          "imas-tc-split-title--start": __props.align === "start"
        }]
      }, _attrs))} data-v-4e088938><h2 class="imas-tc-split-title__heading" data-v-4e088938><span class="imas-tc-split-title__primary" data-v-4e088938>${ssrInterpolate(__props.primary)}</span><span class="imas-tc-split-title__accent" data-v-4e088938>${ssrInterpolate(__props.accent)}</span></h2>`);
      if (__props.showDivider) {
        _push(`<hr class="imas-tc-split-title__divider" data-v-4e088938>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</header>`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Base/resources/assets/js/components/TurkishCitizenshipSplitTitle.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
const TurkishCitizenshipSplitTitle = /* @__PURE__ */ _export_sfc(_sfc_main, [["__scopeId", "data-v-4e088938"]]);
export {
  TurkishCitizenshipSplitTitle as T
};
