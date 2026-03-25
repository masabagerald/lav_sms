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
                            <img src="{{ Auth::user()->photo }}" width="38" height="38" class="rounded-circle" alt="photo">
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
                        <a href="{{ route('my_account') }}" class="text-white">
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

                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link {{ (Route::is('dashboard')) ? 'active' : '' }}">
                        <i class="icon-home4"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                {{-- Administrative --}}
                @if(Qs::userIsAdministrative())
                    <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['payments.index','payments.create','payments.manage']) ? 'nav-item-expanded nav-item-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="icon-office"></i>
                            <span>Administrative</span>
                        </a>

                        <ul class="nav nav-group-sub">
                            @if(Qs::userIsTeamAccount())
                                <li class="nav-item">
                                    <a href="{{ route('payments.create') }}" class="nav-link {{ Route::is('payments.create') ? 'active' : '' }}">
                                        Create Payment
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('payments.index') }}" class="nav-link">
                                        Manage Payments
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="{{ route('payments.manage') }}" class="nav-link">
                                        Student Payments
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Students --}}
                @if(Qs::userIsTeamSAT())
                    <li class="nav-item nav-item-submenu {{ in_array(Route::currentRouteName(), ['students.list','students.create']) ? 'nav-item-expanded nav-item-open' : '' }}">
                        <a href="#" class="nav-link">
                            <i class="icon-users"></i>
                            <span>Students</span>
                        </a>

                        <ul class="nav nav-group-sub">

                            @if(Qs::userIsTeamSA())
                                <li class="nav-item">
                                    <a href="{{ route('students.create') }}" class="nav-link">
                                        Admit Student
                                    </a>
                                </li>
                            @endif

                            <li class="nav-item nav-item-submenu">
                                <a href="#" class="nav-link">Student Information</a>
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

                        </ul>
                    </li>
                @endif

                {{-- Dynamic menu --}}
                @include('pages.'.Qs::getUserType().'.menu')

                {{-- My Account --}}
                <li class="nav-item">
                    <a href="{{ route('my_account') }}" class="nav-link">
                        <i class="icon-user"></i>
                        <span>My Account</span>
                    </a>
                </li>

            </ul>

            {{-- 🔐 LICENSE PANEL --}}
            @php
                $license = app(\App\Services\LicenseService::class)->validate();
            @endphp

            <div class="px-3 mt-4 mb-3">
                <div class="card bg-dark border-0 shadow-sm">
                    <div class="card-body p-2 text-center">

                        @if($license['valid'])
                            @php
                                $data = $license['data'];
                                $daysLeft = now()->diffInDays($data['expires_at'], false);
                            @endphp

                            @if($daysLeft <= 7)
                                <span class="badge badge-warning mb-1">
                                    ⚠️ Expiring Soon
                                </span>
                                <div class="text-warning small">
                                    {{ $daysLeft }} day(s) left
                                </div>
                            @else
                                <span class="badge badge-success mb-1">
                                    Active License
                                </span>
                            @endif

                            <div class="text-light small mt-1">
                                {{ \Illuminate\Support\Str::limit($data['client'], 25) }}
                            </div>

                            <div class="text-muted small">
                                Expires: {{ \Carbon\Carbon::parse($data['expires_at'])->format('d M Y') }}
                            </div>

                        @else
                            <span class="badge badge-danger mb-1">
                                License Issue
                            </span>

                            <div class="text-danger small">
                                {{ $license['message'] }}
                            </div>

                            <a href="{{ route('license.upload') }}" class="btn btn-sm btn-outline-light mt-2">
                                Fix License
                            </a>
                        @endif

                    </div>
                </div>
            </div>
            {{-- END LICENSE PANEL --}}

        </div>
    </div>
</div>