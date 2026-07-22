@section('title' , __('Dashboard'))
@section('toolbar')
    @php
        $breadcrumbItems = [
            ['label' => 'Dashboard'],

        ];
    @endphp
    <x-admin.breadcrumb :pageTitle="__('Dashboard')" :breadcrumbItems="$breadcrumbItems"/>
@endsection
<x-admin-layout>
    <div class="row g-5 g-xl-8 mb-5 mb-xl-10">
        <div class="col-md-4">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($analytics['viewers']['total']) }}</span>
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ __('Total Viewers') }}</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <div class="d-flex align-items-center">
                        <span class="symbol symbol-40px me-3">
                            <span class="symbol-label bg-light-primary">
                                <i class="ki-duotone ki-eye fs-2 text-primary">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                        </span>
                        <span class="text-gray-500 fw-semibold fs-7">{{ __('Unique visitors tracked across the site') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($analytics['viewers']['last_month']) }}</span>
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ __('Last Month Viewers') }}</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <div class="d-flex align-items-center">
                        <span class="symbol symbol-40px me-3">
                            <span class="symbol-label bg-light-warning">
                                <i class="ki-duotone ki-calendar fs-2 text-warning">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                        </span>
                        <span class="text-gray-500 fw-semibold fs-7">{{ __('Unique visitors during the previous calendar month') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($analytics['viewers']['today']) }}</span>
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ __('Today Viewers') }}</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <div class="d-flex align-items-center">
                        <span class="symbol symbol-40px me-3">
                            <span class="symbol-label bg-light-success">
                                <i class="ki-duotone ki-graph-up fs-2 text-success">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                    <span class="path4"></span>
                                    <span class="path5"></span>
                                    <span class="path6"></span>
                                </i>
                            </span>
                        </span>
                        <span class="text-gray-500 fw-semibold fs-7">{{ __('Unique visitors so far today') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-flush mb-5 mb-xl-10">
        <div class="card-header pt-5">
            <h3 class="card-title align-items-start flex-column">
                <span class="card-label fw-bold text-gray-900">{{ __('Viewers Trend') }}</span>
                <span class="text-gray-500 mt-1 fw-semibold fs-6">{{ __('Last 30 days') }}</span>
            </h3>
        </div>
        <div class="card-body pt-3">
            <div id="kt_dashboard_viewers_chart" style="height: 350px;"></div>
        </div>
    </div>

    <div class="row g-5 g-xl-8 mb-5 mb-xl-10">
        <div class="col-xl-6">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">{{ __('Top 10 Most Visited Pages') }}</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-6">{{ __('Ranked by recorded page views') }}</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-0 gy-4 my-0">
                            <thead>
                            <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                                <th class="p-0 pb-3 min-w-200px">{{ __('Page') }}</th>
                                <th class="p-0 pb-3 min-w-100px text-end">{{ __('Views') }}</th>
                                <th class="p-0 pb-3 min-w-100px text-end">{{ __('Viewers') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($analytics['top_pages'] as $page)
                                <tr>
                                    <td>
                                        <span class="text-gray-800 fw-bold d-block fs-6">{{ $page['path'] }}</span>
                                        @if(! empty($page['route_name']))
                                            <span class="text-gray-500 fw-semibold fs-7">{{ $page['route_name'] }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <span class="text-gray-800 fw-bold fs-6">{{ number_format($page['views']) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge badge-light-primary fs-7 fw-bold">{{ number_format($page['viewers']) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-gray-500 py-10">{{ __('No page views recorded yet.') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card card-flush h-xl-100">
                <div class="card-header pt-5">
                    <h3 class="card-title align-items-start flex-column">
                        <span class="card-label fw-bold text-gray-900">{{ __('Top 10 Traffic Sources') }}</span>
                        <span class="text-gray-500 mt-1 fw-semibold fs-6">{{ __('External referrers and direct traffic') }}</span>
                    </h3>
                </div>
                <div class="card-body pt-5">
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-0 gy-4 my-0">
                            <thead>
                            <tr class="fs-7 fw-bold text-gray-500 border-bottom-0">
                                <th class="p-0 pb-3 min-w-200px">{{ __('Source') }}</th>
                                <th class="p-0 pb-3 min-w-100px text-end">{{ __('Views') }}</th>
                                <th class="p-0 pb-3 min-w-100px text-end">{{ __('Viewers') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($analytics['traffic_sources'] as $source)
                                <tr>
                                    <td>
                                        <span class="text-gray-800 fw-bold d-block fs-6">{{ $source['source'] }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="text-gray-800 fw-bold fs-6">{{ number_format($source['views']) }}</span>
                                    </td>
                                    <td class="text-end">
                                        <span class="badge badge-light-info fs-7 fw-bold">{{ number_format($source['viewers']) }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-gray-500 py-10">{{ __('No traffic sources recorded yet.') }}</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-5 g-xl-8 mb-5 mb-xl-10">
        <div class="col-md-6">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($analytics['contacts_total']) }}</span>
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ __('Total Contacts') }}</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <span class="text-gray-500 fw-semibold fs-7">{{ __('Submitted contact form messages') }}</span>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card card-flush h-md-100">
                <div class="card-header pt-5">
                    <div class="card-title d-flex flex-column">
                        <span class="fs-2hx fw-bold text-gray-900 me-2 lh-1 ls-n2">{{ number_format($analytics['subscribers_total']) }}</span>
                        <span class="text-gray-500 pt-1 fw-semibold fs-6">{{ __('Total Subscribers') }}</span>
                    </div>
                </div>
                <div class="card-body d-flex align-items-end pt-0">
                    <span class="text-gray-500 fw-semibold fs-7">{{ __('Newsletter subscribers') }}</span>
                </div>
            </div>
        </div>
    </div>

    @can('App Monitoring')
    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-5 g-xl-9">
        <div class="col-md-4">
            <!--begin::Card-->
            <div class="card h-md-100">
                <!--begin::Card body-->
                <div class="card-body d-flex flex-center">
                    <!--begin::Button-->
                    <a target="_blank" href="/{{config('telescope.path')}}">
                        <!--begin::Illustration-->
                        <!--end::Illustration-->
                        <!--begin::Label-->
                        <span class="fw-bolder fs-3 text-gray-600 text-hover-primary">Telescope</span>
                        <!--end::Label-->
                    </a>
                    <!--end::Button-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
        <div class="col-md-4">
            <!--begin::Card-->
            <div class="card h-md-100">
                <!--begin::Card body-->
                <div class="card-body d-flex flex-center">
                    <!--begin::Button-->
                    <a target="_blank" href="/{{config('pulse.path')}}">
                        <!--begin::Illustration-->
                        <!--end::Illustration-->
                        <!--begin::Label-->
                        <span class="fw-bolder fs-3 text-gray-600 text-hover-primary">Pulse</span>
                        <!--end::Label-->
                    </a>
                    <!--end::Button-->
                </div>
                <!--end::Card body-->
            </div>
            <!--end::Card-->
        </div>
    </div>
    @endcan

    @push('scripts')
        <script>
            (function () {
                const element = document.getElementById('kt_dashboard_viewers_chart');
                if (!element || typeof ApexCharts === 'undefined') {
                    return;
                }

                const labels = @json($analytics['trend']['labels']);
                const data = @json($analytics['trend']['data']);
                const primaryColor = KTUtil.getCssVariableValue('--bs-primary');
                const borderColor = KTUtil.getCssVariableValue('--bs-border-dashed-color');
                const grayColor = KTUtil.getCssVariableValue('--bs-gray-500');

                const options = {
                    series: [{
                        name: @json(__('Viewers')),
                        data: data
                    }],
                    chart: {
                        fontFamily: 'inherit',
                        type: 'area',
                        height: 350,
                        toolbar: {show: false},
                        zoom: {enabled: false}
                    },
                    dataLabels: {enabled: false},
                    stroke: {
                        curve: 'smooth',
                        show: true,
                        width: 3,
                        colors: [primaryColor]
                    },
                    xaxis: {
                        categories: labels,
                        axisBorder: {show: false},
                        axisTicks: {show: false},
                        labels: {
                            style: {
                                colors: grayColor,
                                fontSize: '12px'
                            }
                        },
                        crosshairs: {
                            position: 'front',
                            stroke: {
                                color: primaryColor,
                                width: 1,
                                dashArray: 3
                            }
                        }
                    },
                    yaxis: {
                        min: 0,
                        tickAmount: 4,
                        labels: {
                            style: {
                                colors: grayColor,
                                fontSize: '12px'
                            },
                            formatter: function (value) {
                                return Math.round(value);
                            }
                        }
                    },
                    tooltip: {
                        style: {fontSize: '12px'},
                        y: {
                            formatter: function (value) {
                                return value + ' ' + @json(__('viewers'));
                            }
                        }
                    },
                    colors: [primaryColor],
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.4,
                            opacityTo: 0.05,
                            stops: [0, 90, 100]
                        }
                    },
                    grid: {
                        borderColor: borderColor,
                        strokeDashArray: 4,
                        yaxis: {lines: {show: true}}
                    },
                    markers: {
                        strokeColors: primaryColor,
                        strokeWidth: 3
                    }
                };

                const chart = new ApexCharts(element, options);
                chart.render();
            })();
        </script>
    @endpush
</x-admin-layout>
