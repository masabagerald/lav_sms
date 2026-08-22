@extends('layouts.master')
@section('page_title', 'System Settings')

@push('styles')
<style>
    /* ---- Micro labels (shared with admission form styling) ---- */
    .ctl-label {
        display: block; font-size: .72rem; font-weight: 600;
        text-transform: uppercase; letter-spacing: .05em;
        color: #6c757d; margin-bottom: .35rem;
    }

    /* ---- Console header ---- */
    .settings-tabs .nav-link { padding: .8rem 1.2rem; font-weight: 500; }
    .settings-tabs .nav-link i { margin-right: .45rem; font-size: .95rem; }

    /* ---- Field layout ---- */
    .set-card {
        border: 1px solid #eef0f4; border-radius: .5rem;
        padding: 1rem 0; height: 100%;
    }
    .set-section-title { font-size: .78rem; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #8895a7; }

    /* ---- Module cards ---- */
    .module-card {
        border: 1px solid #e8ebf0; border-radius: .55rem; background: #fff;
        transition: box-shadow .15s ease, border-color .15s ease;
        display: flex; flex-direction: column; height: 100%;
    }
    .module-card:hover { box-shadow: 0 3px 14px rgba(15,40,80,.08); }
    .module-card.is-disabled { background: #fafbfc; }
    .module-card.is-disabled .module-icon { opacity: .45; }
    .mc-head { display: flex; align-items: flex-start; gap: .75rem; }
    .module-icon {
        flex: 0 0 auto; width: 42px; height: 42px; border-radius: .45rem;
        display: flex; align-items: center; justify-content: center;
        background: #eef4fd; color: #1f4e8c; font-size: 1.05rem;
    }
    .is-disabled .module-icon { background: #eceff1; color: #90a4ae; }
    .mc-name { font-weight: 600; font-size: .95rem; line-height: 1.2; }
    .mc-desc { font-size: .78rem; color: #7b879b; margin-top: .25rem; }
    .mc-meta { font-size: .72rem; color: #9aa7b8; }
    .status-badge {
        display: inline-flex; align-items: center; gap: .35rem;
        font-size: .68rem; font-weight: 700; letter-spacing: .04em;
        text-transform: uppercase; padding: .18rem .55rem; border-radius: 1rem;
    }
    .status-badge .dot { width: .45rem; height: .45rem; border-radius: 50%; }
    .st-enabled  { background: #e8f5e9; color: #2e7d32; }
    .st-enabled .dot { background: #43a047; }
    .st-disabled { background: #efebe9; color: #6d4c41; }
    .st-disabled .dot { background: #8d6e63; }
    .st-required { background: #e3f2fd; color: #1565c0; }

    /* ---- Module on/off switch ---- */
    .mod-switch {
        position: relative; display: inline-block; width: 42px; height: 23px;
        flex: 0 0 auto; margin-left: .5rem;
    }
    .mod-switch input {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        opacity: 0; margin: 0; cursor: pointer; z-index: 2;
    }
    .mod-slider {
        position: absolute; top: 0; left: 0; right: 0; bottom: 0;
        background: #b9c4ce; border-radius: 999px; pointer-events: none;
        transition: background .18s ease;
    }
    .mod-slider::before {
        content: ""; position: absolute; width: 17px; height: 17px; left: 3px; top: 3px;
        border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,.3);
        transition: transform .18s ease;
    }
    .mod-switch input:checked + .mod-slider { background: #43a047; }
    .mod-switch input:checked + .mod-slider::before { transform: translateX(19px); }
    .mod-switch:hover .mod-slider { box-shadow: 0 0 0 3px rgba(30,136,229,.12); }
    .mod-switch input:focus-visible + .mod-slider { box-shadow: 0 0 0 3px rgba(30,136,229,.28); }
    .dep-note { font-size: .72rem; color: #9aa7b8; }
    .dep-note b { color: #7b879b; font-weight: 600; }

    /* ---- Activity feed ---- */
    .act-item { display: flex; gap: .65rem; padding: .55rem 0; border-bottom: 1px dashed #eef0f4; }
    .act-item:last-child { border-bottom: none; }
    .act-dot { flex: 0 0 auto; width: 30px; height: 30px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: .8rem; }
    .act-on  { background: #e8f5e9; color: #2e7d32; }
    .act-off { background: #efebe9; color: #6d4c41; }
    .act-time { font-size: .68rem; color: #9aa7b8; }
</style>
@endpush

@section('content')

{{-- ===================== Console header ===================== --}}
<div class="mb-3">
    <h5 class="mb-1 font-weight-semibold"><i class="icon-cog5 mr-2 text-primary"></i>System Settings</h5>
    <span class="text-muted">Administration console — school profile, academic calendar, finance and module management.</span>
</div>

<div class="card">
    <div class="card-header bg-white header-elements-inline pt-3 pb-0" style="border-bottom: none;">
        <ul class="nav nav-tabs nav-tabs-highlight card-header-tabs settings-tabs w-100" role="tablist">
            <li class="nav-item"><a href="#tab-school" class="nav-link active" data-toggle="tab"><i class="icon-office"></i>School Information</a></li>
            <li class="nav-item"><a href="#tab-academic" class="nav-link" data-toggle="tab"><i class="icon-calendar52"></i>Academic</a></li>
            <li class="nav-item"><a href="#tab-finance" class="nav-link" data-toggle="tab"><i class="icon-cash2"></i>Finance</a></li>
            @if(Qs::userIsSuperAdmin())
                <li class="nav-item"><a href="#tab-modules" class="nav-link" data-toggle="tab"><i class="icon-grid7"></i>Modules <span class="badge badge-light border ml-1">{{ $modules->count() }}</span></a></li>
            @endif
        </ul>
    </div>

    <div class="card-body pt-3">

        {{-- ============ General / Academic / Finance share one form ============ --}}
        <form enctype="multipart/form-data" method="post" action="{{ route('settings.update') }}">
            @csrf @method('PUT')
            <div class="tab-content">

                {{-- ---------- School information ---------- --}}
                <div class="tab-pane fade show active" id="tab-school">
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="ctl-label">School Name <span class="text-danger">*</span></label>
                                        <input name="system_name" value="{{ $s['system_name'] }}" required type="text" class="form-control" placeholder="Name of School">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="ctl-label">School Acronym</label>
                                        <input name="system_title" value="{{ $s['system_title'] }}" type="text" class="form-control" placeholder="e.g. MPSS">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="ctl-label">Phone</label>
                                        <input name="phone" value="{{ $s['phone'] }}" type="text" class="form-control" placeholder="Contact phone">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="ctl-label">School Email</label>
                                        <input name="system_email" value="{{ $s['system_email'] }}" type="email" class="form-control" placeholder="info@school.com">
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="ctl-label">Address <span class="text-danger">*</span></label>
                                <input required name="address" value="{{ $s['address'] }}" type="text" class="form-control" placeholder="School Address">
                            </div>
                        </div>

                        <div class="col-lg-5">
                            <div class="set-card d-flex flex-column align-items-center justify-content-center text-center px-3">
                                <p class="set-section-title mb-3">School Logo</p>
                                <img src="{{ $s['logo'] ?? '' }}" alt="logo" style="width:96px;height:96px;object-fit:contain;" class="rounded-circle border p-1 mb-3">
                                <input name="logo" accept="image/*" type="file" class="file-input" data-show-caption="false" data-show-upload="false" data-fouc>
                            </div>
                        </div>
                    </div>

                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-primary">Save Changes <i class="icon-paperplane ml-2"></i></button>
                    </div>
                </div>

                {{-- ---------- Academic ---------- --}}
                <div class="tab-pane fade" id="tab-academic">
                    <div class="row">
                        <div class="col-lg-7">
                            <div class="form-group">
                                <label class="ctl-label" for="current_session">Academic Year <span class="text-danger">*</span></label>
                                <select data-placeholder="Choose..." required name="current_session" id="current_session" class="select-search form-control">
                                    <option value=""></option>
                                    @for($y = date('Y', strtotime('-3 years')); $y <= date('Y', strtotime('+1 years')); $y++)
                                        <option {{ ($s['current_session'] == $y) ? 'selected' : '' }}>{{ $y }}</option>
                                    @endfor
                                </select>
                                <span class="form-text text-muted">The active session used across students, fees and reports.</span>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="ctl-label">This Term Ends</label>
                                        <input name="term_ends" value="{{ $s['term_ends'] }}" type="text" class="form-control date-pick" placeholder="M-D-Y">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label class="ctl-label">Next Term Begins</label>
                                        <input name="term_begins" value="{{ $s['term_begins'] }}" type="text" class="form-control date-pick" placeholder="M-D-Y">
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="ctl-label" for="lock_exam">Lock Exam Marks</label>
                                <select class="form-control select" name="lock_exam" id="lock_exam">
                                    <option {{ $s['lock_exam'] ? 'selected' : '' }} value="1">Yes</option>
                                    <option {{ $s['lock_exam'] ?: 'selected' }} value="0">No</option>
                                </select>
                                <span class="form-text text-info">{{ __('msg.lock_exam') }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-primary">Save Changes <i class="icon-paperplane ml-2"></i></button>
                    </div>
                </div>

                {{-- ---------- Finance ---------- --}}
                <div class="tab-pane fade" id="tab-finance">
                    <p class="set-section-title">Next Term Fees (per student)</p>
                    <div class="row mt-2">
                        @foreach($class_types as $ct)
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>{{ $ct->name }}</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text font-weight-semibold text-success-600">UGX</span>
                                        </div>
                                        <input class="form-control" type="number" min="0"
                                               value="{{ $s['next_term_fees_'.strtolower($ct->code)] }}"
                                               name="next_term_fees_{{ strtolower($ct->code) }}" placeholder="0">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="text-right mt-3">
                        <button type="submit" class="btn btn-primary">Save Changes <i class="icon-paperplane ml-2"></i></button>
                    </div>
                </div>
            </div>
        </form>

        {{-- ---------- Modules (Super Admin only) ---------- --}}
        @if(Qs::userIsSuperAdmin())
        <div class="tab-pane fade" id="tab-modules">

            <div class="row">
                <div class="col-lg-8">

                    {{-- Toolbar: search + status filter --}}
                    <div class="d-flex flex-wrap align-items-center mb-3">
                        <div class="flex-fill mr-2" style="min-width:200px;">
                            <input type="text" id="module-search" class="form-control" placeholder="Search modules...">
                        </div>
                        <div class="btn-group btn-group-toggle" data-toggle="buttons" id="module-filter">
                            <label class="btn btn-outline-secondary btn-sm active"><input type="radio" checked value="all">All</label>
                            <label class="btn btn-outline-secondary btn-sm"><input type="radio" value="enabled">Enabled</label>
                            <label class="btn btn-outline-secondary btn-sm"><input type="radio" value="disabled">Disabled</label>
                        </div>
                    </div>

                    <div class="alert alert-info alert-styled-left alert-dismissible mt-0 mb-3" style="font-size:.82rem;">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        Disabling a module only makes it unavailable (menus and pages) — <strong>no data is ever deleted</strong>.
                        Re-enabling restores everything instantly.
                    </div>

                    {{-- Cards grouped by category --}}
                    <div id="modules-grid">
                        @foreach($grouped_modules as $category => $mods)
                            <p class="set-section-title mt-2">{{ $category }}</p>
                            <div class="row">
                                @foreach($mods as $slug => $m)
                                    @php
                                        $enabled = Qm::enabled($slug);
                                        $required = !empty($m['required']);
                                        $deps = collect($m['depends_on'] ?? [])->map(function ($s) { return Qm::get($s)['name'] ?? $s; });
                                        $dependents = Qm::activeDependents($slug);
                                    @endphp
                                    <div class="col-md-6 mb-3 module-item"
                                         data-slug="{{ $slug }}"
                                         data-state="{{ $required ? 'required' : ($enabled ? 'enabled' : 'disabled') }}"
                                         data-name="{{ strtolower($m['name']) }}">
                                        <div class="module-card {{ !$enabled && !$required ? 'is-disabled' : '' }} h-100">
                                            <div class="card-body pb-2">
                                                <div class="mc-head">
                                                    <span class="module-icon"><i class="{{ $m['icon'] }}"></i></span>
                                                    <div class="flex-fill">
                                                        <div class="d-flex justify-content-between align-items-start">
                                                            <span class="mc-name">{{ $m['name'] }}</span>
                                                            @if($required)
                                                                <span class="status-badge st-required"><i class="icon-lock2"></i> Required</span>
                                                            @elseif($enabled)
                                                                <span class="status-badge st-enabled mod-status"><span class="dot"></span>Enabled</span>
                                                            @else
                                                                <span class="status-badge st-disabled mod-status"><span class="dot"></span>Disabled</span>
                                                            @endif
                                                        </div>
                                                        <div class="mc-desc">{{ $m['description'] }}</div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="card-footer border-0 bg-transparent pt-0 mt-auto d-flex justify-content-between align-items-center">
                                                <span class="dep-note">
                                                    @if($deps->count())
                                                        Requires: <b>{{ $deps->implode(', ') }}</b><br>
                                                    @endif
                                                    @if(!$required && $dependents)
                                                        Used by: <b>{{ collect($dependents)->map(function ($s) { return Qm::get($s)['name'] ?? $s; })->implode(', ') }}</b>
                                                    @endif
                                                    @if(!$deps->count() && !$dependents)&nbsp;@endif
                                                </span>

                                                @if(!$required)
                                                    <label class="mod-switch"
                                                           title="{{ $enabled ? 'Click to disable '.addslashes($m['name']) : 'Click to enable '.addslashes($m['name']) }}">
                                                        <input type="checkbox" class="module-toggle"
                                                               id="mod-{{ $slug }}" data-slug="{{ $slug }}" data-name="{{ $m['name'] }}"
                                                               {{ $enabled ? 'checked' : '' }}>
                                                        <span class="mod-slider"></span>
                                                    </label>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach

                        {{-- Empty state (search/filter miss) --}}
                        <div id="modules-empty" class="text-center text-muted py-5 d-none">
                            <i class="icon-grid7 icon-2x d-block mb-2 opacity-40"></i>
                            No modules match your search.
                        </div>
                    </div>
                </div>

                {{-- Activity feed --}}
                <div class="col-lg-4">
                    <div class="card border">
                        <div class="card-header header-elements-inline py-2">
                            <span class="font-weight-semibold"><i class="icon-history mr-2"></i>Recent Activity</span>
                            <div class="header-elements">
                                <a href="#" id="activity-refresh" class="text-muted" title="Refresh"><i class="icon-rotate-cw2"></i></a>
                            </div>
                        </div>
                        <div class="card-body py-2" id="activity-feed">
                            @forelse($activity_logs as $log)
                                <div class="act-item">
                                    <span class="act-dot {{ strpos($log->action, '.enabled') !== false ? 'act-on' : 'act-off' }}">
                                        <i class="icon-{{ strpos($log->action, '.enabled') !== false ? 'checkmark3' : 'block' }}"></i>
                                    </span>
                                    <div>
                                        <div style="font-size:.82rem;">{{ $log->description }}</div>
                                        <span class="act-time">{{ optional($log->user)->name ?? 'System' }} · {{ $log->created_at->format('d M Y, H:i') }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center text-muted py-4" style="font-size:.82rem;">
                                    <i class="icon-history d-block mb-1 opacity-40"></i>
                                    No activity recorded yet.
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection

@section('scripts')
<script>
(function () {
    var csrf = $('meta[name="csrf-token"]').attr('content');
    var filterState = 'all', searchTerm = '';

    // ---------- Inline toast (dependency-free) ----------
    function toast(type, msg) {
        var $t = $('<div class="alert alert-' + type + ' shadow-sm px-3 py-2" ' +
            'style="position:fixed;top:14px;right:14px;z-index:1080;min-width:280px;font-size:.85rem;">' +
            msg + '</div>');
        $('body').append($t);
        setTimeout(function () { $t.fadeOut(300, function () { $t.remove(); }); }, 2800);
    }
    // ---------- Filtering ----------
    function applyFilter() {
        var anyVisible = false;
        $('.module-item').each(function () {
            var $el = $(this);
            var matchText = String($el.data('name')).indexOf(searchTerm) !== -1;
            var matchState = filterState === 'all'
                || $el.data('state') === filterState
                || (filterState === 'enabled' && $el.data('state') === 'required');

            var show = matchText && matchState;
            $el.toggleClass('d-none', !show);
            if (show) anyVisible = true;
        });
        $('#modules-empty').toggleClass('d-none', anyVisible);
    }

    $('#module-search').on('keyup', function () {
        searchTerm = $(this).val().toLowerCase().trim();
        applyFilter();
    });

    $('#module-filter input').on('change', function () {
        filterState = this.value;
        applyFilter();
    });

    // ---------- Toggle handling ----------
    function postToggle(slug, force) {
        return $.post({
            url: '{{ route("settings.modules.toggle") }}',
            data: { slug: slug, force: force ? 1 : 0, _token: csrf },
        });
    }

    function updateCardUI(slug, enabled, name) {
        var $item = $('.module-item[data-slug="' + slug + '"]');
        $item.attr('data-state', enabled ? 'enabled' : 'disabled');
        $item.find('.module-card').toggleClass('is-disabled', !enabled);

        var $badge = $item.find('.mod-status');
        if (enabled) {
            $badge.attr('class', 'status-badge st-enabled mod-status').html('<span class="dot"></span>Enabled');
        } else {
            $badge.attr('class', 'status-badge st-disabled mod-status').html('<span class="dot"></span>Disabled');
        }
        loadActivity();
        toast(enabled ? 'success' : 'warning', name + ' module ' + (enabled ? 'enabled' : 'disabled'));
    }

    function confirmDisable(name, slug) {
        swal({
            title: 'Disable ' + name + '?',
            text: 'Users will no longer be able to access "' + name + '" while it is disabled. Existing data will NOT be deleted and is restored instantly when re-enabled.',
            icon: 'warning',
            buttons: { cancel: 'Cancel', confirm: { text: 'Yes, Disable Module', closeModal: true } },
            dangerMode: true,
        }).then(function (ok) {
            if (!ok) { restoreSwitch(slug, true); return; }
            sendToggle(slug, false);
        });
    }

    function confirmForceDisable(name, slug, dependents) {
        swal({
            title: name + ' has dependencies',
            text: 'Disabling "' + name + '" may affect these enabled modules: ' + dependents + '. Their pages may stop working until it is enabled again.',
            icon: 'warning',
            buttons: { cancel: 'Cancel', confirm: { text: 'Disable Anyway', closeModal: true } },
            dangerMode: true,
        }).then(function (ok) {
            if (!ok) { restoreSwitch(slug, true); return; }
            sendToggle(slug, true);
        });
    }

    function restoreSwitch(slug, checked) {
        $('.module-toggle[data-slug="' + slug + '"]').prop('checked', checked);
    }

    function sendToggle(slug, force) {
        var name = $('.module-toggle[data-slug="' + slug + '"]').data('name');

        postToggle(slug, force)
            .done(function (resp) {
                updateCardUI(resp.slug, resp.state === 'enabled', name || resp.slug);
            })
            .fail(function (xhr) {
                var resp = xhr.responseJSON || {};
                if (xhr.status === 409 && resp.code === 'dependent_modules') {
                    confirmForceDisable(name, slug, resp.dependents);
                } else {
                    restoreSwitch(slug, !force);
                    toast('error', resp.msg || 'Could not change the module state.');
                }
            });
    }

    $(document).on('change', '.module-toggle', function () {
        var $sw = $(this);
        var slug = $sw.data('slug');
        var name = $sw.data('name');
        var turningOn = $sw.prop('checked');

        if (turningOn) {
            // Enabling happens immediately
            sendToggle(slug, false);
        } else {
            confirmDisable(name, slug);
        }
    });

    // ---------- Activity feed ----------
    function loadActivity() {
        $.getJSON('{{ route("settings.activity") }}', function (resp) {
            var $feed = $('#activity-feed').empty();
            if (!resp.log.length) {
                $feed.html('<div class="text-center text-muted py-4" style="font-size:.82rem;">No activity recorded yet.</div>');
                return;
            }
            $.each(resp.log, function (i, l) {
                var on = l.action.indexOf('.enabled') !== -1;
                $feed.append(
                    '<div class="act-item">' +
                        '<span class="act-dot ' + (on ? 'act-on' : 'act-off') + '">' +
                            '<i class="icon-' + (on ? 'checkmark3' : 'block') + '"></i></span>' +
                        '<div><div style="font-size:.82rem;">' + l.description + '</div>' +
                        '<span class="act-time">' + (l.by || 'System') + ' · ' + l.at + '</span></div>' +
                    '</div>'
                );
            });
        });
    }

    $('#activity-refresh').on('click', function (e) {
        e.preventDefault();
        loadActivity();
    });
})();
</script>
@endsection
