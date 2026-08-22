<?php

/**
 * System Module Registry
 * ----------------------
 * Single source of truth for the application's toggleable modules.
 *
 * - Metadata lives in code (this file) so definitions never drift from the codebase.
 * - The enable/disable STATE is persisted in the `settings` table (type: "disabled_modules",
 *   JSON array of slugs) via App\Helpers\Qm, so it survives restarts/logins.
 * - `routes` lists route-NAME prefixes belonging to the module. The EnsureModuleEnabled
 *   middleware matches the current route name against these prefixes to protect access,
 *   and the sidebar hides nav items whose module is disabled.
 *
 * Fields:
 *  - slug        stable internal identifier used for logic (never display names)
 *  - name        display name
 *  - description short explanation shown on the module card
 *  - icon        icomoon icon class
 *  - category    grouping shown in the management grid
 *  - routes      route-name prefixes owned by this module
 *  - depends_on  slugs this module requires to function
 *  - required    true = fundamental to the system, cannot be disabled
 */
return [

    'students' => [
        'name'        => 'Students',
        'description' => 'Student registration, profiles, guardians and student records.',
        'icon'        => 'icon-users4',
        'category'    => 'Core School Management',
        'routes'      => ['students.create', 'students.list', 'students.show', 'students.edit',
                          'students.update', 'students.destroy', 'students.store', 'students.index',
                          'students.graduated', 'st.not_graduated', 'st.reset_pass',
                          'students.import', 'students.import.progress'],
        'depends_on'  => [],
        'required'    => false,
    ],

    'classes' => [
        'name'        => 'Classes & Sections',
        'description' => 'Manage school classes, streams/sections and class types.',
        'icon'        => 'icon-office',
        'category'    => 'Core School Management',
        'routes'      => ['classes.', 'sections.', 'get_class_sections'],
        'depends_on'  => [],
        'required'    => false,
    ],

    'subjects' => [
        'name'        => 'Subjects',
        'description' => 'Subjects offered per class and teacher allocations.',
        'icon'        => 'icon-books',
        'category'    => 'Core School Management',
        'routes'      => ['subjects.', 'get_class_subjects'],
        'depends_on'  => ['classes'],
        'required'    => false,
    ],

    'users' => [
        'name'        => 'Users & Staff',
        'description' => 'Staff accounts, roles and user administration.',
        'icon'        => 'icon-user-tie',
        'category'    => 'Administration',
        'routes'      => ['users.', 'ajax.search'],
        'depends_on'  => [],
        'required'    => true, // fundamental: authentication & staff management
    ],

    'examinations' => [
        'name'        => 'Examinations',
        'description' => 'Exams, marks entry, grading scales and result computation.',
        'icon'        => 'icon-file-text2',
        'category'    => 'Academic',
        'routes'      => ['exams.', 'marks.', 'grades.', 'examIsLocked'],
        'depends_on'  => ['students', 'classes'],
        'required'    => false,
    ],

    'promotions' => [
        'name'        => 'Promotions',
        'description' => 'End-of-year promotion of students between classes.',
        'icon'        => 'icon-stairs',
        'category'    => 'Academic',
        'routes'      => ['students.promotion_manage', 'students.promotion_reset', 'students.promotion_reset_all',
                          'students.promotion', 'students.promote_selector', 'students.promote'],
        'depends_on'  => ['students', 'classes'],
        'required'    => false,
    ],

    'timetables' => [
        'name'        => 'Timetables',
        'description' => 'Class timetables, time slots and printable schedules.',
        'icon'        => 'icon-calendar5',
        'category'    => 'Academic',
        'routes'      => ['tt.', 'ttr.', 'ts.'],
        'depends_on'  => ['classes', 'subjects'],
        'required'    => false,
    ],

    'pins' => [
        'name'        => 'Result Check Pins',
        'description' => 'PINs and serial codes students use to check results online.',
        'icon'        => 'icon-key',
        'category'    => 'Academic',
        'routes'      => ['pins.'],
        'depends_on'  => ['examinations'],
        'required'    => false,
    ],

    'finance' => [
        'name'        => 'Finance & Fees',
        'description' => 'Fee structures, payment recording, invoices and receipts.',
        'icon'        => 'icon-cash2',
        'category'    => 'Finance',
        'routes'      => ['payments.', 'students.payments'],
        'depends_on'  => ['students'],
        'required'    => false,
    ],

    'reports' => [
        'name'        => 'Reports',
        'description' => 'Financial and academic reports for decision making.',
        'icon'        => 'icon-statistics',
        'category'    => 'Finance',
        'routes'      => ['reports.index', 'reports.show', 'reports.create', 'reports.store',
                          'reports.edit', 'reports.update', 'reports.destroy', 'reports.payments'],
        'depends_on'  => ['finance'],
        'required'    => false,
    ],

    'dormitories' => [
        'name'        => 'Dormitories',
        'description' => 'Boarding houses, rooms and student accommodation.',
        'icon'        => 'icon-home2',
        'category'    => 'Boarding',
        'routes'      => ['dorms.'],
        'depends_on'  => ['students'],
        'required'    => false,
    ],
];
