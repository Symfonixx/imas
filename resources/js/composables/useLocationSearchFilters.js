import { computed, ref, toValue, watch } from "vue";
import {
    filterAreasByCities,
    filterDistrictsByCities,
    pruneDistrictAreaIds,
} from "@/utils/locationSearchFilters.js";

/**
 * @param {import('vue').MaybeRefOrGetter<Array<{ id: number|string, parent_id?: number|string|null }>>} cities
 * @param {import('vue').MaybeRefOrGetter<Array<{ id: number|string, parent_id?: number|string|null }>>} districts
 * @param {import('vue').MaybeRefOrGetter<Array<{ id: number|string, parent_id?: number|string|null }>>} areas
 */
export function useLocationSearchFilters(cities, districts, areas) {
    const searchCityIds = ref([]);
    const searchLocationIds = ref([]);

    const filteredDistricts = computed(() =>
        filterDistrictsByCities(toValue(districts), searchCityIds.value),
    );

    const filteredAreas = computed(() =>
        filterAreasByCities(
            toValue(areas),
            toValue(districts),
            searchCityIds.value,
        ),
    );

    watch(searchCityIds, () => {
        searchLocationIds.value = pruneDistrictAreaIds(
            searchLocationIds.value,
            filteredDistricts.value,
            filteredAreas.value,
        );
    });

    return {
        searchCityIds,
        searchLocationIds,
        filteredDistricts,
        filteredAreas,
    };
}
