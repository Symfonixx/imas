import { d as _plugin_vue_export_helper_default, m as useOpenAuthModal } from "../ssr.js";
import { t as _sfc_main$1 } from "./App-BI701647.js";
import { Head, usePage } from "@inertiajs/vue3";
import { computed, createVNode, onMounted, toDisplayString, unref, useSSRContext, withCtx } from "vue";
import { ssrInterpolate, ssrRenderComponent } from "vue/server-renderer";
//#region Modules/User/resources/assets/js/Pages/Auth/ForgotPassword.vue
var _sfc_main = {
	__name: "ForgotPassword",
	__ssrInlineRender: true,
	props: { errors: {
		type: Object,
		default: () => ({})
	} },
	setup(__props) {
		const page = usePage();
		const { openAuthModal } = useOpenAuthModal();
		const appName = computed(() => page.props.appName);
		const trans = (key) => page.props.translations?.[key] || key;
		function openForgotModal() {
			openAuthModal("forgot");
		}
		onMounted(() => {
			openForgotModal();
			window.setTimeout(openForgotModal, 50);
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<!--[-->`);
			_push(ssrRenderComponent(unref(Head), { title: `${trans("Forgot Password")} | ${appName.value}` }, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<meta head-key="robots" name="robots" content="noindex, nofollow" data-v-4299aaff${_scopeId}>`);
					else return [createVNode("meta", {
						"head-key": "robots",
						name: "robots",
						content: "noindex, nofollow"
					})];
				}),
				_: 1
			}, _parent));
			_push(ssrRenderComponent(_sfc_main$1, null, {
				default: withCtx((_, _push, _parent, _scopeId) => {
					if (_push) _push(`<div class="container mt-5 mb-5" data-v-4299aaff${_scopeId}><div class="row justify-content-center" data-v-4299aaff${_scopeId}><div class="col-md-6" data-v-4299aaff${_scopeId}><div class="card imas-auth-page-card" data-v-4299aaff${_scopeId}><div class="card-header text-center" data-v-4299aaff${_scopeId}><h3 class="text-md font-semibold mb-0" data-v-4299aaff${_scopeId}>${ssrInterpolate(trans("Forgot Password"))}</h3></div><div class="card-body text-center" data-v-4299aaff${_scopeId}><p class="text-sm text-dim mb-3" data-v-4299aaff${_scopeId}>${ssrInterpolate(trans("auth_modal.forgot_page_opening"))}</p><button type="button" class="btn btn-primary" data-v-4299aaff${_scopeId}>${ssrInterpolate(trans("Forgot Password"))}</button></div></div></div></div></div>`);
					else return [createVNode("div", { class: "container mt-5 mb-5" }, [createVNode("div", { class: "row justify-content-center" }, [createVNode("div", { class: "col-md-6" }, [createVNode("div", { class: "card imas-auth-page-card" }, [createVNode("div", { class: "card-header text-center" }, [createVNode("h3", { class: "text-md font-semibold mb-0" }, toDisplayString(trans("Forgot Password")), 1)]), createVNode("div", { class: "card-body text-center" }, [createVNode("p", { class: "text-sm text-dim mb-3" }, toDisplayString(trans("auth_modal.forgot_page_opening")), 1), createVNode("button", {
						type: "button",
						class: "btn btn-primary",
						onClick: openForgotModal
					}, toDisplayString(trans("Forgot Password")), 1)])])])])])];
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
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/User/resources/assets/js/Pages/Auth/ForgotPassword.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var ForgotPassword_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main, [["__scopeId", "data-v-4299aaff"]]);
//#endregion
export { ForgotPassword_default as default };
