<template>
    <section
        v-if="items.length"
        class="imas-contact-faq imas-contact-page__panel imas-contact-page__panel--faq"
        aria-labelledby="imas-contact-faq-title"
    >
        <header class="imas-contact-faq__header text-start">
            <h3
                id="imas-contact-faq-title"
                class="imas-contact-page__heading imas-contact-faq__title text-xl font-semibold text-start"
            >
                {{ title }}
            </h3>
            <p
                v-if="subtitle"
                class="imas-contact-faq__subtitle text-card-excerpt text-dim text-start"
            >
                {{ subtitle }}
            </p>
        </header>
        <ul class="imas-contact-faq__list">
            <li
                v-for="(item, index) in items"
                :key="index"
                class="imas-contact-faq__item"
                :class="{ 'imas-contact-faq__item--open': openIndex === index }"
            >
                <div class="imas-contact-faq__item-inner">
                    <button
                        type="button"
                        class="imas-contact-faq__trigger"
                        :aria-expanded="openIndex === index"
                        :aria-controls="`imas-contact-faq-panel-${index}`"
                        @click="toggle(index, $event)"
                    >
                        <span class="imas-contact-faq__question text-start">{{ item.question }}</span>
                        <span class="imas-contact-faq__icon" aria-hidden="true" />
                    </button>
                    <div
                        :id="`imas-contact-faq-panel-${index}`"
                        class="imas-contact-faq__content"
                        :hidden="openIndex !== index"
                    >
                        <p class="imas-contact-faq__answer text-card-excerpt text-dim text-start">
                            {{ item.answer }}
                        </p>
                    </div>
                </div>
            </li>
        </ul>
    </section>
</template>

<script setup>
import { computed, ref } from "vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const openIndex = ref(-1);

function trans(key) {
    return page.props.translations[key] ?? key;
}

const title = computed(() => trans("contact_us.faq.title"));
const subtitle = computed(() => {
    const value = trans("contact_us.faq.subtitle");
    return value === "contact_us.faq.subtitle" ? "" : value;
});

const items = computed(() => {
    const raw = page.props.translations["contact_us.faq.items"];
    if (!Array.isArray(raw)) {
        return [];
    }

    return raw.filter(
        (item) =>
            item &&
            typeof item.question === "string" &&
            item.question.trim() !== "" &&
            typeof item.answer === "string" &&
            item.answer.trim() !== "",
    );
});

function toggle(index, event) {
    openIndex.value = openIndex.value === index ? -1 : index;
    event?.currentTarget?.blur();
}
</script>
