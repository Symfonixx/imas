@section('title', __('Add attribute'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Attributes'), 'url' => route('admin.attributes.index')],
            ['label' => __('Add attribute')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Add attribute')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

<x-admin-layout>
    <form method="POST" action="{{ route('admin.attributes.store') }}">
        @csrf
        <div class="row gx-5 gx-xl-10">
            <div class="col-xxl-8 col-xl-8 mb-5 mb-xl-0">
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-sliders text-primary fs-3 me-2"></i>
                                {{ __('General') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <x-admin.form-group label="Name" name="name" required translatable>
                            <input type="text"
                                   id="name"
                                   name="name"
                                   class="form-control form-control-solid"
                                   value="{{ old('name') }}"
                                   placeholder="{{ __('Name') }}"/>
                        </x-admin.form-group>

                        <x-admin.form-group label="Code" name="code" required
                                            helper="{{ __('Lowercase letters, numbers, and underscores. Must start with a letter.') }}">
                            <input type="text"
                                   name="code"
                                   class="form-control form-control-solid"
                                   value="{{ old('code') }}"
                                   placeholder="bedrooms_count"
                                   pattern="[a-z][a-z0-9_]*"
                                   maxlength="64"/>
                        </x-admin.form-group>

                        <x-admin.form-group label="Type" name="type" required>
                            <select name="type" class="form-select form-select-solid" required>
                                @foreach(\Modules\Property\Enums\AttributeType::cases() as $case)
                                    <option value="{{ $case->value }}" @selected(old('type') === $case->value)>
                                        {{ __($case->value) }}
                                    </option>
                                @endforeach
                            </select>
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
                                {{ __('Flags') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <x-admin.toggle-switch
                            name="is_filterable"
                            label="Filterable"
                            helper="{{ __('Allow filtering listings by this attribute.') }}"
                            icon="bi bi-funnel"
                            tone="primary"
                            :checked="old('is_filterable', false)"
                            value="1"
                        />
                        <x-admin.toggle-switch
                            name="is_required"
                            label="Required"
                            helper="{{ __('Attribute must have a value when present on a property.') }}"
                            icon="bi bi-asterisk"
                            tone="warning"
                            :checked="old('is_required', false)"
                            value="1"
                        />
                        <x-admin.toggle-switch
                            name="is_trans"
                            label="Translatable value"
                            helper="{{ __('When enabled, attribute values can be translated per locale.') }}"
                            icon="bi bi-translate"
                            tone="success"
                            :checked="old('is_trans', false)"
                            value="1"
                            last
                        />
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end py-6">
            <a href="{{ route('admin.attributes.index') }}"
               class="btn btn-light btn-active-light-primary me-3">{{ __('Discard') }}</a>
            <button type="submit" class="btn btn-primary">
                <span class="indicator-label">{{ __('Save Changes') }}</span>
            </button>
        </div>
    </form>
</x-admin-layout>
