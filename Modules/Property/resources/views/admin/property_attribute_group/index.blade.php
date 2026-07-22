@section('title', __('Attribute groups'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Properties'), 'url' => '#'],
            ['label' => __('Attribute groups')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Attribute groups')" :breadcrumbItems="$breadcrumbItems"/>
    <div class="d-flex align-items-center gap-2">
        <button class="btn btn-sm btn-light-danger" type="submit" form="delete-attribute-groups">
            {{ __('Delete selected') }}
        </button>
        <a class="btn btn-sm fw-bold btn-primary" href="{{ route('admin.property_attribute_groups.create') }}">
            {{ __('Add group') }}
        </a>
    </div>
@endsection

@section('js')
    @include('property::admin.property_attribute_group._index_scripts')
@endsection

<x-admin-layout>
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <form id="delete-attribute-groups" method="POST"
          action="{{ route('admin.property_attribute_groups.deleteMulti') }}">
        @csrf
        @method('DELETE')
    </form>

    <form id="attribute-group-order" method="POST"
          action="{{ route('admin.property_attribute_groups.reorder') }}">
        @csrf
        <div id="ordering-fields"></div>

        <div id="attribute-groups" class="d-flex flex-column gap-5">
            @foreach($groups as $group)
                <section class="card card-flush attribute-group" data-group-id="{{ $group->id }}" draggable="true">
                    <div class="card-header">
                        <div class="card-title gap-3">
                            <span class="text-muted cursor-move" aria-hidden="true">⋮⋮</span>
                            <input class="form-check-input" type="checkbox" name="ids[]" value="{{ $group->id }}"
                                   form="delete-attribute-groups"
                                   aria-label="{{ __('Select group') }} {{ $group->name }}"/>
                            <h2 class="mb-0">{{ $group->name }}</h2>
                            @unless($group->is_active)
                                <span class="badge badge-light-secondary">{{ __('Inactive') }}</span>
                            @endunless
                        </div>
                        <div class="card-toolbar gap-2">
                            <button class="btn btn-sm btn-icon btn-light group-up" type="button"
                                    aria-label="{{ __('Move group up') }}">↑</button>
                            <button class="btn btn-sm btn-icon btn-light group-down" type="button"
                                    aria-label="{{ __('Move group down') }}">↓</button>
                            <a class="btn btn-sm btn-light-primary"
                               href="{{ route('admin.property_attribute_groups.edit', $group) }}">
                                {{ __('Edit') }}
                            </a>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="attribute-list d-flex flex-column gap-2 min-h-50px"
                             data-destination="{{ $group->id }}">
                            @foreach($group->attributes as $attribute)
                                @include('property::admin.property_attribute_group._attribute', [
                                    'attribute' => $attribute,
                                    'currentGroupId' => $group->id,
                                ])
                            @endforeach
                        </div>
                    </div>
                </section>
            @endforeach
        </div>

        <section class="card card-flush mt-7">
            <div class="card-header">
                <div class="card-title"><h2>{{ __('Unassigned attributes') }}</h2></div>
            </div>
            <div class="card-body pt-0">
                <div id="unassigned-attributes" class="attribute-list d-flex flex-column gap-2 min-h-50px"
                     data-destination="unassigned">
                    @foreach($unassignedAttributes as $attribute)
                        @include('property::admin.property_attribute_group._attribute', [
                            'attribute' => $attribute,
                            'currentGroupId' => null,
                        ])
                    @endforeach
                </div>
            </div>
        </section>

        <div class="d-flex justify-content-end py-6">
            <button type="submit" class="btn btn-primary">{{ __('Save ordering') }}</button>
        </div>
    </form>
</x-admin-layout>
