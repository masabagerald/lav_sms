@push('styles')
<style>
    /* ================= Reports shared styling ================= */
    .rep-header h5 { margin: 0; font-weight: 600; }
    .rep-header .sub { color: #8a97a8; font-size: .85rem; }

    .kpi-card {
        position: relative; overflow: hidden; border-radius: .55rem;
        padding: 1rem 1.15rem; color: #fff; height: 100%;
    }
    .kpi-card .kpi-label { font-size: .7rem; text-transform: uppercase; letter-spacing: .07em; opacity: .85; }
    .kpi-card .kpi-value { font-size: 1.45rem; font-weight: 700; line-height: 1.25; }
    .kpi-card .kpi-foot { font-size: .72rem; opacity: .85; }
    .kpi-card .kpi-icon { position: absolute; right: -10px; bottom: -16px; font-size: 4.6rem; opacity: .15; }

    .rep-filter label { font-size: .7rem; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; color: #6c757d; }

    /* Report tables */
    .rep-table thead th {
        background: #f5f7fa; border-bottom: 2px solid #e3e8ee;
        font-size: .72rem; text-transform: uppercase; letter-spacing: .05em; color: #5b6879;
        white-space: nowrap;
    }
    .rep-table td, .rep-table th { vertical-align: middle; }
    .rep-table .num { text-align: right; font-variant-numeric: tabular-nums; white-space: nowrap; }
    .rep-table tfoot td { font-weight: 700; border-top: 2px solid #d7dde5; background: #fafbfd; }
    .row-total td { background: #f0f4f9 !important; font-weight: 700; }

    .money { white-space: nowrap; font-variant-numeric: tabular-nums; }
    .neg { color: #c62828; }
    .pos { color: #2e7d32; }

    .status-pill { display: inline-block; padding: .14rem .55rem; border-radius: 1rem; font-size: .7rem; font-weight: 700; letter-spacing: .03em; }
    .st-paid     { background: #e8f5e9; color: #2e7d32; }
    .st-partial  { background: #fff3e0; color: #ef6c00; }
    .st-unpaid   { background: #ffebee; color: #c62828; }
    .st-neutral  { background: #eceff1; color: #546e7a; }

    .cat-card { transition: box-shadow .15s ease, transform .15s ease; height: 100%; }
    .cat-card:hover { box-shadow: 0 4px 16px rgba(20,40,80,.10); }
    .cat-icon { width: 40px; height: 40px; border-radius: .45rem; display: flex; align-items: center; justify-content: center; font-size: 1rem; }

    @media print {
        .sidebar, .navbar, header.navbar, .rep-no-print, .site-footer { display: none !important; }
        .content-wrapper { margin-left: 0 !important; }
        .card { border: none !important; box-shadow: none !important; }
        a { text-decoration: none !important; color: inherit !important; }
        .rep-print-title { display: block !important; margin-bottom: .6rem; }
    }
    .rep-print-title { display: none; }
</style>
@endpush
