<template>
    <section
        v-if="items.length"
        class="imas-contact-faq faq service-details imas-contact-page__panel"
        aria-labelledby="imas-contact-faq-title"
    >
        <h3
            id="imas-contact-faq-title"
            class="imas-contact-page__heading imas-contact-faq__title"
        >
            {{ title }}
        </h3>
        <p v-if="subtitle" class="imas-contact-faq__subtitle text-dim text-card-excerpt">
            {{ subtitle }}
        </p>
        <ul class="accordion accordion-1 one-open imas-contact-faq__list">
            <li
                v-for="(item, index) in items"
                :key="index"
                :class="{ active: openIndex === index }"
            >
                <button
                    type="button"
                    class="title imas-contact-faq__trigger"
                    :aria-expanded="openIndex === index"
                    @click="toggle(index)"
                >
                    <span>{{ item.question }}</span>
                </button>
                <div class="content imas-contact-faq__content">
                    <p>{{ item.answer }}</p>
                </div>
            </li>
        </ul>
    </section>
</template>

<script setup>
import { computed, ref } from "vue";
import { usePage } from "@inertiajs/vue3";

const page = usePage();
const openIndex = ref(0);

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

function toggle(index) {
    openIndex.value = openIndex.value === index ? -1 : index;
}
</script>
