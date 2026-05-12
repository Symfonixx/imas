<template>
    <div class="widget-boxed mt-5 imas-recent-properties-sidebar">
        <div class="widget-boxed-header d-flex justify-content-between align-items-center">
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

<style lang="scss">
/* Unique root: overrides theme `.inner-pages .recent-img img` (90×70 + margin-right). */
.imas-recent-properties-sidebar .recent-post .recent-main:not(:last-child) {
    margin-bottom: 1.5rem;
}

.imas-recent-properties-sidebar .recent-main {
    display: flex;
    flex-direction: row;
    align-items: flex-start;
    gap: 0.875rem;
}

.imas-recent-properties-sidebar .recent-img {
    flex: 0 0 120px;
    width: 120px;
    height: 70px;
    min-width: 120px;
    min-height: 70px;
    max-width: 120px;
    max-height: 70px;
    overflow: hidden;
    border-radius: 4px;
}

.imas-recent-properties-sidebar .recent-img > a {
    display: block;
    width: 100%;
    height: 100%;
    line-height: 0;
}

.imas-recent-properties-sidebar .recent-img img {
    display: block;
    width: 100% !important;
    height: 100% !important;
    max-width: none !important;
    max-height: none !important;
    object-fit: cover;
    object-position: center;
    margin: 0 !important;
}

.imas-recent-properties-sidebar .info-img {
    flex: 1 1 0;
    min-width: 0;
    text-align: start;
}

.imas-recent-properties-sidebar .info-img a {
    display: block;
    text-align: start;
}

.imas-recent-properties-sidebar .info-img p {
    text-align: start;
    margin-bottom: 0;
}

.imas-recent-properties-sidebar .info-img h6 {
    display: -webkit-box;
    -webkit-box-orient: vertical;
    line-clamp: 2;
    -webkit-line-clamp: 2;
    overflow: hidden;
    text-overflow: ellipsis;
    word-break: break-word;
    overflow-wrap: anywhere;
    line-height: 1.35;
    margin: 0;
    text-align: start;
    /* Reserve two lines so rows align when title is short */
    min-height: calc(1.35em * 2);
}

@media screen and (max-width: 992px) {
    .inner-pages .imas-recent-properties-sidebar .recent-main {
        flex-wrap: nowrap;
    }

    .inner-pages .imas-recent-properties-sidebar .info-img {
        margin-top: 0;
    }
}

.inner-pages .imas-recent-properties-sidebar .recent-img img {
    width: 100% !important;
    height: 100% !important;
    margin: 0 !important;
}
</style>
