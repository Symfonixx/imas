<div class="menu-item">
    <a class="menu-link {{ isset($active['dashboard']) ? 'active' : '' }}"
       href="{{ route('admin.dashboard.index') }}">
        <span class="menu-icon">
            <i class="bi bi-speedometer2"></i>
        </span>
        <span class="menu-title">{{ __('Dashboard') }}</span>
    </a>
</div>

@can('Property Management')
    <div data-kt-menu-trigger="click"
         class="menu-item menu-accordion {{ isset($active['properties']) ? 'show hover' : '' }}">
        <span class="menu-link">
            <span class="menu-icon">
                <i class="bi bi-buildings"></i>
            </span>
            <span class="menu-title">{{ __('Projects') }}</span>
            <span class="menu-arrow"></span>
        </span>

        <div class="menu-sub menu-sub-accordion {{ isset($active['properties']) ? 'show' : '' }}">
            <div class="menu-item">
                <a class="menu-link {{isset($active['projects']) ? 'active' : '' }}"
                   href="{{ route('admin.properties.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ __('Properties') }}</span>
                </a>
            </div>

            <div class="menu-item menu-accordion {{ isset($active['location_cities']) || isset($active['locations']) || isset($active['property_types']) || isset($active['project_unit_types']) ? 'show hover' : '' }}"
                 data-kt-menu-trigger="click">
                <span class="menu-link">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ __('Project settings') }}</span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion {{ isset($active['location_cities']) || isset($active['locations']) || isset($active['property_types']) || isset($active['project_unit_types']) ? 'show' : '' }}">

                    <div class="menu-item">
                        <a class="menu-link {{ isset($active['locations']) ? 'active' : '' }}"
                           href="{{ route('admin.locations.index') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">{{ __('Locations') }}</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link {{ isset($active['property_types']) ? 'active' : '' }}"
                           href="{{ route('admin.property_types.index') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">{{ __('Project type') }}</span>
                        </a>
                    </div>
                    <div class="menu-item">
                        <a class="menu-link {{ isset($active['project_unit_types']) ? 'active' : '' }}"
                           href="{{ route('admin.project_unit_types.index') }}">
                            <span class="menu-bullet">
                                <span class="bullet bullet-dot"></span>
                            </span>
                            <span class="menu-title">{{ __('Project unit types') }}</span>
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endcan

