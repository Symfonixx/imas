/**
 * IMas admin sidebar — search filter + accordion expand helpers
 */
(function () {
    'use strict';

    function ready(fn) {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', fn);
        } else {
            fn();
        }
    }

    ready(function () {
        const input = document.getElementById('imas_admin_sidebar_search');
        const clearBtn = document.getElementById('imas_admin_sidebar_search_clear');
        const emptyEl = document.getElementById('imas_admin_sidebar_search_empty');
        const menuRoot = document.getElementById('kt_app_sidebar_menu');

        if (!input || !menuRoot) {
            return;
        }

        const items = () => Array.from(menuRoot.querySelectorAll('[data-imas-menu-item]'));

        const normalize = (value) => (value || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();

        const setSearching = (on) => {
            document.body.classList.toggle('imas-admin-sidebar-searching', on);
        };

        const showSub = (sub) => {
            if (!sub) {
                return;
            }
            sub.classList.add('show');
            sub.style.display = 'flex';
            sub.style.overflow = 'visible';
        };

        const expandAncestors = (el) => {
            let node = el.parentElement;
            while (node && node !== menuRoot) {
                if (node.classList && node.classList.contains('menu-item') && node.classList.contains('menu-accordion')) {
                    node.classList.add('show', 'hover');
                    showSub(node.querySelector(':scope > .menu-sub'));
                }
                if (node.classList && node.classList.contains('menu-sub')) {
                    showSub(node);
                }
                node = node.parentElement;
            }
        };

        const clearInlineSubStyles = () => {
            menuRoot.querySelectorAll('.menu-sub').forEach((sub) => {
                sub.style.removeProperty('display');
                sub.style.removeProperty('overflow');
            });
        };

        let debounceTimer = null;

        const filterMenu = () => {
            const query = normalize(input.value);
            const allItems = items();

            allItems.forEach((item) => {
                item.classList.remove(
                    'imas-admin-search-hidden',
                    'imas-admin-search-match',
                    'imas-admin-search-match-parent'
                );
            });

            if (!query) {
                clearInlineSubStyles();
                if (emptyEl) {
                    emptyEl.classList.add('d-none');
                }
                if (clearBtn) {
                    clearBtn.classList.add('d-none');
                }
                setSearching(false);
                return;
            }

            setSearching(true);
            if (clearBtn) {
                clearBtn.classList.remove('d-none');
            }

            const selfMatches = new Set();

            allItems.forEach((item) => {
                const title = normalize(item.getAttribute('data-imas-menu-title'));
                if (title.includes(query)) {
                    selfMatches.add(item);
                }
            });

            const visible = new Set();

            selfMatches.forEach((item) => {
                visible.add(item);
                item.querySelectorAll('[data-imas-menu-item]').forEach((child) => visible.add(child));
                let node = item.parentElement;
                while (node && node !== menuRoot) {
                    if (node.matches && node.matches('[data-imas-menu-item]')) {
                        visible.add(node);
                    }
                    node = node.parentElement;
                }
            });

            allItems.forEach((item) => {
                if (visible.has(item)) {
                    if (selfMatches.has(item)) {
                        item.classList.add('imas-admin-search-match');
                    } else {
                        item.classList.add('imas-admin-search-match-parent');
                    }
                    expandAncestors(item);
                    if (item.classList.contains('menu-accordion')) {
                        item.classList.add('show', 'hover');
                        const directSubs = item.querySelectorAll(':scope > .menu-sub');
                        directSubs.forEach(showSub);
                    }
                } else {
                    item.classList.add('imas-admin-search-hidden');
                }
            });

            if (emptyEl) {
                emptyEl.classList.toggle('d-none', visible.size > 0);
            }
        };

        const scheduleFilter = () => {
            if (debounceTimer) {
                clearTimeout(debounceTimer);
            }
            debounceTimer = setTimeout(filterMenu, 80);
        };

        input.addEventListener('input', scheduleFilter);
        input.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                input.value = '';
                filterMenu();
                input.blur();
            }
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', () => {
                input.value = '';
                filterMenu();
                input.focus();
            });
        }

        const kbd = document.querySelector('.imas-admin-sidebar-search__kbd');
        if (kbd) {
            const isApple = /Mac|iPhone|iPad|iPod/i.test(navigator.platform || navigator.userAgent || '');
            kbd.textContent = isApple ? '⌘K' : 'Ctrl K';
        }

        document.addEventListener('keydown', (event) => {
            const isK = event.key === 'k' || event.key === 'K';
            if ((event.ctrlKey || event.metaKey) && isK) {
                const tag = (event.target && event.target.tagName) || '';
                if (tag === 'INPUT' || tag === 'TEXTAREA' || event.target.isContentEditable) {
                    return;
                }
                event.preventDefault();
                // Ensure sidebar is expanded enough to show search on desktop minimize
                input.focus();
                input.select();
            }
        });
    });
})();
