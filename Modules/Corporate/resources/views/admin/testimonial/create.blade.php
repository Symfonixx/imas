@section('title', __('Add New Testimonial'))

@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard', 'url' => route('admin.dashboard.index')],
            ['label' => __('Testimonials'), 'url' => route('admin.corporate_testimonials.index')],
            ['label' => __('Add New Testimonial')],
        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Add New Testimonial')" :breadcrumbItems="$breadcrumbItems"/>
@endsection

<x-admin-layout>
    <form method="POST" action="{{ route('admin.corporate_testimonials.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="row gx-5 gx-xl-10">
            <div class="col-xxl-8 col-xl-8 mb-5 mb-xl-0">
                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-chat-quote text-primary fs-3 me-2"></i>
                                {{ __('General') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <x-admin.form-group label="Name" name="name" required translatable>
                            <input type="text" name="name" class="form-control form-control-solid"
                                   value="{{ old('name') }}" placeholder="{{ __('Name') }}"/>
                        </x-admin.form-group>

                        <x-admin.form-group label="Client" name="client" required>
                            <input type="text" name="client" class="form-control form-control-solid"
                                   value="{{ old('client') }}" placeholder="{{ __('Client') }}"/>
                        </x-admin.form-group>

                        <x-admin.form-group label="Position" name="position" translatable>
                            <input type="text" name="position" class="form-control form-control-solid"
                                   value="{{ old('position') }}" placeholder="{{ __('Position') }}"/>
                        </x-admin.form-group>

                        <x-admin.form-group label="Quote" name="quote" required translatable>
                            <textarea name="quote" rows="4" class="form-control form-control-solid"
                                      placeholder="{{ __('Quote') }}">{{ old('quote') }}</textarea>
                        </x-admin.form-group>

                        <x-admin.form-group label="Avatar" name="img"
                                            helper="{{ __('Optional. If empty, a default avatar is shown.') }}">
                            <x-admin.image-input name="img"/>
                        </x-admin.form-group>

                        <x-admin.form-group label="Link" name="link">
                            <input type="url" name="link" class="form-control form-control-solid"
                                   value="{{ old('link') }}" placeholder="https://"/>
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
                                {{ __('Status') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <x-admin.toggle-switch
                            name="publish"
                            label="Active"
                            helper="When active, the content is visible to the public."
                            icon="bi bi-broadcast-pin"
                            tone="success"
                            :checked="old('publish', 'Published') === 'Published'"
                            value="Published"
                            last
                        />
                    </div>
                </div>

                <div class="card card-flush mb-7">
                    <div class="card-header">
                        <div class="card-title">
                            <h2 class="d-flex align-items-center">
                                <i class="bi bi-sort-numeric-down text-primary fs-3 me-2"></i>
                                {{ __('Rank') }}
                            </h2>
                        </div>
                    </div>
                    <div class="card-body pt-3">
                        <x-admin.form-group label="Rank" name="rank" required>
                            <input type="number" min="0" name="rank" class="form-control form-control-solid"
                                   value="{{ old('rank', 0) }}"/>
                        </x-admin.form-group>
                    </div>
                </div>
            </div>
        </div>

        <div class="d-flex justify-content-end py-6">
            <a href="{{ route('admin.corporate_testimonials.index') }}"
               class="btn btn-light btn-active-light-primary me-3">{{ __('Discard') }}</a>
            <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
        </div>
    </form>
</x-admin-layout>
