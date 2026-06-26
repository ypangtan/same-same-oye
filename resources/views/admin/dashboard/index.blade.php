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

    .dt-buttons{
        display: none;
    }

    .dt-length {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .stream-link-card {
        display: block;
    }
    .stream-link-card .card {
        transition: box-shadow .15s, transform .15s;
    }
    .stream-link-card:hover .card {
        box-shadow: 0 4px 16px rgba(0,0,0,.10);
        transform: translateY(-2px);
    }
</style>

{{-- PAGE TITLE --}}
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between flex-wrap gap-2">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.dashboard' ) }}</h3>
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

{{-- SECTION 3 — APP ANALYTICS (Firebase GA4) --}}
<div class="nk-block mb-4" id="section-app-analytics">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <p class="section-title mb-0">App Analytics <small class="text-muted fw-normal ms-2" style="font-size:.75rem;border:none;padding:0">Firebase — Production data</small></p>
        <select class="form-select form-select-sm w-auto" id="app-analytics-period" style="font-size:.78rem">
            <option value="7">Last 7 Days</option>
            <option value="30" selected>Last 30 Days</option>
            <option value="90">Last 90 Days</option>
        </select>
    </div>
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="gap-3 d-flex align-items-center h-100">
                        <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32"><em class="icon ni ni-android"></em></div>
                        <div>
                            <div class="stat-value" id="stat-android-installs">—</div>
                            <div class="stat-label">Android Installs</div>
                            <div style="font-size:.68rem;color:#2e7d32;margin-top:2px">Google Play</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="gap-3 d-flex align-items-center h-100">
                        <div class="stat-icon" style="background:#e3f2fd;color:#1565c0"><em class="icon ni ni-apple"></em></div>
                        <div>
                            <div class="stat-value" id="stat-ios-installs">—</div>
                            <div class="stat-label">iOS Installs</div>
                            <div style="font-size:.68rem;color:#1565c0;margin-top:2px">App Store</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="gap-3 d-flex align-items-center h-100">
                        <div class="stat-icon" style="background:#e5edff;color:#3c58d0"><em class="icon ni ni-download"></em></div>
                        <div>
                            <div class="stat-value" id="stat-total-installs">—</div>
                            <div class="stat-label">Total Installs</div>
                            <div style="font-size:.68rem;color:#3c58d0;margin-top:2px">Both Stores</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="gap-3 d-flex align-items-center h-100">
                        <div class="stat-icon" style="background:#fce4ec;color:#c62828"><em class="icon ni ni-cross-circle"></em></div>
                        <div>
                            <div class="stat-value" id="stat-app-removed">—</div>
                            <div class="stat-label">App Removed</div>
                            <div style="font-size:.68rem;color:#c62828;margin-top:2px">Google Play only</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SECTION 4 — STREAM SUMMARY CARDS --}}
