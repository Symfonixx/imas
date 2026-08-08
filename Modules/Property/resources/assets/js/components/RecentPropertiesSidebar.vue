<template>
    <div
        v-if="recentProperties.length > 0"
        class="imas-blog-v2-sidebar__box"
    >
        <h4 class="imas-blog-v2-sidebar__heading text-start">
            {{ trans("listing_page.recent_properties") }}
        </h4>
        <div class="imas-blog-v2-sidebar__recent">
            <a
                v-for="p in recentProperties"
                :key="p.id"
                :href="p.url"
                class="imas-blog-v2-sidebar__recent-item"
            >
                <img
                    :src="p.thumbnail_url"
                    :alt="displayTitle(p)"
                    loading="lazy"
                />
                <div class="imas-blog-v2-sidebar__recent-body">
                    <div class="imas-blog-v2-sidebar__recent-title">
                        {{ displayTitle(p) }}
                    </div>
                    <div
                        class="imas-blog-v2-sidebar__recent-date text-dim"
                    >
                        {{ formatMoney(propertyStartPrice(p)) }}
                    </div>
                </div>
            </a>
        </div>
    </div>
</template>

<script setup>
import { usePage } from "@inertiajs/vue3";
import { formatPropertyMoney, propertyStartPrice } from "../utils/propertyPrice.js";

defineProps({
    recentProperties: { type: Array, default: () => [] },
});

const page = usePage();

function trans(key) {
    return page.props.translations[key] || key;
}

function locale() {
    return page.props.locale || "en";
}

function displayTitle(p) {
    const t = p.title;
    if (typeof t === "string" && t.trim() !== "") {
        return t;
    }
    if (t && typeof t === "object") {
        const loc = locale();
        const raw =
            t[loc] ??
            t.en ??
            Object.values(t).find((v) => typeof v === "string");
        if (typeof raw === "string" && raw.trim() !== "") {
            return raw;
        }
    }
    const pn = p.project_name;
    if (typeof pn === "string" && pn.trim() !== "") {
        return pn;
    }
    if (typeof pn === "object" && pn !== null) {
        const loc = locale();
        const raw =
            pn[loc] ??
            pn.en ??
            Object.values(pn).find((v) => typeof v === "string");
        if (typeof raw === "string") {
            return raw;
        }
    }
    return p.project_code || "—";
}

function formatMoney(amount) {
    return formatPropertyMoney(amount, locale());
}
</script>
