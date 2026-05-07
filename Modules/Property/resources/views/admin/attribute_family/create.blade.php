@section('title', __('Add attribute family'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Attribute families'), 'url' => route('admin.attribute_families.index')],
            ['label' => __('Add attribute family')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Add attribute family')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

<x-admin-layout>
    <form method="POST" action="{{ route('admin.attribute_families.store') }}">
        @csrf
        <div class="row gx-5 gx-xl-10">
            <div class="col-xxl-8 col-xl-8 mb-5 mb-xl-0">
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-collection text-primary fs-3 me-2"></i>
                                {{ __('General') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <x-admin.form-group label="Name" name="name" required translatable>
                            <input type="text"
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
                                   placeholder="residential_default"
                                   pattern="[a-z][a-z0-9_]*"
                                   maxlength="64"/>
                        </x-admin.form-group>
                    </div>
                </div>
            </div>

            <div class="col-xxl-12">
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-list-check text-primary fs-3 me-2"></i>
                                {{ __('Attributes in this family') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <p class="text-muted fs-7 mb-5">
                            {{ __('Check attributes to include. Use position numbers to control display order (lower numbers first).') }}
                        </p>
                        @if($allAttributes->isEmpty())
                            <div class="alert alert-warning">{{ __('Create attributes first, then assign them to a family.') }}</div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-row-bordered align-middle">
                                    <thead>
                                    <tr class="text-muted fw-bold fs-7 text-uppercase">
                                        <th>{{ __('Name') }}</th>
                                        <th>{{ __('Code') }}</th>
                                        <th class="w-125px">{{ __('Include') }}</th>
                                        <th class="w-150px">{{ __('Position') }}</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($allAttributes as $attr)
                                        <tr>
                                            <td class="fw-semibold">{{ $attr->name }}</td>
                                            <td><code>{{ $attr->code }}</code></td>
                                            <td>
                                                <div class="form-check form-check-solid">
                                                    <input class="form-check-input" type="checkbox"
                                                           name="in_family[{{ $attr->id }}]" value="1"
                                                           @checked(old("in_family.{$attr->id}"))/>
                                                </div>
                                            </td>
                                            <td>
                                                <input type="number" name="position[{{ $attr->id }}]" min="0" step="1"
                                                       class="form-control form-control-solid form-control-sm"
                                                       value="{{ old("position.{$attr->id}", 0) }}"/>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end py-6">
            <a href="{{ route('admin.attribute_families.index') }}"
               class="btn btn-light btn-active-light-primary me-3">{{ __('Discard') }}</a>
            <button type="submit" class="btn btn-primary" @disabled($allAttributes->isEmpty())>
                <span class="indicator-label">{{ __('Save Changes') }}</span>
            </button>
        </div>
    </form>
</x-admin-layout>
