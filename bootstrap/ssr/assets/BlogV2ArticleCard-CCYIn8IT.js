import { computed, mergeProps, onBeforeUnmount, onMounted, ref, useSSRContext } from "vue";
import { ssrInterpolate, ssrRenderAttr, ssrRenderAttrs, ssrRenderClass } from "vue/server-renderer";
//#region Modules/Cms/resources/assets/js/Components/BlogV2ArticleCard.vue
var _sfc_main = {
	__name: "BlogV2ArticleCard",
	__ssrInlineRender: true,
	props: {
		article: {
			type: Object,
			required: true
		},
		staggerIndex: {
			type: Number,
			default: 0
		},
		readMoreLabel: {
			type: String,
			default: "Read more"
		},
		readArticleLabel: {
			type: String,
			default: "Read article ›"
		}
	},
	setup(__props) {
		const props = __props;
		const cardRef = ref(null);
		const isVisible = ref(false);
		let observer = null;
		const staggerStyle = computed(() => ({ transitionDelay: `${props.staggerIndex * 100}ms` }));
		const categoryName = computed(() => {
			const name = props.article?.category?.name;
			return typeof name === "string" && name.trim() !== "" ? name.trim() : "";
		});
		onMounted(() => {
			const el = cardRef.value;
			if (!el) return;
			if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
				isVisible.value = true;
				return;
			}
			observer = new IntersectionObserver((entries) => {
				entries.forEach((entry) => {
					if (entry.isIntersecting) {
						isVisible.value = true;
						observer?.unobserve(entry.target);
					}
				});
			}, { threshold: .12 });
			observer.observe(el);
		});
		onBeforeUnmount(() => {
			observer?.disconnect();
			observer = null;
		});
		return (_ctx, _push, _parent, _attrs) => {
			_push(`<article${ssrRenderAttrs(mergeProps({
				ref_key: "cardRef",
				ref: cardRef,
				class: ["imas-blog-v2-card", { "is-visible": isVisible.value }],
				style: staggerStyle.value
			}, _attrs))}><a${ssrRenderAttr("href", __props.article.url)} class="imas-blog-v2-card__link"${ssrRenderAttr("aria-label", __props.article.title)}></a><div class="imas-blog-v2-card__thumb"><img${ssrRenderAttr("src", __props.article.image)}${ssrRenderAttr("alt", __props.article.title)} loading="lazy"></div>`);
			if (categoryName.value) _push(`<span class="imas-blog-show__category-label imas-blog-v2-card__category-label">${ssrInterpolate(categoryName.value)}</span>`);
			else _push(`<!---->`);
			_push(`<div class="${ssrRenderClass([{ "imas-blog-v2-card__body--has-category": categoryName.value }, "imas-blog-v2-card__body"])}"><h3 class="imas-blog-v2-card__title text-md font-semibold text-start">${ssrInterpolate(__props.article.title)}</h3><div class="imas-blog-v2-card__meta text-md text-dim">`);
			if (__props.article.date) _push(`<span>${ssrInterpolate(__props.article.date)}</span>`);
			else _push(`<!---->`);
			if (__props.article.date && __props.article.visits != null) _push(`<span class="imas-blog-v2-card__dot" aria-hidden="true">/</span>`);
			else _push(`<!---->`);
			if (__props.article.visits != null) _push(`<span class="imas-blog-v2-card__views"><i class="fa fa-eye" aria-hidden="true"></i> ${ssrInterpolate(__props.article.visits)}</span>`);
			else _push(`<!---->`);
			_push(`</div>`);
			if (__props.article.excerpt) _push(`<p class="imas-blog-v2-card__excerpt text-card-excerpt text-dim">${ssrInterpolate(__props.article.excerpt)}</p>`);
			else _push(`<!---->`);
			_push(`<div class="imas-blog-v2-card__cta-wrap"><span class="imas-blog-v2-card__cta-text">${ssrInterpolate(__props.readMoreLabel)}</span><span class="imas-blog-v2-card__pill">${ssrInterpolate(__props.readArticleLabel)}</span></div></div></article>`);
		};
	}
};
var _sfc_setup = _sfc_main.setup;
_sfc_main.setup = (props, ctx) => {
	const ssrContext = useSSRContext();
	(ssrContext.modules || (ssrContext.modules = /* @__PURE__ */ new Set())).add("Modules/Cms/resources/assets/js/Components/BlogV2ArticleCard.vue");
	return _sfc_setup ? _sfc_setup(props, ctx) : void 0;
};
//#endregion
export { _sfc_main as t };
