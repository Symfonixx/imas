import { resolveComponent, withCtx, createVNode, toDisplayString, withModifiers, withDirectives, vModelText, openBlock, createBlock, createCommentVNode, vModelCheckbox, computed, useSSRContext } from "vue";
import { Head, Link, usePage, useForm } from "@inertiajs/vue3";
import { _ as _sfc_main$1 } from "./App-DYVlVBS1.js";
import { ssrRenderComponent, ssrInterpolate, ssrRenderAttr, ssrIncludeBooleanAttr, ssrLooseContain } from "vue/server-renderer";
import { _ as _export_sfc } from "../ssr.js";
import "gsap";
import "gsap/ScrollTrigger";
import "@inertiajs/vue3/server";
import "@vue/server-renderer";
const _sfc_main = {
  components: {
    AppLayout: _sfc_main$1,
    Link,
    Head
  },
  props: {
    errors: Object
  },
  setup() {
    const page = usePage();
    const appName = computed(() => page.props.appName);
    const trans = (key) => page.props.translations[key] || key;
    const form = useForm({
      email: "",
      password: "",
      remember: false
    });
    return { form, appName, trans };
  }
};
function _sfc_ssrRender(_ctx, _push, _parent, _attrs, $props, $setup, $data, $options) {
  const _component_Head = resolveComponent("Head");
  const _component_app_layout = resolveComponent("app-layout");
  _push(`<!--[-->`);
  _push(ssrRenderComponent(_component_Head, {
    title: `${$setup.trans("Login")} | ${$setup.appName}`
  }, {
    default: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(`<meta head-key="robots" name="robots" content="noindex, nofollow" data-v-386d3819${_scopeId}>`);
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
  _push(ssrRenderComponent(_component_app_layout, null, {
    default: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(`<div class="container mt-5" data-v-386d3819${_scopeId}><div class="row justify-content-center" data-v-386d3819${_scopeId}><div class="col-md-6" data-v-386d3819${_scopeId}><div class="card" data-v-386d3819${_scopeId}><div class="card-header text-center" data-v-386d3819${_scopeId}><h3 data-v-386d3819${_scopeId}>${ssrInterpolate($setup.trans("Login"))}</h3></div><div class="card-body" data-v-386d3819${_scopeId}><form data-v-386d3819${_scopeId}><div class="mb-3" data-v-386d3819${_scopeId}><label class="form-label" for="email" data-v-386d3819${_scopeId}>${ssrInterpolate($setup.trans("Email"))}</label><input id="email"${ssrRenderAttr("value", $setup.form.email)} autofocus class="form-control" required type="email" data-v-386d3819${_scopeId}>`);
        if ($props.errors.email) {
          _push2(`<span class="invalid-feedback d-block" data-v-386d3819${_scopeId}>${ssrInterpolate($props.errors.email)}</span>`);
        } else {
          _push2(`<!---->`);
        }
        _push2(`</div><div class="mb-3" data-v-386d3819${_scopeId}><label class="form-label" for="password" data-v-386d3819${_scopeId}>${ssrInterpolate($setup.trans("Password"))}</label><input id="password"${ssrRenderAttr("value", $setup.form.password)} class="form-control" required type="password" data-v-386d3819${_scopeId}>`);
        if ($props.errors.password) {
          _push2(`<span class="invalid-feedback d-block" data-v-386d3819${_scopeId}>${ssrInterpolate($props.errors.password)}</span>`);
        } else {
          _push2(`<!---->`);
        }
        _push2(`</div><div class="mb-3 form-check" data-v-386d3819${_scopeId}><input id="remember"${ssrIncludeBooleanAttr(Array.isArray($setup.form.remember) ? ssrLooseContain($setup.form.remember, null) : $setup.form.remember) ? " checked" : ""} class="form-check-input" type="checkbox" data-v-386d3819${_scopeId}><label class="form-check-label" for="remember" data-v-386d3819${_scopeId}>${ssrInterpolate($setup.trans("Remember Me"))}</label></div><button class="btn btn-primary w-100" type="submit" data-v-386d3819${_scopeId}>${ssrInterpolate($setup.trans("Sign In"))}</button></form></div></div></div></div></div>`);
      } else {
        return [
          createVNode("div", { class: "container mt-5" }, [
            createVNode("div", { class: "row justify-content-center" }, [
              createVNode("div", { class: "col-md-6" }, [
                createVNode("div", { class: "card" }, [
                  createVNode("div", { class: "card-header text-center" }, [
                    createVNode("h3", null, toDisplayString($setup.trans("Login")), 1)
                  ]),
                  createVNode("div", { class: "card-body" }, [
                    createVNode("form", {
                      onSubmit: withModifiers(($event) => $setup.form.post(_ctx.route("login")), ["prevent"])
                    }, [
                      createVNode("div", { class: "mb-3" }, [
                        createVNode("label", {
                          class: "form-label",
                          for: "email"
                        }, toDisplayString($setup.trans("Email")), 1),
                        withDirectives(createVNode("input", {
                          id: "email",
                          "onUpdate:modelValue": ($event) => $setup.form.email = $event,
                          autofocus: "",
                          class: "form-control",
                          required: "",
                          type: "email"
                        }, null, 8, ["onUpdate:modelValue"]), [
                          [vModelText, $setup.form.email]
                        ]),
                        $props.errors.email ? (openBlock(), createBlock("span", {
                          key: 0,
                          class: "invalid-feedback d-block"
                        }, toDisplayString($props.errors.email), 1)) : createCommentVNode("", true)
                      ]),
                      createVNode("div", { class: "mb-3" }, [
                        createVNode("label", {
                          class: "form-label",
                          for: "password"
                        }, toDisplayString($setup.trans("Password")), 1),
                        withDirectives(createVNode("input", {
                          id: "password",
                          "onUpdate:modelValue": ($event) => $setup.form.password = $event,
                          class: "form-control",
                          required: "",
                          type: "password"
                        }, null, 8, ["onUpdate:modelValue"]), [
                          [vModelText, $setup.form.password]
                        ]),
                        $props.errors.password ? (openBlock(), createBlock("span", {
                          key: 0,
                          class: "invalid-feedback d-block"
                        }, toDisplayString($props.errors.password), 1)) : createCommentVNode("", true)
                      ]),
                      createVNode("div", { class: "mb-3 form-check" }, [
                        withDirectives(createVNode("input", {
                          id: "remember",
                          "onUpdate:modelValue": ($event) => $setup.form.remember = $event,
                          class: "form-check-input",
                          type: "checkbox"
                        }, null, 8, ["onUpdate:modelValue"]), [
                          [vModelCheckbox, $setup.form.remember]
                        ]),
                        createVNode("label", {
                          class: "form-check-label",
                          for: "remember"
                        }, toDisplayString($setup.trans("Remember Me")), 1)
                      ]),
                      createVNode("button", {
                        class: "btn btn-primary w-100",
                        type: "submit"
                      }, toDisplayString($setup.trans("Sign In")), 1)
                    ], 40, ["onSubmit"])
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
}
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/User/resources/assets/js/Pages/Auth/Login.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
const Login = /* @__PURE__ */ _export_sfc(_sfc_main, [["ssrRender", _sfc_ssrRender], ["__scopeId", "data-v-386d3819"]]);
export {
  Login as default
};
