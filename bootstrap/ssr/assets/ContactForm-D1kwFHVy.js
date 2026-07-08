import { ref, computed, watch, onBeforeUnmount, unref, useSSRContext } from "vue";
import { ssrRenderAttrs, ssrInterpolate, ssrRenderClass, ssrRenderAttr, ssrRenderComponent, ssrIncludeBooleanAttr } from "vue/server-renderer";
import { usePage, useForm } from "@inertiajs/vue3";
import { P as PhoneCountryInput } from "./PhoneCountryInput-wjibwJ1Y.js";
import { _ as _export_sfc } from "../ssr.js";
const _sfc_main = {
  __name: "ContactForm",
  __ssrInlineRender: true,
  props: {
    contactStoreUrl: {
      type: String,
      required: true
    },
    hideTitle: {
      type: Boolean,
      default: false
    },
    variant: {
      type: String,
      default: "page"
    },
    defaultSubject: {
      type: String,
      default: ""
    },
    hideSubject: {
      type: Boolean,
      default: false
    },
    defaultMessage: {
      type: String,
      default: ""
    },
    sourcePage: {
      type: String,
      default: ""
    }
  },
  setup(__props) {
    const props = __props;
    const page = usePage();
    ref(null);
    const flash = computed(() => page.props.flash ?? {});
    const successVisible = ref(false);
    let successHideTimer = null;
    function clearSuccessTimers() {
      if (successHideTimer) {
        clearTimeout(successHideTimer);
        successHideTimer = null;
      }
    }
    function showSuccessAlert() {
      clearSuccessTimers();
      successVisible.value = true;
      successHideTimer = setTimeout(() => {
        successVisible.value = false;
      }, 8e3);
    }
    watch(
      () => {
        var _a;
        return (_a = flash.value) == null ? void 0 : _a.contact_sent;
      },
      (sent) => {
        if (sent) {
          showSuccessAlert();
        }
      },
      { immediate: true }
    );
    onBeforeUnmount(clearSuccessTimers);
    const isPairedLayout = computed(() => props.variant !== "sidebar");
    const messageRows = computed(() => props.variant === "sidebar" ? 3 : 8);
    const pairRowClass = computed(
      () => isPairedLayout.value ? "imas-contact-form__pair" : null
    );
    const pairColClass = computed(
      () => isPairedLayout.value ? "imas-contact-form__pair-field" : null
    );
    const form = useForm({
      first_name: "",
      last_name: "",
      email: "",
      mobile: "",
      subject: props.defaultSubject ?? "",
      source_url: "",
      source_page: props.sourcePage ?? "",
      message: props.defaultMessage ?? ""
    });
    function applyDefaultSubject(value) {
      if (typeof value !== "string" || value.trim() === "") {
        return;
      }
      if (props.hideSubject || !form.subject) {
        form.subject = value;
      }
    }
    watch(() => props.defaultSubject, applyDefaultSubject, { immediate: true });
    function applyDefaultMessage(value) {
      if (typeof value !== "string" || value.trim() === "") {
        return;
      }
      if (!form.message) {
        form.message = value;
      }
    }
    watch(() => props.defaultMessage, applyDefaultMessage, { immediate: true });
    function applySourcePage(value) {
      if (typeof value !== "string" || value.trim() === "") {
        return;
      }
      form.source_page = value;
    }
    watch(() => props.sourcePage, applySourcePage, { immediate: true });
    watch(
      () => props.hideSubject,
      (hidden) => {
        if (hidden) {
          applyDefaultSubject(props.defaultSubject);
        }
      }
    );
    function trans(key) {
      return page.props.translations[key] || key;
    }
    return (_ctx, _push, _parent, _attrs) => {
      _push(`<div${ssrRenderAttrs(_attrs)} data-v-8ed81194>`);
      if (!__props.hideTitle) {
        _push(`<h3 class="imas-contact-page__heading text-xl font-semibold mb-4 text-start" data-v-8ed81194>${ssrInterpolate(trans("contact_us.title"))}</h3>`);
      } else {
        _push(`<!---->`);
      }
      if (successVisible.value) {
        _push(`<div class="alert alert-success imas-contact-page__alert imas-contact-page__alert--success text-start" role="status" data-v-8ed81194>${ssrInterpolate(trans("contact_us.message_sent"))}</div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`<form class="${ssrRenderClass([{ "imas-contact-form--sidebar": __props.variant === "sidebar" }, "contact-form imas-contact-form"])}" data-v-8ed81194><div class="imas-contact-form__pair" data-v-8ed81194><div class="imas-contact-form__pair-field" data-v-8ed81194><div class="form-group" data-v-8ed81194><input${ssrRenderAttr("value", unref(form).first_name)} type="text" required maxlength="120" class="${ssrRenderClass([{ "is-invalid": unref(form).errors.first_name }, "form-control input-custom input-full"])}"${ssrRenderAttr("placeholder", trans("contact_us.first_name"))} autocomplete="given-name" data-v-8ed81194>`);
      if (unref(form).errors.first_name) {
        _push(`<div class="invalid-feedback d-block" data-v-8ed81194>${ssrInterpolate(unref(form).errors.first_name)}</div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div><div class="imas-contact-form__pair-field" data-v-8ed81194><div class="form-group" data-v-8ed81194><input${ssrRenderAttr("value", unref(form).last_name)} type="text" required maxlength="120" class="${ssrRenderClass([{ "is-invalid": unref(form).errors.last_name }, "form-control input-custom input-full"])}"${ssrRenderAttr("placeholder", trans("contact_us.last_name"))} autocomplete="family-name" data-v-8ed81194>`);
      if (unref(form).errors.last_name) {
        _push(`<div class="invalid-feedback d-block" data-v-8ed81194>${ssrInterpolate(unref(form).errors.last_name)}</div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div></div><div class="${ssrRenderClass([
        pairRowClass.value,
        isPairedLayout.value && "imas-contact-form__pair--stack-sm"
      ])}" data-v-8ed81194><div class="${ssrRenderClass(pairColClass.value)}" data-v-8ed81194><div class="form-group" data-v-8ed81194><input${ssrRenderAttr("value", unref(form).email)} type="email" required maxlength="255" class="${ssrRenderClass([{ "is-invalid": unref(form).errors.email }, "form-control input-custom input-full"])}"${ssrRenderAttr("placeholder", trans("contact_us.email"))} autocomplete="email" data-v-8ed81194>`);
      if (unref(form).errors.email) {
        _push(`<div class="invalid-feedback d-block" data-v-8ed81194>${ssrInterpolate(unref(form).errors.email)}</div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div><div class="${ssrRenderClass(pairColClass.value)}" data-v-8ed81194><div class="form-group" data-v-8ed81194>`);
      _push(ssrRenderComponent(PhoneCountryInput, {
        class: "phon_num_input",
        modelValue: unref(form).mobile,
        "onUpdate:modelValue": ($event) => unref(form).mobile = $event,
        "input-id": "imas-contact-mobile",
        placeholder: trans("auth_modal.mobile_national_placeholder"),
        invalid: !!unref(form).errors.mobile
      }, null, _parent));
      if (unref(form).errors.mobile) {
        _push(`<div class="invalid-feedback d-block" data-v-8ed81194>${ssrInterpolate(unref(form).errors.mobile)}</div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div></div></div>`);
      if (!__props.hideSubject) {
        _push(`<div class="form-group" data-v-8ed81194><input${ssrRenderAttr("value", unref(form).subject)} type="text" class="${ssrRenderClass([{ "is-invalid": unref(form).errors.subject }, "form-control input-custom input-full"])}"${ssrRenderAttr("placeholder", trans("contact_us.subject_optional"))} data-v-8ed81194>`);
        if (unref(form).errors.subject) {
          _push(`<div class="invalid-feedback d-block" data-v-8ed81194>${ssrInterpolate(unref(form).errors.subject)}</div>`);
        } else {
          _push(`<!---->`);
        }
        _push(`</div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`<div class="form-group" data-v-8ed81194><textarea${ssrRenderAttr("rows", messageRows.value)} required maxlength="5000" class="${ssrRenderClass([{ "is-invalid": unref(form).errors.message }, "form-control textarea-custom input-full"])}"${ssrRenderAttr("placeholder", trans("contact_us.message"))} data-v-8ed81194>${ssrInterpolate(unref(form).message)}</textarea>`);
      if (unref(form).errors.message) {
        _push(`<div class="invalid-feedback d-block" data-v-8ed81194>${ssrInterpolate(unref(form).errors.message)}</div>`);
      } else {
        _push(`<!---->`);
      }
      _push(`</div><div class="d-flex justify-content-start" data-v-8ed81194><button type="submit" class="${ssrRenderClass([
        __props.variant === "sidebar" ? "multiple-send-message w-100" : "btn-lg",
        "btn btn-primary imas-contact-page__submit"
      ])}"${ssrIncludeBooleanAttr(unref(form).processing) ? " disabled" : ""} data-v-8ed81194>${ssrInterpolate(__props.variant === "sidebar" ? trans("property_show.connect_with_us_today") : trans("contact_us.submit"))}</button></div></form></div>`);
    };
  }
};
const _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
  const ssrContext = useSSRContext();
  (ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Support/resources/assets/js/Components/ContactForm.vue");
  return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
const ContactForm = /* @__PURE__ */ _export_sfc(_sfc_main, [["__scopeId", "data-v-8ed81194"]]);
export {
  ContactForm as C
};
