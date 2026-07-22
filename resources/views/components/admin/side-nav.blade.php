{{-- Sidebar menu items — icons + searchable titles --}}
<div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Dashboard') }}">
    <a class="menu-link {{ isset($active['dashboard']) ? 'active' : '' }}"
       href="{{ route('admin.dashboard.index') }}">
        <span class="menu-icon">
            <i class="bi bi-grid-1x2-fill"></i>
        </span>
        <span class="menu-title">{{ __('Dashboard') }}</span>
    </a>
</div>

@php
    $propertySettingsActive = isset($active['location_cities'])
        || isset($active['locations'])
        || isset($active['property_types'])
        || isset($active['project_unit_types'])
        || isset($active['slide_categories'])
        || isset($active['property_attributes'])
        || isset($active['property_attribute_groups']);
@endphp

@can('Property Management')
    <div data-kt-menu-trigger="click" data-imas-menu-item data-imas-menu-title="{{ __('Projects') }}"
         class="menu-item menu-accordion {{ isset($active['properties']) ? 'show hover' : '' }}">
        <span class="menu-link">
            <span class="menu-icon">
                <i class="bi bi-buildings-fill"></i>
            </span>
            <span class="menu-title">{{ __('Projects') }}</span>
            <span class="menu-arrow"></span>
        </span>

        <div class="menu-sub menu-sub-accordion {{ isset($active['properties']) ? 'show' : '' }}">
            <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Properties') }}">
                <a class="menu-link {{ isset($active['projects']) ? 'active' : '' }}"
                   href="{{ route('admin.properties.index') }}">
                    <span class="menu-icon menu-icon--sm">
                        <i class="bi bi-house-door"></i>
                    </span>
                    <span class="menu-title">{{ __('Properties') }}</span>
                </a>
            </div>

            <div class="menu-item menu-accordion {{ $propertySettingsActive ? 'show hover' : '' }}"
                 data-kt-menu-trigger="click"
                 data-imas-menu-item
                 data-imas-menu-title="{{ __('Property Settings') }}">
                <span class="menu-link">
                    <span class="menu-icon menu-icon--sm">
                        <i class="bi bi-sliders"></i>
                    </span>
                    <span class="menu-title">{{ __('Property Settings') }}</span>
                    <span class="menu-arrow"></span>
                </span>
                <div class="menu-sub menu-sub-accordion {{ $propertySettingsActive ? 'show' : '' }}">
                    <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Locations') }}">
                        <a class="menu-link {{ isset($active['locations']) ? 'active' : '' }}"
                           href="{{ route('admin.locations.index') }}">
                            <span class="menu-icon menu-icon--sm">
                                <i class="bi bi-geo-alt"></i>
                            </span>
                            <span class="menu-title">{{ __('Locations') }}</span>
                        </a>
                    </div>
                    <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Project type') }}">
                        <a class="menu-link {{ isset($active['property_types']) ? 'active' : '' }}"
                           href="{{ route('admin.property_types.index') }}">
                            <span class="menu-icon menu-icon--sm">
                                <i class="bi bi-tags"></i>
                            </span>
                            <span class="menu-title">{{ __('Project type') }}</span>
                        </a>
                    </div>
                    <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Project unit types') }}">
                        <a class="menu-link {{ isset($active['project_unit_types']) ? 'active' : '' }}"
                           href="{{ route('admin.project_unit_types.index') }}">
                            <span class="menu-icon menu-icon--sm">
                                <i class="bi bi-grid-3x3-gap"></i>
                            </span>
                            <span class="menu-title">{{ __('Project unit types') }}</span>
                        </a>
                    </div>
                    <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Slide categories') }}">
                        <a class="menu-link {{ isset($active['slide_categories']) ? 'active' : '' }}"
                           href="{{ route('admin.slide_categories.index') }}">
                            <span class="menu-icon menu-icon--sm">
                                <i class="bi bi-collection-play"></i>
                            </span>
                            <span class="menu-title">{{ __('Slide categories') }}</span>
                        </a>
                    </div>
                    <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Attributes') }}">
                        <a class="menu-link {{ isset($active['property_attributes']) ? 'active' : '' }}"
                           href="{{ route('admin.property_attributes.index') }}">
                            <span class="menu-icon menu-icon--sm">
                                <i class="bi bi-ui-checks"></i>
                            </span>
                            <span class="menu-title">{{ __('Attributes') }}</span>
                        </a>
                    </div>
                    <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Attribute Groups') }}">
                        <a class="menu-link {{ isset($active['property_attribute_groups']) ? 'active' : '' }}"
                           href="{{ route('admin.property_attribute_groups.index') }}">
                            <span class="menu-icon menu-icon--sm">
                                <i class="bi bi-collection"></i>
                            </span>
                            <span class="menu-title">{{ __('Attribute Groups') }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endcan

@canany(['CMS Management', 'Settings Management', 'Corporate Management', 'Media Library Management', 'Property Management'])
    <div data-kt-menu-trigger="click" data-imas-menu-item data-imas-menu-title="{{ __('CMS') }}"
         class="menu-item menu-accordion {{ isset($active['cms']) ? 'show hover' : '' }}">
        <span class="menu-link">
            <span class="menu-icon">
                <i class="bi bi-layout-text-window-reverse"></i>
            </span>
            <span class="menu-title">{{ __('CMS') }}</span>
            <span class="menu-arrow"></span>
        </span>

        @can('CMS Management')
            <div class="menu-sub menu-sub-accordion {{ isset($active['slides']) ? 'show' : '' }}">
                <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Slider') }}">
                    <a class="menu-link {{ isset($active['slides']) ? 'active' : '' }}"
                       href="{{ route('admin.slides.index') }}">
                        <span class="menu-icon menu-icon--sm">
                            <i class="bi bi-images"></i>
                        </span>
                        <span class="menu-title">{{ __('Slider') }}</span>
                    </a>
                </div>
            </div>

            <div class="menu-sub menu-sub-accordion {{ isset($active['pages']) ? 'show' : '' }}">
                <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Pages') }}">
                    <a class="menu-link {{ isset($active['pages']) ? 'active' : '' }}"
                       href="{{ route('admin.pages.index') }}">
                        <span class="menu-icon menu-icon--sm">
                            <i class="bi bi-file-earmark-text"></i>
                        </span>
                        <span class="menu-title">{{ __('Pages') }}</span>
                    </a>
                </div>
            </div>

            <div class="menu-sub menu-sub-accordion {{ isset($active['blogs']) ? 'show' : '' }}">
                <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Blogs') }}">
                    <a class="menu-link {{ isset($active['blogs']) ? 'active' : '' }}"
                       href="{{ route('admin.blogs.index') }}">
                        <span class="menu-icon menu-icon--sm">
                            <i class="bi bi-journal-richtext"></i>
                        </span>
                        <span class="menu-title">{{ __('Blogs') }}</span>
                    </a>
                </div>
            </div>
        @endcan

        @can('Property Management')
            <div class="menu-sub menu-sub-accordion {{ isset($active['turkish_citizenship']) ? 'show' : '' }}">
                <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Turkish Citizenship') }}">
                    <a class="menu-link {{ isset($active['turkish_citizenship']) ? 'active' : '' }}"
                       href="{{ route('admin.turkish_citizenship.index') }}">
                        <span class="menu-icon menu-icon--sm">
                            <i class="bi bi-flag"></i>
                        </span>
                        <span class="menu-title">{{ __('Turkish Citizenship') }}</span>
                    </a>
                </div>
            </div>
        @endcan

        @can('Corporate Management')
            <div class="menu-sub menu-sub-accordion {{ isset($active['corporate_services']) ? 'show' : '' }}">
                <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Services') }}">
                    <a class="menu-link {{ isset($active['corporate_services']) ? 'active' : '' }}"
                       href="{{ route('admin.corporate_services.index') }}">
                        <span class="menu-icon menu-icon--sm">
                            <i class="bi bi-briefcase"></i>
                        </span>
                        <span class="menu-title">{{ __('Services') }}</span>
                    </a>
                </div>
            </div>
        @endcan

        @can('Settings Management')
            <div class="menu-sub menu-sub-accordion {{ isset($active['about_us']) ? 'show' : '' }}">
                <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('About Us') }}">
                    <a class="menu-link {{ isset($active['about_us']) ? 'active' : '' }}"
                       href="{{ route('admin.about_us.index') }}">
                        <span class="menu-icon menu-icon--sm">
                            <i class="bi bi-info-circle"></i>
                        </span>
                        <span class="menu-title">{{ __('About Us') }}</span>
                    </a>
                </div>
            </div>
        @endcan

        @can('CMS Management')
            <div class="menu-sub menu-sub-accordion {{ isset($active['faqs']) ? 'show' : '' }}">
                <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('FAQs') }}">
                    <a class="menu-link {{ isset($active['faqs']) ? 'active' : '' }}"
                       href="{{ route('admin.faqs.index') }}">
                        <span class="menu-icon menu-icon--sm">
                            <i class="bi bi-question-circle"></i>
                        </span>
                        <span class="menu-title">{{ __('FAQs') }}</span>
                    </a>
                </div>
            </div>
        @endcan

        @canany(['CMS Management', 'Media Library Management'])
            <div class="menu-sub menu-sub-accordion {{ isset($active['blogs_categories']) || isset($active['media_library']) ? 'show' : '' }}">
                <div class="menu-item menu-accordion {{ isset($active['blogs_categories']) || isset($active['media_library']) ? 'show hover' : '' }}"
                     data-kt-menu-trigger="click"
                     data-imas-menu-item
                     data-imas-menu-title="{{ __('CMS settings') }}">
                    <span class="menu-link">
                        <span class="menu-icon menu-icon--sm">
                            <i class="bi bi-gear-wide-connected"></i>
                        </span>
                        <span class="menu-title">{{ __('CMS settings') }}</span>
                        <span class="menu-arrow"></span>
                    </span>
                    <div class="menu-sub menu-sub-accordion {{ isset($active['blogs_categories']) || isset($active['media_library']) ? 'show' : '' }}">
                        @can('CMS Management')
                            <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Blog Categories') }}">
                                <a class="menu-link {{ isset($active['blogs_categories']) ? 'active' : '' }}"
                                   href="{{ route('admin.blogs_categories.index') }}">
                                    <span class="menu-icon menu-icon--sm">
                                        <i class="bi bi-folder2"></i>
                                    </span>
                                    <span class="menu-title">{{ __('Blog Categories') }}</span>
                                </a>
                            </div>
                        @endcan
                        @can('Media Library Management')
                            <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Media Library') }}">
                                <a class="menu-link {{ isset($active['media_library']) ? 'active' : '' }}"
                                   href="{{ route('admin.media_library.index') }}">
                                    <span class="menu-icon menu-icon--sm">
                                        <i class="bi bi-image"></i>
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
    <div data-kt-menu-trigger="click" data-imas-menu-item data-imas-menu-title="{{ __('Corporate') }}"
         class="menu-item menu-accordion {{ isset($active['corporate']) ? 'show hover' : '' }}">
        <span class="menu-link">
            <span class="menu-icon">
                <i class="bi bi-building"></i>
            </span>
            <span class="menu-title">{{ __('Corporate') }}</span>
            <span class="menu-arrow"></span>
        </span>

        <div class="menu-sub menu-sub-accordion {{ isset($active['corporate_testimonials']) ? 'show' : '' }}">
            <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Testimonials') }}">
                <a class="menu-link {{ isset($active['corporate_testimonials']) ? 'active' : '' }}"
                   href="{{ route('admin.corporate_testimonials.index') }}">
                    <span class="menu-icon menu-icon--sm">
                        <i class="bi bi-chat-quote"></i>
                    </span>
                    <span class="menu-title">{{ __('Testimonials') }}</span>
                </a>
            </div>
        </div>

        <div class="menu-sub menu-sub-accordion {{ isset($active['corporate_teams']) ? 'show' : '' }}">
            <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Team') }}">
                <a class="menu-link {{ isset($active['corporate_teams']) ? 'active' : '' }}"
                   href="{{ route('admin.corporate_teams.index') }}">
                    <span class="menu-icon menu-icon--sm">
                        <i class="bi bi-people"></i>
                    </span>
                    <span class="menu-title">{{ __('Team') }}</span>
                </a>
            </div>
        </div>
    </div>
