<template>
    <head>
        <title>{{trans("Register")}} | {{appName}}</title>
    </head>

    <app-layout>
        <div class="container mt-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header text-center">
                            <h3>{{ trans("Register") }}</h3>
                        </div>
                        <div class="card-body">
                            <form @submit.prevent="form.post(route('register'))">

                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="first_name">{{ trans("contact_us.first_name") }}</label>
                                        <input
                                            id="first_name"
                                            v-model="form.first_name"
                                            autofocus
                                            class="form-control"
                                            required
                                            type="text"
                                            maxlength="120"
                                            autocomplete="given-name"
                                        />
                                        <span v-if="errors.first_name" class="invalid-feedback d-block">{{ errors.first_name }}</span>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="last_name">{{ trans("contact_us.last_name") }}</label>
                                        <input
                                            id="last_name"
                                            v-model="form.last_name"
                                            class="form-control"
                                            required
                                            type="text"
                                            maxlength="120"
                                            autocomplete="family-name"
                                        />
                                        <span v-if="errors.last_name" class="invalid-feedback d-block">{{ errors.last_name }}</span>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="email">{{ trans("Email") }}</label>
                                    <input
                                        id="email"
                                        v-model="form.email"
                                        class="form-control"
                                        required
                                        type="email"
                                    />
                                    <span v-if="errors.email" class="invalid-feedback d-block">{{ errors.email }}</span>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="mobile">{{ trans("Mobile") }}</label>
                                    <input
                                        id="mobile"
                                        v-model="form.mobile"
                                        class="form-control"
                                        required
                                        type="mobile"
                                    />
                                    <span v-if="errors.mobile"
                                          class="invalid-feedback d-block">{{ errors.mobile }}</span>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label" for="password">{{ trans("Password") }}</label>
                                    <input
                                        id="password"
                                        v-model="form.password"
                                        class="form-control"
                                        required
                                        type="password"
                                    />
                                    <span v-if="errors.password"
                                          class="invalid-feedback d-block">{{ errors.password }}</span>

                                </div>

                                <div class="mb-3">
                                    <label class="form-label"
                                           for="password_confirmation">{{ trans("Confirm Password") }}</label>
                                    <input
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        class="form-control"
                                        required
                                        type="password_confirmation"
                                    />
                                    <span v-if="errors.password_confirmation"
                                          class="invalid-feedback d-block">{{ errors.password_confirmation }}</span>

                                </div>


                                <button class="btn btn-primary w-100" type="submit">
                                    {{ trans("Register") }}
                                </button>
                            </form>

                            <!-- Standalone auth pages disabled; use navbar AuthModal instead.
                            <div class="mt-3 text-center">
                                <Link :href="route('login')">{{ trans("Already Have An Account?") }}</Link>
                            </div>
                            -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </app-layout>
</template>


<script>
import {computed} from 'vue';
import {usePage, Link, useForm} from '@inertiajs/vue3';
import AppLayout from '@/Layouts/App.vue';

export default {
    components: {
        AppLayout, Link
    },
    props: {
        errors: Object
    },
    setup() {
        const page = usePage();

        const appName = computed(() => page.props.appName)
        const trans = (key) => page.props.translations[key] || key;

        const form = useForm({
            first_name: '',
            last_name: '',
            email: '',
            mobile: '',
            password: '',
            password_confirmation: '',

        });

        return {form, appName, trans};
    }
}

</script>

<style scoped>
/* Add styles specific to the Index page here */
</style>
