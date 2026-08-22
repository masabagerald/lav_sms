<div class="sidebar sidebar-dark sidebar-main sidebar-expand-md">

    <!-- Sidebar mobile toggler -->
    <div class="sidebar-mobile-toggler text-center">
        <a href="#" class="sidebar-mobile-main-toggle">
            <i class="icon-arrow-left8"></i>
        </a>
        Navigation
        <a href="#" class="sidebar-mobile-expand">
            <i class="icon-screen-full"></i>
            <i class="icon-screen-normal"></i>
        </a>
    </div>
    <!-- /sidebar mobile toggler -->

    <div class="sidebar-content">

        <!-- User menu -->
        <div class="sidebar-user">
            <div class="card-body">
                <div class="media">
                    <div class="mr-3">
                        <a href="{{ route('my_account') }}">
                            @include('partials.user_avatar', ['size' => 38])
                        </a>
                    </div>

                    <div class="media-body">
                        <div class="media-title font-weight-semibold">{{ Auth::user()->name }}</div>
                        <div class="font-size-xs opacity-50">
                            <i class="icon-user font-size-sm"></i>
                            {{ ucwords(str_replace('_', ' ', Auth::user()->user_type)) }}
                        </div>
                    </div>

                    <div class="ml-3 align-self-center">
                        <a href="{{ route('my_account') }}" class="text-white" aria-label="My account settings">
                            <i class="icon-cog3"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <!-- /user menu -->

        <!-- Navigation -->
        <div class="card card-sidebar-mobile">
            <ul class="nav nav-sidebar" data-nav-type="accordion">

                {{-- ============ Overview ============ --}}
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ Route::is('dashboard') ? 'active' : '' }}">
                        <i class="icon-home4"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                {{-- ============ Students ============ --}}
                @if(Qs::userIsTeamSAT() && (Qm::enabled('students') || Qm::enabled('promotions')))
                    <li class="nav-caption">Students</li>

                    @if(Qs::userIsTeamSA() && Qm::enabled('students'))
                        <li class="nav-item">
                            <a href="{{ route('students.create') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['students.create']) ? 'active' : '' }}">
                                <i class="icon-plus2"></i>
                                <span>Admit New Student</span>
                            </a>
                        </li>
                    @endif

                    @if(Qm::enabled('students'))
                    <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['students.list', 'students.show', 'students.edit', 'students.import']) ? 'nav-item-expanded nav-item-open' : '' }}">
                        <a href="#" class="nav-link {{ Route::is('students.list') ? 'active' : '' }}">
                            <i class="icon-users4"></i>
                            <span>All Students</span>
                        </a>
                        <ul class="nav nav-group-sub">
                            @foreach(App\Models\MyClass::orderBy('name')->get() as $c)
                                <li class="nav-item">
                                    <a href="{{ route('students.list', $c->id) }}" class="nav-link">
                                        {{ $c->name }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </li>
                    @endif

                    @if(Qs::userIsTeamSA() && Qm::enabled('students'))
                        <li class="nav-item">
                            <a href="{{ route('students.graduated') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['students.graduated']) ? 'active' : '' }}">
                                <i class="icon-switch2"></i>
                                <span>Graduated Students</span>
                            </a>
                        </li>
                    @endif

                    @if(Qs::userIsTeamSA() && Qm::enabled('promotions'))
                        <li class="nav-item">
                            <a href="{{ route('students.promotion_manage') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['students.promotion_manage', 'students.promotion']) ? 'active' : '' }}">
                                <i class="icon-stairs"></i>
                                <span>Promotions</span>
                            </a>
                        </li>
                    @endif
                @endif

                {{-- ============ Academics ============ --}}
                @if(Qs::userIsTeamSAT() && (Qm::enabled('classes') || Qm::enabled('subjects') || Qm::enabled('examinations') || Qm::enabled('timetables')))
                    <li class="nav-caption">Academics</li>

                    @if(Qs::userIsTeamSA())
                        @if(Qm::enabled('classes'))
                            <li class="nav-item">
                                <a href="{{ route('classes.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['classes.index', 'classes.edit']) ? 'active' : '' }}">
                                    <i class="icon-office"></i>
                                    <span>Classes</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('sections.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['sections.index', 'sections.edit']) ? 'active' : '' }}">
                                    <i class="icon-menu9"></i>
                                    <span>Sections</span>
                                </a>
                            </li>
                        @endif
                        @if(Qm::enabled('subjects'))
                            <li class="nav-item">
                                <a href="{{ route('subjects.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['subjects.index', 'subjects.edit']) ? 'active' : '' }}">
                                    <i class="icon-books"></i>
                                    <span>Subjects</span>
                                </a>
                            </li>
                        @endif
                        @if(Qm::enabled('examinations'))
                            <li class="nav-item">
                                <a href="{{ route('grades.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['grades.index', 'grades.edit']) ? 'active' : '' }}">
                                    <i class="icon-check"></i>
                                    <span>Grading System</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('exams.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['exams.index', 'exams.edit']) ? 'active' : '' }}">
                                    <i class="icon-file-text2"></i>
                                    <span>Exams</span>
                                </a>
                            </li>
                        @endif
                    @endif

                    @if(Qm::enabled('examinations'))
                        <li class="nav-item">
                            <a href="{{ route('marks.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['marks.index', 'marks.manage', 'marks.bulk']) ? 'active' : '' }}">
                                <i class="icon-pencil"></i>
                                <span>Marks Entry</span>
                            </a>
                        </li>
                    @endif

                    @if(Qm::enabled('timetables'))
                        <li class="nav-item">
                            <a href="{{ route('tt.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['tt.index', 'ttr.manage', 'ttr.show']) ? 'active' : '' }}">
                                <i class="icon-calendar5"></i>
                                <span>Timetables</span>
                            </a>
                        </li>
                    @endif
                @endif

                {{-- ============ Finance ============ --}}
                @if(Qs::userIsTeamAccount() && (Qm::enabled('finance') || Qm::enabled('reports')))
                    <li class="nav-caption">Finance</li>

                    @if(Qm::enabled('finance'))
                        <li class="nav-item">
                            <a href="{{ route('payments.create') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['payments.create']) ? 'active' : '' }}">
                                <i class="icon-plus2"></i>
                                <span>Fee Setup</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('payments.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['payments.index', 'payments.show']) ? 'active' : '' }}">
                                <i class="icon-cash2"></i>
                                <span>Payments Overview</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('payments.manage') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['payments.manage', 'payments.invoice', 'payments.receipts']) ? 'active' : '' }}">
                                <i class="icon-calculator"></i>
                                <span>Record Payments</span>
                            </a>
                        </li>
                    @endif

                    @if(Qm::enabled('reports'))
                        <li class="nav-item">
                            <a href="{{ route('reports.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['reports.index']) ? 'active' : '' }}">
                                <i class="icon-statistics"></i>
                                <span>Payment Reports</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="{{ route('students.payments') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['students.payments']) ? 'active' : '' }}">
                                <i class="icon-file-text2"></i>
                                <span>All-Students Report</span>
                            </a>
                        </li>
                    @endif
                @endif

                {{-- ============ Administration ============ --}}
                @if(Qs::userIsTeamSA())
                    <li class="nav-caption">Administration</li>

                    <li class="nav-item">
                        <a href="{{ route('users.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['users.index', 'users.edit', 'users.show']) ? 'active' : '' }}">
                            <i class="icon-user-tie"></i>
                            <span>Staff &amp; Users</span>
                        </a>
                    </li>
                    @if(Qm::enabled('dormitories'))
                        <li class="nav-item">
                            <a href="{{ route('dorms.index') }}" class="nav-link {{ in_array(Route::currentRouteName(), ['dorms.index', 'dorms.edit']) ? 'active' : '' }}">
                                <i class="icon-home2"></i>
                                <span>Dormitories</span>
                            </a>
                        </li>
                    @endif
                    @if(Qs::userIsSuperAdmin())
                        <li class="nav-item">
                            <a href="{{ route('settings') }}" class="nav-link {{ Route::is('settings') ? 'active' : '' }}">
                                <i class="icon-cog5"></i>
                                <span>System Settings</span>
                            </a>
                        </li>
                    @endif
                @endif

                {{-- Role-specific links (student marksheet, parent children...) --}}
                @include('pages.'.Qs::getUserType().'.menu')

                {{-- ============ Personal ============ --}}
                <li class="nav-item mt-2">
                    <a href="{{ route('my_account') }}" class="nav-link">
                        <i class="icon-user"></i>
                        <span>My Account</span>
                    </a>
                </li>

            </ul>

            {{-- License status (cached to avoid re-validating on every render) --}}
            @php
                $license = cache()->remember('license.sidebar', now()->addMinutes(10), function () {
                    return app(\App\Services\LicenseService::class)->validate();
                });
            @endphp

            <div class="px-3 mt-4 mb-3">
                <div class="card bg-dark border-0 shadow-sm">
                    <div class="card-body p-2 text-center">

                        @if($license['valid'])
                            @php $daysLeft = now()->diffInDays($license['data']['expires_at'], false); @endphp

                            @if($daysLeft <= 7)
                                <span class="badge badge-warning mb-1">Expiring Soon</span>
                                <div class="text-warning small">{{ $daysLeft }} day(s) left</div>
                            @else
                                <span class="badge badge-success mb-1">Licensed</span>
                            @endif

                            <div class="text-muted small mt-1">
                                Expires: {{ \Carbon\Carbon::parse($license['data']['expires_at'])->format('d M Y') }}
                            </div>
                        @else
                            <span class="badge badge-danger mb-1">License Issue</span>
                            <div class="text-danger small">{{ \Illuminate\Support\Str::limit($license['message'], 60) }}</div>
                            <a href="{{ route('license.upload') }}" class="btn btn-sm btn-outline-light mt-2">Fix License</a>
                        @endif

                    </div>
                </div>
            </div>
            {{-- /license --}}

        </div>
    </div>
</div>