@endcan

@can('Hr Management')
    <div data-kt-menu-trigger="click" data-imas-menu-item data-imas-menu-title="{{ __('HR') }}"
         class="menu-item menu-accordion {{ isset($active['hr']) ? 'show hover' : '' }}">
        <span class="menu-link">
            <span class="menu-icon">
                <i class="bi bi-person-badge"></i>
            </span>
            <span class="menu-title">{{ __('HR') }}</span>
            <span class="menu-arrow"></span>
        </span>

        <div class="menu-sub menu-sub-accordion {{ isset($active['roles']) || isset($active['staffs']) ? 'show' : '' }}">
            <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Roles') }}">
                <a class="menu-link {{ isset($active['roles']) ? 'active' : '' }}"
                   href="{{ route('admin.roles.index') }}">
                    <span class="menu-icon menu-icon--sm">
                        <i class="bi bi-shield-lock"></i>
                    </span>
                    <span class="menu-title">{{ __('Roles') }}</span>
                </a>
            </div>

            <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Staff') }}">
                <a class="menu-link {{ isset($active['staffs']) ? 'active' : '' }}"
                   href="{{ route('admin.staffs.index') }}">
                    <span class="menu-icon menu-icon--sm">
                        <i class="bi bi-person-check"></i>
                    </span>
                    <span class="menu-title">{{ __('Staff') }}</span>
                </a>
            </div>
        </div>
    </div>
