<script>
document.addEventListener('DOMContentLoaded', () => {
    if (window.__propertyAttributeGroupOrderingBound) {
        return;
    }
    window.__propertyAttributeGroupOrderingBound = true;

    const groups = document.getElementById('attribute-groups');
    const form = document.getElementById('attribute-group-order');
    if (!groups || !form) {
        return;
    }

    let draggedGroup = null;
    let draggedAttribute = null;

    const destinationList = (destination) => document.querySelector(
        `.attribute-list[data-destination="${String(destination).replace(/"/g, '\\"')}"]`
    );

    const moveAttribute = (row, destination) => {
        const list = destinationList(destination);
        if (!list) {
            return;
        }

        list.appendChild(row);
        const select = row.querySelector('.attribute-destination');
        if (select) {
            select.value = String(destination);
        }
    };

    document.querySelectorAll('.attribute-group').forEach(group => {
        group.addEventListener('dragstart', event => {
            if (event.target.closest('.attribute-row')) {
                return;
            }
            draggedGroup = group;
            event.dataTransfer.effectAllowed = 'move';
            group.classList.add('opacity-50');
        });
        group.addEventListener('dragend', () => {
            group.classList.remove('opacity-50');
            draggedGroup = null;
        });
        group.addEventListener('dragover', event => {
            if (!draggedGroup || draggedGroup === group) {
                return;
            }
            event.preventDefault();
            groups.insertBefore(draggedGroup, group);
        });
        group.querySelector('.group-up')?.addEventListener('click', () => {
            if (group.previousElementSibling) {
                groups.insertBefore(group, group.previousElementSibling);
            }
        });
        group.querySelector('.group-down')?.addEventListener('click', () => {
            if (group.nextElementSibling) {
                groups.insertBefore(group.nextElementSibling, group);
            }
        });
    });

    document.querySelectorAll('.attribute-row').forEach(row => {
        row.addEventListener('dragstart', event => {
            event.stopPropagation();
            draggedAttribute = row;
            draggedGroup = null;
            event.dataTransfer.effectAllowed = 'move';
            row.classList.add('opacity-50');
        });
        row.addEventListener('dragend', () => {
            row.classList.remove('opacity-50');
            draggedAttribute = null;
        });
        row.querySelector('.attribute-up')?.addEventListener('click', () => {
            if (row.previousElementSibling) {
                row.parentElement.insertBefore(row, row.previousElementSibling);
            }
        });
        row.querySelector('.attribute-down')?.addEventListener('click', () => {
            if (row.nextElementSibling) {
                row.parentElement.insertBefore(row.nextElementSibling, row);
            }
        });
        row.querySelector('.attribute-destination')?.addEventListener('change', event => {
            moveAttribute(row, event.target.value);
        });
    });

    document.querySelectorAll('.attribute-list').forEach(list => {
        list.addEventListener('dragover', event => {
            if (!draggedAttribute) {
                return;
            }
            event.preventDefault();
            event.stopPropagation();
            const target = event.target.closest('.attribute-row');
            if (target && target !== draggedAttribute && target.parentElement === list) {
                list.insertBefore(draggedAttribute, target);
            } else if (!target || target.parentElement !== list) {
                list.appendChild(draggedAttribute);
            }
        });
        list.addEventListener('drop', event => {
            if (!draggedAttribute) {
                return;
            }
            event.preventDefault();
            event.stopPropagation();
            const select = draggedAttribute.querySelector('.attribute-destination');
            if (select) {
                select.value = String(list.dataset.destination);
            }
        });
    });

    form.addEventListener('submit', () => {
        const fields = document.getElementById('ordering-fields');
        fields.replaceChildren();
        const add = (name, value) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = name;
            input.value = value;
            fields.appendChild(input);
        };

        Array.from(groups.children)
            .filter(group => group.classList.contains('attribute-group'))
            .forEach((group, groupIndex) => {
                add(`groups[${groupIndex}][id]`, group.dataset.groupId);
                const rows = Array.from(group.querySelectorAll('.attribute-list .attribute-row'));
                if (rows.length === 0) {
                    // Empty marker is normalized to [] server-side.
                    add(`groups[${groupIndex}][attributes]`, '');
                    return;
                }
                rows.forEach((row, attributeIndex) => {
                    add(`groups[${groupIndex}][attributes][${attributeIndex}]`, row.dataset.attributeId);
                });
            });

        const unassignedRows = Array.from(
            document.querySelectorAll('#unassigned-attributes .attribute-row')
        );
        // Always send an array field. An empty marker becomes [] after ConvertEmptyStringsToNull.
        if (unassignedRows.length === 0) {
            add('unassigned', '');
        } else {
            unassignedRows.forEach((row, index) => {
                add(`unassigned[${index}]`, row.dataset.attributeId);
            });
        }
    });
});
</script>
