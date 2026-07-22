@php
    $isEdit = isset($slideCategory);
    $currentStatus = old(
        'status',
        $isEdit ? ($slideCategory->status?->value ?? $slideCategory->status) : \Modules\User\Enums\CmsStatus::PUBLISHED->value
    );
@endphp

<div class="row gx-5 gx-xl-10">
    <div class="col-xxl-8 col-xl-8 mb-5 mb-xl-0">
        <div class="card card-flush mb-7">
            <div class="card-header">
                <div class="card-title">
                    <h2 class="d-flex align-items-center">
                        <i class="bi bi-collection-play text-primary fs-3 me-2"></i>
                        {{ __('General') }}
                    </h2>
                </div>
            </div>
            <div class="card-body pt-3">
                <x-admin.form-group label="Name" name="name" required translatable>
                    <input type="text"
                           id="slide_category_name"
                           name="name"
                           class="form-control form-control-solid"
                           value="{{ old('name', $slideCategory->name ?? '') }}"
                           placeholder="{{ __('Name') }}"/>
                </x-admin.form-group>

                <x-admin.form-group label="Description" name="description">
                    <textarea name="description"
                              rows="5"
                              class="form-control form-control-solid"
                              placeholder="{{ __('Description') }}">{{ old('description', $slideCategory->description ?? '') }}</textarea>
                </x-admin.form-group>

                <x-admin.form-group label="URL slug" name="slug" required
                                    helper="{{ __('Lowercase, hyphens only. Used as the unique category identifier.') }}">
                    <input type="text"
                           name="slug"
                           class="form-control form-control-solid"
                           value="{{ old('slug', $slideCategory->slug ?? '') }}"
                           placeholder="project-gallery"
                           pattern="[a-z0-9]+(-[a-z0-9]+)*"
                           maxlength="191"/>
                </x-admin.form-group>
            </div>
        </div>

    </div>

    <div class="col-xxl-4 col-xl-4">
        <div class="card card-flush mb-7">
            <div class="card-header">
                <div class="card-title">
                    <h2 class="d-flex align-items-center">
                        <i class="bi bi-toggles text-primary fs-3 me-2"></i>
                        {{ __('Publishing') }}
                    </h2>
                </div>
            </div>
            <div class="card-body pt-3">
                <x-admin.form-group label="Status" name="status" required>
                    <select name="status" class="form-select form-select-solid">
                        @foreach($statuses as $status)
                            <option value="{{ $status->value }}" @selected($currentStatus === $status->value)>
                                {{ __($status->value) }}
                            </option>
                        @endforeach
                    </select>
                </x-admin.form-group>

                <x-admin.form-group label="Position" name="position" required
                                    helper="{{ __('Lower values appear first.') }}">
                    <input type="number"
                           min="0"
                           name="position"
                           class="form-control form-control-solid"
                           value="{{ old('position', $slideCategory->position ?? 0) }}"/>
                </x-admin.form-group>
            </div>
        </div>

        @if($isEdit)
            <div class="card card-flush mb-7">
                <div class="card-header">
                    <div class="card-title">
                        <h2 class="d-flex align-items-center">
                            <i class="bi bi-translate text-primary fs-3 me-2"></i>
                            {{ __('Update Other Languages') }}
                        </h2>
                    </div>
                </div>
                <div class="card-body pt-3">
                    <label class="form-check form-check-custom form-check-solid">
                        <input class="form-check-input"
                               type="checkbox"
                               name="update_translations"
                               value="1"
                               @checked(old('update_translations', false))/>
                        <span class="form-check-label fs-7 ms-2">
                            {{ __('Use Google Translate to update all other languages.') }}
                        </span>
                    </label>
                </div>
            </div>
        @endif
    </div>
</div>

<x-admin.form-actions :discard-url="route('admin.slide_categories.index')"/>
