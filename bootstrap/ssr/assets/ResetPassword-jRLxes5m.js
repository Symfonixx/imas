import { computed, onMounted, unref, withCtx, createVNode, toDisplayString, useSSRContext } from "vue";
import { ssrRenderComponent, ssrInterpolate } from "vue/server-renderer";
import { usePage, Head } from "@inertiajs/vue3";
import { _ as _sfc_main$1, a as useOpenAuthModal } from "./App-DYVlVBS1.js";
import { _ as _export_sfc } from "../ssr.js";
import "gsap";
import "gsap/ScrollTrigger";
import "@inertiajs/vue3/server";
import "@vue/server-renderer";
const _sfc_main = {
  __name: "ResetPassword",
  __ssrInlineRender: true,
  props: {
    email: {
      type: String,
      default: ""
    },
    token: {
      type: String,
      default: ""
    },
    errors: {
      type: Object,
      default: () => ({})
    }
  },
  setup(__props) {
    const page = usePage();
    const { openAuthModal } = useOpenAuthModal();
    const appName = computed(() => page.props.appName);
    const trans = (key) => {
      var _a;
      return ((_a = page.props.translations) == null ? void 0 : _a[key]) || key;
    };
    function openResetModal() {
      openAuthModal("reset");
    }
    onMounted(() => {
      openResetModal();
      window.setTimeout(openResetModal, 50);
    });
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<!--[-->`);
      _push(ssrRenderComponent(unref(Head), {
        title: `${trans("Reset Password")} | ${appName.value}`
      }, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<meta head-key="robots" name="robots" content="noindex, nofollow" data-v-844d89ac${_scopeId}>`);
          } else {
            return [
              createVNode("meta", {
                "head-key": "robots",
                name: "robots",
                content: "noindex, nofollow"
              })
            ];
          }
        }),
        _: 1
      }, _parent));
      _push(ssrRenderComponent(_sfc_main$1, null, {
        default: withCtx((_, _push2, _parent2, _scopeId) => {
          if (_push2) {
            _push2(`<div class="container mt-5 mb-5" data-v-844d89ac${_scopeId}><div class="row justify-content-center" data-v-844d89ac${_scopeId}><div class="col-md-6" data-v-844d89ac${_scopeId}><div class="card imas-auth-page-card" data-v-844d89ac${_scopeId}><div class="card-header text-center" data-v-844d89ac${_scopeId}><h3 class="text-md font-semibold mb-0" data-v-844d89ac${_scopeId}>${ssrInterpolate(trans("Reset Password"))}</h3></div><div class="card-body text-center" data-v-844d89ac${_scopeId}><p class="text-sm text-dim mb-3" data-v-844d89ac${_scopeId}>${ssrInterpolate(trans(
              "auth_modal.reset_page_opening"
            ))}</p><button type="button" class="btn btn-primary" data-v-844d89ac${_scopeId}>${ssrInterpolate(trans("Reset Password"))}</button></div></div></div></div></div>`);
          } else {
            return [
              createVNode("div", { class: "container mt-5 mb-5" }, [
                createVNode("div", { class: "row justify-content-center" }, [
                  createVNode("div", { class: "col-md-6" }, [
                    createVNode("div", { class: "card imas-auth-page-card" }, [
                      createVNode("div", { class: "card-header text-center" }, [
                        createVNode("h3", { class: "text-md font-semibold mb-0" }, toDisplayString(trans("Reset Password")), 1)
                      ]),
                      createVNode("div", { class: "card-body text-center" }, [
                        createVNode("p", { class: "text-sm text-dim mb-3" }, toDisplayString(trans(
                          "auth_modal.reset_page_opening"
                        )), 1),
                        createVNode("button", {
                          type: "button",
                          class: "btn btn-primary",
                          onClick: openResetModal
                        }, toDisplayString(trans("Reset Password")), 1)
                      ])
                    ])
                  ])
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/User/resources/assets/js/Pages/Auth/ResetPassword.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
const ResetPassword = /* @__PURE__ */ _export_sfc(_sfc_main, [["__scopeId", "data-v-844d89ac"]]);
export {
  ResetPassword as default
};
