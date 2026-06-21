<style>
    .page-title {
        color: #ae4342;
        margin-bottom: -10px;
    }

    .section-title {
        font-size: .9rem;
        font-weight: 600;
        color: #364a63;
        border-left: 3px solid #ae4342;
        padding-left: 10px;
        margin-bottom: 16px;
    }

    /* Stat cards */
    .stat-card .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #364a63;
    }

    .stat-card .stat-label {
        font-size: .78rem;
        color: #8094ae;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .stat-card .stat-icon {
        width: 44px;
        height: 44px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }

    /* Stream mini */
    .stream-mini {
        padding: 14px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .stream-mini .sm-icon {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
        flex-shrink: 0;
    }

    .stream-mini .sm-val {
        font-size: 1.4rem;
        font-weight: 700;
        color: #364a63;
        line-height: 1;
    }

    .stream-mini .sm-label {
        font-size: .72rem;
        color: #8094ae;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    /* Inline badge styles */
    .bx {
        display: inline-block;
        border-radius: 4px;
        padding: 2px 8px;
        font-size: .75rem;
        font-weight: 600;
    }

    .bx-active {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .bx-inactive {
        background: #fce4ec;
        color: #b71c1c;
    }

    .bx-paid {
        background: #e8f5e9;
        color: #2e7d32;
    }

    .bx-trial {
        background: #fff3e0;
        color: #e65100;
    }

    .bx-type {
        background: #e5edff;
        color: #3c58d0;
    }

    .thumb-img {
        width: 60px;
        height: 36px;
        object-fit: cover;
        border-radius: 4px;
    }
</style>

{{-- PAGE TITLE --}}
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between flex-wrap gap-2">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.dashboard' ) }}</h3>
        </div>
        <div class="nk-block-head-content">
            <div class="d-flex align-items-center gap-4">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="toggle-radio-graph">
                    <label class="form-check-label fw-medium" for="toggle-radio-graph">Radio Graph</label>
                </div>
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" id="toggle-subs">
                    <label class="form-check-label fw-medium" for="toggle-subs">Subscription Records</label>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 1 — ACTIVE USERS --}}
<div class="nk-block mb-4">
    <p class="section-title">Active Users Overview</p>
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="gap-3 d-flex align-items-center h-100">
                        <div class="stat-icon bg-primary-dim text-primary"><em class="icon ni ni-users"></em></div>
                        <div>
                            <div class="stat-value" id="stat-total-active">—</div>
                            <div class="stat-label">Total Active</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="gap-3 d-flex align-items-center h-100">
                        <div class="stat-icon" style="background:#e5edff;color:#3c58d0"><em class="icon ni ni-user"></em>
                        </div>
                        <div>
                            <div class="stat-value" id="stat-free">—</div>
                            <div class="stat-label">Free</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="gap-3 d-flex align-items-center h-100">
                        <div class="stat-icon" style="background:#fff3e0;color:#e65100"><em class="icon ni ni-clock"></em>
                        </div>
                        <div>
                            <div class="stat-value" id="stat-trial">—</div>
                            <div class="stat-label">Trial</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="gap-3 d-flex align-items-center h-100">
                        <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32"><em class="icon ni ni-star"></em>
                        </div>
                        <div>
                            <div class="stat-value" id="stat-paid">—</div>
                            <div class="stat-label">Paid</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 2 — NEW USERS + SUBS COUNT --}}
