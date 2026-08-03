import Alpine from 'alpinejs';
import axios from 'axios';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
}

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('imasAuthModal', (config = {}) => ({
        open: false,
        tab: config.startTab === 'forgot' ? 'login' : (config.startTab || 'login'),
        subview: config.startTab === 'forgot' ? 'forgot' : null,
        resetToken: config.resetToken || '',
        resetEmail: config.resetEmail || '',
        countryId: config.defaultCountryId,
        mobileLocal: '',
        termsAccepted: false,
        termsError: '',
        showLoginPass: false,
        showRegPass: false,
        showRegConfirm: false,
        notes: {
            login: config.loginNote || 'Login',
            register: config.registerNote || 'Register',
            reset: config.resetNote || 'Reset Password',
            forgot: config.forgotNote || 'Forgot Password',
            termsRequired: config.termsRequired || 'Please accept the terms',
            mobileInvalid: config.mobileInvalid || 'Invalid mobile',
        },
        init() {
            const path = window.location.pathname || '';
            if (/\/reset-password\//.test(path)) {
                this.openWith('reset');
            } else if (/\/forgot-password\/?$/.test(path)) {
                this.openWith('forgot');
            } else if (config.flashStatus) {
                this.openWith('login');
            }
            this.$watch('open', (v) => {
                document.documentElement.classList.toggle('hid-body', !!v);
                document.body.classList.toggle('hid-body', !!v);
            });
        },
        openWith(tab = 'login') {
            if (tab === 'forgot') {
                this.subview = 'forgot';
                this.tab = 'login';
            } else {
                this.subview = null;
                this.tab = ['register', 'reset', 'login'].includes(tab) ? tab : 'login';
            }
            this.open = true;
        },
        close() {
            this.open = false;
            this.subview = null;
        },
        noteText() {
            if (this.subview === 'forgot') return this.notes.forgot;
            if (this.tab === 'register') return this.notes.register;
            if (this.tab === 'reset') return this.notes.reset;
            return this.notes.login;
        },
        fullMobile() {
            const sel = this.$el.querySelector('select[name="country_id"]');
            const opt = sel?.selectedOptions?.[0];
            const cc = (opt?.dataset?.code || '').replace(/\D/g, '');
            let local = String(this.mobileLocal || '').replace(/\D/g, '');
            while (local.startsWith('0')) local = local.slice(1);
            return cc + local;
        },
        prepareRegister(e) {
            this.termsError = '';
            if (!this.termsAccepted) {
                e.preventDefault();
                this.termsError = this.notes.termsRequired;
                return;
            }
            const mobile = this.fullMobile();
            if (mobile.length < 8 || mobile.length > 15) {
                e.preventDefault();
                this.termsError = this.notes.mobileInvalid;
            }
        },
    }));
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-dropdown-toggle]').forEach((trigger) => {
        const wrap = trigger.closest('[data-dropdown]');
        if (!wrap) return;
        trigger.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();
            const open = wrap.classList.toggle('is-open');
            wrap.classList.toggle('lang-wrap--open', open && wrap.classList.contains('lang-wrap'));
            if (wrap.classList.contains('header-user-menu') || wrap.classList.contains('UserMenu')) {
                wrap.classList.toggle('active', open);
            }
            // Also toggle on parent user menu when dropdown is nested
            const userMenu = wrap.closest('.header-user-menu');
            if (userMenu && userMenu !== wrap) {
                userMenu.classList.toggle('active', open);
            }
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    });

    document.addEventListener('click', () => {
        document.querySelectorAll('[data-dropdown].is-open').forEach((el) => {
            el.classList.remove('is-open', 'lang-wrap--open', 'active');
            el.closest('.header-user-menu')?.classList.remove('active');
            el.querySelector('[data-dropdown-toggle]')?.setAttribute('aria-expanded', 'false');
        });
    });

    document.querySelectorAll('[data-open-auth]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const tab = btn.getAttribute('data-open-auth') || 'login';
            window.dispatchEvent(new CustomEvent('imas-open-auth', { detail: { tab } }));
        });
    });

    const header = document.getElementById('header');
    const shell = document.getElementById('header-container');
    if (header && shell && !shell.classList.contains('head-tr')) {
        const onScroll = () => {
            const pinned = window.scrollY > 80;
            header.classList.toggle('imas-scroll-pinned', pinned);
            header.classList.toggle('imas-scroll-pinned--in', pinned);
            shell.classList.toggle('imas-header-scroll-pinned', pinned);
        };
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }

    if (window.jQuery && typeof window.jQuery.fn.mmenu === 'function') {
        const $ = window.jQuery;
        const $nav = $('#navigation');
        if ($nav.length && !$nav.data('mmenu')) {
            $nav.mmenu(
                {
                    extensions: ['theme-dark'],
                    navbar: { title: 'Menu' },
                },
                {
                    clone: true,
                    offCanvas: {
                        pageSelector: '#wrapper',
                    },
                },
            );
            $('.mmenu-trigger').on('click', () => {
                $nav.data('mmenu')?.open?.();
            });
        }
    }
});

Alpine.start();
