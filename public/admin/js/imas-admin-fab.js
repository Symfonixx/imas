/**
 * IMas admin floating quick-actions FAB
 * Opens only when hovering the + button (not empty space above it).
 * Stays open while pointer is on the menu; click toggles on touch.
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
        const fab = document.querySelector('[data-imas-fab]');
        if (!fab) {
            return;
        }

        const trigger = fab.querySelector('[data-imas-fab-trigger]');
        const rail = fab.querySelector('.imas-admin-fab__rail');
        if (!trigger || !rail) {
            return;
        }

        const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');

        const setOpen = (open) => {
            fab.classList.toggle('is-open', open);
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        };

        // Desktop: open only from the + button hover
        trigger.addEventListener('mouseenter', () => {
            if (finePointer.matches) {
                setOpen(true);
            }
        });

        // Keep open while over button or menu; close when leaving both
        fab.addEventListener('mouseleave', () => {
            if (finePointer.matches) {
                setOpen(false);
            }
        });

        rail.addEventListener('mouseenter', () => {
            if (finePointer.matches) {
                setOpen(true);
            }
        });

        // Touch / click fallback
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            setOpen(!fab.classList.contains('is-open'));
        });

        document.addEventListener('click', (event) => {
            if (!fab.contains(event.target)) {
                setOpen(false);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        });
    });
})();