@canany(['CMS Management', 'Settings Management', 'Corporate Management', 'Media Library Management', 'Property Management'])
    <div data-kt-menu-trigger="click"
         class="menu-item menu-accordion {{ isset($active['cms']) ? 'show hover' : '' }}">
        <span class="menu-link">
            <span class="menu-icon">
                <i class="bi bi-intersect"></i>
            </span>
            <span class="menu-title">{{ __('CMS') }}</span>
            <span class="menu-arrow"></span>
        </span>

        @can('CMS Management')
            <div class="menu-sub menu-sub-accordion {{ isset($active['slides']) ? 'show' : '' }}">
                <div class="menu-item">
                    <a class="menu-link {{ isset($active['slides']) ? 'active' : '' }}"
                       href="{{ route('admin.slides.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ __('Slider') }}</span>
                    </a>
                </div>
            </div>

            <div class="menu-sub menu-sub-accordion {{ isset($active['pages']) ? 'show' : '' }}">
                <div class="menu-item">
                    <a class="menu-link {{ isset($active['pages']) ? 'active' : '' }}"
                       href="{{ route('admin.pages.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ __('Pages') }}</span>
                    </a>
                </div>
            </div>

            <div class="menu-sub menu-sub-accordion {{ isset($active['blogs']) ? 'show' : '' }}">
                <div class="menu-item">
                    <a class="menu-link {{ isset($active['blogs']) ? 'active' : '' }}"
                       href="{{ route('admin.blogs.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ __('Blogs') }}</span>
                    </a>
                </div>
            </div>
        @endcan

        @can('Property Management')
            <div class="menu-sub menu-sub-accordion {{ isset($active['turkish_citizenship']) ? 'show' : '' }}">
                <div class="menu-item">
                    <a class="menu-link {{ isset($active['turkish_citizenship']) ? 'active' : '' }}"
                       href="{{ route('admin.turkish_citizenship.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ __('Turkish Citizenship') }}</span>
                    </a>
                </div>
            </div>
        @endcan

        @can('Corporate Management')
            <div class="menu-sub menu-sub-accordion {{ isset($active['corporate_services']) ? 'show' : '' }}">
                <div class="menu-item">
                    <a class="menu-link {{ isset($active['corporate_services']) ? 'active' : '' }}"
                       href="{{ route('admin.corporate_services.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ __('Services') }}</span>
                    </a>
                </div>
            </div>
        @endcan

        @can('Settings Management')
            <div class="menu-sub menu-sub-accordion {{ isset($active['about_us']) ? 'show' : '' }}">
                <div class="menu-item">
                    <a class="menu-link {{ isset($active['about_us']) ? 'active' : '' }}"
                       href="{{ route('admin.about_us.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ __('About Us') }}</span>
                    </a>
                </div>
            </div>
        @endcan

        @can('CMS Management')
            <div class="menu-sub menu-sub-accordion {{ isset($active['faqs']) ? 'show' : '' }}">
                <div class="menu-item">
                    <a class="menu-link {{ isset($active['faqs']) ? 'active' : '' }}"
                       href="{{ route('admin.faqs.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ __('FAQs') }}</span>
                    </a>
                </div>
            </div>
        @endcan

        @canany(['CMS Management', 'Media Library Management'])
            <div class="menu-sub menu-sub-accordion {{ isset($active['blogs_categories']) || isset($active['media_library']) ? 'show' : '' }}">
                <div class="menu-item menu-accordion {{ isset($active['blogs_categories']) || isset($active['media_library']) ? 'show hover' : '' }}"
                     data-kt-menu-trigger="click">
                    <span class="menu-link">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ __('CMS settings') }}</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion {{ isset($active['blogs_categories']) || isset($active['media_library']) ? 'show' : '' }}">
                        @can('CMS Management')
                            <div class="menu-item">
                                <a class="menu-link {{ isset($active['blogs_categories']) ? 'active' : '' }}"
                                   href="{{ route('admin.blogs_categories.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">{{ __('Blog Categories') }}</span>
                                </a>
                            </div>
                        @endcan
                        @can('Media Library Management')
                            <div class="menu-item">
                                <a class="menu-link {{ isset($active['media_library']) ? 'active' : '' }}"
                                   href="{{ route('admin.media_library.index') }}">
                                    <span class="menu-bullet">
                                        <span class="bullet bullet-dot"></span>
                                    </span>
                                    <span class="menu-title">{{ __('Media Library') }}</span>
                                </a>
                            </div>
                        @endcan
                    </div>
                </div>
            </div>
        @endcanany
    </div>
@endcanany

@can('Corporate Management')
    <div data-kt-menu-trigger="click"
         class="menu-item menu-accordion {{ isset($active['corporate']) ? 'show hover' : '' }}">
        <span class="menu-link">
            <span class="menu-icon">
                <i class="bi bi-building"></i>
            </span>
            <span class="menu-title">{{ __('Corporate') }}</span>
            <span class="menu-arrow"></span>
        </span>

        <div class="menu-sub menu-sub-accordion {{ isset($active['corporate_testimonials']) ? 'show' : '' }}">
            <div class="menu-item">
                <a class="menu-link {{ isset($active['corporate_testimonials']) ? 'active' : '' }}"
                   href="{{ route('admin.corporate_testimonials.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ __('Testimonials') }}</span>
                </a>
            </div>
        </div>

        <div class="menu-sub menu-sub-accordion {{ isset($active['corporate_teams']) ? 'show' : '' }}">
            <div class="menu-item">
                <a class="menu-link {{ isset($active['corporate_teams']) ? 'active' : '' }}"
                   href="{{ route('admin.corporate_teams.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ __('Team') }}</span>
                </a>
            </div>
        </div>
    </div>
@endcan

