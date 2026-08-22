You are working on an existing **Laravel School Management System**.

Your role is to act as a **Senior Laravel Architect, Senior Software Engineer, Product Designer, and UX Engineer**.

Your objective is to thoroughly understand the existing application and then improve its interface so that it feels like a **professional, modern, production-grade School Management Information System (SMIS)**.

You are allowed to be innovative. However, improvements must be grounded in the actual application, its users, workflows, architecture, and existing functionality.

---

# CRITICAL WORKING RULE

**Do not start writing UI code immediately.**

First understand the existing codebase, architecture, technology stack, modules, workflows, and design patterns.

However, **do not stop after the audit and wait for my instruction**.

Once the audit is complete, continue autonomously with the implementation.

The workflow should be:

**Understand → Plan → Create Git Branch → Implement → Test → Review Your Own Work → Report**

---

# PHASE 0 — CODEBASE DISCOVERY

Before modifying application code, thoroughly inspect the repository.

Determine:

### Technology Stack

Identify the actual:

* Laravel version
* PHP version
* Database
* Frontend technology
* CSS framework
* JavaScript libraries/frameworks
* Build tooling
* Authentication
* Authorization/permissions
* Testing framework
* Package manager
* Third-party integrations
* APIs
* Queues/jobs
* Caching
* Storage
* Other significant dependencies

Inspect relevant files including:

* `composer.json`
* `package.json`
* `vite.config.*`
* `.env.example`
* Routes
* Controllers
* Models
* Migrations
* Middleware
* Policies
* Form Requests
* Services
* Blade templates/components
* CSS
* JavaScript
* Tests
* Configuration

**Never expose secrets from `.env`.**

---

# PHASE 1 — UNDERSTAND THE ARCHITECTURE

Understand how the application is actually structured.

Determine:

* MVC architecture
* Application layers
* Domain/module organization
* Model relationships
* Controller responsibilities
* Service/repository patterns
* Validation approach
* Authorization approach
* Frontend architecture
* Blade/component architecture
* Reusable UI components
* Database structure
* API architecture
* Integration patterns

Do not impose your preferred architecture on the project.

Work with the architecture that already exists unless there is a compelling reason to improve it.

---

# PHASE 2 — UNDERSTAND THE SCHOOL SYSTEM

Build an inventory of the functionality that actually exists.

Identify modules such as:

* Students
* Parents/Guardians
* Teachers
* Staff
* Classes
* Sections
* Subjects
* Attendance
* Examinations
* Grades
* Report cards
* Fees
* Payments
* Admissions
* Timetables
* Communication
* Reports
* User management
* Roles and permissions
* School configuration

Do not assume that a module exists simply because it is common in school-management software.

Use the actual codebase as the source of truth.

---

# PHASE 3 — UNDERSTAND USER ROLES

Identify the actual roles and permissions.

Determine what each role needs to accomplish and which parts of the system they interact with.

For example:

* Administrator
* School administrator
* Teacher
* Accountant/Bursar
* Parent/Guardian
* Student
* Other roles implemented by the application

Do not hard-code permissions into the UI.

Respect the existing authorization system.

---

# PHASE 4 — AUDIT THE EXISTING UI/UX

Inspect the actual interface.

Review:

* Login
* Dashboard
* Sidebar
* Header
* Navigation
* Student management
* Student profiles
* Forms
* Tables
* Search
* Filters
* Modals
* Notifications
* Reports
* Settings
* User management
* Responsive behaviour

Identify:

* Inconsistencies
* Clutter
* Poor navigation
* Weak information hierarchy
* Poor forms
* Poor tables
* Redundant UI
* Outdated visual patterns
* Missing feedback
* Poor empty states
* Poor mobile experience
* Accessibility problems
* Opportunities for useful innovation

---

# PHASE 5 — CREATE A SAFE GIT WORKING BRANCH

Before implementing changes, check the current Git state.

Determine:

* Current branch
* Whether there are uncommitted changes
* Remote configuration
* Existing branch naming conventions

