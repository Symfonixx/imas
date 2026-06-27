<template>
    <div class="call-info imas-contact-page__details">
        <h3 class="imas-contact-page__heading text-xl font-semibold text-start">
            {{ trans("contact_us.contact_details") }}
        </h3>
        <p class="imas-contact-page__intro text-card-excerpt text-dim mb-5 text-start">
            {{
                trans(
                    "contact_us.Please_find_below_contact_details_and_contact_us_today",
                )
            }}
        </p>
        <ul>
            <li v-if="contact.address">
                <div class="info text-start">
                    <i class="fa fa-map-marker m-end" aria-hidden="true"></i>
                    <p class="in-p">{{ contact.address }}</p>
                </div>
            </li>
            <li v-if="phoneDisplay" class="imas-contact-phone">
                <div class="info">
                    <i class="fa fa-phone m-end" aria-hidden="true"></i>
                    <p class="in-p">
                        <a
                            v-if="phoneHref"
                            :href="phoneHref"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            {{ phoneDisplay }}
                        </a>
                        <template v-else>{{ phoneDisplay }}</template>
                    </p>
                </div>
            </li>
            <li v-if="contact.email" class="imas-contact-email">
                <div class="info">
                    <i class="fa fa-envelope m-end" aria-hidden="true"></i>
                    <p class="in-p ti">
                        <a :href="'mailto:' + contact.email">{{ contact.email }}</a>
                    </p>
                </div>
            </li>
        </ul>

        <template v-if="socialLinks.length">
            <h4
                class="imas-contact-page__social-title text-lg font-semibold mt-4 mb-3 text-start"
            >
                {{ trans("contact_us.follow_us") }}
            </h4>
            <ul class="netsocials d-flex flex-wrap ">
                <li v-for="item in socialLinks" :key="item.key">
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
        </template>
    </div>
</template>

<script setup>
import { computed } from "vue";
import { usePage } from "@inertiajs/vue3";
import { resolveWhatsAppContactHref } from "@/utils/whatsappUrl.js";
import {
    formatTurkishPhone,
    normalizeTurkishPhoneDigits,
} from "@/utils/turkishPhone.js";

const page = usePage();

const globals = computed(() => page.props.globals ?? {});
const settings = computed(() => page.props.settings ?? {});
const contact = computed(() => globals.value.contact ?? {});

const rawPhone = computed(() => String(contact.value.phone ?? "").trim());

const phoneDisplay = computed(
    () => formatTurkishPhone(rawPhone.value) || rawPhone.value,
);

const phoneHref = computed(() => {
    const raw = rawPhone.value;
    if (!raw) {
        return "";
    }

    const social = globals.value.social ?? {};
    const normalized = normalizeTurkishPhoneDigits(raw);
    const phoneForWhatsApp = normalized ? `+${normalized}` : raw;

    return resolveWhatsAppContactHref({
        whatsapp: social.whatsapp || settings.value.whatsapp,
        phone: phoneForWhatsApp,
    });
});

const socialLinks = computed(() => {
    const s = globals.value.social ?? {};
    const defs = [
        { key: "facebook", label: "Facebook", icon: "fa fa-facebook" },
        { key: "twitter", label: "Twitter", icon: "fa fa-twitter" },
        { key: "instagram", label: "Instagram", icon: "fab fa-instagram" },
        { key: "youtube", label: "YouTube", icon: "fa fa-youtube" },
        { key: "tiktok", label: "TikTok", icon: "fab fa-tiktok" },
        { key: "whatsapp", label: "WhatsApp", icon: "fa fa-whatsapp" },
    ];
    return defs
        .map((d) => {
            const raw = String(s[d.key] ?? "").trim();
            if (!raw) {
                return null;
            }
            const href =
                d.key === "whatsapp"
                    ? resolveWhatsAppContactHref({
                          whatsapp: raw,
                          phone: contact.value.phone,
                      })
                    : raw;
            if (!href) {
                return null;
            }
            return { ...d, href };
        })
        .filter(Boolean);
});

function trans(key) {
    return page.props.translations[key] || key;
}
</script>

<style scoped>
.in-p a {
    color: inherit;
    text-decoration: none;
    transition: color 0.2s ease;
}

.in-p a:hover {
    color: var(--brand-gold, #d9a800);
}

.netsocials {
    gap: 25px;
}

html[dir="rtl"] .m-end {
    margin-inline-end: 1.5rem !important;
}
</style>
