import { resolveComponent, withCtx, createVNode, toDisplayString, withModifiers, withDirectives, vModelText, openBlock, createBlock, createCommentVNode, computed, useSSRContext } from "vue";
import { Head, Link, usePage, useForm } from "@inertiajs/vue3";
import { _ as _sfc_main$1 } from "./App-BMYoBaMl.js";
import { P as PhoneCountryInput } from "./PhoneCountryInput-wjibwJ1Y.js";
import { ssrRenderComponent, ssrInterpolate, ssrRenderAttr } from "vue/server-renderer";
import { _ as _export_sfc } from "../ssr.js";
import "gsap";
import "gsap/ScrollTrigger";
import "@inertiajs/vue3/server";
import "@vue/server-renderer";
const _sfc_main = {
  components: {
    AppLayout: _sfc_main$1,
    Link,
    PhoneCountryInput,
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
      first_name: "",
      last_name: "",
      email: "",
      mobile: "",
      password: "",
      password_confirmation: ""
    });
    return { form, appName, trans };
  }
};
function _sfc_ssrRender(_ctx, _push, _parent, _attrs, $props, $setup, $data, $options) {
  const _component_Head = resolveComponent("Head");
  const _component_app_layout = resolveComponent("app-layout");
  const _component_PhoneCountryInput = resolveComponent("PhoneCountryInput");
  _push(`<!--[-->`);
  _push(ssrRenderComponent(_component_Head, {
    title: `${$setup.trans("Register")} | ${$setup.appName}`
  }, {
    default: withCtx((_, _push2, _parent2, _scopeId) => {
      if (_push2) {
        _push2(`<meta head-key="robots" name="robots" content="noindex, nofollow" data-v-5f03b53b${_scopeId}>`);
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
        _push2(`<div class="container mt-5" data-v-5f03b53b${_scopeId}><div class="row justify-content-center" data-v-5f03b53b${_scopeId}><div class="col-md-6" data-v-5f03b53b${_scopeId}><div class="card" data-v-5f03b53b${_scopeId}><div class="card-header text-center" data-v-5f03b53b${_scopeId}><h3 data-v-5f03b53b${_scopeId}>${ssrInterpolate($setup.trans("Register"))}</h3></div><div class="card-body" data-v-5f03b53b${_scopeId}><form data-v-5f03b53b${_scopeId}><div class="row mb-3" data-v-5f03b53b${_scopeId}><div class="col-md-6" data-v-5f03b53b${_scopeId}><label class="form-label" for="first_name" data-v-5f03b53b${_scopeId}>${ssrInterpolate($setup.trans("contact_us.first_name"))}</label><input id="first_name"${ssrRenderAttr("value", $setup.form.first_name)} autofocus class="form-control" required type="text" maxlength="120" autocomplete="given-name" data-v-5f03b53b${_scopeId}>`);
        if ($props.errors.first_name) {
          _push2(`<span class="invalid-feedback d-block" data-v-5f03b53b${_scopeId}>${ssrInterpolate($props.errors.first_name)}</span>`);
        } else {
          _push2(`<!---->`);
        }
        _push2(`</div><div class="col-md-6" data-v-5f03b53b${_scopeId}><label class="form-label" for="last_name" data-v-5f03b53b${_scopeId}>${ssrInterpolate($setup.trans("contact_us.last_name"))}</label><input id="last_name"${ssrRenderAttr("value", $setup.form.last_name)} class="form-control" required type="text" maxlength="120" autocomplete="family-name" data-v-5f03b53b${_scopeId}>`);
        if ($props.errors.last_name) {
          _push2(`<span class="invalid-feedback d-block" data-v-5f03b53b${_scopeId}>${ssrInterpolate($props.errors.last_name)}</span>`);
        } else {
          _push2(`<!---->`);
        }
        _push2(`</div></div><div class="mb-3" data-v-5f03b53b${_scopeId}><label class="form-label" for="email" data-v-5f03b53b${_scopeId}>${ssrInterpolate($setup.trans("Email"))}</label><input id="email"${ssrRenderAttr("value", $setup.form.email)} class="form-control" required type="email" data-v-5f03b53b${_scopeId}>`);
        if ($props.errors.email) {
          _push2(`<span class="invalid-feedback d-block" data-v-5f03b53b${_scopeId}>${ssrInterpolate($props.errors.email)}</span>`);
        } else {
          _push2(`<!---->`);
        }
        _push2(`</div><div class="mb-3" data-v-5f03b53b${_scopeId}><label class="form-label" for="register-mobile" data-v-5f03b53b${_scopeId}>${ssrInterpolate($setup.trans("Mobile"))}</label>`);
        _push2(ssrRenderComponent(_component_PhoneCountryInput, {
          modelValue: $setup.form.mobile,
          "onUpdate:modelValue": ($event) => $setup.form.mobile = $event,
          "input-id": "register-mobile",
          placeholder: $setup.trans("auth_modal.mobile_national_placeholder"),
          invalid: !!$props.errors.mobile,
          required: ""
        }, null, _parent2, _scopeId));
        if ($props.errors.mobile) {
          _push2(`<span class="invalid-feedback d-block" data-v-5f03b53b${_scopeId}>${ssrInterpolate($props.errors.mobile)}</span>`);
        } else {
          _push2(`<!---->`);
        }
        _push2(`</div><div class="mb-3" data-v-5f03b53b${_scopeId}><label class="form-label" for="password" data-v-5f03b53b${_scopeId}>${ssrInterpolate($setup.trans("Password"))}</label><input id="password"${ssrRenderAttr("value", $setup.form.password)} class="form-control" required type="password" data-v-5f03b53b${_scopeId}>`);
        if ($props.errors.password) {
          _push2(`<span class="invalid-feedback d-block" data-v-5f03b53b${_scopeId}>${ssrInterpolate($props.errors.password)}</span>`);
        } else {
          _push2(`<!---->`);
        }
        _push2(`</div><div class="mb-3" data-v-5f03b53b${_scopeId}><label class="form-label" for="password_confirmation" data-v-5f03b53b${_scopeId}>${ssrInterpolate($setup.trans("Confirm Password"))}</label><input id="password_confirmation"${ssrRenderAttr("value", $setup.form.password_confirmation)} class="form-control" required type="password_confirmation" data-v-5f03b53b${_scopeId}>`);
        if ($props.errors.password_confirmation) {
          _push2(`<span class="invalid-feedback d-block" data-v-5f03b53b${_scopeId}>${ssrInterpolate($props.errors.password_confirmation)}</span>`);
        } else {
          _push2(`<!---->`);
        }
        _push2(`</div><button class="btn btn-primary w-100" type="submit" data-v-5f03b53b${_scopeId}>${ssrInterpolate($setup.trans("Register"))}</button></form></div></div></div></div></div>`);
      } else {
        return [
          createVNode("div", { class: "container mt-5" }, [
            createVNode("div", { class: "row justify-content-center" }, [
              createVNode("div", { class: "col-md-6" }, [
                createVNode("div", { class: "card" }, [
                  createVNode("div", { class: "card-header text-center" }, [
                    createVNode("h3", null, toDisplayString($setup.trans("Register")), 1)
                  ]),
                  createVNode("div", { class: "card-body" }, [
                    createVNode("form", {
                      onSubmit: withModifiers(($event) => $setup.form.post(_ctx.route("register")), ["prevent"])
                    }, [
                      createVNode("div", { class: "row mb-3" }, [
                        createVNode("div", { class: "col-md-6" }, [
                          createVNode("label", {
                            class: "form-label",
                            for: "first_name"
                          }, toDisplayString($setup.trans("contact_us.first_name")), 1),
                          withDirectives(createVNode("input", {
                            id: "first_name",
                            "onUpdate:modelValue": ($event) => $setup.form.first_name = $event,
                            autofocus: "",
                            class: "form-control",
                            required: "",
                            type: "text",
                            maxlength: "120",
                            autocomplete: "given-name"
                          }, null, 8, ["onUpdate:modelValue"]), [
                            [vModelText, $setup.form.first_name]
                          ]),
                          $props.errors.first_name ? (openBlock(), createBlock("span", {
                            key: 0,
                            class: "invalid-feedback d-block"
                          }, toDisplayString($props.errors.first_name), 1)) : createCommentVNode("", true)
                        ]),
                        createVNode("div", { class: "col-md-6" }, [
                          createVNode("label", {
                            class: "form-label",
                            for: "last_name"
                          }, toDisplayString($setup.trans("contact_us.last_name")), 1),
                          withDirectives(createVNode("input", {
                            id: "last_name",
                            "onUpdate:modelValue": ($event) => $setup.form.last_name = $event,
                            class: "form-control",
                            required: "",
                            type: "text",
                            maxlength: "120",
                            autocomplete: "family-name"
                          }, null, 8, ["onUpdate:modelValue"]), [
                            [vModelText, $setup.form.last_name]
                          ]),
                          $props.errors.last_name ? (openBlock(), createBlock("span", {
                            key: 0,
                            class: "invalid-feedback d-block"
                          }, toDisplayString($props.errors.last_name), 1)) : createCommentVNode("", true)
                        ])
                      ]),
                      createVNode("div", { class: "mb-3" }, [
                        createVNode("label", {
                          class: "form-label",
                          for: "email"
                        }, toDisplayString($setup.trans("Email")), 1),
                        withDirectives(createVNode("input", {
                          id: "email",
                          "onUpdate:modelValue": ($event) => $setup.form.email = $event,
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
                          for: "register-mobile"
                        }, toDisplayString($setup.trans("Mobile")), 1),
                        createVNode(_component_PhoneCountryInput, {
                          modelValue: $setup.form.mobile,
                          "onUpdate:modelValue": ($event) => $setup.form.mobile = $event,
                          "input-id": "register-mobile",
                          placeholder: $setup.trans("auth_modal.mobile_national_placeholder"),
                          invalid: !!$props.errors.mobile,
                          required: ""
                        }, null, 8, ["modelValue", "onUpdate:modelValue", "placeholder", "invalid"]),
                        $props.errors.mobile ? (openBlock(), createBlock("span", {
                          key: 0,
                          class: "invalid-feedback d-block"
                        }, toDisplayString($props.errors.mobile), 1)) : createCommentVNode("", true)
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
                      createVNode("div", { class: "mb-3" }, [
                        createVNode("label", {
                          class: "form-label",
                          for: "password_confirmation"
                        }, toDisplayString($setup.trans("Confirm Password")), 1),
                        withDirectives(createVNode("input", {
                          id: "password_confirmation",
                          "onUpdate:modelValue": ($event) => $setup.form.password_confirmation = $event,
                          class: "form-control",
                          required: "",
                          type: "password_confirmation"
                        }, null, 8, ["onUpdate:modelValue"]), [
                          [vModelText, $setup.form.password_confirmation]
                        ]),
                        $props.errors.password_confirmation ? (openBlock(), createBlock("span", {
                          key: 0,
                          class: "invalid-feedback d-block"
                        }, toDisplayString($props.errors.password_confirmation), 1)) : createCommentVNode("", true)
                      ]),
                      createVNode("button", {
                        class: "btn btn-primary w-100",
                        type: "submit"
                      }, toDisplayString($setup.trans("Register")), 1)
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
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/User/resources/assets/js/Pages/Auth/Register.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
const Register = /* @__PURE__ */ _export_sfc(_sfc_main, [["ssrRender", _sfc_ssrRender], ["__scopeId", "data-v-5f03b53b"]]);
export {
  Register as default
};