@endcan

@can('Support Management')
    <div data-kt-menu-trigger="click" data-imas-menu-item data-imas-menu-title="{{ __('Support Hub') }}"
         class="menu-item menu-accordion {{ isset($active['support']) ? 'show hover' : '' }}">
        <span class="menu-link">
            <span class="menu-icon">
                <i class="bi bi-headset"></i>
            </span>
            <span class="menu-title">{{ __('Support Hub') }}</span>
            <span class="menu-arrow"></span>
        </span>

        <div class="menu-sub menu-sub-accordion {{ isset($active['contact_forms']) ? 'show' : '' }}">
            <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Leads') }}">
                <a class="menu-link {{ isset($active['contact_forms']) ? 'active' : '' }}"
                   href="{{ route('admin.contact_forms.index') }}">
                    <span class="menu-icon menu-icon--sm">
                        <i class="bi bi-inbox"></i>
                    </span>
                    <span class="menu-title">{{ __('Leads') }}</span>
                </a>
            </div>
        </div>
        <div class="menu-sub menu-sub-accordion {{ isset($active['subscribers']) ? 'show' : '' }}">
            <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Newsletter Subscribers') }}">
                <a class="menu-link {{ isset($active['subscribers']) ? 'active' : '' }}"
                   href="{{ route('admin.subscribers.index') }}">
                    <span class="menu-icon menu-icon--sm">
                        <i class="bi bi-envelope-open"></i>
                    </span>
                    <span class="menu-title">{{ __('Newsletter Subscribers') }}</span>
                </a>
            </div>
        </div>
        @can('Hr Management')
            <div class="menu-sub menu-sub-accordion {{ isset($active['users']) ? 'show' : '' }}">
                <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Users') }}">
                    <a class="menu-link {{ isset($active['users']) ? 'active' : '' }}"
                       href="{{ route('admin.users.index') }}">
                        <span class="menu-icon menu-icon--sm">
                            <i class="bi bi-people"></i>
                        </span>
                        <span class="menu-title">{{ __('Users') }}</span>
                    </a>
                </div>
            </div>
        @endcan
    </div>