<div class="nk-block mb-4">
    <p class="section-title">Streaming Activity (All Time)</p>
    <div class="row g-3" id="stream-cards-row">
        {{-- Radio always first (static) --}}
        <a href="{{ route('admin.dashboard.stream') }}?page=radio" class="col-6 col-md-4 col-lg-3 stream-link-card text-decoration-none">
            <div class="card stat-card h-100">
                <div class="card-body">
                    <div class="gap-3 d-flex align-items-center h-100">
                        <div class="stat-icon" style="background:#fce4ec;color:#c62828"><em class="icon ni ni-signal"></em></div>
                        <div>
                            <div class="stat-value" id="stat-radio">—</div>
                            <div class="stat-label">Radio Streams</div>
                        </div>
                    </div>
                </div>
            </div>
        </a>
        {{-- Per-type cards appended here by JS --}}
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
    </div>
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <table class="table" style="width:100%" id="popups-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Image</th>
                        <th>Title</th>
                        <th>Status</th>
                        <th>Clicks</th>
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
            export       : false,
            scrollX      : true,
            dom          : DT_DOM,
            buttons      : makeButtons(key),
            language     : DT_LANG,
            createdRow   : function (row) { $(row).addClass('nk-tb-item'); },
            initComplete : makeInitComplete(key),
            drawCallback : function () {
                var api = this.api();
                var info = api.page.info();

                api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
                    cell.innerHTML = info.start + i + 1;
                });
            },
        });
    }

    function noColDef() {
        return {
            targets  : 0,
            orderable: false,
            searchable: false,
            render   : function () { return ''; },
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
            items:       { bg: '#fff3e0', color: '#e65100', icon: 'ni-music',  label: 'Streams',     prefix: 'items'  },
            playlists:   { bg: '#e8f5e9', color: '#2e7d32', icon: 'ni-list',   label: 'Playlists',   prefix: 'plists' },
            collections: { bg: '#e5edff', color: '#3c58d0', icon: 'ni-folder', label: 'Collections', prefix: 'colls'  },
        };
        var $row = $('#stream-cards-row');
        $row.find('.stream-type-card').remove();
        var CARD_TYPE_NAMES = { 'Song': 'Music' };
        var streamBaseUrl = '{{ route("admin.dashboard.stream") }}';
        (d.stream_types || []).forEach(function (type) {
            var typeName = CARD_TYPE_NAMES[type.name] || type.name;
            ['items', 'playlists', 'collections'].forEach(function (ct) {
                var c      = CT[ct];
                var lbl    = esc(typeName) + ' ' + c.label;
                var pageId = c.prefix + '-t' + type.id;
                $row.append(
                    '<a href="' + streamBaseUrl + '?page=' + pageId + '" class="col-6 col-md-4 col-lg-3 stream-type-card stream-link-card text-decoration-none">' +
                    '<div class="card stat-card h-100"><div class="card-body">' +
                    '<div class="gap-3 d-flex align-items-center h-100">' +
                    '<div class="stat-icon" style="background:' + c.bg + ';color:' + c.color + '">' +
                    '<em class="icon ni ' + c.icon + '"></em></div>' +
                    '<div><div class="stat-value">' + (type[ct] || '0') + '</div>' +
                    '<div class="stat-label">' + lbl + '</div></div>' +
                    '</div></div></div></a>'
                );
            });
        });
    });

    /* ── App Analytics (Firebase GA4) ─────────────────────────────── */

    function loadAppAnalytics(period) {
        document.getElementById('stat-android-installs').textContent = '…';
        document.getElementById('stat-ios-installs').textContent     = '…';
        document.getElementById('stat-total-installs').textContent   = '…';
        document.getElementById('stat-app-removed').textContent      = '…';

        post('{{ route("admin.dashboard.getAppAnalytics") }}', { period: period })
            .then(function (d) {
                if (d.error) {
                    ['stat-android-installs','stat-ios-installs','stat-total-installs','stat-app-removed']
                        .forEach(function (id) { document.getElementById(id).textContent = '—'; });
                    return;
                }
                document.getElementById('stat-android-installs').textContent = d.installs.android ?? '0';
                document.getElementById('stat-ios-installs').textContent     = d.installs.ios     ?? '0';
                document.getElementById('stat-total-installs').textContent   = d.installs.total   ?? '0';
                document.getElementById('stat-app-removed').textContent      = d.removals.android ?? '0';
            })
            .catch(function () {
                ['stat-android-installs','stat-ios-installs','stat-total-installs','stat-app-removed']
                    .forEach(function (id) { document.getElementById(id).textContent = '—'; });
            });
    }

    loadAppAnalytics('30');

    $('#app-analytics-period').on('change', function () {
        loadAppAnalytics($(this).val());
    });

    /* ══════════════════════════════════════════════════════════════════
       SECTION 6 — SUBSCRIPTIONS
       Cols: 0=chk 1=no 2=user 3=email 4=plan 5=type 6=status 7=start 8=end
    ══════════════════════════════════════════════════════════════════ */

    var dtSubs = null, subsDateRange = '', subsLoaded = false;

    function loadSubs() {
        post('{{ route("admin.dashboard.getSubscriptionsTable") }}', { date_range: subsDateRange }).then(function (d) {
            subsLoaded = true;
            dtSubs = makeDT('subs-table', d.subscriptions, [
                { data: null         },
                { data: 'user'       },
                { data: 'email'      },
                { data: 'plan'       },
                { data: 'type'       },
                { data: 'status'     },
                { data: 'start_date' },
                { data: 'end_date'   },
            ], [
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

    initToggle('toggle-subs', 'section-subs', loadSubs);

    /* ══════════════════════════════════════════════════════════════════
       SECTION 10 — BANNER CLICKS
       Cols: 0=chk 1=no 2=image 3=name 4=status 5=clicks 6=created
    ══════════════════════════════════════════════════════════════════ */

    var dtBanners = null;

    post('{{ route("admin.dashboard.getBannerClickStats") }}').then(function (d) {
        dtBanners = makeDT('banners-table', d.banners, [
            { data: null         },
            { data: 'image_path' },
            { data: 'status'     },
            { data: 'clicks'     },
        ], [
            noColDef(),
            { targets: 1, orderable: false,
              render: function (data) {
                  return data ? '<img src="' + esc(data) + '" class="thumb-img">' : '—';
              } },
            { targets: 2, orderable: false,
              render: function (data) {
                  var c = data === 'Active' ? 'bx-active' : 'bx-inactive';
                  return '<span class="bx ' + c + '">' + esc(data) + '</span>';
              } },
            { targets: 3, render: function (data) { return '<strong>' + (data || 0) + '</strong>'; } },
        ], 3);
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
            { data: null         },
            { data: 'image_path' },
            { data: 'title'      },
            { data: 'status'     },
            { data: 'clicks'     },
        ], [
            noColDef(),
            { targets: 1, orderable: false,
              render: function (data) {
                  return data ? '<img src="' + esc(data) + '" class="thumb-img">' : '—';
              } },
            { targets: 3, orderable: false,
              render: function (data) {
                  var c = data === 'Active' ? 'bx-active' : 'bx-inactive';
                  return '<span class="bx ' + c + '">' + esc(data) + '</span>';
              } },
            { targets: 4, render: function (data) { return '<strong>' + (data || 0) + '</strong>'; } },
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