**Do not overwrite or discard existing user work.**

If the working branch is `develop`, create a dedicated feature branch from the current `develop` state.

Use a meaningful branch name such as:

`feature/school-management-ui-ux`

or another appropriate name based on the repository's existing conventions.

All redesign work must happen on this feature branch.

### Important

**NEVER directly modify `develop` for this task.**

The purpose of the branch is to allow me to:

1. Review the implementation
2. Run the application
3. Test the changes
4. Request further modifications
5. Approve the work
6. Merge the branch into `develop`

Do not merge the feature branch into `develop`.

Do not delete the branch.

---

# PHASE 6 — PLAN THE REDESIGN

Based on the actual codebase audit, establish a coherent UI/UX direction.

The application should feel like a **professional commercial School Management System**, not a generic Laravel CRUD application.

Aim for:

* Professional
* Modern
* Clean
* Intuitive
* Efficient
* Consistent
* Accessible
* Responsive
* Data-focused
* Trustworthy

Do not simply change colors or apply a template.

Improve the actual user experience.

---

# PHASE 7 — ESTABLISH A CONSISTENT DESIGN SYSTEM

Where appropriate, standardize:

* Typography
* Spacing
* Colors
* Buttons
* Forms
* Tables
* Cards
* Badges
* Alerts
* Modals
* Icons
* Page headers
* Breadcrumbs
* Navigation
* Status indicators
* Empty states
* Loading states

Prioritize reuse.

If reusable components already exist, improve and extend them instead of creating duplicate implementations.

Avoid excessive cards, gradients, animations, shadows, or decorative UI.

The interface should look intentional and professional.

---

# PHASE 8 — IMPROVE THE MAIN APPLICATION EXPERIENCE

Improve the core application shell:

### Navigation

Create a logical structure for school operations.

Use:

* Clear sidebar navigation
* Logical grouping
* Active states
* Collapsible sections where appropriate
* Breadcrumbs where useful
* Search where useful
* User profile
* Notifications
* Responsive navigation

### Dashboard

Transform the dashboard from a basic statistics page into a useful school-management command center.

Where the underlying data exists, consider:

* Total students
* Attendance
* New admissions
* Fee collection
* Outstanding fees
* Upcoming examinations
* Recent activities
* Academic performance
* Alerts
* Trends
* Quick actions

Do not create fake data simply to make the dashboard look impressive.

---

# PHASE 9 — IMPROVE DATA MANAGEMENT

School systems contain large amounts of data.

Improve tables with:

* Search
* Filtering
* Sorting
* Pagination
* Bulk actions where useful
* Clear status indicators
* Appropriate row actions
* Good empty states
* Responsive behaviour
* Export functionality where already supported or clearly appropriate

Avoid displaying excessive information in a single table.

---

# PHASE 10 — IMPROVE STUDENT EXPERIENCE

Where student functionality exists, make the student record a strong central experience.

Consider a 360-degree student profile containing appropriate existing information such as:

* Personal details
* Guardian information
* Academic information
* Attendance
* Fees
* Assessments
* Discipline
* Documents
* Activity/history

Only expose information that actually exists in the system.

---

# PHASE 11 — IMPROVE FORMS

Audit important forms.

Improve:

* Field grouping
* Layout
* Labels
* Validation
* Error messages
* Required indicators
* Input types
* Dropdowns
* Date fields
* Save/cancel actions
* Loading states

Large forms should be organized into logical sections.

Where genuinely useful, introduce:

* Multi-step forms
* Wizards
* Better defaults
* Inline validation
* Draft states

Do not introduce complexity unnecessarily.

---

# PHASE 12 — IMPROVE FEEDBACK

Ensure users receive clear feedback for actions.

Improve:

* Success messages
* Error messages
* Warnings
* Confirmation dialogs
* Loading states
* Empty states
* Permission errors
* Validation errors

Destructive operations should require appropriate confirmation.

---

# PHASE 13 — RESPONSIVE DESIGN

