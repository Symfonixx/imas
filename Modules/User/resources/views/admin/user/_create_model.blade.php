<div class="modal-dialog">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">{{__('Add New User')}}</h3>

            <!--begin::Close-->
            <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal"
                 aria-label="Close">
                <i class="ki-duotone ki-cross fs-1">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>
            </div>
            <!--end::Close-->
        </div>

        <form method="POST" action="{{route('admin.users.store')}}">
            @csrf

            <input type="hidden" name="type" value="user">

            <div class="modal-body">
                <div class="mb-5">
                    <div class="row">
                        <div class="col-md-12 mb-7">
                            <label for="name" class=" required form-label">{{__('Name')}}</label>
                            <input type="text" id="name"
                                   class="form-control form-control-solid @error('name') is-invalid @enderror"
                                   name="name" value="{{ old('name') }}" required autofocus>
                            @error('name')
                            <span class="invalid-feedback" role="alert">
                                        <strong>{{ $message }}</strong>
                                    </span>
                            @enderror
                        </div>

                        <div class="col-md-12 mb-7">
                            <x-admin.email-input
                                input-id="email_user_create"
                                :value="old('email')"
                                :required="true"
                            />
                        </div>

                        <div class="col-md-12 mb-7">
                            <x-admin.phone-country-input
                                input-id="mobile_user_create"
                                :value="old('mobile')"
                                :required="true"
                            />
                        </div>

                        <div class="col-md-12 mb-7">
                            <x-admin.password-field input-id="password_user_create" :required="true"/>
                        </div>

                    </div>
                </div>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light"
                        data-bs-dismiss="modal">{{__('Discard')}}</button>
                <button type="submit" class="btn btn-primary">{{__('Submit')}}</button>
            </div>
        </form>

    </div>
</div>
