<template>
    <div class="widget-boxed mt-5">
        <div class="widget-boxed-header mb-5">
            <h4>{{ trans("listing_page.feature_properties") }}</h4>
        </div>
        <div class="widget-boxed-body">
            <div class="slick-lancers imas-featured-properties-stack">
                <div
                    v-for="p in featuredProperties"
                    :key="p.id"
                    class="agents-grid mr-0"
                >
                    <div class="listing-item compact">
                        <a :href="p.url" class="listing-img-container">
                            <div class="listing-badges">
                                <span class="featured">{{ formatMoney(p.price) }}</span>
                                <span>{{ trans("listing_page.for_sale") }}</span>
                            </div>
                            <div class="listing-img-content">
                                <span class="listing-compact-title">
                                    {{ displayTitle(p) }}
                                    <i v-if="locationLine(p)">{{ locationLine(p) }}</i>
                                </span>
                                <ul class="listing-hidden-content">
                                    <li
                                        v-for="(row, idx) in statRows(p)"
                                        :key="idx"
                                    >
                                        {{ row.label }}
                                        <span>{{ row.value }}</span>
                                    </li>
                                </ul>
                            </div>
                            <img :src="p.thumbnail_url" alt="" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { usePage } from "@inertiajs/vue3";

defineProps({
    featuredProperties: { type: Array, default: () => [] },
});

const page = usePage();

function trans(key) {
    return page.props.translations[key] || key;
}

const locale = () => page.props.locale || "en";

function displayTitle(p) {
    const t = p.title;
    if (typeof t === "string" && t.trim() !== "") {
        return t;
    }
    if (t && typeof t === "object") {
        const loc = locale();
        const raw =
            t[loc] ?? t.en ?? Object.values(t).find((v) => typeof v === "string");
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

function locationLine(p) {
    const n = p.location?.name;
    if (typeof n === "string" && n.trim() !== "") {
        return n;
    }
    if (n && typeof n === "object") {
        const loc = locale();
        const raw =
            n[loc] ?? n.en ?? Object.values(n).find((v) => typeof v === "string");
        if (typeof raw === "string" && raw.trim() !== "") {
            return raw;
        }
    }
    return "";
}

function formatMoney(amount) {
    const n = Number(amount);
    if (!Number.isFinite(n)) {
        return "—";
    }
    return new Intl.NumberFormat(locale(), {
        style: "currency",
        currency: "USD",
        maximumFractionDigits: 0,
    }).format(n);
}

function statRows(p) {
    const attrs = Array.isArray(p.attributes) ? p.attributes : [];
    return attrs.slice(0, 4).map((a) => ({
        label: a.name || a.code || "",
        value: a.display || "",
    }));
}
</script>

<style scoped>
.imas-featured-properties-stack {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}
</style>
