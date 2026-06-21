<style>
    .dt-buttons { display: none }
    .apexcharts-datalabels { display: none }
    .page-title { color: #ae4342; margin-bottom: -10px; }

    /* ── Stat cards ── */
    .stat-card .stat-value { font-size: 1.75rem; font-weight: 700; color: #364a63; }
    .stat-card .stat-label { font-size: .78rem; color: #8094ae; text-transform: uppercase; letter-spacing: .05em; }
    .stat-card .stat-icon  { width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }

    /* ── Section headings ── */
    .section-title { font-size: .9rem; font-weight: 600; color: #364a63; border-left: 3px solid #ae4342; padding-left: 10px; margin-bottom: 16px; }

    /* ── Stream summary mini-cards ── */
    .stream-mini { padding: 14px 18px; display: flex; align-items: center; gap: 12px; }
    .stream-mini .sm-icon  { width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; flex-shrink: 0; }
    .stream-mini .sm-val   { font-size: 1.4rem; font-weight: 700; color: #364a63; line-height: 1; }
    .stream-mini .sm-label { font-size: .72rem; color: #8094ae; text-transform: uppercase; letter-spacing: .05em; }

    /* ── Period filter ── */
    .period-bar { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; }
    .period-bar .period-label { font-size: .78rem; color: #8094ae; font-weight: 600; text-transform: uppercase; letter-spacing: .05em; margin-right: 4px; }
    .btn-period { font-size: .78rem; padding: 4px 12px; border-radius: 20px; border: 1px solid #dee2e6; background: #fff; color: #526484; cursor: pointer; transition: all .15s; }
    .btn-period:hover   { border-color: #ae4342; color: #ae4342; }
    .btn-period.active  { background: #ae4342; border-color: #ae4342; color: #fff; }

    /* ── Sub-section label inside type card ── */
    .type-section-header { background: #f7f7f9; padding: 10px 16px; font-weight: 600; font-size: .85rem; color: #364a63; border-bottom: 1px solid #e5e9f0; }
    .sub-label { font-size: .75rem; font-weight: 600; color: #8094ae; text-transform: uppercase; letter-spacing: .06em; border-bottom: 1px solid #e5e9f0; padding-bottom: 6px; margin-bottom: 8px; }
    .type-card { border-top: 3px solid #ae4342; }

    /* ── Table shared ── */
    .dash-table th, .dash-table td { vertical-align: middle !important; font-size: .82rem; }
    .dash-table .thumb { width: 60px; height: 36px; object-fit: cover; border-radius: 4px; }

    /* ── Badges ── */
    .b-active  { background: #e8f5e9; color: #2e7d32; border-radius: 4px; padding: 2px 8px; font-size: .75rem; font-weight: 600; }
    .b-inactive { background: #fce4ec; color: #b71c1c; border-radius: 4px; padding: 2px 8px; font-size: .75rem; font-weight: 600; }
    .b-paid   { background: #e8f5e9; color: #2e7d32; border-radius: 4px; padding: 2px 8px; font-size: .75rem; font-weight: 600; }
    .b-trial  { background: #fff3e0; color: #e65100; border-radius: 4px; padding: 2px 8px; font-size: .75rem; font-weight: 600; }
    .b-free   { background: #e5edff; color: #3c58d0; border-radius: 4px; padding: 2px 8px; font-size: .75rem; font-weight: 600; }
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
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary-dim text-primary"><em class="icon ni ni-users"></em></div>
                    <div><div class="stat-value" id="stat-total-active">—</div><div class="stat-label">Total Active</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:#e5edff;color:#3c58d0"><em class="icon ni ni-user"></em></div>
                    <div><div class="stat-value" id="stat-free">—</div><div class="stat-label">Free</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:#fff3e0;color:#e65100"><em class="icon ni ni-clock"></em></div>
                    <div><div class="stat-value" id="stat-trial">—</div><div class="stat-label">Trial</div></div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32"><em class="icon ni ni-star"></em></div>
                    <div><div class="stat-value" id="stat-paid">—</div><div class="stat-label">Paid</div></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ SECTION 2 — NEW USERS + SUBSCRIPTIONS COUNTS ════════════════════════ --}}
<div class="nk-block mb-4">
    <div class="row g-3">
        <div class="col-12 col-md-6">
            <p class="section-title">New Users</p>
            <div class="row g-3">
                <div class="col-6">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon bg-info-dim text-info"><em class="icon ni ni-user-add"></em></div>
                            <div><div class="stat-value" id="stat-new-today">—</div><div class="stat-label">Today</div></div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon bg-info-dim text-info"><em class="icon ni ni-user-add"></em></div>
                            <div><div class="stat-value" id="stat-new-month">—</div><div class="stat-label">This Month</div></div>
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
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon" style="background:#f3e5f5;color:#7b1fa2"><em class="icon ni ni-check-circle"></em></div>
                            <div><div class="stat-value" id="stat-subs-today">—</div><div class="stat-label">Today</div></div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon" style="background:#f3e5f5;color:#7b1fa2"><em class="icon ni ni-check-circle"></em></div>
                            <div><div class="stat-value" id="stat-subs-month">—</div><div class="stat-label">This Month</div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ═══ SECTION 3 — DAILY USER CHART ══════════════════════════════════════════ --}}
<div class="nk-block mb-4">
    <div class="card">
        <div class="card-inner">
            <p class="section-title mb-3">Active Users by Type (Last 30 Days)</p>
            <div id="chart-daily-users"></div>
        </div>
    </div>
</div>

{{-- ═══ SECTION 4 — STREAM SUMMARY MINI-CARDS ═════════════════════════════════ --}}
<div class="nk-block mb-4">
    <p class="section-title">Streaming Activity (All Time)</p>
    <div class="row g-3">
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body stream-mini">
                <div class="sm-icon" style="background:#fce4ec;color:#c62828"><em class="icon ni ni-signal"></em></div>
                <div><div class="sm-val" id="stat-radio">—</div><div class="sm-label">Radio</div></div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body stream-mini">
                <div class="sm-icon bg-warning-dim text-warning"><em class="icon ni ni-music"></em></div>
                <div><div class="sm-val" id="stat-items">—</div><div class="sm-label">Items</div></div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body stream-mini">
                <div class="sm-icon bg-success-dim text-success"><em class="icon ni ni-list"></em></div>
                <div><div class="sm-val" id="stat-playlists">—</div><div class="sm-label">Playlists</div></div>
            </div></div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body stream-mini">
                <div class="sm-icon bg-primary-dim text-primary"><em class="icon ni ni-folder"></em></div>
                <div><div class="sm-val" id="stat-collections">—</div><div class="sm-label">Collections</div></div>
            </div></div>
        </div>
    </div>
</div>

{{-- ═══ SECTION 5 — RADIO STREAMS GRAPH ═══════════════════════════════════════ --}}
<div class="nk-block mb-4">
    <div class="card">
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

{{-- ═══════════════════════════════════════════════════════════════════════════
     GLOBAL PERIOD SELECTOR  (applies to sections 6, 7, 8, 9)
══════════════════════════════════════════════════════════════════════════════ --}}
<div class="nk-block mb-2">
    <div class="period-bar">
        <span class="period-label">View period:</span>
        <button class="btn-period" data-period="today">Today</button>
        <button class="btn-period active" data-period="month">This Month</button>
        <button class="btn-period" data-period="year">This Year</button>
        <button class="btn-period" data-period="all">All Time</button>
    </div>
</div>

{{-- ═══ SECTION 6 — SUBSCRIPTIONS DATATABLE ══════════════════════════════════ --}}
<div class="nk-block mb-4">
    <div class="card">
        <div class="card-inner">
            <p class="section-title mb-3">Subscription Records</p>
            <div class="table-responsive">
                <table class="table table-bordered dash-table" id="subs-table">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Plan</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                        </tr>
                    </thead>
                    <tbody id="subs-tbody">
                        <tr><td colspan="8" class="text-center text-muted">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ═══ SECTION 7 — STREAMS BY CONTENT TYPE (DATATABLES) ══════════════════════ --}}
<div class="nk-block mb-4" id="streams-by-type-wrapper">
    <p class="section-title">Streams by Content Type</p>
    <div id="streams-by-type-container">
        <div class="text-center text-muted py-4">Loading…</div>
    </div>
</div>

{{-- ═══ SECTION 8 — BANNER CLICKS ══════════════════════════════════════════════ --}}
<div class="nk-block mb-4">
    <div class="card">
        <div class="card-inner">
            <p class="section-title mb-3">Banner Clicks</p>
            <div class="table-responsive">
                <table class="table table-bordered dash-table" id="banner-table">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Status</th>
                            <th>Clicks</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody id="banner-tbody">
                        <tr><td colspan="5" class="text-center text-muted">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ═══ SECTION 9 — POP ANNOUNCEMENT CLICKS ════════════════════════════════════ --}}
<div class="nk-block mb-4">
    <div class="card">
        <div class="card-inner">
            <p class="section-title mb-3">Pop Announcement Clicks</p>
            <div class="table-responsive">
                <table class="table table-bordered dash-table" id="popup-table">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Clicks</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody id="popup-tbody">
                        <tr><td colspan="6" class="text-center text-muted">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var csrf    = '{{ csrf_token() }}';
    var dtInst  = {};  // keyed by table id

    /* ── Utilities ─────────────────────────────────────────────────────────── */

    function post(url, body) {
        return fetch(url, {
            method : 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body   : JSON.stringify(body || {}),
        }).then(function (r) { return r.json(); });
    }

    function esc(s) {
        return s == null ? '' : String(s)
            .replace(/&/g,'&amp;').replace(/</g,'&lt;')
            .replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function fmt(s) { return s ? String(s).substring(0, 16) : '—'; }

    function badge(val, map) {
        var cls = map[val] || 'b-inactive';
        return '<span class="' + cls + '">' + esc(val) + '</span>';
    }

    /* ── DataTable helpers ─────────────────────────────────────────────────── */

    function dtInit(id, opts) {
        if ($.fn.DataTable.isDataTable('#' + id)) {
            $('#' + id).DataTable().destroy();
        }
        dtInst[id] = $('#' + id).DataTable($.extend({
            pageLength : 10,
            lengthMenu : [10, 25, 50, 100],
            ordering   : true,
            searching  : true,
            responsive : true,
            language   : { emptyTable: 'No data for this period.' },
        }, opts || {}));
    }

    function dtDestroy(id) {
        if (dtInst[id]) { dtInst[id].destroy(); delete dtInst[id]; }
    }

    /* ── One-time loads (no period) ────────────────────────────────────────── */

    // Engagement summary cards
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
            fill       : { type: 'gradient', gradient: { opacityFrom: 0.25, opacityTo: 0.02 } },
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
            tooltip    : { y: { formatter: function (v) { return v + (v !== 1 ? ' streams' : ' stream'); } } },
        }).render();
    });

    // Banner clicks (static — no period)
    post('{{ route("admin.dashboard.getBannerClickStats") }}').then(function (d) {
        var rows = (d.banners || []).map(function (b, i) {
            var img = b.image_path ? '<img src="' + esc(b.image_path) + '" class="thumb">' : '—';
            return '<tr><td>' + (i+1) + '</td><td>' + img + '</td>'
                 + '<td>' + badge(b.status == 10 ? 'Active' : 'Inactive', {'Active':'b-active','Inactive':'b-inactive'}) + '</td>'
                 + '<td><strong>' + (b.clicks || 0) + '</strong></td>'
                 + '<td>' + esc(b.created_at || '—') + '</td></tr>';
        });
        document.getElementById('banner-tbody').innerHTML = rows.join('') || '<tr><td colspan="5" class="text-center text-muted">No banners found.</td></tr>';
        dtInit('banner-table', { order: [[3, 'desc']] });
    });

    // Popup clicks (static — no period)
    post('{{ route("admin.dashboard.getPopAnnouncementClickStats") }}').then(function (d) {
        var rows = (d.popups || []).map(function (p, i) {
            var img = p.image_path ? '<img src="' + esc(p.image_path) + '" class="thumb">' : '—';
            return '<tr><td>' + (i+1) + '</td><td>' + img + '</td>'
                 + '<td>' + esc(p.title || '—') + '</td>'
                 + '<td>' + badge(p.status == 10 ? 'Active' : 'Inactive', {'Active':'b-active','Inactive':'b-inactive'}) + '</td>'
                 + '<td><strong>' + (p.clicks || 0) + '</strong></td>'
                 + '<td>' + esc(p.created_at || '—') + '</td></tr>';
        });
        document.getElementById('popup-tbody').innerHTML = rows.join('') || '<tr><td colspan="6" class="text-center text-muted">No announcements found.</td></tr>';
        dtInit('popup-table', { order: [[4, 'desc']] });
    });

    /* ── Period-sensitive sections ─────────────────────────────────────────── */

    function loadSubscriptions(period) {
        dtDestroy('subs-table');
        document.getElementById('subs-tbody').innerHTML = '<tr><td colspan="8" class="text-center text-muted">Loading…</td></tr>';

        post('{{ route("admin.dashboard.getSubscriptionsTable") }}', { period: period }).then(function (d) {
            var rows = (d.subscriptions || []).map(function (s, i) {
                return '<tr>'
                    + '<td>' + (i+1) + '</td>'
                    + '<td>' + esc(s.user) + '</td>'
                    + '<td>' + esc(s.email) + '</td>'
                    + '<td>' + esc(s.plan) + '</td>'
                    + '<td>' + badge(s.type, { 'Paid': 'b-paid', 'Trial': 'b-trial' }) + '</td>'
                    + '<td>' + badge(s.status, { 'Active': 'b-active', 'Inactive': 'b-inactive' }) + '</td>'
                    + '<td>' + esc(s.start_date) + '</td>'
                    + '<td>' + esc(s.end_date) + '</td>'
                    + '</tr>';
            });
            document.getElementById('subs-tbody').innerHTML = rows.join('') || '<tr><td colspan="8" class="text-center text-muted">No subscriptions for this period.</td></tr>';
            dtInit('subs-table', { order: [[6, 'desc']] });
        });
    }

    function loadStreamsByType(period) {
        // Destroy any existing sub-table DataTable instances
        Object.keys(dtInst).forEach(function (k) {
            if (k.startsWith('st-')) { dtInst[k].destroy(); delete dtInst[k]; }
        });
        var container = document.getElementById('streams-by-type-container');
        container.innerHTML = '<div class="text-center text-muted py-3">Loading…</div>';

        post('{{ route("admin.dashboard.getStreamsByType") }}', { period: period }).then(function (d) {
            if (!d.types || !d.types.length) {
                container.innerHTML = '<div class="text-center text-muted py-4">No stream data for this period.</div>';
                return;
            }

            container.innerHTML = d.types.map(function (type, ti) {
                var uid = 'type-' + ti;
                return '<div class="card mb-3 type-card">'
                    + '<div class="type-section-header"><em class="icon ni ni-tag me-1"></em>' + esc(type.name) + '</div>'
                    + '<div class="card-body">'
                    + '<div class="row g-4">'

                    // Items
                    + '<div class="col-12 col-xl-4">'
                    + '<div class="sub-label"><em class="icon ni ni-music me-1"></em>Items</div>'
                    + subTable(uid + '-items', type.items, ['Title','Author','Plays','Last Played'], function (r) {
                        return '<td>' + esc(r.title || '—') + '</td>'
                             + '<td>' + esc(r.author || '—') + '</td>'
                             + '<td>' + (r.total || 0) + '</td>'
                             + '<td>' + fmt(r.last_played) + '</td>';
                    })
                    + '</div>'

                    // Playlists
                    + '<div class="col-12 col-xl-4">'
                    + '<div class="sub-label"><em class="icon ni ni-list me-1"></em>Playlists</div>'
                    + subTable(uid + '-plists', type.playlists, ['Name','Plays','Last Played'], function (r) {
                        return '<td>' + esc(r.name || '—') + '</td>'
                             + '<td>' + (r.total || 0) + '</td>'
                             + '<td>' + fmt(r.last_played) + '</td>';
                    })
                    + '</div>'

                    // Collections
                    + '<div class="col-12 col-xl-4">'
                    + '<div class="sub-label"><em class="icon ni ni-folder me-1"></em>Collections</div>'
                    + subTable(uid + '-colls', type.collections, ['Name','Plays','Last Played'], function (r) {
                        return '<td>' + esc(r.name || '—') + '</td>'
                             + '<td>' + (r.total || 0) + '</td>'
                             + '<td>' + fmt(r.last_played) + '</td>';
                    })
                    + '</div>'

                    + '</div></div></div>';
            }).join('');

            // Init DataTables for each sub-table
            d.types.forEach(function (type, ti) {
                var uid = 'type-' + ti;
                var def = { pageLength: 5, lengthMenu: [5, 10, 25], order: [[2, 'desc']] };
                if (type.items       && type.items.length)       dtInit('st-' + uid + '-items',  def);
                if (type.playlists   && type.playlists.length)   dtInit('st-' + uid + '-plists', def);
                if (type.collections && type.collections.length) dtInit('st-' + uid + '-colls',  def);
            });
        });
    }

    function subTable(id, rows, headers, rowFn) {
        if (!rows || !rows.length) {
            return '<p class="text-muted small">No data.</p>';
        }
        var thead = headers.map(function (h) { return '<th>' + h + '</th>'; }).join('');
        var tbody = rows.map(function (r) { return '<tr>' + rowFn(r) + '</tr>'; }).join('');
        return '<div class="table-responsive">'
             + '<table class="table table-sm dash-table" id="st-' + id + '">'
             + '<thead class="table-light"><tr>' + thead + '</tr></thead>'
             + '<tbody>' + tbody + '</tbody>'
             + '</table></div>';
    }

    /* ── Period buttons ────────────────────────────────────────────────────── */

    var currentPeriod = 'month';

    function applyPeriod(period) {
        currentPeriod = period;
        document.querySelectorAll('.btn-period').forEach(function (btn) {
            btn.classList.toggle('active', btn.dataset.period === period);
        });
        loadSubscriptions(period);
        loadStreamsByType(period);
    }

    document.querySelectorAll('.btn-period').forEach(function (btn) {
        btn.addEventListener('click', function () { applyPeriod(this.dataset.period); });
    });

    // Initial load with default period (This Month)
    applyPeriod('month');

})();
</script>
