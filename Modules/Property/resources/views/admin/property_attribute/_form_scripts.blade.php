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

    if (!type || !card || !list || !defaultValue || !addButton) {
        return;
    }

    const updateNames = () => {
        [...list.children].forEach((row, index) => {
            row.querySelectorAll('[data-field]').forEach(input => {
                input.name = `options[${index}][${input.dataset.field}]`;
            });
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

    addButton.addEventListener('click', () => {
        if (addButton.disabled) {
            return;
        }

        const row = document.createElement('div');
        row.className = 'attribute-option d-flex align-items-center gap-2';
        row.draggable = true;
        row.innerHTML = `
            <span class="text-muted cursor-move" aria-hidden="true">⋮⋮</span>
            <input class="form-control form-control-solid" data-field="label" aria-label="{{ __('Option label') }}">
            <label class="form-check form-switch form-check-custom form-check-solid">
                <input type="hidden" data-field="is_active" value="0">
                <input class="form-check-input" type="checkbox" data-field="is_active" value="1" checked>
                <span class="form-check-label">{{ __('Active') }}</span>
            </label>
            <button type="button" class="btn btn-sm btn-icon btn-light move-up" aria-label="{{ __('Move option up') }}">↑</button>
            <button type="button" class="btn btn-sm btn-icon btn-light move-down" aria-label="{{ __('Move option down') }}">↓</button>
            <button type="button" class="btn btn-sm btn-icon btn-light-danger remove-option" aria-label="{{ __('Remove option') }}">×</button>`;
        list.appendChild(row);
        bindRow(row);
        updateNames();
        row.querySelector('[data-field="label"]')?.focus();
    });

    [...list.children].forEach(bindRow);
    type.addEventListener('change', updateVisibility);
    updateNames();
    updateVisibility();
});
</script>