<div class="nk-block mb-4">
    <div class="row g-3">
        <div class="col-12 col-md-6">
            <p class="section-title">New Users</p>
            <div class="row g-3">
                <div class="col-6">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="gap-3 d-flex align-items-center h-100">
                                <div class="stat-icon bg-info-dim text-info"><em class="icon ni ni-user-add"></em></div>
                                <div>
                                    <div class="stat-value" id="stat-new-today">—</div>
                                    <div class="stat-label">Today</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="gap-3 d-flex align-items-center h-100">
                                <div class="stat-icon bg-info-dim text-info"><em class="icon ni ni-user-add"></em></div>
                                <div>
                                    <div class="stat-value" id="stat-new-month">—</div>
                                    <div class="stat-label">This Month</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-6">
            <p class="section-title">Subscriptions</p>
            <div class="row g-3">
                <div class="col-6">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="gap-3 d-flex align-items-center h-100">
                                <div class="stat-icon" style="background:#f3e5f5;color:#7b1fa2"><em
                                        class="icon ni ni-check-circle"></em></div>
                                <div>
                                    <div class="stat-value" id="stat-subs-today">—</div>
                                    <div class="stat-label">Today</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card stat-card h-100">
                        <div class="card-body">
                            <div class="gap-3 d-flex align-items-center h-100">
                                <div class="stat-icon" style="background:#f3e5f5;color:#7b1fa2"><em
                                        class="icon ni ni-check-circle"></em></div>
                                <div>
                                    <div class="stat-value" id="stat-subs-month">—</div>
                                    <div class="stat-label">This Month</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 3 — DAILY USER CHART --}}
<div class="nk-block mb-4 d-none">
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
        <div class="col-6 col-md-4 col-lg-3">
            <div class="card h-100">
                <div class="card-body stream-mini">
                    <div class="sm-icon" style="background:#fce4ec;color:#c62828"><em class="icon ni ni-signal"></em>
                    </div>
                    <div>
                        <div class="sm-val" id="stat-radio">—</div>
                        <div class="sm-label">Radio Streams</div>
                    </div>
                </div>
            </div>
        </div>
        {{-- Per-type cards appended here by JS --}}
    </div>
</div>

{{-- SECTION 5 — RADIO GRAPH --}}
<div id="section-radio-graph" class="nk-block mb-4" style="display:none">
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
<div id="section-subs" class="nk-block mb-4" style="display:none">
    <p class="section-title">Subscription Records</p>
    <div class="listing-filter">
        <input type="text" class="form-control form-control-sm" placeholder="Search date range…" id="subs-date"
            style="background:#fff" />
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
                <thead>
                    <tr>
                        <th></th>
                        <th>No.</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Plan</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- SECTIONS 7-9 — STREAMS BY TYPE (grouped per category) --}}
<div class="nk-block mb-4">
    <div id="streams-by-type-container">
        <div class="text-muted py-3">Loading…</div>
    </div>
</div>

