@if(isset($modal) && $modal)
<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal{{$blogs_category->id}}" tabindex="-1" aria-labelledby="editCategoryModalLabel{{$blogs_category->id}}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.blogs_categories.update', $blogs_category->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title" id="editCategoryModalLabel{{$blogs_category->id}}">{{ __('Edit Category') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name_{{$blogs_category->id}}" class="form-label d-flex align-items-center">
                            <i class="bi bi-translate text-primary me-1" data-bs-toggle="tooltip"
                               title="{{ __('Translatable') }}"></i>
                            {{ __('Name') }} <span class="text-danger ms-1">*</span>
                        </label>
                        <input id="name_{{$blogs_category->id}}"
                               type="text"
                               class="form-control"
                               name="name"
                               value="{{ old('name', $blogs_category->name) }}"
                               required>
                    </div>

                    <div class="mb-3">
                        <label for="slug_readonly_{{ $blogs_category->id }}" class="form-label">{{ __('Slug') }}</label>
                        <input id="slug_readonly_{{ $blogs_category->id }}" type="text" class="form-control" name="slug"
                               value="{{ $blogs_category->slug }}" required readonly>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-check-custom form-check-solid">
                            <input type="hidden" name="add_to_navbar" value="0">
                            <input class="form-check-input" type="checkbox" name="add_to_navbar"
                                   id="add_to_navbar_{{$blogs_category->id}}" value="1"
                                   @checked(old('add_to_navbar', $blogs_category->add_to_navbar))>
                            <label class="form-check-label" for="add_to_navbar_{{$blogs_category->id}}">
                                {{ __('Add To Navbar') }}
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="meta_title_{{$blogs_category->id}}" class="form-label d-flex align-items-center">
                            <i class="bi bi-translate text-primary me-1" data-bs-toggle="tooltip"
                               title="{{ __('Translatable') }}"></i>
                            {{ __('Meta Title') }}
                        </label>
                        <input id="meta_title_{{$blogs_category->id}}"
                               type="text"
                               class="form-control"
                               name="meta_title"
                               value="{{ old('meta_title', $blogs_category->meta_title) }}">
                    </div>

                    <div class="mb-3">
                        <label for="meta_description_{{$blogs_category->id}}" class="form-label d-flex align-items-center">
                            <i class="bi bi-translate text-primary me-1" data-bs-toggle="tooltip"
                               title="{{ __('Translatable') }}"></i>
                            {{ __('Meta Description') }}
                        </label>
                        <textarea id="meta_description_{{$blogs_category->id}}"
                                  class="form-control"
                                  rows="3"
                                  name="meta_description">{{ old('meta_description', $blogs_category->meta_description) }}</textarea>
                    </div>

                    <div class="mb-3">
                        <label for="meta_keywords_{{$blogs_category->id}}" class="form-label d-flex align-items-center">
                            <i class="bi bi-translate text-primary me-1" data-bs-toggle="tooltip"
                               title="{{ __('Translatable') }}"></i>
                            {{ __('Meta Keywords') }}
                        </label>
                        <input id="meta_keywords_{{$blogs_category->id}}"
                               type="text"
                               class="form-control"
                               name="meta_keywords"
                               value="{{ old('meta_keywords', $blogs_category->meta_keywords) }}"
                               placeholder="keyword 1, keyword 2, keyword 3">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">{{ __('Meta Image') }}</label>
                        <x-admin.image-input name="meta_img" :preview="$blogs_category->meta_image_link"/>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-check-custom form-check-solid">
                            <input class="form-check-input" type="checkbox" name="update_translations"
                                   id="update_translations_{{$blogs_category->id}}" value="1" @checked(old('update_translations'))>
                            <label class="form-check-label fs-7 ms-2" for="update_translations_{{$blogs_category->id}}">
                                {{ __('Use Google Translate to update all other languages.') }}
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('Save and Close') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
