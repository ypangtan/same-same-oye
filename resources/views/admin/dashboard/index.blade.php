<style>
    .apexcharts-datalabels { display: none }
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

{{-- ═══ PAGE TITLE ═══════════════════════════════════════════════════════════ --}}
<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.dashboard' ) }}</h3>
        </div>
    </div>
</div>

{{-- ═══ SECTION 1 — ACTIVE USERS ══════════════════════════════════════════════ --}}
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

{{-- ═══ SECTION 2 — NEW USERS + SUBS COUNT ════════════════════════════════════ --}}
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

{{-- ═══ SECTION 3 — DAILY USER CHART ══════════════════════════════════════════ --}}
<div class="nk-block mb-4">
    <div class="card card-bordered">
        <div class="card-inner">
            <p class="section-title mb-3">Active Users by Type (Last 30 Days)</p>
            <div id="chart-daily-users"></div>
        </div>
    </div>
</div>

{{-- ═══ SECTION 4 — STREAM SUMMARY CARDS ══════════════════════════════════════ --}}
<div class="nk-block mb-4">
    <p class="section-title">Streaming Activity (All Time)</p>
    <div class="row g-3">
        <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body stream-mini">
            <div class="sm-icon" style="background:#fce4ec;color:#c62828"><em class="icon ni ni-signal"></em></div>
            <div><div class="sm-val" id="stat-radio">—</div><div class="sm-label">Radio</div></div>
        </div></div></div>
        <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body stream-mini">
            <div class="sm-icon bg-warning-dim text-warning"><em class="icon ni ni-music"></em></div>
            <div><div class="sm-val" id="stat-items">—</div><div class="sm-label">Items</div></div>
        </div></div></div>
        <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body stream-mini">
            <div class="sm-icon bg-success-dim text-success"><em class="icon ni ni-list"></em></div>
            <div><div class="sm-val" id="stat-playlists">—</div><div class="sm-label">Playlists</div></div>
        </div></div></div>
        <div class="col-6 col-lg-3"><div class="card h-100"><div class="card-body stream-mini">
            <div class="sm-icon bg-primary-dim text-primary"><em class="icon ni ni-folder"></em></div>
            <div><div class="sm-val" id="stat-collections">—</div><div class="sm-label">Collections</div></div>
        </div></div></div>
    </div>
</div>

{{-- ═══ SECTION 5 — RADIO GRAPH ════════════════════════════════════════════════ --}}
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

{{-- ═══ SECTION 6 — SUBSCRIPTIONS DATATABLE ═══════════════════════════════════ --}}
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
                <thead>
                    <tr>
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

{{-- ═══ SECTION 7 — ITEM STREAMS DATATABLE ════════════════════════════════════ --}}
<div class="nk-block mb-4">
    <p class="section-title">Item Streams</p>

    <div class="listing-filter">
        <input type="text" class="form-control form-control-sm" placeholder="Search date range…" id="items-date" style="background:#fff" />
        <input type="text" class="form-control form-control-sm" placeholder="Search title / author…" id="items-search" />
        <select class="form-select form-select-sm" id="items-type">
            <option value="">All Types</option>
        </select>
        <div></div>
    </div>
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <table class="table" style="width:100%" id="items-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Type</th>
                        <th>Title</th>
                        <th>Author</th>
                        <th>Plays</th>
                        <th>Last Played</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══ SECTION 8 — PLAYLIST STREAMS DATATABLE ════════════════════════════════ --}}
<div class="nk-block mb-4">
    <p class="section-title">Playlist Streams</p>

    <div class="listing-filter">
        <input type="text" class="form-control form-control-sm" placeholder="Search date range…" id="plists-date" style="background:#fff" />
        <input type="text" class="form-control form-control-sm" placeholder="Search playlist name…" id="plists-search" />
        <select class="form-select form-select-sm" id="plists-type">
            <option value="">All Types</option>
        </select>
        <div></div>
    </div>
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <table class="table" style="width:100%" id="plists-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Type</th>
                        <th>Playlist Name</th>
                        <th>Plays</th>
                        <th>Last Played</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══ SECTION 9 — COLLECTION STREAMS DATATABLE ══════════════════════════════ --}}
<div class="nk-block mb-4">
    <p class="section-title">Collection Streams</p>

    <div class="listing-filter">
        <input type="text" class="form-control form-control-sm" placeholder="Search date range…" id="colls-date" style="background:#fff" />
        <input type="text" class="form-control form-control-sm" placeholder="Search collection name…" id="colls-search" />
        <select class="form-select form-select-sm" id="colls-type">
            <option value="">All Types</option>
        </select>
        <div></div>
    </div>
    <div class="card card-bordered card-preview">
        <div class="card-inner">
            <table class="table" style="width:100%" id="colls-table">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Type</th>
                        <th>Collection Name</th>
                        <th>Plays</th>
                        <th>Last Played</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- ═══ SECTION 10 — BANNER CLICKS DATATABLE ══════════════════════════════════ --}}
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
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Image</th>
                        <th>Name</th>
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