{{-- SECTION 10 — BANNER CLICKS --}}
<div class="nk-block mb-4">
    <p class="section-title">Banner Clicks</p>
    <div class="listing-filter" style="grid-template-columns:1fr 1fr">
        <input type="text" class="form-control form-control-sm d-none" placeholder="Search banner name…" id="banners-search" />
        <select class="form-select form-select-sm d-none" id="banners-status">
            <option value="">All Status</option>
            <option value="Active">Active</option>
            <option value="Inactive">Inactive</option>
        </select>
    </div>
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <table class="table" style="width:100%" id="banners-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>No.</th>
                        <th>Image</th>
                        <th>Status</th>
                        <th>Clicks</th>
                    </tr>
                </thead>
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
                <thead>
                    <tr>
                        <th></th>
                        <th>No.</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Clicks</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
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
            $('#' + id).DataTable().destroy(); /* keep <table> in DOM for reinit */
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
        var CARD_TYPE_NAMES = { 'Song': 'Music' };
        (d.stream_types || []).forEach(function (type) {
            var typeName = CARD_TYPE_NAMES[type.name] || type.name;
            ['items', 'playlists', 'collections'].forEach(function (ct) {
                var c   = CT[ct];
                var lbl = esc(typeName) + ' ' + c.label;
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

    /* ── Radio graph (lazy — only loads when section is shown) ── */
    var radioChartLoaded = false;
    function loadRadioGraph() {
        if (radioChartLoaded) return;
        radioChartLoaded = true;
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
    }

    /* ══════════════════════════════════════════════════════════════════
       SECTION 6 — SUBSCRIPTIONS
       Cols: 0=chk 1=no 2=user 3=email 4=plan 5=type 6=status 7=start 8=end
    ══════════════════════════════════════════════════════════════════ */

    var dtSubs = null, subsDateRange = '', subsLoaded = false;

    function loadSubs() {
        post('{{ route("admin.dashboard.getSubscriptionsTable") }}', { date_range: subsDateRange }).then(function (d) {
            subsLoaded = true;
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

    /* ── Toggle switches (localStorage-persisted) ─────────────────── */
    function initToggle(toggleId, sectionId, onShow) {
        var stored = localStorage.getItem('dash_' + toggleId);
        var on     = stored === '1';
        var $chk   = $('#' + toggleId);
        var $sec   = $('#' + sectionId);

        $chk.prop('checked', on);
        $sec.toggle(on);
        if (on && onShow) onShow();

        $chk.on('change', function () {
            var nowOn = $(this).is(':checked');
            localStorage.setItem('dash_' + toggleId, nowOn ? '1' : '0');
            $sec.toggle(nowOn);
            if (nowOn && onShow) onShow();
        });
    }

    initToggle('toggle-radio-graph', 'section-radio-graph', loadRadioGraph);
    initToggle('toggle-subs',        'section-subs',        loadSubs);

    /* ══════════════════════════════════════════════════════════════════
       SECTIONS 7-9 — STREAM TABLES (per type, each with own date+search)

       Initial load fetches all rows once and splits client-side by type.
       Each per-type date filter re-calls the API with that type_id only.
    ══════════════════════════════════════════════════════════════════ */

    /* Build HTML for one per-type block; date + search filters above the card */
    function buildTypeBlock(containerId, prefix, type, thead, suffix) {
        var safeKey  = prefix + '-t' + type.id;
        var tableId  = safeKey + '-table';
        var searchId = safeKey + '-search';
        var title    = esc(type.name) + (suffix ? ' ' + suffix : '');

        $('#' + containerId).append(
            '<p class="section-title mt-3 mb-2">' + title + '</p>' +
            '<div class="listing-filter" style="grid-template-columns:1fr 3fr">' +
            '<input type="text" class="form-control form-control-sm" placeholder="Search…" id="' + searchId + '">' +
            '<div></div>' +
            '</div>' +
            '<div class="card card-bordered card-preview mb-3"><div class="card-inner">' +
            '<table class="table" style="width:100%" id="' + tableId + '">' +
            '<thead><tr>' + thead + '</tr></thead><tbody></tbody>' +
            '</table></div></div>'
        );
        return { tableId: tableId, searchId: searchId };
    }

    /* Wire per-type block: load all data once, search only */
    function wireTypeBlock(ids, rows, cols, defs, orderCol) {
        var dt = makeDT(ids.tableId, rows, cols, defs, orderCol);
        var timer;
        $('#' + ids.searchId).on('input', function () {
            var v = $(this).val(); clearTimeout(timer);
            timer = setTimeout(function () { if (dt) dt.search(v).draw(); }, 400);
        });
    }

    /* ══════════════════════════════════════════════════════════════════
       SECTIONS 7-9 — STREAMS BY TYPE
       Order per type: {Type} → {Type} Playlist → {Type} Collection
    ══════════════════════════════════════════════════════════════════ */

    var ITEMS_THEAD  = '<th></th><th>No.</th><th>Title</th><th>Plays</th>';
    var ITEMS_COLS   = [
        { data: null,  render: CHK_RENDER },
        { data: null   },
        { data: 'title'},
        { data: 'total'},
    ];
    var ITEMS_DEFS   = [
        { targets: 0, orderable: false, render: CHK_RENDER },
        noColDef(),
        { targets: 3, render: function (d) { return '<strong>' + (d || 0) + '</strong>'; } },
    ];

    var PLISTS_THEAD = '<th></th><th>No.</th><th>Playlist Name</th><th>Plays</th>';
    var PLISTS_COLS  = [
        { data: null, render: CHK_RENDER },
        { data: null   },
        { data: 'name' },
        { data: 'total'},
    ];
    var PLISTS_DEFS  = [
        { targets: 0, orderable: false, render: CHK_RENDER },
        noColDef(),
        { targets: 3, render: function (d) { return '<strong>' + (d || 0) + '</strong>'; } },
    ];

    var COLLS_THEAD  = '<th></th><th>No.</th><th>Collection Name</th><th>Plays</th>';
    var COLLS_COLS   = [
        { data: null, render: CHK_RENDER },
        { data: null   },
        { data: 'name' },
        { data: 'total'},
    ];
    var COLLS_DEFS   = [
        { targets: 0, orderable: false, render: CHK_RENDER },
        noColDef(),
        { targets: 3, render: function (d) { return '<strong>' + (d || 0) + '</strong>'; } },
    ];

    Promise.all([
        post('{{ route("admin.dashboard.getItemStreams") }}',       {}),
        post('{{ route("admin.dashboard.getPlaylistStreams") }}',   {}),
        post('{{ route("admin.dashboard.getCollectionStreams") }}', {}),
    ]).then(function (results) {
        var dI = results[0], dP = results[1], dC = results[2];
        var $c = $('#streams-by-type-container').empty();

        /* Merge types from all 3 responses, rename, then sort alphabetically */
        var TYPE_NAMES = { 'Song': 'Music' };
        var typesMap = {};
        [(dI.types || []), (dP.types || []), (dC.types || [])].forEach(function (arr) {
            arr.forEach(function (t) {
                if (!typesMap[t.id]) typesMap[t.id] = { id: t.id, name: TYPE_NAMES[t.name] || t.name };
            });
        });
        var types = Object.keys(typesMap)
            .map(function (k) { return typesMap[k]; })
            .sort(function (a, b) { return a.name.localeCompare(b.name); });

        if (!types.length) { $c.html('<div class="text-muted py-3">No data.</div>'); return; }

        var allItems  = dI.items        || [];
        var allPlists = dP.playlists    || [];
        var allColls  = dC.collections  || [];

        types.forEach(function (type) {
            var tid = type.id;

            var iIds = buildTypeBlock('streams-by-type-container', 'items', type, ITEMS_THEAD, null);
            wireTypeBlock(iIds, allItems.filter(function (r) { return r.type_id == tid; }), ITEMS_COLS, ITEMS_DEFS, 3);

            var pIds = buildTypeBlock('streams-by-type-container', 'plists', type, PLISTS_THEAD, 'Playlist');
            wireTypeBlock(pIds, allPlists.filter(function (r) { return r.type_id == tid; }), PLISTS_COLS, PLISTS_DEFS, 3);

            var cIds = buildTypeBlock('streams-by-type-container', 'colls', type, COLLS_THEAD, 'Collection');
            wireTypeBlock(cIds, allColls.filter(function (r) { return r.type_id == tid; }), COLLS_COLS, COLLS_DEFS, 3);
        });
    }).catch(function () {
        $('#streams-by-type-container').html('<div class="text-danger py-3">Failed to load.</div>');
    });

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
            { data: 'status'     },
            { data: 'clicks'     },
        ], [
            { targets: 0, orderable: false, render: CHK_RENDER },
            noColDef(),
            { targets: 2, orderable: false,
              render: function (data) {
                  return data ? '<img src="' + esc(data) + '" class="thumb-img">' : '—';
              } },
            { targets: 4, orderable: false,
              render: function (data) {
                  var c = data === 'Active' ? 'bx-active' : 'bx-inactive';
                  return '<span class="bx ' + c + '">' + esc(data) + '</span>';
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
                  var c = data === 'Active' ? 'bx-active' : 'bx-inactive';
                  return '<span class="bx ' + c + '">' + esc(data) + '</span>';
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

});
</script>