@php
    // Auto breadcrumbs: derive [Group, Page] from the current route name.
    $routeName = Route::currentRouteName();

    $crumbMap = [
        'dashboard'            => ['Dashboard', null],
        'my_account'           => ['My Account', null],

        // Students
        'students.create'      => ['Students', 'Admit New Student'],
        'students.list'        => ['Students', 'Student List'],
        'students.show'        => ['Students', 'Student Profile'],
        'students.edit'        => ['Students', 'Edit Student'],
        'students.graduated'   => ['Students', 'Graduated Students'],
        'students.promotion_manage' => ['Students', 'Promotions'],

        // Academics
        'classes.index'        => ['Academics', 'Classes'],
        'sections.index'       => ['Academics', 'Sections'],
        'subjects.index'       => ['Academics', 'Subjects'],
        'grades.index'         => ['Academics', 'Grading System'],
        'exams.index'          => ['Academics', 'Exams'],
        'marks.index'          => ['Academics', 'Marks Entry'],
        'tt.index'             => ['Academics', 'Timetables'],

        // Finance
        'payments.create'      => ['Finance', 'Fee Setup'],
        'payments.index'       => ['Finance', 'Payments Overview'],
        'payments.manage'      => ['Finance', 'Record Payments'],
        'reports.index'        => ['Finance', 'Payment Reports'],
        'students.payments'    => ['Finance', 'All-Students Report'],

        // Administration
        'users.index'          => ['Administration', 'Staff & Users'],
        'dorms.index'          => ['Administration', 'Dormitories'],
        'settings'             => ['Administration', 'System Settings'],
    ];

    $crumbs = $crumbMap[$routeName] ?? null;
@endphp

<div id="page-header" class="page-header page-header-light">
    <div class="page-header-content header-elements-md-inline">
        <div class="page-title d-flex">
            <h4><span class="font-weight-semibold">@yield('page_title')</span></h4>
            <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
        </div>

        <div class="header-elements d-flex align-items-center">
            <span class="badge badge-light border text-muted mr-2" title="Current academic session">
                <i class="icon-calendar5 mr-1 opacity-60"></i>{{ Qs::getCurrentSession() }}
            </span>
        </div>
    </div>

    @if($crumbs)
        <div class="breadcrumb-line breadcrumb-line-light header-elements-md-inline">
            <div class="d-flex">
                <div class="breadcrumb" aria-label="Breadcrumb">
                    <a href="{{ route('dashboard') }}" class="breadcrumb-item"><i class="icon-home4 mr-1"></i> Home</a>
                    <a href="#" class="breadcrumb-item" onclick="return false;">{{ $crumbs[0] }}</a>
                    <span class="breadcrumb-item active" aria-current="page">{{ $crumbs[1] }}</span>
                </div>

                <a href="#" class="header-elements-toggle text-default d-md-none"><i class="icon-more"></i></a>
            </div>
        </div>
    @endif
</div>