@can('Hr Management')
    <div data-kt-menu-trigger="click"
         class="menu-item menu-accordion {{ isset($active['hr']) ? 'show hover' : '' }}">
        <span class="menu-link">
            <span class="menu-icon">
                <i class="bi bi-journal-text"></i>
            </span>
            <span class="menu-title">{{ __('HR') }}</span>
            <span class="menu-arrow"></span>
        </span>

        <div class="menu-sub menu-sub-accordion {{ isset($active['roles']) || isset($active['staffs']) ? 'show' : '' }}">
            <div class="menu-item">
                <a class="menu-link {{ isset($active['roles']) ? 'active' : '' }}"
                   href="{{ route('admin.roles.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ __('Roles') }}</span>
                </a>
            </div>

            <div class="menu-item">
                <a class="menu-link {{ isset($active['staffs']) ? 'active' : '' }}"
                   href="{{ route('admin.staffs.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ __('Staff') }}</span>
                </a>
            </div>
        </div>
    </div>
@endcan

@can('Support Management')
    <div data-kt-menu-trigger="click"
         class="menu-item menu-accordion {{ isset($active['support']) ? 'show hover' : '' }}">
        <span class="menu-link">
            <span class="menu-icon">
                <i class="bi bi-headset"></i>
            </span>
            <span class="menu-title">{{ __('Support Hub') }}</span>
            <span class="menu-arrow"></span>
        </span>

        <div class="menu-sub menu-sub-accordion {{ isset($active['contact_forms']) ? 'show' : '' }}">
            <div class="menu-item">
                <a class="menu-link {{ isset($active['contact_forms']) ? 'active' : '' }}"
                   href="{{ route('admin.contact_forms.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ __('Contacts') }}</span>
                </a>
            </div>
        </div>
        <div class="menu-sub menu-sub-accordion {{ isset($active['subscribers']) ? 'show' : '' }}">
            <div class="menu-item">
                <a class="menu-link {{ isset($active['subscribers']) ? 'active' : '' }}"
                   href="{{ route('admin.subscribers.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ __('Newsletter Subscribers') }}</span>
                </a>
            </div>
        </div>
        @can('Hr Management')
            <div class="menu-sub menu-sub-accordion {{ isset($active['users']) ? 'show' : '' }}">
                <div class="menu-item">
                    <a class="menu-link {{ isset($active['users']) ? 'active' : '' }}"
                       href="{{ route('admin.users.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ __('Users') }}</span>
                    </a>
                </div>
            </div>
        @endcan
    </div>
@endcan

@can('Settings Management')
    <div data-kt-menu-trigger="click"
         class="menu-item menu-accordion {{ isset($active['settings']) ? 'show hover' : '' }}">
        <span class="menu-link">
            <span class="menu-icon">
                <i class="bi bi-gear"></i>
            </span>
            <span class="menu-title">{{ __('Settings') }}</span>
            <span class="menu-arrow"></span>
        </span>

        <div class="menu-sub menu-sub-accordion {{ isset($active['websiteConfigurations']) ? 'show' : '' }}">
            <div class="menu-item">
                <a class="menu-link {{ isset($active['websiteConfigurations']) ? 'active' : '' }}"
                   href="{{ route('admin.settings.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ __('Website Configurations') }}</span>
                </a>
            </div>
        </div>

        <div class="menu-sub menu-sub-accordion {{ isset($active['seo']) ? 'show' : '' }}">
            <div class="menu-item">
                <a class="menu-link {{ isset($active['seo']) ? 'active' : '' }}"
                   href="{{ route('admin.seo.index') }}">
                    <span class="menu-bullet">
                        <span class="bullet bullet-dot"></span>
                    </span>
                    <span class="menu-title">{{ __('Seo Configurations') }}</span>
                </a>
            </div>
        </div>

        @can('Logs Management')
            <div class="menu-sub menu-sub-accordion {{ isset($active['logs']) ? 'show' : '' }}">
                <div class="menu-item">
                    <a class="menu-link {{ isset($active['logs']) ? 'active' : '' }}"
                       href="{{ route('admin.logs.index') }}">
                        <span class="menu-bullet">
                            <span class="bullet bullet-dot"></span>
                        </span>
                        <span class="menu-title">{{ __('Logs & Bugs') }}</span>
                    </a>
                </div>
            </div>
        @endcan
    </div>
@endcan
