<style>
    .page-title  { color: #ae4342; margin-bottom: -10px; }
    .section-title { font-size: .9rem; font-weight: 600; color: #364a63; border-left: 3px solid #ae4342; padding-left: 10px; margin-bottom: 16px; }

    /* Stat cards */
    .stat-card .stat-value { font-size: 1.75rem; font-weight: 700; color: #364a63; }
    .stat-card .stat-label { font-size: .78rem; color: #8094ae; text-transform: uppercase; letter-spacing: .05em; }
    .stat-card .stat-icon  { width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }

    /* Stream mini */
    .stream-mini { padding: 14px 18px; display: flex; align-items: center; gap: 12px; }
    .stream-mini .sm-icon  { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .stream-mini .sm-val   { font-size: 1.4rem; font-weight: 700; color: #364a63; line-height: 1; }
    .stream-mini .sm-label { font-size: .72rem; color: #8094ae; text-transform: uppercase; letter-spacing: .05em; }

    /* Inline badge styles */
    .bx { display: inline-block; border-radius: 4px; padding: 2px 8px; font-size: .75rem; font-weight: 600; }
    .bx-active   { background: #e8f5e9; color: #2e7d32; }
    .bx-inactive { background: #fce4ec; color: #b71c1c; }
    .bx-paid     { background: #e8f5e9; color: #2e7d32; }
    .bx-trial    { background: #fff3e0; color: #e65100; }
    .bx-type     { background: #e5edff; color: #3c58d0; }
    .thumb-img   { width: 60px; height: 36px; object-fit: cover; border-radius: 4px; }
</style>

{{-- PAGE TITLE --}}
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.dashboard' ) }}</h3>
        </div>
    </div>
</div>

{{-- SECTION 1 — ACTIVE USERS --}}
<div class="nk-block mb-4">
    <p class="section-title">Active Users Overview</p>
    <div class="row g-3">
        <div class="col-6 col-md-3"><div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
            <div class="stat-icon bg-primary-dim text-primary"><em class="icon ni ni-users"></em></div>
            <div><div class="stat-value" id="stat-total-active">—</div><div class="stat-label">Total Active</div></div>
        </div></div></div>
        <div class="col-6 col-md-3"><div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:#e5edff;color:#3c58d0"><em class="icon ni ni-user"></em></div>
            <div><div class="stat-value" id="stat-free">—</div><div class="stat-label">Free</div></div>
        </div></div></div>
        <div class="col-6 col-md-3"><div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:#fff3e0;color:#e65100"><em class="icon ni ni-clock"></em></div>
            <div><div class="stat-value" id="stat-trial">—</div><div class="stat-label">Trial</div></div>
        </div></div></div>
        <div class="col-6 col-md-3"><div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
            <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32"><em class="icon ni ni-star"></em></div>
            <div><div class="stat-value" id="stat-paid">—</div><div class="stat-label">Paid</div></div>
        </div></div></div>
    </div>
</div>

{{-- SECTION 2 — NEW USERS + SUBS COUNT --}}
<div class="nk-block mb-4">
    <div class="row g-3">
        <div class="col-12 col-md-6">
            <p class="section-title">New Users</p>
            <div class="row g-3">
                <div class="col-6"><div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info-dim text-info"><em class="icon ni ni-user-add"></em></div>
                    <div><div class="stat-value" id="stat-new-today">—</div><div class="stat-label">Today</div></div>
                </div></div></div>
                <div class="col-6"><div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-info-dim text-info"><em class="icon ni ni-user-add"></em></div>
                    <div><div class="stat-value" id="stat-new-month">—</div><div class="stat-label">This Month</div></div>
                </div></div></div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <p class="section-title">Subscriptions</p>
            <div class="row g-3">
                <div class="col-6"><div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:#f3e5f5;color:#7b1fa2"><em class="icon ni ni-check-circle"></em></div>
                    <div><div class="stat-value" id="stat-subs-today">—</div><div class="stat-label">Today</div></div>
                </div></div></div>
                <div class="col-6"><div class="card stat-card h-100"><div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:#f3e5f5;color:#7b1fa2"><em class="icon ni ni-check-circle"></em></div>
                    <div><div class="stat-value" id="stat-subs-month">—</div><div class="stat-label">This Month</div></div>
                </div></div></div>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 3 — DAILY USER CHART --}}
<div class="nk-block mb-4">
    <div class="card card-bordered">
        <div class="card-inner">
            <p class="section-title mb-3">Active Users by Type (Last 30 Days)</p>
            <div id="chart-daily-users"></div>
        </div>
    </div>
</div>

{{-- SECTION 4 — STREAM SUMMARY CARDS --}}
<div class="nk-block mb-4">
    <p class="section-title">Streaming Activity (All Time)</p>
    <div class="row g-3" id="stream-cards-row">
        {{-- Radio always first (static) --}}
        <div class="col-6 col-md-4 col-lg-3"><div class="card h-100"><div class="card-body stream-mini">
            <div class="sm-icon" style="background:#fce4ec;color:#c62828"><em class="icon ni ni-signal"></em></div>
            <div><div class="sm-val" id="stat-radio">—</div><div class="sm-label">Radio Streams</div></div>
        </div></div></div>
        {{-- Per-type cards appended here by JS --}}
    </div>
</div>

{{-- SECTION 5 — RADIO GRAPH --}}
<div class="nk-block mb-4">
    <div class="card card-bordered">
        <div class="card-inner">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <p class="section-title mb-0">Radio Streams (Last 30 Days)</p>
                <small class="text-muted">Top 8 stations</small>
            </div>
            <div id="chart-radio-streams" style="min-height:300px">
                <div class="d-flex align-items-center justify-content-center" style="height:300px">
                    <span class="text-muted">Loading…</span>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 6 — SUBSCRIPTIONS --}}
<div class="nk-block mb-4">
    <p class="section-title">Subscription Records</p>
    <div class="listing-filter">
        <input type="text" class="form-control form-control-sm" placeholder="Search date range…" id="subs-date" style="background:#fff" />
        <input type="text" class="form-control form-control-sm" placeholder="Search name / email…" id="subs-search" />
        <select class="form-select form-select-sm" id="subs-type">
            <option value="">All Types</option>
            <option value="Paid">Paid</option>
            <option value="Trial">Trial</option>
        </select>
        <select class="form-select form-select-sm" id="subs-status">
            <option value="">All Status</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>
    </div>
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <table class="table" style="width:100%" id="subs-table">
                <thead><tr>
                    <th></th>
                    <th>No.</th>
                    <th>User</th>
                    <th>Email</th>
                    <th>Plan</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                </tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- SECTION 7 — ITEM STREAMS (per type, built dynamically) --}}
<div class="nk-block mb-4">
    <p class="section-title">Item Streams by Type</p>
    <div class="listing-filter" style="grid-template-columns:1fr 3fr">
        <input type="text" class="form-control form-control-sm" placeholder="Filter by date range…" id="items-date" style="background:#fff" />
        <div></div>
    </div>
    <div id="items-tables-container">
        <div class="text-muted py-3">Loading…</div>
    </div>
</div>

{{-- SECTION 8 — PLAYLIST STREAMS (per type, built dynamically) --}}
<div class="nk-block mb-4">
    <p class="section-title">Playlist Streams by Type</p>
    <div class="listing-filter" style="grid-template-columns:1fr 3fr">
        <input type="text" class="form-control form-control-sm" placeholder="Filter by date range…" id="plists-date" style="background:#fff" />
        <div></div>
    </div>
    <div id="plists-tables-container">
        <div class="text-muted py-3">Loading…</div>
    </div>
</div>

{{-- SECTION 9 — COLLECTION STREAMS (per type, built dynamically) --}}
<div class="nk-block mb-4">
    <p class="section-title">Collection Streams by Type</p>
    <div class="listing-filter" style="grid-template-columns:1fr 3fr">
        <input type="text" class="form-control form-control-sm" placeholder="Filter by date range…" id="colls-date" style="background:#fff" />
        <div></div>
    </div>
    <div id="colls-tables-container">
        <div class="text-muted py-3">Loading…</div>
    </div>
</div>

{{-- SECTION 10 — BANNER CLICKS --}}
<div class="nk-block mb-4">
    <p class="section-title">Banner Clicks</p>
    <div class="listing-filter" style="grid-template-columns:1fr 1fr">
        <input type="text" class="form-control form-control-sm" placeholder="Search banner name…" id="banners-search" />
        <select class="form-select form-select-sm" id="banners-status">
            <option value="">All Status</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>
    </div>
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <table class="table" style="width:100%" id="banners-table">
                <thead><tr>
                    <th></th>
                    <th>No.</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Status</th>
                    <th>Clicks</th>
                    <th>Created</th>
                </tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- SECTION 11 — POPUP CLICKS --}}
<div class="nk-block mb-4">
    <p class="section-title">Pop Announcement Clicks</p>
    <div class="listing-filter" style="grid-template-columns:1fr 1fr">
        <input type="text" class="form-control form-control-sm" placeholder="Search title…" id="popups-search" />
        <select class="form-select form-select-sm" id="popups-status">
            <option value="">All Status</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>
    </div>
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <table class="table" style="width:100%" id="popups-table">
                <thead><tr>
                    <th></th>
                    <th>No.</th>
                    <th>Image</th>
                    <th>Title</th>
                    <th>Status</th>
                    <th>Clicks</th>
                    <th>Created</th>
                </tr></thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function () {
    var csrf = '{{ csrf_token() }}';

    /* ── helpers ─────────────────────────────────────────────────────── */

    function post(url, body) {
        return fetch(url, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body   : JSON.stringify(body || {}),
        }).then(function (r) { return r.json(); });
    }

    function esc(s) {
        return s == null ? '' : String(s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    var DT_DOM = "<'row'<'col-sm-12 col-md-6'B><'col-sm-12 col-md-6 text-end'l>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'mt-2 col-sm-12 col-md-5'i><'mt-2 col-sm-12 col-md-7 text-end'p>>";

    var DT_LANG = {
        lengthMenu   : '_MENU_ per page',
        zeroRecords  : 'No records found',
        info         : 'Showing _START_ to _END_ of _TOTAL_',
        infoEmpty    : 'No records available',
        infoFiltered : '(filtered from _MAX_)',
        paginate     : { previous: '‹', next: '›' },
    };

    /* Build per-table export buttons — mirrors dataTable.init.js exactly,
       but uses per-table class names so multiple tables don't conflict.   */
    function makeButtons(key) {
        var chkId    = 'exportSelected-' + key;
        var copyCls  = 'buttons-copy-'   + key;
        var excelCls = 'buttons-excel-'  + key;
        var csvCls   = 'buttons-csv-'    + key;
        var pdfCls   = 'buttons-pdf-'    + key;

        function exportOpts(cls) {
            return {
                modifier: { page: 'all' },
                rows: function (idx, data, node) {
                    if ($('#' + chkId).is(':checked')) {
                        return $(node).find('.select-row').is(':checked');
                    }
                    return true;
                },
                columns: ':not(:last-child)',
            };
        }

        function visibleAction(proxyClass) {
            return function (e, dt) {
                if ($('#' + chkId).is(':checked')) {
                    $('.' + proxyClass).click();
                } else {
                    dt.page.len(-1).draw();
                    dt.one('draw', function () {
                        $('.' + proxyClass).click();
                        setTimeout(function () { dt.page.len(10).draw(); }, 1000);
                    });
                }
            };
        }

        return [
            { extend: 'copyHtml5',  className: 'd-none ' + copyCls,  exportOptions: exportOpts(copyCls) },
            { text: '<i class="fa fa-copy"></i>',       className: 'btn btn-light',   titleAttr: 'Copy',           action: visibleAction(copyCls) },
            { extend: 'excelHtml5', className: 'd-none ' + excelCls, exportOptions: exportOpts(excelCls) },
            { text: '<i class="fa fa-file-excel"></i>', className: 'btn btn-success', titleAttr: 'Export to EXCEL', action: visibleAction(excelCls) },
            { extend: 'csvHtml5',   className: 'd-none ' + csvCls,   exportOptions: exportOpts(csvCls) },
            { text: '<i class="fa fa-file-csv"></i>',   className: 'btn btn-info',    titleAttr: 'Export to CSV',   action: visibleAction(csvCls) },
            { extend: 'pdfHtml5',   className: 'd-none ' + pdfCls,   exportOptions: exportOpts(pdfCls) },
            { text: '<i class="fa fa-file-pdf"></i>',   className: 'btn btn-danger',  titleAttr: 'Export to PDF',   action: visibleAction(pdfCls) },
        ];
    }

    /* initComplete: add "Export ONLY selected rows" checkbox scoped to this table */
    function makeInitComplete(key) {
        var chkId = 'exportSelected-' + key;
        return function () {
            var $container = $(this.api().table().container());
            $container.find('.export-check-wrapper').remove();
            $container.find('.dt-buttons').append(
                '<div class="my-3 export-check-wrapper">' +
                '<input type="checkbox" id="' + chkId + '" name="' + chkId + '">' +
                '<label for="' + chkId + '" class="ms-1">Export ONLY selected rows</label>' +
                '</div>'
            );
            $container.find('.dataTables_length select').addClass('custom-dropdown');
        };
    }

    /* Generic table factory */
    function makeDT(id, data, columns, columnDefs, orderCol) {
        var key = id.replace('-table', '').replace(/-/g, '');
        if ($.fn.DataTable.isDataTable('#' + id)) {
            $('#' + id).DataTable().destroy(true);
        }
        return $('#' + id).DataTable({
            data         : data || [],
            columns      : columns,
            columnDefs   : columnDefs || [],
            order        : [[ orderCol || 0, 'desc' ]],
            pageLength   : 10,
            lengthMenu   : [5, 10, 25, 50, 100],
            searching    : true,
            ordering     : true,
            scrollX      : true,
            dom          : DT_DOM,
            buttons      : makeButtons(key),
            language     : DT_LANG,
            createdRow   : function (row) { $(row).addClass('nk-tb-item'); },
            initComplete : makeInitComplete(key),
        });
    }

    /* Shared column helpers */
    var CHK_RENDER = function () { return '<input type="checkbox" class="select-row">'; };

    function noColDef() {  /* No. column is always at target 1 (checkbox at 0) */
        return {
            targets  : 1,
            orderable: false,
            render   : function (data, type, row, meta) { return meta.row + 1; },
        };
    }

    /* ══════════════════════════════════════════════════════════════════
       SECTIONS 1-5: ONE-TIME LOADS
    ══════════════════════════════════════════════════════════════════ */

    post('{{ route("admin.dashboard.getEngagementStats") }}').then(function (d) {
        document.getElementById('stat-total-active').textContent = d.total_active    || '0';
        document.getElementById('stat-free').textContent         = d.free_users      || '0';
        document.getElementById('stat-trial').textContent        = d.trial_users     || '0';
        document.getElementById('stat-paid').textContent         = d.paid_users      || '0';
        document.getElementById('stat-new-today').textContent    = d.new_users_today || '0';
        document.getElementById('stat-new-month').textContent    = d.new_users_month || '0';
        document.getElementById('stat-subs-today').textContent   = d.subs_today      || '0';
        document.getElementById('stat-subs-month').textContent   = d.subs_month      || '0';
        document.getElementById('stat-radio').textContent        = d.stream_radio    || '0';

        /* Build per-type stream cards dynamically */
        var CT = {
            items:       { bg: '#fff3e0', color: '#e65100', icon: 'ni-music',  label: 'Streams'     },
            playlists:   { bg: '#e8f5e9', color: '#2e7d32', icon: 'ni-list',   label: 'Playlists'   },
            collections: { bg: '#e5edff', color: '#3c58d0', icon: 'ni-folder', label: 'Collections' },
        };
        var $row = $('#stream-cards-row');
        $row.find('.stream-type-card').remove();
        (d.stream_types || []).forEach(function (type) {
            ['items', 'playlists', 'collections'].forEach(function (ct) {
                var c   = CT[ct];
                var lbl = esc(type.name) + ' ' + c.label;
                $row.append(
                    '<div class="col-6 col-md-4 col-lg-3 stream-type-card">' +
                    '<div class="card h-100"><div class="card-body stream-mini">' +
                    '<div class="sm-icon" style="background:' + c.bg + ';color:' + c.color + '">' +
                    '<em class="icon ni ' + c.icon + '"></em></div>' +
                    '<div><div class="sm-val">' + (type[ct] || '0') + '</div>' +
                    '<div class="sm-label">' + lbl + '</div></div>' +
                    '</div></div></div>'
                );
            });
        });
    });

    post('{{ route("admin.dashboard.getDailyUserStats") }}').then(function (d) {
        if (typeof ApexCharts === 'undefined') return;
        new ApexCharts(document.getElementById('chart-daily-users'), {
            chart      : { type: 'area', height: 260, toolbar: { show: false } },
            series     : [
                { name: 'Free',  data: d.free_data  || [] },
                { name: 'Trial', data: d.trial_data || [] },
                { name: 'Paid',  data: d.paid_data  || [] },
            ],
            xaxis      : { categories: d.xAxis || [], labels: { rotate: -45, style: { fontSize: '10px' } } },
            colors     : ['#3c58d0','#e65100','#2e7d32'],
            fill       : { type: 'gradient', gradient: { opacityFrom: .25, opacityTo: .02 } },
            stroke     : { curve: 'smooth', width: 2 },
            legend     : { position: 'top' },
            dataLabels : { enabled: false },
            grid       : { borderColor: '#f1f3f7' },
        }).render();
    });

    post('{{ route("admin.dashboard.getRadioStreamGraph") }}').then(function (d) {
        var el = document.getElementById('chart-radio-streams');
        if (!d.series || !d.series.length) {
            el.innerHTML = '<div class="d-flex align-items-center justify-content-center" style="height:300px"><span class="text-muted">No radio stream data yet.</span></div>';
            return;
        }
        if (typeof ApexCharts === 'undefined') return;
        new ApexCharts(el, {
            chart      : { type: 'bar', height: 300, stacked: true, toolbar: { show: false } },
            series     : d.series,
            xaxis      : { categories: d.labels || [], labels: { rotate: -45, style: { fontSize: '10px' } } },
            colors     : ['#ae4342','#e65100','#3c58d0','#2e7d32','#7b1fa2','#00838f','#f9a825','#4e342e'],
            plotOptions: { bar: { columnWidth: '60%' } },
            legend     : { position: 'top', fontSize: '12px' },
            dataLabels : { enabled: false },
            grid       : { borderColor: '#f1f3f7' },
        }).render();
    });

    /* ══════════════════════════════════════════════════════════════════
       SECTION 6 — SUBSCRIPTIONS
       Cols: 0=chk 1=no 2=user 3=email 4=plan 5=type 6=status 7=start 8=end
    ══════════════════════════════════════════════════════════════════ */

    var dtSubs = null, subsDateRange = '';

    function loadSubs() {
        post('{{ route("admin.dashboard.getSubscriptionsTable") }}', { date_range: subsDateRange }).then(function (d) {
            dtSubs = makeDT('subs-table', d.subscriptions, [
                { data: null, render: CHK_RENDER },
                { data: null         },
                { data: 'user'       },
                { data: 'email'      },
                { data: 'plan'       },
                { data: 'type'       },
                { data: 'status'     },
                { data: 'start_date' },
                { data: 'end_date'   },
            ], [
                { targets: 0, orderable: false, render: CHK_RENDER },
                noColDef(),
                { targets: 5, orderable: false,
                  render: function (data) {
                      var c = data === 'Trial' ? 'bx-trial' : 'bx-paid';
                      return '<span class="bx ' + c + '">' + esc(data) + '</span>';
                  } },
                { targets: 6, orderable: false,
                  render: function (data) {
                      var c = data === 'Active' ? 'bx-active' : 'bx-inactive';
                      return '<span class="bx ' + c + '">' + esc(data) + '</span>';
                  } },
            ], 7);
        });
    }

    $('#subs-date').flatpickr({ mode: 'range', disableMobile: true,
        onClose: function (sel, dateStr) { subsDateRange = dateStr; loadSubs(); } });

    var subsTimer;
    $('#subs-search').on('input', function () {
        var v = $(this).val(); clearTimeout(subsTimer);
        subsTimer = setTimeout(function () { if (dtSubs) dtSubs.search(v).draw(); }, 400);
    });
    $('#subs-type').on('change',   function () { if (dtSubs) dtSubs.column(5).search($(this).val()).draw(); });
    $('#subs-status').on('change', function () { if (dtSubs) dtSubs.column(6).search($(this).val()).draw(); });

    loadSubs();

    /* ══════════════════════════════════════════════════════════════════
       SECTIONS 7-9 — STREAM TABLES (per type category, built dynamically)

       Each content type (items / playlists / collections) gets ONE shared
       flatpickr date filter.  After each API load the rows are split by
       type_id and a separate DataTable is created per type.
    ══════════════════════════════════════════════════════════════════ */

    /* Track active DT instances so we can destroy them before rebuilding */
    var itemsDTs  = [];
    var plistsDTs = [];
    var collsDTs  = [];

    /* Destroy all DTs in an array then clear it */
    function destroyDTs(arr) {
        arr.forEach(function (dt) { try { dt.destroy(); } catch (e) {} });
        arr.length = 0;
    }

    /* Build HTML for one per-type table block and return {tableId, searchId} */
    function buildTypeBlock(containerId, prefix, type, thead) {
        var safeKey  = prefix + '-t' + type.id;
        var tableId  = safeKey + '-table';
        var searchId = safeKey + '-search';

        $('#' + containerId).append(
            '<p class="section-title mt-3 mb-2">' + esc(type.name) + '</p>' +
            '<div class="listing-filter" style="grid-template-columns:1fr 3fr">' +
            '<input type="text" class="form-control form-control-sm" placeholder="Search…" id="' + searchId + '">' +
            '<div></div></div>' +
            '<div class="card card-bordered card-preview mb-3"><div class="card-inner">' +
            '<table class="table" style="width:100%" id="' + tableId + '">' +
            '<thead><tr>' + thead + '</tr></thead><tbody></tbody>' +
            '</table></div></div>'
        );
        return { tableId: tableId, searchId: searchId };
    }

    /* Wire text-search input to a DataTable instance */
    function wireSearch(searchId, getDt) {
        var timer;
        $('#' + searchId).on('input', function () {
            var v = $(this).val(); clearTimeout(timer);
            timer = setTimeout(function () { var dt = getDt(); if (dt) dt.search(v).draw(); }, 400);
        });
    }

    /* ── Items (cols: chk 0, no 1, title 2, author 3, plays 4, last 5) ── */
    var itemsDateRange = '';
    var ITEMS_THEAD = '<th></th><th>No.</th><th>Title</th><th>Author</th><th>Plays</th><th>Last Played</th>';
    var ITEMS_COLS  = [
        { data: null, render: CHK_RENDER },
        { data: null         },
        { data: 'title'      },
        { data: 'author'     },
        { data: 'total'      },
        { data: 'last_played'},
    ];
    var ITEMS_DEFS = [
        { targets: 0, orderable: false, render: CHK_RENDER },
        noColDef(),
        { targets: 4, render: function (d) { return '<strong>' + (d || 0) + '</strong>'; } },
        { targets: 5, render: function (d) { return d ? String(d).substring(0, 16) : '—'; } },
    ];

    function buildItemTables(types, allRows) {
        destroyDTs(itemsDTs);
        var $c = $('#items-tables-container').empty();
        if (!types || !types.length) {
            $c.html('<div class="text-muted py-3">No data.</div>');
            return;
        }
        types.forEach(function (type) {
            var ids    = buildTypeBlock('items-tables-container', 'items', type, ITEMS_THEAD);
            var rows   = allRows.filter(function (r) { return r.type_id == type.id; });
            var dt     = makeDT(ids.tableId, rows, ITEMS_COLS, ITEMS_DEFS, 4);
            itemsDTs.push(dt);
            (function (ref) { wireSearch(ids.searchId, function () { return ref; }); })(dt);
        });
    }

    function loadItems() {
        post('{{ route("admin.dashboard.getItemStreams") }}', { date_range: itemsDateRange })
            .then(function (d) { buildItemTables(d.types, d.items); })
            .catch(function () { $('#items-tables-container').html('<div class="text-danger py-3">Failed to load.</div>'); });
    }

    $('#items-date').flatpickr({ mode: 'range', disableMobile: true,
        onClose: function (sel, dateStr) { itemsDateRange = dateStr; loadItems(); } });

    loadItems();

    /* ── Playlists (cols: chk 0, no 1, name 2, plays 3, last 4) ──────── */
    var plistsDateRange = '';
    var PLISTS_THEAD = '<th></th><th>No.</th><th>Playlist Name</th><th>Plays</th><th>Last Played</th>';
    var PLISTS_COLS  = [
        { data: null, render: CHK_RENDER },
        { data: null          },
        { data: 'name'        },
        { data: 'total'       },
        { data: 'last_played' },
    ];
    var PLISTS_DEFS = [
        { targets: 0, orderable: false, render: CHK_RENDER },
        noColDef(),
        { targets: 3, render: function (d) { return '<strong>' + (d || 0) + '</strong>'; } },
        { targets: 4, render: function (d) { return d ? String(d).substring(0, 16) : '—'; } },
    ];

    function buildPlistTables(types, allRows) {
        destroyDTs(plistsDTs);
        var $c = $('#plists-tables-container').empty();
        if (!types || !types.length) {
            $c.html('<div class="text-muted py-3">No data.</div>');
            return;
        }
        types.forEach(function (type) {
            var ids  = buildTypeBlock('plists-tables-container', 'plists', type, PLISTS_THEAD);
            var rows = allRows.filter(function (r) { return r.type_id == type.id; });
            var dt   = makeDT(ids.tableId, rows, PLISTS_COLS, PLISTS_DEFS, 3);
            plistsDTs.push(dt);
            (function (ref) { wireSearch(ids.searchId, function () { return ref; }); })(dt);
        });
    }

    function loadPlists() {
        post('{{ route("admin.dashboard.getPlaylistStreams") }}', { date_range: plistsDateRange })
            .then(function (d) { buildPlistTables(d.types, d.playlists); })
            .catch(function () { $('#plists-tables-container').html('<div class="text-danger py-3">Failed to load.</div>'); });
    }

    $('#plists-date').flatpickr({ mode: 'range', disableMobile: true,
        onClose: function (sel, dateStr) { plistsDateRange = dateStr; loadPlists(); } });

    loadPlists();

    /* ── Collections (cols: chk 0, no 1, name 2, plays 3, last 4) ────── */
    var collsDateRange = '';
    var COLLS_THEAD = '<th></th><th>No.</th><th>Collection Name</th><th>Plays</th><th>Last Played</th>';
    var COLLS_COLS  = [
        { data: null, render: CHK_RENDER },
        { data: null          },
        { data: 'name'        },
        { data: 'total'       },
        { data: 'last_played' },
    ];
    var COLLS_DEFS = [
        { targets: 0, orderable: false, render: CHK_RENDER },
        noColDef(),
        { targets: 3, render: function (d) { return '<strong>' + (d || 0) + '</strong>'; } },
        { targets: 4, render: function (d) { return d ? String(d).substring(0, 16) : '—'; } },
    ];

    function buildCollTables(types, allRows) {
        destroyDTs(collsDTs);
        var $c = $('#colls-tables-container').empty();
        if (!types || !types.length) {
            $c.html('<div class="text-muted py-3">No data.</div>');
            return;
        }
        types.forEach(function (type) {
            var ids  = buildTypeBlock('colls-tables-container', 'colls', type, COLLS_THEAD);
            var rows = allRows.filter(function (r) { return r.type_id == type.id; });
            var dt   = makeDT(ids.tableId, rows, COLLS_COLS, COLLS_DEFS, 3);
            collsDTs.push(dt);
            (function (ref) { wireSearch(ids.searchId, function () { return ref; }); })(dt);
        });
    }

    function loadColls() {
        post('{{ route("admin.dashboard.getCollectionStreams") }}', { date_range: collsDateRange })
            .then(function (d) { buildCollTables(d.types, d.collections); })
            .catch(function () { $('#colls-tables-container').html('<div class="text-danger py-3">Failed to load.</div>'); });
    }

    $('#colls-date').flatpickr({ mode: 'range', disableMobile: true,
        onClose: function (sel, dateStr) { collsDateRange = dateStr; loadColls(); } });

    loadColls();

    /* ══════════════════════════════════════════════════════════════════
       SECTION 10 — BANNER CLICKS
       Cols: 0=chk 1=no 2=image 3=name 4=status 5=clicks 6=created
    ══════════════════════════════════════════════════════════════════ */

    var dtBanners = null;

    post('{{ route("admin.dashboard.getBannerClickStats") }}').then(function (d) {
        dtBanners = makeDT('banners-table', d.banners, [
            { data: null, render: CHK_RENDER },
            { data: null         },
            { data: 'image_path' },
            { data: 'name'       },
            { data: 'status'     },
            { data: 'clicks'     },
            { data: 'created_at' },
        ], [
            { targets: 0, orderable: false, render: CHK_RENDER },
            noColDef(),
            { targets: 2, orderable: false,
              render: function (data) {
                  return data ? '<img src="' + esc(data) + '" class="thumb-img">' : '—';
              } },
            { targets: 4, orderable: false,
              render: function (data) {
                  var c = data == 10 ? 'bx-active' : 'bx-inactive';
                  var l = data == 10 ? 'Active'    : 'Inactive';
                  return '<span class="bx ' + c + '">' + l + '</span>';
              } },
            { targets: 5, render: function (data) { return '<strong>' + (data || 0) + '</strong>'; } },
        ], 5);
    });

    var bannersTimer;
    $('#banners-search').on('input', function () {
        var v = $(this).val(); clearTimeout(bannersTimer);
        bannersTimer = setTimeout(function () { if (dtBanners) dtBanners.search(v).draw(); }, 400);
    });
    $('#banners-status').on('change', function () { if (dtBanners) dtBanners.column(4).search($(this).val()).draw(); });

    /* ══════════════════════════════════════════════════════════════════
       SECTION 11 — POPUP CLICKS
       Cols: 0=chk 1=no 2=image 3=title 4=status 5=clicks 6=created
    ══════════════════════════════════════════════════════════════════ */

    var dtPopups = null;

    post('{{ route("admin.dashboard.getPopAnnouncementClickStats") }}').then(function (d) {
        dtPopups = makeDT('popups-table', d.popups, [
            { data: null, render: CHK_RENDER },
            { data: null         },
            { data: 'image_path' },
            { data: 'title'      },
            { data: 'status'     },
            { data: 'clicks'     },
            { data: 'created_at' },
        ], [
            { targets: 0, orderable: false, render: CHK_RENDER },
            noColDef(),
            { targets: 2, orderable: false,
              render: function (data) {
                  return data ? '<img src="' + esc(data) + '" class="thumb-img">' : '—';
              } },
            { targets: 4, orderable: false,
              render: function (data) {
                  var c = data == 10 ? 'bx-active' : 'bx-inactive';
                  var l = data == 10 ? 'Active'    : 'Inactive';
                  return '<span class="bx ' + c + '">' + l + '</span>';
              } },
            { targets: 5, render: function (data) { return '<strong>' + (data || 0) + '</strong>'; } },
        ], 5);
    });

    var popupsTimer;
    $('#popups-search').on('input', function () {
        var v = $(this).val(); clearTimeout(popupsTimer);
        popupsTimer = setTimeout(function () { if (dtPopups) dtPopups.search(v).draw(); }, 400);
    });
    $('#popups-status').on('change', function () { if (dtPopups) dtPopups.column(4).search($(this).val()).draw(); });

})();
</script>
