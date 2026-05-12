<template>
    <div class="widget-boxed mt-5">
        <div class="widget-boxed-header">
            <h4>{{ trans("listing_page.recent_properties") }}</h4>
        </div>
        <div class="widget-boxed-body">
            <div class="recent-post">
                <div
                    v-for="p in recentProperties"
                    :key="p.id"
                    class="recent-main"
                >
                    <div class="recent-img">
                        <a :href="p.url"><img :src="p.thumbnail_url" alt="" /></a>
                    </div>
                    <div class="info-img">
                        <a :href="p.url"><h6>{{ displayTitle(p) }}</h6></a>
                        <p>{{ formatMoney(p.price) }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { usePage } from "@inertiajs/vue3";

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
</script>

<style scoped>
.recent-post .recent-main:not(:last-child) {
    margin-bottom: 1.5rem;
}
</style>
