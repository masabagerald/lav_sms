@extends('layouts.login_master')

@push('styles')
<style>
    .login-shell {
        display: flex;
        width: 100%;
        max-width: 980px;
        background: #fff;
        border-radius: 1.25rem;
        overflow: hidden;
        box-shadow: 0 30px 70px rgba(10, 30, 60, .45);
    }

    /* ---- Left branding panel ---- */
    .login-brand {
        flex: 0 0 55%;
        position: relative;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        padding: 2.5rem;
        color: #fff;
        background: linear-gradient(140deg, #16305c 0%, #1f4e8c 55%, #2d9cdb 130%);
    }

    .login-brand::before,
    .login-brand::after {
        content: "";
        position: absolute;
        border-radius: 50%;
        pointer-events: none;
    }

    .login-brand::before {
        width: 320px; height: 320px; top: -120px; right: -100px;
        background: rgba(255, 255, 255, .07);
    }

    .login-brand::after {
        width: 220px; height: 220px; bottom: -80px; left: -60px;
        background: rgba(255, 255, 255, .06);
    }

    .login-badge {
        display: inline-block;
        align-self: flex-start;
        padding: .35rem .9rem;
        border-radius: 2rem;
        font-size: .75rem;
        font-weight: 600;
        letter-spacing: .08em;
        text-transform: uppercase;
        background: rgba(255, 255, 255, .15);
        backdrop-filter: blur(2px);
    }

    .login-brand h1 {
        margin: 1.25rem 0 .75rem;
        font-size: 1.9rem;
        line-height: 1.25;
    }

    .login-brand-text {
        color: rgba(255, 255, 255, .85);
        max-width: 30rem;
    }

    .login-feature {
        position: relative;
        z-index: 1;
        display: flex;
        align-items: center;
        margin-top: 1.1rem;
    }

    .login-feature i {
        flex: 0 0 auto;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px; height: 38px;
        margin-right: .85rem;
        font-size: 1rem;
        border-radius: 50%;
        background: rgba(255, 255, 255, .15);
    }

    .login-feature strong { display: block; font-size: .95rem; }
    .login-feature span   { display: block; font-size: .8rem; color: rgba(255, 255, 255, .75); }

    .login-motto {
        position: relative;
        z-index: 1;
        margin-top: 2rem;
        padding-left: 1rem;
        font-style: italic;
        color: rgba(255, 255, 255, .75);
        border-left: 3px solid rgba(255, 255, 255, .4);
    }

    /* ---- Right form panel ---- */
    .login-form {
        flex: 1 1 45%;
        width: auto;
        min-width: 340px;
        margin: 0;
        padding: 3rem 2.5rem;
        background: #fff;
    }

    .login-avatar {
        width: 96px; height: 96px;
        object-fit: cover;
        border-radius: 50%;
    }

    .input-icon-group { position: relative; }

    .input-icon-group > i {
        position: absolute;
        top: 50%;
        left: .9rem;
        transform: translateY(-50%);
        color: #999;
        font-size: .95rem;
        pointer-events: none;
    }

    .input-icon-group > input {
        padding-left: 2.5rem !important;
        height: calc(2.6rem + 2px);
        border-radius: .45rem;
        background: #f8f9fa;
    }

    .input-icon-group > input:focus {
        background: #fff;
        box-shadow: 0 0 0 .2rem rgba(31, 78, 140, .12);
        border-color: #1f4e8c;
    }

    .btn-login {
        height: calc(2.6rem + 2px);
        border-radius: .45rem;
        background: linear-gradient(135deg, #16305c, #1f4e8c);
        border: none;
        letter-spacing: .02em;
    }

    .btn-login:hover  { filter: brightness(1.15); }
    .btn-login:active { transform: translateY(1px); }

    .password-toggle {
        position: absolute;
        top: 50%; right: .75rem;
        transform: translateY(-50%);
        border: none;
        background: transparent;
        color: #999;
        cursor: pointer;
        padding: 0;
    }

    .password-toggle:hover { color: #1f4e8c; }

    @media (max-width: 767.98px) {
        .login-shell   { border-radius: 1rem; }
        .login-form    { min-width: 0; padding: 2rem 1.5rem; }
    }
</style>
@endpush

@section('content')
    <div class="page-content login-cover">

        <!-- Main content -->
        <div class="content-wrapper">

            <!-- Content area -->
            <div class="content d-flex justify-content-center align-items-center py-4">

                <div class="login-shell">

                    <!-- Branding panel -->
                    <div class="login-brand d-none d-md-flex">
                        <div>
                            <span class="login-badge"><i class="icon-office mr-1"></i> School Management System</span>

                            <h1 class="font-weight-semibold">Welcome to<br>{{ Qs::getSystemName() }}</h1>
                            <p class="login-brand-text mb-0">
                                One smart platform to run your school — admissions, attendance,
                                results and fees, all in one place.
                            </p>

                            <div class="login-feature">
                                <i class="icon-users4"></i>
                                <div>
                                    <strong>Student &amp; Staff Records</strong>
                                    <span>Profiles, classes and attendance at a glance.</span>
                                </div>
                            </div>

                            <div class="login-feature">
                                <i class="icon-books"></i>
                                <div>
                                    <strong>Classes &amp; Subjects</strong>
                                    <span>Timetables and teacher allocations made easy.</span>
                                </div>
                            </div>

                            <div class="login-feature">
                                <i class="icon-statistics"></i>
                                <div>
                                    <strong>Exams &amp; Results</strong>
                                    <span>Marks entry, report cards and performance analytics.</span>
                                </div>
                            </div>

                            <div class="login-feature">
                                <i class="icon-cash2"></i>
                                <div>
                                    <strong>Fees &amp; Payments</strong>
                                    <span>Track balances and payment history in real time.</span>
                                </div>
                            </div>
                        </div>

                        <div class="login-motto">"Nurturing excellence, building futures."</div>
                    </div>
                    <!-- /branding panel -->

                    <!-- Login card -->
                    <form class="login-form" method="post" action="{{ route('login') }}">
                        @csrf

                        <div class="text-center mb-4">
                            <img src="{{ asset('global_assets/images/mubende_parents.jpg') }}"
                                 class="login-avatar border-warning-400 border-1 p-1"
                                 alt="School logo">
                            <h5 class="font-weight-semibold mt-3 mb-0">Welcome back</h5>
                            <span class="d-block text-muted">Sign in to your {{ Qs::getSystemName() }} account</span>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-styled-left alert-dismissible">
                                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                                <span class="font-weight-semibold">Oops!</span> {{ implode('<br>', $errors->all()) }}
                            </div>
                        @endif

                        <div class="form-group">
                            <div class="input-icon-group">
                                <i class="icon-user"></i>
                                <input type="text" class="form-control" name="identity" value="{{ old('identity') }}" placeholder="Login ID or Email" autocomplete="username" autofocus>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-icon-group">
                                <i class="icon-lock2"></i>
                                <input id="password-field" required name="password" type="password" class="form-control" placeholder="{{ __('Password') }}" autocomplete="current-password">
                                <button type="button" class="password-toggle" id="toggle-password" tabindex="-1" aria-label="Show password"><i class="icon-eye"></i></button>
                            </div>
                        </div>

                        <div class="form-group d-flex align-items-center">
                            <div class="form-check mb-0">
                                <label class="form-check-label">
                                    <input type="checkbox" name="remember" class="form-input-styled" {{ old('remember') ? 'checked' : '' }} data-fouc>
                                    Remember me
                                </label>
                            </div>

                            <a href="{{ route('password.request') }}" class="ml-auto">Forgot password?</a>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block btn-login font-weight-semibold">
                                Sign in to dashboard <i class="icon-circle-right2 ml-2"></i>
                            </button>
                        </div>

                        <p class="text-muted text-center mb-0 mt-4" style="font-size:.85rem;">
                            Trouble signing in? Contact the school administration office.
                        </p>
                    </form>
                    <!-- /login card -->

                </div>

            </div>
            <!-- /content area -->

        </div>
        <!-- /main content -->

    </div>

    <script>
        document.getElementById('toggle-password').addEventListener('click', function () {
            var field = document.getElementById('password-field');
            var icon = this.querySelector('i');
            var show = field.type === 'password';
            field.type = show ? 'text' : 'password';
            icon.className = show ? 'icon-eye-blocked' : 'icon-eye';
            this.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        });
    </script>
@endsection