@endcan

@can('Settings Management')
    <div data-kt-menu-trigger="click" data-imas-menu-item data-imas-menu-title="{{ __('Settings') }}"
         class="menu-item menu-accordion {{ isset($active['settings']) ? 'show hover' : '' }}">
        <span class="menu-link">
            <span class="menu-icon">
                <i class="bi bi-gear-fill"></i>
            </span>
            <span class="menu-title">{{ __('Settings') }}</span>
            <span class="menu-arrow"></span>
        </span>

        <div class="menu-sub menu-sub-accordion {{ isset($active['websiteConfigurations']) ? 'show' : '' }}">
            <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Website Configurations') }}">
                <a class="menu-link {{ isset($active['websiteConfigurations']) ? 'active' : '' }}"
                   href="{{ route('admin.settings.index') }}">
                    <span class="menu-icon menu-icon--sm">
                        <i class="bi bi-globe2"></i>
                    </span>
                    <span class="menu-title">{{ __('Website Configurations') }}</span>
                </a>
            </div>
        </div>

        <div class="menu-sub menu-sub-accordion {{ isset($active['seo']) ? 'show' : '' }}">
            <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Seo Configurations') }}">
                <a class="menu-link {{ isset($active['seo']) ? 'active' : '' }}"
                   href="{{ route('admin.seo.index') }}">
                    <span class="menu-icon menu-icon--sm">
                        <i class="bi bi-search"></i>
                    </span>
                    <span class="menu-title">{{ __('Seo Configurations') }}</span>
                </a>
            </div>
        </div>

        @can('Logs Management')
            <div class="menu-sub menu-sub-accordion {{ isset($active['logs']) ? 'show' : '' }}">
                <div class="menu-item" data-imas-menu-item data-imas-menu-title="{{ __('Logs & Bugs') }}">
                    <a class="menu-link {{ isset($active['logs']) ? 'active' : '' }}"
                       href="{{ route('admin.logs.index') }}">
                        <span class="menu-icon menu-icon--sm">
                            <i class="bi bi-bug"></i>
                        </span>
                        <span class="menu-title">{{ __('Logs & Bugs') }}</span>
                    </a>
                </div>
            </div>
        @endcan
    </div>
@endcan
