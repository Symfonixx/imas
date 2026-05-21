<template>
    <div class="imas-top-bar topbar" role="region" :aria-label="trans('Contacts')">
        <div class="container imas-nav__container imas-top-bar__inner">
            <div class="imas-top-bar__contacts contact">
                <a
                    v-if="phoneDisplay"
                    class="imas-top-bar__link"
                    :href="phoneHref"
                >
                    <i class="fa fa-phone" aria-hidden="true"></i>
                    <span>{{ phoneDisplay }}</span>
                </a>
                <a
                    v-if="emailDisplay"
                    class="imas-top-bar__link"
                    :href="emailHref"
                >
                    <i class="fa fa-envelope" aria-hidden="true"></i>
                    <span>{{ emailDisplay }}</span>
                </a>
                <template v-if="topBarPages.length && hasContactInfo">
                    <span
                        class="imas-top-bar__separator"
                        aria-hidden="true"
                        >|</span
                    >
                </template>
                <Link
                    v-for="p in topBarPages"
                    :key="p.id"
                    class="imas-top-bar__link imas-top-bar__page-link"
                    :href="cmsPageUrl(p.slug)"
                >
                    {{ p.title }}
                </Link>
            </div>
            <ul
                v-if="topSocialLinks.length"
                class="imas-top-bar__socials socials"
                :aria-label="trans('Social media')"
            >
                <li v-for="item in topSocialLinks" :key="item.key">
                    <a
                        :href="item.href"
                        target="_blank"
                        rel="noopener noreferrer"
                        :aria-label="item.label"
                    >
                        <i :class="item.icon" aria-hidden="true"></i>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { Link, usePage } from "@inertiajs/vue3";
import { cmsPageUrl } from "@/utils/cmsPageUrl.js";

const page = usePage();

const settings = computed(() => page.props.settings || {});

const topBarPages = computed(
    () => page.props.globals?.pages?.top_bar ?? [],
);

const fallbackPhone = "+456 875 369 208";
const fallbackEmail = "support@example.com";

const phoneDisplay = computed(
    () =>
        String(settings.value.contact_phone || settings.value.phone || "").trim() ||
        fallbackPhone,
);
const emailDisplay = computed(
    () =>
        String(settings.value.contact_email || settings.value.email || "").trim() ||
        fallbackEmail,
);

const hasContactInfo = computed(
    () => Boolean(phoneDisplay.value || emailDisplay.value),
);

const phoneHref = computed(() => {
    const raw = String(settings.value.contact_phone || settings.value.phone || "").trim();
    const digits = raw.replace(/[^\d+]/g, "");
    return digits ? `tel:${digits}` : `tel:${fallbackPhone.replace(/[^\d+]/g, "")}`;
});

const emailHref = computed(() => {
    const e = String(settings.value.contact_email || settings.value.email || "").trim();
    return `mailto:${e || fallbackEmail}`;
});

/** Same network list as `UserFooter.vue` `footerSocialLinks`. */
const topSocialLinks = computed(() => {
    const s = settings.value;
    const defs = [
        { key: "facebook", label: "Facebook", icon: "fa fa-facebook" },
        { key: "twitter", label: "Twitter", icon: "fa fa-twitter" },
        { key: "instagram", label: "Instagram", icon: "fab fa-instagram" },
        { key: "youtube", label: "YouTube", icon: "fa fa-youtube" },
        { key: "tiktok", label: "TikTok", icon: "fab fa-tiktok" },
    ];
    return defs
        .map((d) => {
            const raw = String(s[d.key] ?? "").trim();
            if (!raw) {
                return null;
            }
            return { ...d, href: raw };
        })
        .filter(Boolean);
});

function trans(key) {
    return page.props.translations[key] || key;
}
</script>

<style scoped>
.imas-top-bar {
    background: var(--footer-bg, #06101f);
    border-bottom: 1px solid var(--divider, rgba(255, 255, 255, 0.06));
    color: var(--text-dim, #9aa6bd);
    font-size: 0.875rem;
    font-weight: 400;
    position: relative;
    z-index: var(--z-top-bar, 100000);
    width: 100%;
}

.imas-top-bar__inner {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 0.5rem 1rem;
    min-height: 38px;
}

.imas-top-bar__contacts {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0 18px;
}

.imas-top-bar__contacts > .imas-top-bar__link + .imas-top-bar__link,
.imas-top-bar__contacts > span + .imas-top-bar__link {
    margin-inline-start: 0;
}

.imas-top-bar__link {
    color: var(--text-dim, #9aa6bd);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    white-space: nowrap;
}

.imas-top-bar__link:hover {
    color: var(--brand-gold, #d9a800);
    text-decoration: none;
}

.imas-top-bar__link i {
    font-size: 0.85rem;
    opacity: 0.9;
}

.imas-top-bar__separator {
    color: rgba(255, 255, 255, 0.45);
    user-select: none;
    line-height: 1;
}

.imas-top-bar__page-link {
    font-weight: 500;
}

.imas-top-bar__socials {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    list-style: none;
    margin: 0;
    padding: 0;
    gap: 0;
}

.imas-top-bar__socials li + li {
    margin-inline-start: 12px;
}

.imas-top-bar__socials a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: var(--text-dim, #9aa6bd);
    text-decoration: none;
    transition: color 0.2s ease;
}

.imas-top-bar__socials a:hover {
    color: var(--brand-gold, #d9a800);
}

.imas-top-bar__socials i {
    font-size: 0.95rem;
    line-height: 1;
}
</style>