Ensure important workflows work properly on:

* Desktop
* Laptop
* Tablet
* Mobile

Pay particular attention to:

* Tables
* Forms
* Navigation
* Dashboards
* Student profiles
* Modals

Do not merely shrink desktop layouts.

Adapt layouts appropriately for smaller screens.

---

# PHASE 14 — INNOVATION

Innovation is encouraged.

If you identify genuinely useful opportunities, implement them.

Potential examples include:

* Global search
* Quick student lookup
* Command palette
* Quick actions
* Recent records
* Notification center
* Activity timelines
* Academic-year/term switcher
* Better student profiles
* Contextual actions
* Bulk operations
* Keyboard shortcuts
* Improved report workflows

However:

**Do not add features just because they look impressive.**

Every innovation should improve an actual school workflow.

---

# PHASE 15 — PRESERVE EXISTING FUNCTIONALITY

This is an existing application.

Do not unnecessarily change:

* Routes
* Business logic
* Database relationships
* Permissions
* Validation rules
* Integrations
* Existing workflows

unless required to support the improvement.

Do not rewrite the application merely for the sake of modernization.

Prefer incremental, maintainable improvements.

---

# PHASE 16 — TEST EVERYTHING

After implementing changes:

Run the project's existing tests.

Also perform appropriate checks such as:

* Laravel application boot
* Route validation
* Frontend build
* JavaScript errors
* Blade rendering
* Authentication
* Authorization
* Forms
* Main workflows
* Responsive layouts

Fix issues you introduce.

Do not leave the branch in a broken state.

---

# PHASE 17 — SELF-REVIEW

Before considering the task complete, review your own work as if you were:

### A School Administrator

Can I quickly understand what is happening in the school?

### A Teacher

Can I quickly access my students, classes, attendance, and academic tasks?

### An Accountant

Can I efficiently manage fees and payments?

### A Parent

Can I easily understand information about my child?

### A Product Designer

Is the interface consistent, clear, and intuitive?

### A Senior Engineer

Is the implementation maintainable and consistent with the existing Laravel architecture?

Fix obvious problems discovered during this review.

---

# PHASE 18 — GIT COMMITS

Make logical, meaningful commits rather than one enormous commit.

For example:

* `refactor: standardize application layout`
* `feat: improve school dashboard`
* `feat: redesign student management interface`
* `feat: improve forms and data tables`
* `fix: improve responsive navigation`

Use commit messages appropriate to the actual work performed.

Do not commit secrets, `.env` files, generated credentials, or unrelated files.

---

# PHASE 19 — FINAL REPORT

When the implementation is complete, provide a concise report containing:

### Architecture discovered

Summarize the technology stack and architecture.

### UI/UX problems identified

Summarize the major problems found.

### Improvements implemented

List the major improvements.

### New innovations

Explain any genuinely new UX improvements introduced.

### Files/components changed

Summarize the important areas changed.

### Testing performed

Explain what was tested and the results.

### Git branch

Clearly state the feature branch used.

### Commits

Summarize the major commits.

### Remaining recommendations

Mention improvements that were identified but intentionally not implemented.

---

# MOST IMPORTANT RULES

1. **Understand the codebase before writing code.**
2. **Do not wait for another instruction after the audit. Continue autonomously.**
3. **Create a dedicated Git feature branch before implementation.**
4. **Never directly modify or merge into `develop`.**
5. **Do not destroy or overwrite existing uncommitted work.**
6. **Preserve existing business logic and functionality.**
7. **Do not impose a new architecture without justification.**
8. **Use the actual codebase as the source of truth.**
9. **Innovation is encouraged when it improves real workflows.**
10. **Test your changes before finishing.**
11. **Self-review the complete interface before reporting completion.**
12. **Leave the feature branch ready for human review and eventual merge into `develop`.**

The ultimate objective is not simply to make the application "look nicer."

The objective is to make the existing Laravel application feel like a **well-designed, professional, modern School Management Information System that schools could confidently use in day-to-day operations.**
