@props([
    'model',
    'dataTable' => true,
    'class' => null,
    'formUrl' => null,
    'search' => null,
    'title' => null,
    'empty' => null,
])

@php
    $hasRows = false;
    if (isset($model)) {
        $hasRows = $model instanceof \Illuminate\Support\Collection
            ? $model->isNotEmpty()
            : (is_countable($model) ? count($model) > 0 : false);
    }
    $emptyMessage = $empty ? __($empty) : __('No records found.');
@endphp

<div class="card card-flush imas-admin-table">
    <div class="card-header border-0 pt-6">
        @if($search || $title)
            <div class="card-title">
                @if($search)
                    <div class="d-flex align-items-center position-relative my-1 imas-admin-table__search">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <input type="text"
                               data-kt-data-table-filter="search"
                               class="form-control form-control-solid w-250px ps-12"
                               placeholder="{{ __($search) }}"
                               aria-label="{{ __($search) }}"/>
                    </div>
                @endif
                @if($title)
                    <h2 class="mb-0">{{ __($title) }}</h2>
                @endif
            </div>
        @elseif($formUrl)
            <div></div>
        @endif

        @if($formUrl)
            <div class="card-toolbar">
                <div class="d-flex justify-content-end" data-kt-comp-table-toolbar="base"></div>
                <div class="d-none align-items-center gap-3 imas-admin-table__toolbar-selected"
                     data-kt-comp-table-toolbar="selected">
                    <div class="fw-semibold text-gray-800">
                        <span class="me-1" data-kt-comp-table-toolbar="selected_count"></span>{{ __('Selected') }}
                    </div>
                    <button type="button"
                            class="btn btn-sm btn-danger"
                            data-kt-comp-table-toolbar="delete_selected">
                        {{ __('Delete Selected') }}
                    </button>
                </div>
            </div>
        @endif
    </div>

    @if($formUrl)
        <form method="post" id="delete_all" action="{{ $formUrl }}">
            @csrf
            @method('DELETE')
    @endif

    <div class="card-body pt-3">
        <div class="imas-admin-table__scroll">
            <table class="table align-middle table-row-dashed fs-6 gy-5 {{ $class }}"
                   @if($dataTable) id="dataTable" @endif>
                {{ $slot }}
            </table>
        </div>

        @unless($hasRows)
            <div class="imas-admin-table__empty" data-imas-admin-table-empty>
                <div class="imas-admin-table__empty-icon" aria-hidden="true">
                    <i class="bi bi-inbox"></i>
                </div>
                <div class="fw-semibold text-gray-700">{{ $emptyMessage }}</div>
            </div>
        @endunless
    </div>

    @if($formUrl)
        </form>
    @endif

    @if(isset($model) && $model instanceof \Illuminate\Pagination\LengthAwarePaginator)
        <div class="card-footer">
            {!! $model->withQueryString()->links() !!}
        </div>
    @endif
</div>

@if($dataTable)
    @push('scripts')
        <script>
            $(document).ready(function () {
                const tableEl = document.getElementById('dataTable');
                if (!tableEl) {
                    return;
                }

                const dataTable = $('#dataTable').DataTable({
                    responsive: true,
                    paging: false,
                    searching: true,
                    info: false,
                    order: [],
                    ordering: true,
                });

                const searchInput = document.querySelector('[data-kt-data-table-filter="search"]');
                if (searchInput) {
                    searchInput.addEventListener('keyup', (event) => {
                        dataTable.search(event.target.value).draw();
                    });
                }

                const checkboxes = tableEl.querySelectorAll('[type="checkbox"]');
                const baseToolbar = document.querySelector('[data-kt-comp-table-toolbar="base"]');
                const selectedToolbar = document.querySelector('[data-kt-comp-table-toolbar="selected"]');
                const selectedCount = document.querySelector('[data-kt-comp-table-toolbar="selected_count"]');
                const deleteSelected = document.querySelector('[data-kt-comp-table-toolbar="delete_selected"]');

                if (!baseToolbar || !selectedToolbar || !selectedCount || !deleteSelected) {
                    return;
                }

                const syncSelectionToolbar = () => {
                    const bodyChecks = tableEl.querySelectorAll('tbody [type="checkbox"]');
                    let checkedCount = 0;
                    bodyChecks.forEach((checkbox) => {
                        if (checkbox.checked) {
                            checkedCount++;
                        }
                    });

                    if (checkedCount > 0) {
                        selectedCount.innerHTML = checkedCount;
                        baseToolbar.classList.add('d-none');
                        selectedToolbar.classList.remove('d-none');
                        selectedToolbar.classList.add('d-flex');
                    } else {
                        baseToolbar.classList.remove('d-none');
                        selectedToolbar.classList.add('d-none');
                        selectedToolbar.classList.remove('d-flex');
                    }
                };

                checkboxes.forEach((checkbox) => {
                    checkbox.addEventListener('click', () => {
                        setTimeout(syncSelectionToolbar, 50);
                    });
                });

                deleteSelected.addEventListener('click', () => {
                    Swal.fire({
                        text: "{{ __('This action cannot be undone.') }}",
                        icon: 'warning',
                        showCancelButton: true,
                        buttonsStyling: false,
                        confirmButtonText: "{{ __('Yes Delete!') }}",
                        cancelButtonText: "{{ __('No Cancel') }}",
                        customClass: {
                            confirmButton: 'btn fw-bold btn-danger',
                            cancelButton: 'btn fw-bold btn-active-light-primary',
                        },
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#delete_all').submit();
                        }
                    });
                });
            });
        </script>
    @endpush
@endif