{{-- ═══ SECTION 11 — POPUP CLICKS DATATABLE ═══════════════════════════════════ --}}
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
(function () {
    var csrf = '{{ csrf_token() }}';

    /* ── helpers ──────────────────────────────────────────────────────────── */

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

    var DT_DOM = "<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'mt-2 col-sm-12 col-md-5'i><'mt-2 col-sm-12 col-md-7 text-end'p>>";

    var DT_LANG = {
        lengthMenu    : '_MENU_ per page',
        zeroRecords   : 'No records found',
        info          : 'Showing _START_ to _END_ of _TOTAL_',
        infoEmpty     : 'No records available',
        infoFiltered  : '(filtered from _MAX_)',
        paginate      : { previous: '‹', next: '›' },
    };

    function makeDT(id, data, columns, columnDefs, orderCol) {
        if ($.fn.DataTable.isDataTable('#' + id)) {
            $('#' + id).DataTable().destroy();
            $('#' + id + ' tbody').empty();
        }
        return $('#' + id).DataTable({
            data       : data || [],
            columns    : columns,
            columnDefs : columnDefs || [],
            order      : [[ orderCol || 0, 'desc' ]],
            pageLength : 10,
            lengthMenu : [5, 10, 25, 50, 100],
            searching  : true,
            ordering   : true,
            scrollX    : true,
            dom        : DT_DOM,
            language   : DT_LANG,
            createdRow : function (row) { $(row).addClass('nk-tb-item'); },
        });
    }

    function populateTypeSelect(selectId, types) {
        var $sel = $('#' + selectId);
        var cur  = $sel.val();
        $sel.find('option:not(:first)').remove();
        (types || []).forEach(function (t) {
            $sel.append('<option value="' + t.id + '">' + esc(t.name) + '</option>');
        });
        if (cur) $sel.val(cur);
    }

    /* ══════════════════════════════════════════════════════════════════════
       SECTION 1-5: ONE-TIME LOADS (no filter)
    ═══════════════════════════════════════════════════════════════════════ */

    // Engagement stats
    post('{{ route("admin.dashboard.getEngagementStats") }}').then(function (d) {
        document.getElementById('stat-total-active').textContent = d.total_active   || '0';
        document.getElementById('stat-free').textContent         = d.free_users     || '0';
        document.getElementById('stat-trial').textContent        = d.trial_users    || '0';
        document.getElementById('stat-paid').textContent         = d.paid_users     || '0';
        document.getElementById('stat-new-today').textContent    = d.new_users_today  || '0';
        document.getElementById('stat-new-month').textContent    = d.new_users_month  || '0';
        document.getElementById('stat-subs-today').textContent   = d.subs_today     || '0';
        document.getElementById('stat-subs-month').textContent   = d.subs_month     || '0';
        document.getElementById('stat-radio').textContent        = d.stream_radio        || '0';
        document.getElementById('stat-items').textContent        = d.stream_items        || '0';
        document.getElementById('stat-playlists').textContent    = d.stream_playlists    || '0';
        document.getElementById('stat-collections').textContent  = d.stream_collections  || '0';
    });

    // Daily user chart
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

    // Radio graph
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

    /* ══════════════════════════════════════════════════════════════════════
       SECTION 6 — SUBSCRIPTIONS
    ═══════════════════════════════════════════════════════════════════════ */

    var dtSubs = null, subsDateRange = '';

    function loadSubs() {
        post('{{ route("admin.dashboard.getSubscriptionsTable") }}', { date_range: subsDateRange }).then(function (d) {
            dtSubs = makeDT('subs-table', d.subscriptions, [
                { data: null },
                { data: 'user'       },
                { data: 'email'      },
                { data: 'plan'       },
                { data: 'type'       },
                { data: 'status'     },
                { data: 'start_date' },
                { data: 'end_date'   },
            ], [
                { targets: 0, orderable: false,
                  render: function (data, type, row, meta) { return meta.row + 1; } },
                { targets: 4, orderable: false,
                  render: function (data) {
                      var cls = data === 'Trial' ? 'bx-trial' : 'bx-paid';
                      return '<span class="bx ' + cls + '">' + esc(data) + '</span>';
                  } },
                { targets: 5, orderable: false,
                  render: function (data) {
                      var cls = data === 'Active' ? 'bx-active' : 'bx-inactive';
                      return '<span class="bx ' + cls + '">' + esc(data) + '</span>';
                  } },
            ], 6);
        });
    }

    $('#subs-date').flatpickr({
        mode: 'range', disableMobile: true,
        onClose: function (sel, dateStr) { subsDateRange = dateStr; loadSubs(); }
    });

    var subsSearchTimer;
    $('#subs-search').on('input', function () {
        var v = $(this).val();
        clearTimeout(subsSearchTimer);
        subsSearchTimer = setTimeout(function () { if (dtSubs) dtSubs.search(v).draw(); }, 400);
    });

    $('#subs-type').on('change', function () {
        if (dtSubs) dtSubs.column(4).search($(this).val()).draw();
    });
    $('#subs-status').on('change', function () {
        if (dtSubs) dtSubs.column(5).search($(this).val()).draw();
    });

    loadSubs();

    /* ══════════════════════════════════════════════════════════════════════
       SECTION 7 — ITEM STREAMS
    ═══════════════════════════════════════════════════════════════════════ */

    var dtItems = null, itemsDateRange = '', itemsTypesLoaded = false;

    function loadItems() {
        post('{{ route("admin.dashboard.getItemStreams") }}', { date_range: itemsDateRange }).then(function (d) {
            if (!itemsTypesLoaded) {
                populateTypeSelect('items-type', d.types);
                itemsTypesLoaded = true;
            }
            dtItems = makeDT('items-table', d.items, [
                { data: null         },
                { data: 'type_name'  },
                { data: 'title'      },
                { data: 'author'     },
                { data: 'total'      },
                { data: 'last_played'},
            ], [
                { targets: 0, orderable: false,
                  render: function (data, type, row, meta) { return meta.row + 1; } },
                { targets: 1, orderable: false,
                  render: function (data) {
                      return data ? '<span class="bx bx-type">' + esc(data) + '</span>' : '—';
                  } },
                { targets: 4, render: function (data) { return '<strong>' + (data || 0) + '</strong>'; } },
                { targets: 5, render: function (data) { return data ? String(data).substring(0, 16) : '—'; } },
            ], 4);
        });
    }

    $('#items-date').flatpickr({
        mode: 'range', disableMobile: true,
        onClose: function (sel, dateStr) { itemsDateRange = dateStr; loadItems(); }
    });

    var itemsSearchTimer;
    $('#items-search').on('input', function () {
        var v = $(this).val();
        clearTimeout(itemsSearchTimer);
        itemsSearchTimer = setTimeout(function () { if (dtItems) dtItems.search(v).draw(); }, 400);
    });

    $('#items-type').on('change', function () {
        if (dtItems) dtItems.column(1).search($(this).val()).draw();
    });

    loadItems();

    /* ══════════════════════════════════════════════════════════════════════
       SECTION 8 — PLAYLIST STREAMS
    ═══════════════════════════════════════════════════════════════════════ */

    var dtPlists = null, plistsDateRange = '', plistsTypesLoaded = false;

    function loadPlists() {
        post('{{ route("admin.dashboard.getPlaylistStreams") }}', { date_range: plistsDateRange }).then(function (d) {
            if (!plistsTypesLoaded) {
                populateTypeSelect('plists-type', d.types);
                plistsTypesLoaded = true;
            }
            dtPlists = makeDT('plists-table', d.playlists, [
                { data: null         },
                { data: 'type_name'  },
                { data: 'name'       },
                { data: 'total'      },
                { data: 'last_played'},
            ], [
                { targets: 0, orderable: false,
                  render: function (data, type, row, meta) { return meta.row + 1; } },
                { targets: 1, orderable: false,
                  render: function (data) {
                      return data ? '<span class="bx bx-type">' + esc(data) + '</span>' : '—';
                  } },
                { targets: 3, render: function (data) { return '<strong>' + (data || 0) + '</strong>'; } },
                { targets: 4, render: function (data) { return data ? String(data).substring(0, 16) : '—'; } },
            ], 3);
        });
    }

    $('#plists-date').flatpickr({
        mode: 'range', disableMobile: true,
        onClose: function (sel, dateStr) { plistsDateRange = dateStr; loadPlists(); }
    });

    var plistsSearchTimer;
    $('#plists-search').on('input', function () {
        var v = $(this).val();
        clearTimeout(plistsSearchTimer);
        plistsSearchTimer = setTimeout(function () { if (dtPlists) dtPlists.search(v).draw(); }, 400);
    });

    $('#plists-type').on('change', function () {
        if (dtPlists) dtPlists.column(1).search($(this).val()).draw();
    });

    loadPlists();

    /* ══════════════════════════════════════════════════════════════════════
       SECTION 9 — COLLECTION STREAMS
    ═══════════════════════════════════════════════════════════════════════ */

    var dtColls = null, collsDateRange = '', collsTypesLoaded = false;

    function loadColls() {
        post('{{ route("admin.dashboard.getCollectionStreams") }}', { date_range: collsDateRange }).then(function (d) {
            if (!collsTypesLoaded) {
                populateTypeSelect('colls-type', d.types);
                collsTypesLoaded = true;
            }
            dtColls = makeDT('colls-table', d.collections, [
                { data: null         },
                { data: 'type_name'  },
                { data: 'name'       },
                { data: 'total'      },
                { data: 'last_played'},
            ], [
                { targets: 0, orderable: false,
                  render: function (data, type, row, meta) { return meta.row + 1; } },
                { targets: 1, orderable: false,
                  render: function (data) {
                      return data ? '<span class="bx bx-type">' + esc(data) + '</span>' : '—';
                  } },
                { targets: 3, render: function (data) { return '<strong>' + (data || 0) + '</strong>'; } },
                { targets: 4, render: function (data) { return data ? String(data).substring(0, 16) : '—'; } },
            ], 3);
        });
    }

    $('#colls-date').flatpickr({
        mode: 'range', disableMobile: true,
        onClose: function (sel, dateStr) { collsDateRange = dateStr; loadColls(); }
    });

    var collsSearchTimer;
    $('#colls-search').on('input', function () {
        var v = $(this).val();
        clearTimeout(collsSearchTimer);
        collsSearchTimer = setTimeout(function () { if (dtColls) dtColls.search(v).draw(); }, 400);
    });

    $('#colls-type').on('change', function () {
        if (dtColls) dtColls.column(1).search($(this).val()).draw();
    });

    loadColls();

    /* ══════════════════════════════════════════════════════════════════════
       SECTION 10 — BANNER CLICKS (static, no date filter)
    ═══════════════════════════════════════════════════════════════════════ */

    var dtBanners = null;

    post('{{ route("admin.dashboard.getBannerClickStats") }}').then(function (d) {
        dtBanners = makeDT('banners-table', d.banners, [
            { data: null         },
            { data: 'image_path' },
            { data: 'name'       },
            { data: 'status'     },
            { data: 'clicks'     },
            { data: 'created_at' },
        ], [
            { targets: 0, orderable: false,
              render: function (data, type, row, meta) { return meta.row + 1; } },
            { targets: 1, orderable: false,
              render: function (data) {
                  return data ? '<img src="' + esc(data) + '" class="thumb-img">' : '—';
              } },
            { targets: 3, orderable: false,
              render: function (data) {
                  var cls = data == 10 ? 'bx-active' : 'bx-inactive';
                  var lbl = data == 10 ? 'Active' : 'Inactive';
                  return '<span class="bx ' + cls + '">' + lbl + '</span>';
              } },
            { targets: 4, render: function (data) { return '<strong>' + (data || 0) + '</strong>'; } },
        ], 4);
    });

    var bannersSearchTimer;
    $('#banners-search').on('input', function () {
        var v = $(this).val();
        clearTimeout(bannersSearchTimer);
        bannersSearchTimer = setTimeout(function () { if (dtBanners) dtBanners.search(v).draw(); }, 400);
    });
    $('#banners-status').on('change', function () {
        if (dtBanners) dtBanners.column(3).search($(this).val()).draw();
    });

    /* ══════════════════════════════════════════════════════════════════════
       SECTION 11 — POPUP CLICKS (static, no date filter)
    ═══════════════════════════════════════════════════════════════════════ */

    var dtPopups = null;

    post('{{ route("admin.dashboard.getPopAnnouncementClickStats") }}').then(function (d) {
        dtPopups = makeDT('popups-table', d.popups, [
            { data: null         },
            { data: 'image_path' },
            { data: 'title'      },
            { data: 'status'     },
            { data: 'clicks'     },
            { data: 'created_at' },
        ], [
            { targets: 0, orderable: false,
              render: function (data, type, row, meta) { return meta.row + 1; } },
            { targets: 1, orderable: false,
              render: function (data) {
                  return data ? '<img src="' + esc(data) + '" class="thumb-img">' : '—';
              } },
            { targets: 3, orderable: false,
              render: function (data) {
                  var cls = data == 10 ? 'bx-active' : 'bx-inactive';
                  var lbl = data == 10 ? 'Active' : 'Inactive';
                  return '<span class="bx ' + cls + '">' + lbl + '</span>';
              } },
            { targets: 4, render: function (data) { return '<strong>' + (data || 0) + '</strong>'; } },
        ], 4);
    });

    var popupsSearchTimer;
    $('#popups-search').on('input', function () {
        var v = $(this).val();
        clearTimeout(popupsSearchTimer);
        popupsSearchTimer = setTimeout(function () { if (dtPopups) dtPopups.search(v).draw(); }, 400);
    });
    $('#popups-status').on('change', function () {
        if (dtPopups) dtPopups.column(3).search($(this).val()).draw();
    });

})();
</script>
