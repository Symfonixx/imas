<template>
    <div v-if="attributes.length > 0" class="imas-property-attributes mb-30">
        <div class="imas-property-attributes__grid">
            <section
                v-for="attribute in attributes"
                :key="attribute.id"
                class="imas-property-attributes__card imas-property-show-panel"
                :class="`imas-property-attributes__card--${attribute.layout}`"
            >
                <h5
                    class="imas-section-title imas-property-attributes__title mb-4"
                >
                    <img
                        v-if="attribute.icon_url"
                        class="imas-property-attributes__icon"
                        :src="attribute.icon_url"
                        alt=""
                        aria-hidden="true"
                        width="36"
                        height="36"
                        loading="lazy"
                    />
                    <span>{{ attribute.name }}</span>
                </h5>
                <div class="imas-property-attributes__value">
                    <p
                        v-if="attribute.type === 'textarea'"
                        class="imas-property-attributes__text"
                    >
                        {{ attribute.value }}
                    </p>

                    <span v-else-if="attribute.type === 'text'">{{
                        attribute.value
                    }}</span>

                    <span v-else-if="attribute.type === 'number'">{{
                        formatNumber(attribute.value)
                    }}</span>

                    <span
                        v-else-if="attribute.type === 'price'"
                        class="text-gold"
                        >{{ formatPrice(attribute.value) }}</span
                    >

                    <span
                        v-else-if="attribute.type === 'boolean'"
                        class="imas-property-attributes__flag"
                        :class="{
                            'imas-property-attributes__flag--on': attribute.value,
                        }"
                    >
                        <i
                            class="fa"
                            :class="attribute.value ? 'fa-check' : 'fa-times'"
                            aria-hidden="true"
                        ></i>
                        <span>{{
                            attribute.value ? trans("property_show.yes") : trans("property_show.no")
                        }}</span>
                    </span>

                    <span
                        v-else-if="
                            attribute.type === 'radio' ||
                            attribute.type === 'select'
                        "
                        class="imas-property-attributes__option"
                    >
                        <i
                            v-if="attribute.value.icon"
                            :class="attribute.value.icon"
                            class="imas-property-attributes__option-icon"
                            aria-hidden="true"
                        ></i>
                        <span>{{ attribute.value.label }}</span>
                    </span>

                    <ul
                        v-else-if="
                            attribute.type === 'checkbox' ||
                            attribute.type === 'multiselect'
                        "
                        class="imas-property-attributes__chips"
                    >
                        <li
                            v-for="option in attribute.value"
                            :key="option.id"
                            class="imas-property-attributes__chip"
                        >
                            <i
                                v-if="option.icon"
                                :class="option.icon"
                                class="imas-property-attributes__option-icon"
                                aria-hidden="true"
                            ></i>
                            <span>{{ option.label }}</span>
                        </li>
                    </ul>

                    <img
                        v-else-if="attribute.type === 'image'"
                        class="imas-property-attributes__image"
                        :src="attribute.value.url"
                        :alt="attribute.value.alt"
                        loading="lazy"
                    />

                    <div
                        v-else-if="attribute.type === 'gallery'"
                        class="imas-property-attributes__gallery"
                    >
                        <img
                            v-for="(image, index) in attribute.value"
                            :key="`${attribute.id}-${index}`"
                            class="imas-property-attributes__image"
                            :src="image.url"
                            :alt="image.alt"
                            loading="lazy"
                        />
                    </div>

                    <a
                        v-else-if="attribute.type === 'file'"
                        class="imas-property-attributes__file"
                        :href="attribute.value.url"
                        target="_blank"
                        rel="noopener"
                        :download="attribute.value.name"
                    >
                        <i class="fa fa-download" aria-hidden="true"></i>
                        <span>{{ attribute.value.name }}</span>
                    </a>

                    <span v-else-if="attribute.type === 'date'">{{
                        formatDate(attribute.value)
                    }}</span>

                    <span v-else-if="attribute.type === 'datetime'">{{
                        formatDateTime(attribute.value)
                    }}</span>
                </div>
            </section>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { formatPropertyMoney } from "../utils/propertyPrice.js";

defineProps({
    attributes: { type: Array, default: () => [] },
});

const page = usePage();
const locale = computed(() => page.props.locale || "en");

function trans(key) {
    return page.props.translations?.[key] || key;
}

function formatNumber(value) {
    const number = Number(value);
    if (!Number.isFinite(number)) {
        return "—";
    }

    return new Intl.NumberFormat(locale.value, {
        maximumFractionDigits: 2,
    }).format(number);
}

function formatPrice(value) {
    return formatPropertyMoney(value, locale.value);
}

function formatDate(value) {
    return formatTemporal(value, {
        year: "numeric",
        month: "long",
        day: "numeric",
    });
}

function formatDateTime(value) {
    return formatTemporal(value, {
        year: "numeric",
        month: "long",
        day: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

function formatTemporal(value, options) {
    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) {
        return typeof value === "string" ? value : "—";
    }

    return new Intl.DateTimeFormat(locale.value, options).format(parsed);
}
</script>

<style scoped lang="scss">
.imas-property-attributes__grid {
    display: grid;
    grid-template-columns: 1fr;
    gap: 1.25rem;
    margin: 0;
}

@media (min-width: 768px) {
    .imas-property-attributes__grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
    }

    .imas-property-attributes__card--block {
        grid-column: 1 / -1;
    }
}

.imas-property-attributes__card {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    gap: 0.35rem;
    text-align: start;
}

.imas-property-attributes__title.imas-section-title {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    max-width: 100%;
}

.imas-property-attributes__icon {
    width: 24px;
    height: 24px;
    object-fit: contain;
    flex: 0 0 auto;
}

.imas-property-attributes__value {
    width: 100%;
    margin: 0;
    font-size: var(--text-md);
    color: var(--text);
    overflow-wrap: anywhere;
}

.imas-property-attributes__text {
    margin: 0;
    white-space: pre-line;
    line-height: var(--line-height-base);
}

.imas-property-attributes__flag {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    color: var(--text-muted);
}

.imas-property-attributes__flag--on {
    color: var(--brand-gold);
}

.imas-property-attributes__chips {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin: 0;
    padding: 0;
    list-style: none;
}

.imas-property-attributes__chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 5px 10px;
    border-radius: 4px;
    background: var(--chip-bg);
    color: var(--brand-gold);
    font-size: var(--text-sm);
    font-weight: 500;
}

.imas-property-attributes__option {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
}

.imas-property-attributes__option-icon {
    flex: 0 0 auto;
    font-size: 1.05em;
    line-height: 1;
    color: var(--brand-gold);
}

.imas-property-attributes__gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 0.75rem;
}

.imas-property-attributes__image {
    display: block;
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    background: var(--surface-2);
}

.imas-property-attributes__gallery .imas-property-attributes__image {
    width: 100%;
    aspect-ratio: 4 / 3;
    object-fit: cover;
}

.imas-property-attributes__value > .imas-property-attributes__image {
    width: 100%;
    max-height: 360px;
    object-fit: cover;
}

.imas-property-attributes__file {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    color: var(--brand-gold);
    font-weight: 600;
}

.imas-property-attributes__file:hover,
.imas-property-attributes__file:focus-visible {
    color: var(--brand-gold-hover);
}

.imas-property-attributes__file:focus-visible {
    box-shadow: var(--ring);
    border-radius: 4px;
}
</style>
