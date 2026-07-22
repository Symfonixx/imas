@php($group = $group ?? null)
@php($editing = isset($group))

<div class="row gx-5 gx-xl-10">
    <div class="col-xl-8">
        <div class="card card-flush">
            <div class="card-header">
                <div class="card-title"><h2>{{ __('Attribute group') }}</h2></div>
            </div>
            <div class="card-body pt-3">
                <x-admin.form-group label="Name" name="name" required translatable>
                    <input class="form-control form-control-solid" name="name"
                           value="{{ old('name', $group?->name ?? '') }}" maxlength="255" required/>
                </x-admin.form-group>

                <x-admin.form-group label="Position" name="position" required>
                    <input class="form-control form-control-solid" type="number" name="position"
                           value="{{ old('position', $group?->position ?? 0) }}" min="0" required/>
                </x-admin.form-group>

                <label class="form-check form-switch form-check-custom form-check-solid">
                    <input type="hidden" name="is_active" value="0"/>
                    <input class="form-check-input" type="checkbox" name="is_active" value="1"
                           @checked((bool) old('is_active', $group?->is_active ?? true))/>
                    <span class="form-check-label">{{ __('Active') }}</span>
                </label>
            </div>
        </div>
    </div>

    @if($editing)
        <div class="col-xl-4">
            <div class="card card-flush">
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
        </div>
    @endif
</div>

<div class="d-flex justify-content-end py-6">
    <a href="{{ route('admin.property_attribute_groups.index') }}"
       class="btn btn-light btn-active-light-primary me-3">{{ __('Discard') }}</a>
    <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
</div>
