import { u as _plugin_vue_export_helper_default } from "../ssr.js";
import { t as _sfc_main$1 } from "./App-Tm_yWILr.js";
import { Head, Link, useForm, usePage } from "@inertiajs/vue3";
import { computed, createBlock, createCommentVNode, createVNode, openBlock, resolveComponent, toDisplayString, useSSRContext, vModelText, withCtx, withDirectives, withModifiers } from "vue";
import { ssrInterpolate, ssrRenderAttr, ssrRenderComponent } from "vue/server-renderer";
//#region Modules/User/resources/assets/js/Pages/Auth/ForgotPassword.vue
var _sfc_main = {
	components: {
		AppLayout: _sfc_main$1,
		Link,
		Head
	},
	props: { errors: Object },
	setup() {
		const page = usePage();
		const appName = computed(() => page.props.appName);
		const trans = (key) => page.props.translations[key] || key;
		return {
			form: useForm({ email: "" }),
			appName,
			trans
		};
	}
};
function _sfc_ssrRender(_ctx, _push, _parent, _attrs, $props, $setup, $data, $options) {
	const _component_Head = resolveComponent("Head");
	const _component_app_layout = resolveComponent("app-layout");
	_push(`<!--[-->`);
	_push(ssrRenderComponent(_component_Head, { title: `${$setup.trans("Forgot Password")} | ${$setup.appName}` }, {
		default: withCtx((_, _push, _parent, _scopeId) => {
			if (_push) _push(`<meta head-key="robots" name="robots" content="noindex, nofollow" data-v-7903c506${_scopeId}>`);
			else return [createVNode("meta", {
				"head-key": "robots",
				name: "robots",
				content: "noindex, nofollow"
			})];
		}),
		_: 1
	}, _parent));
	_push(ssrRenderComponent(_component_app_layout, null, {
		default: withCtx((_, _push, _parent, _scopeId) => {
			if (_push) {
				_push(`<div class="container mt-5" data-v-7903c506${_scopeId}><div class="row justify-content-center" data-v-7903c506${_scopeId}><div class="col-md-6" data-v-7903c506${_scopeId}><div class="card" data-v-7903c506${_scopeId}><div class="card-header text-center" data-v-7903c506${_scopeId}><h3 data-v-7903c506${_scopeId}>${ssrInterpolate($setup.trans("Forgot Password"))}</h3></div><div class="card-body" data-v-7903c506${_scopeId}><form data-v-7903c506${_scopeId}><div class="mb-3" data-v-7903c506${_scopeId}><label class="form-label" for="email" data-v-7903c506${_scopeId}>${ssrInterpolate($setup.trans("Email"))}</label><input id="email"${ssrRenderAttr("value", $setup.form.email)} autofocus class="form-control" required type="email" data-v-7903c506${_scopeId}>`);
				if ($props.errors.email) _push(`<span class="invalid-feedback d-block" data-v-7903c506${_scopeId}>${ssrInterpolate($props.errors.email)}</span>`);
				else _push(`<!---->`);
				_push(`</div><button class="btn btn-primary w-100" type="submit" data-v-7903c506${_scopeId}>${ssrInterpolate($setup.trans("Send Email Verification"))}</button></form></div></div></div></div></div>`);
			} else return [createVNode("div", { class: "container mt-5" }, [createVNode("div", { class: "row justify-content-center" }, [createVNode("div", { class: "col-md-6" }, [createVNode("div", { class: "card" }, [createVNode("div", { class: "card-header text-center" }, [createVNode("h3", null, toDisplayString($setup.trans("Forgot Password")), 1)]), createVNode("div", { class: "card-body" }, [createVNode("form", { onSubmit: withModifiers(($event) => $setup.form.post(_ctx.route("password.email")), ["prevent"]) }, [createVNode("div", { class: "mb-3" }, [
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
				}, null, 8, ["onUpdate:modelValue"]), [[vModelText, $setup.form.email]]),
				$props.errors.email ? (openBlock(), createBlock("span", {
					key: 0,
					class: "invalid-feedback d-block"
				}, toDisplayString($props.errors.email), 1)) : createCommentVNode("", true)
			]), createVNode("button", {
				class: "btn btn-primary w-100",
				type: "submit"
			}, toDisplayString($setup.trans("Send Email Verification")), 1)], 40, ["onSubmit"])])])])])])];
		}),
		_: 1
	}, _parent));
	_push(`<!--]-->`);
}
var _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/User/resources/assets/js/Pages/Auth/ForgotPassword.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var ForgotPassword_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main, [["ssrRender", _sfc_ssrRender], ["__scopeId", "data-v-7903c506"]]);
//#endregion
export { ForgotPassword_default as default };
