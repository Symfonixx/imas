@if(isset($modal) && $modal)

    <!-- Create Category Modal -->
    <div class="modal fade" id="createCategoryModal" tabindex="-1" aria-labelledby="createCategoryModalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('admin.blogs_categories.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="createCategoryModalLabel">{{ __('Add New Blog Category') }}</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">

                        <div class="mb-3">
                            <label for="name" class="form-label">{{ __('Name') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" required>
                        </div>

                        <div class="mb-3">
                            <label for="slug" class="form-label">{{ __('Url') }} <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="gslug" name="gslug" required>
                            <input type="hidden" name="slug" value="{{old('slug')}}" id="slug">
                            <div class="my-3" id="link">{{old('slug')}}</div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-check-custom form-check-solid">
                                <input type="hidden" name="add_to_navbar" value="0">
                                <input class="form-check-input" type="checkbox" id="add_to_navbar_create" name="add_to_navbar" value="1" @checked(old('add_to_navbar', true))>
                                <label class="form-check-label" for="add_to_navbar_create">
                                    {{ __('Add To Navbar') }}
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="meta_title_create" class="form-label">{{ __('Meta Title') }}</label>
                            <input type="text" class="form-control" id="meta_title_create" name="meta_title"
                                   value="{{ old('meta_title') }}">
                        </div>

                        <div class="mb-3">
                            <label for="meta_description_create" class="form-label">{{ __('Meta Description') }}</label>
                            <textarea class="form-control" id="meta_description_create" name="meta_description" rows="3">{{ old('meta_description') }}</textarea>
                        </div>

                        <div class="mb-3">
                            <label for="meta_keywords_create" class="form-label">{{ __('Meta Keywords') }}</label>
                            <input type="text" class="form-control" id="meta_keywords_create" name="meta_keywords"
                                   value="{{ old('meta_keywords') }}" placeholder="keyword 1, keyword 2, keyword 3">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('Meta Image') }}</label>
                            <x-admin.image-input name="meta_img"/>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">{{ __('Close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endif
