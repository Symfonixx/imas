import { f as _plugin_vue_export_helper_default } from "../ssr.js";
import { useSSRContext } from "vue";
import { ssrRenderAttrs } from "vue/server-renderer";
//#region Modules/Corporate/resources/assets/js/Pages/index.vue
var _sfc_main = {};
function _sfc_ssrRender(_ctx, _push, _parent, _attrs) {
	_push(`<div${ssrRenderAttrs(_attrs)}></div>`);
}
var _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Corporate/resources/assets/js/Pages/index.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
var Pages_default = /* @__PURE__ */ _plugin_vue_export_helper_default(_sfc_main, [["ssrRender", _sfc_ssrRender]]);
//#endregion
export { Pages_default as default };
