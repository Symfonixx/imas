@php
    $currentGroupId = $currentGroupId ?? $attribute->pivot?->group_id;
@endphp
<div class="attribute-row d-flex flex-wrap align-items-center gap-3 border rounded p-3"
     data-attribute-id="{{ $attribute->id }}" draggable="true">
    <span class="text-muted cursor-move" aria-hidden="true">⋮⋮</span>
    <div class="flex-grow-1">
        <span class="fw-bold">{{ $attribute->name }}</span>
        <code class="ms-2">{{ $attribute->code }}</code>
        @unless($attribute->is_active)
            <span class="badge badge-light-secondary ms-2">{{ __('Inactive') }}</span>
        @endunless
    </div>
    <label class="d-flex align-items-center gap-2">
        <span class="visually-hidden">{{ __('Move attribute to group') }}</span>
        <select class="form-select form-select-sm attribute-destination"
                aria-label="{{ __('Move attribute to group') }} {{ $attribute->name }}">
            @foreach($groups as $destinationGroup)
                <option value="{{ $destinationGroup->id }}"
                        @selected((string) ($currentGroupId ?? '') === (string) $destinationGroup->id)>
                    {{ $destinationGroup->name }}
                </option>
            @endforeach
            <option value="unassigned" @selected($currentGroupId === null)>
                {{ __('Unassigned') }}
            </option>
        </select>
    </label>
    <button class="btn btn-sm btn-icon btn-light attribute-up" type="button"
            aria-label="{{ __('Move attribute up') }}">↑</button>
    <button class="btn btn-sm btn-icon btn-light attribute-down" type="button"
            aria-label="{{ __('Move attribute down') }}">↓</button>
</div>
