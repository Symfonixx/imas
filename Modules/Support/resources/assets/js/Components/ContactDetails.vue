<template>
    <div class="call-info">
        <h3 class="text-start">{{ trans("contact_us.contact_details") }}</h3>
        <p class="mb-5 text-start">
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
            <li v-if="contact.phone" class="imas-contact-phone">
                <div class="info">
                    <i class="fa fa-phone m-end" aria-hidden="true"></i>
                    <p class="in-p">
                        <a v-if="phoneTel" :href="'tel:' + phoneTel">{{
                            contact.phone
                        }}</a>
                        <template v-else>{{ contact.phone }}</template>
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
            <h4 class="mt-4 mb-3 text-white text-start">
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

const page = usePage();

const globals = computed(() => page.props.globals ?? {});
const contact = computed(() => globals.value.contact ?? {});

const phoneTel = computed(() => {
    const p = contact.value.phone;
    if (typeof p !== "string") {
        return "";
    }
    return p.replace(/[^\d+]/g, "");
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
            return { ...d, href: raw };
        })
        .filter(Boolean);
});

function trans(key) {
    return page.props.translations[key] || key;
}
</script>

<style scoped>
.imas-contact-phone .in-p,
.imas-contact-phone .in-p a,
.imas-contact-email .in-p,
.imas-contact-email .in-p a {
    color: #ffffff !important;
}

.imas-contact-phone .in-p a:hover,
.imas-contact-phone .in-p a:focus,
.imas-contact-email .in-p a:hover,
.imas-contact-email .in-p a:focus {
    color: #ffffff !important;
    opacity: 0.9;
}
.netsocials{
    gap:25px
}

.call-info .netsocials a,
.call-info .netsocials a i {
    color: var(--brand-gold, #d9a800) !important;
    font-size: 27px;
}

.call-info .netsocials a:hover i,
.call-info .netsocials a:focus i {
    color: var(--brand-gold-hover, #eecb3a) !important;
}
html[dir="rtl"] .m-end{
    margin-inline-end: 1.5rem !important;
}
</style>
