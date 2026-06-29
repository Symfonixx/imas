<template>
    <div v-if="unitTypes.length > 0" class="imas-unit-types-table mb-30">
        <h5 class="imas-section-title mb-4">{{ title }}</h5>
        <dl
            v-if="projectId || projectLocation"
            class="imas-unit-types-table__meta mb-4"
        >
            <div v-if="projectId" class="imas-unit-types-table__meta-item">
                <dt class="imas-unit-types-table__meta-label">
                    {{ projectIdLabel }}
                </dt>
                <dd class="imas-unit-types-table__meta-value">{{ projectId }}</dd>
            </div>
            <div
                v-if="projectLocation"
                class="imas-unit-types-table__meta-item"
            >
                <dt class="imas-unit-types-table__meta-label">
                    {{ projectLocationLabel }}
                </dt>
                <dd class="imas-unit-types-table__meta-value">
                    {{ projectLocation }}
                </dd>
            </div>
        </dl>
        <div class="table-responsive">
            <table class="table imas-unit-types-table__grid mb-0">
                <thead>
                    <tr>
                        <th scope="col">{{ colRooms }}</th>
                        <th scope="col">{{ colArea }}</th>
                        <th scope="col">{{ colPrice }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="row in unitTypes"
                        :key="row.id"
                    >
                        <td>{{ row.name || "—" }}</td>
                        <td>{{ formatArea(row) }}</td>
                        <td>{{ formatPrice(row.price) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { usePage } from "@inertiajs/vue3";

defineProps({
    unitTypes: { type: Array, default: () => [] },
    title: { type: String, required: true },
    colRooms: { type: String, required: true },
    colArea: { type: String, required: true },
    colPrice: { type: String, required: true },
    projectId: { type: String, default: "" },
    projectIdLabel: { type: String, default: "" },
    projectLocation: { type: String, default: "" },
    projectLocationLabel: { type: String, default: "" },
});

const page = usePage();

function locale() {
    return page.props.locale || "en";
}

function formatArea(row) {
    const min = row?.min_area;
    const max = row?.max_area;
    const minN = Number(min);
    const maxN = Number(max);

    if (Number.isFinite(minN) && Number.isFinite(maxN) && minN !== maxN) {
        return `${formatNumber(minN)} – ${formatNumber(maxN)} m²`;
    }
    if (Number.isFinite(minN)) {
        return `${formatNumber(minN)} m²`;
    }
    if (Number.isFinite(maxN)) {
        return `${formatNumber(maxN)} m²`;
    }

    return "—";
}

function formatNumber(value) {
    return new Intl.NumberFormat(locale(), {
        maximumFractionDigits: 0,
    }).format(value);
}

function formatPrice(amount) {
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

<style scoped lang="scss">
.imas-unit-types-table__meta {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    margin: 0;
}

.imas-unit-types-table__meta-item {
    display: flex;
    flex-wrap: wrap;
    align-items: baseline;
    gap: 0.35rem 0.5rem;
    margin: 0;
}

.imas-unit-types-table__meta-label {
    margin: 0;
    font-size: var(--text-sm);
    font-weight: 600;
    color: var(--text-dim);
}

.imas-unit-types-table__meta-value {
    margin: 0;
    font-size: var(--text-sm);
    color: var(--text);
}

.imas-unit-types-table__grid {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    text-align: center;
}

.imas-unit-types-table__grid thead th {
    background: var(--surface-2);
    color: var(--text);
    font-weight: 700;
    font-size: var(--text-sm);
    padding: 0.85rem 0.75rem;
    border: 0;
    vertical-align: middle;
}

.imas-unit-types-table__grid tbody td {
    padding: 0.85rem 0.75rem;
    color: var(--text);
    font-size: var(--text-md);
    border: 0;
    vertical-align: middle;
}

.imas-unit-types-table__grid tbody tr:nth-child(odd) td {
    background: var(--surface);
}

.imas-unit-types-table__grid tbody tr:nth-child(even) td {
    background: var(--surface-2);
}

.imas-unit-types-table__grid tbody tr:hover td {
    background: var(--surface-3);
}

html[dir="rtl"] .imas-unit-types-table__grid {
    direction: rtl;
}

html[dir="rtl"] .imas-unit-types-table__grid thead th,
html[dir="rtl"] .imas-unit-types-table__grid tbody td {
    text-align: center;
}
</style>
