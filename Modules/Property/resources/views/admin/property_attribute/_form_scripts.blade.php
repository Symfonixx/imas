@php
    $optionTypeValues = array_values(array_map(
        static fn ($type) => $type->value,
        array_filter(
            $types,
            static fn ($type) => $type->hasOptions()
        )
    ));
@endphp
<script>
document.addEventListener('DOMContentLoaded', () => {
    const optionTypes = new Set(@json($optionTypeValues));
    const mediaTypes = new Set(['image', 'gallery', 'file']);
    const type = document.getElementById('attribute_type');
    const card = document.getElementById('attribute_options_card');
    const list = document.getElementById('attribute_options');
    const defaultValue = document.getElementById('attribute_default_value');
    const addButton = document.getElementById('add_attribute_option');
    const iconPickerTemplate = document.getElementById('attribute_option_icon_picker_template');

    if (!type || !card || !list || !defaultValue || !addButton) {
        return;
    }

    const updateNames = () => {
        [...list.children].forEach((row, index) => {
            row.querySelectorAll('[data-field]').forEach(input => {
                input.name = `options[${index}][${input.dataset.field}]`;
            });
            const picker = row.querySelector('[data-kt-property-icon-picker]');
            if (picker) {
                const uid = `attr_opt_icon_${index}`;
                picker.id = uid;
                const toggle = picker.querySelector('[data-bs-toggle="dropdown"]');
                if (toggle) {
                    toggle.id = `${uid}_toggle`;
                }
                const menu = picker.querySelector('.dropdown-menu');
                if (menu) {
                    menu.setAttribute('aria-labelledby', `${uid}_toggle`);
                }
            }
        });
    };

    const updateVisibility = () => {
        const visible = optionTypes.has(type.value);
        card.classList.toggle('d-none', !visible);
        card.querySelectorAll('input, button').forEach(input => {
            input.disabled = !visible;
        });
        const defaultVisible = !visible && !mediaTypes.has(type.value);
        defaultValue.classList.toggle('d-none', !defaultVisible);
        defaultValue.querySelectorAll('input').forEach(input => {
            input.disabled = !defaultVisible;
        });
    };

    const bindRow = row => {
        row.querySelector('.remove-option')?.addEventListener('click', () => {
            row.remove();
            updateNames();
        });
        row.querySelector('.move-up')?.addEventListener('click', () => {
            if (row.previousElementSibling) {
                list.insertBefore(row, row.previousElementSibling);
            }
            updateNames();
        });
        row.querySelector('.move-down')?.addEventListener('click', () => {
            if (row.nextElementSibling) {
                list.insertBefore(row.nextElementSibling, row);
            }
            updateNames();
        });
        row.addEventListener('dragstart', () => row.classList.add('opacity-50'));
        row.addEventListener('dragend', () => {
            row.classList.remove('opacity-50');
            updateNames();
        });
        row.addEventListener('dragover', event => {
            event.preventDefault();
            const dragging = list.querySelector('.opacity-50');
            if (dragging && dragging !== row) {
                list.insertBefore(dragging, row);
            }
        });
    };

    const cloneIconPicker = () => {
        if (!iconPickerTemplate) {
            return null;
        }

        const fragment = iconPickerTemplate.content.cloneNode(true);
        const picker = fragment.querySelector('[data-kt-property-icon-picker]');
        if (!picker) {
            return null;
        }

        picker.removeAttribute('data-kt-property-icon-bound');
        const input = picker.querySelector('[data-kt-property-icon-input]');
        if (input) {
            input.value = '';
            input.disabled = false;
        }
        const preview = picker.querySelector('[data-kt-property-icon-preview]');
        if (preview) {
            preview.className = 'bi bi-image fs-3 text-primary flex-shrink-0 opacity-50';
        }
        picker.querySelectorAll('[data-kt-property-icon-value]').forEach(btn => {
            const isNone = (btn.getAttribute('data-kt-property-icon-value') || '') === '';
            btn.classList.toggle('border-primary', isNone);
            btn.classList.toggle('bg-light-primary', isNone);
            btn.classList.toggle('border-gray-200', !isNone);
            btn.setAttribute('aria-pressed', isNone ? 'true' : 'false');
        });

        return picker;
    };

    addButton.addEventListener('click', () => {
        if (addButton.disabled) {
            return;
        }

        const row = document.createElement('div');
        row.className = 'attribute-option d-flex align-items-center gap-2 flex-wrap';
        row.draggable = true;

        const handle = document.createElement('span');
        handle.className = 'text-muted cursor-move';
        handle.setAttribute('aria-hidden', 'true');
        handle.textContent = '⋮⋮';
        row.appendChild(handle);

        const picker = cloneIconPicker();
        if (picker) {
            row.appendChild(picker);
        }

        const label = document.createElement('input');
        label.className = 'form-control form-control-solid flex-grow-1';
        label.dataset.field = 'label';
        label.setAttribute('aria-label', @json(__('Option label')));
        label.style.minWidth = '10rem';
        row.appendChild(label);

        const activeLabel = document.createElement('label');
        activeLabel.className = 'form-check form-switch form-check-custom form-check-solid';
        activeLabel.innerHTML = `
            <input type="hidden" data-field="is_active" value="0">
            <input class="form-check-input" type="checkbox" data-field="is_active" value="1" checked>
            <span class="form-check-label">${@json(__('Active'))}</span>`;
        row.appendChild(activeLabel);

        ['move-up', 'move-down', 'remove-option'].forEach((cls, i) => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = `btn btn-sm btn-icon ${cls === 'remove-option' ? 'btn-light-danger' : 'btn-light'} ${cls}`;
            btn.setAttribute('aria-label', [
                @json(__('Move option up')),
                @json(__('Move option down')),
                @json(__('Remove option')),
            ][i]);
            btn.textContent = ['↑', '↓', '×'][i];
            row.appendChild(btn);
        });

        list.appendChild(row);
        bindRow(row);
        updateNames();
        if (typeof window.initPropertyIconPickers === 'function') {
            window.initPropertyIconPickers(row);
        }
        label.focus();
    });

    [...list.children].forEach(bindRow);
    type.addEventListener('change', updateVisibility);
    updateNames();
    updateVisibility();
    if (typeof window.initPropertyIconPickers === 'function') {
        window.initPropertyIconPickers(list);
    }
});
</script>
