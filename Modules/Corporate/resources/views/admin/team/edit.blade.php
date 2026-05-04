@section('title', __('Edit Team Member'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Team'), 'url' => route('admin.corporate_teams.index')],
            ['label' => __('Edit Team Member')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Edit Team Member')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

<x-admin-layout>
    <form method="POST" action="{{ route('admin.corporate_teams.update', $team->id) }}"
          enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="row gx-5 gx-xl-10">
            <div class="col-xxl-8 col-xl-8 mb-5 mb-xl-0">
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-people text-primary fs-3 me-2"></i>
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
                                   value="{{ old('name', $team->name) }}"
                                   placeholder="{{ __('Name') }}"/>
                        </x-admin.form-group>

                        <x-admin.form-group label="Position" name="position" translatable
                                            helper="{{ __('Optional job title or role.') }}">
                            <input type="text"
                                   name="position"
                                   class="form-control form-control-solid"
                                   value="{{ old('position', $team->position) }}"
                                   placeholder="{{ __('Position') }}"/>
                        </x-admin.form-group>

                        <x-admin.form-group label="Avatar" name="img"
                                            helper="{{ __('Optional. A default image is used when empty.') }}">
                            <x-admin.image-input name="img" :preview="$team->avatar_link"/>
                        </x-admin.form-group>

                        <x-admin.form-group label="Link" name="link"
                                            helper="{{ __('Optional profile or social URL.') }}">
                            <input type="url"
                                   name="link"
                                   class="form-control form-control-solid"
                                   value="{{ old('link', $team->link) }}"
                                   placeholder="https://"/>
                        </x-admin.form-group>
                    </div>
                </div>
            </div>

            <div class="col-xxl-4 col-xl-4">
                @include('cms::admin.partials._status_aside', [
                    'isActive' => old('publish', $team->status) === 'Published',
                    'isFeatured' => false,
                    'showTranslations' => true,
                    'updateTranslations' => (bool) old('update_translations', false),
                ])

                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-sort-numeric-down text-primary fs-3 me-2"></i>
                                {{ __('Rank') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <x-admin.form-group label="Rank" name="rank" required>
                            <input type="number"
                                   min="0"
                                   name="rank"
                                   class="form-control form-control-solid"
                                   value="{{ old('rank', $team->rank) }}"
                                   placeholder="0"/>
                        </x-admin.form-group>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end py-6">
            <a href="{{ route('admin.corporate_teams.index') }}"
               class="btn btn-light btn-active-light-primary me-3">{{ __('Discard') }}</a>
            <button type="submit" class="btn btn-primary" id="submit">
                <span class="indicator-label">{{ __('Save Changes') }}</span>
            </button>
        </div>
    </form>
</x-admin-layout>
