<style>
    .dt-buttons{
        display: none
    }
    .dt-length-0{
        margin-left: 10px
    }
    .apexcharts-datalabels{
        display: none
    }
    .page-title{
        color: #ae4342;
        margin-bottom: -10px;
    }
    .stat-card .stat-value {
        font-size: 1.75rem;
        font-weight: 700;
        color: #364a63;
    }
    .stat-card .stat-label {
        font-size: 0.8rem;
        color: #8094ae;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .stat-card .stat-icon {
        width: 48px;
        height: 48px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
    }
    .section-title {
        font-size: 1rem;
        font-weight: 600;
        color: #364a63;
        border-left: 3px solid #ae4342;
        padding-left: 10px;
        margin-bottom: 16px;
    }
    .click-table th, .click-table td {
        vertical-align: middle !important;
    }
    .click-table .banner-thumb {
        width: 60px;
        height: 36px;
        object-fit: cover;
        border-radius: 4px;
    }
    .badge-free   { background: #e5edff; color: #3c58d0; }
    .badge-trial  { background: #fff3e0; color: #e65100; }
    .badge-paid   { background: #e8f5e9; color: #2e7d32; }
    .badge-status-active   { background: #e8f5e9; color: #2e7d32; }
    .badge-status-suspended { background: #fce4ec; color: #b71c1c; }
</style>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title">{{ __( 'template.dashboard' ) }}</h3>
        </div>
    </div>
</div>

{{-- ===================== SECTION 1+2: USER OVERVIEW ===================== --}}
<div class="nk-block mb-4">
    <p class="section-title">Active Users Overview</p>
    <div class="row g-3" id="user-stat-cards">
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary-dim text-primary"><em class="icon ni ni-users"></em></div>
                    <div>
                        <div class="stat-value" id="stat-total-active">—</div>
                        <div class="stat-label">Total Active</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:#e5edff;color:#3c58d0"><em class="icon ni ni-user"></em></div>
                    <div>
                        <div class="stat-value" id="stat-free">—</div>
                        <div class="stat-label">Free</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:#fff3e0;color:#e65100"><em class="icon ni ni-clock"></em></div>
                    <div>
                        <div class="stat-value" id="stat-trial">—</div>
                        <div class="stat-label">Trial</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:#e8f5e9;color:#2e7d32"><em class="icon ni ni-star"></em></div>
                    <div>
                        <div class="stat-value" id="stat-paid">—</div>
                        <div class="stat-label">Paid</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===================== SECTION 3+4: NEW USERS & SUBSCRIPTIONS ===================== --}}
<div class="nk-block mb-4">
    <div class="row g-3">
        <div class="col-12 col-md-6">
            <p class="section-title">New Users</p>
            <div class="row g-3">
                <div class="col-6">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon bg-info-dim text-info"><em class="icon ni ni-user-add"></em></div>
                            <div>
                                <div class="stat-value" id="stat-new-today">—</div>
                                <div class="stat-label">Today</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
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
        <div class="col-12 col-md-6">
            <p class="section-title">Subscriptions</p>
            <div class="row g-3">
                <div class="col-6">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon" style="background:#f3e5f5;color:#7b1fa2"><em class="icon ni ni-check-circle"></em></div>
                            <div>
                                <div class="stat-value" id="stat-subs-today">—</div>
                                <div class="stat-label">Today</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <div class="stat-icon" style="background:#f3e5f5;color:#7b1fa2"><em class="icon ni ni-check-circle"></em></div>
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

{{-- ===================== SECTION 1 CHART: DAILY USER BREAKDOWN ===================== --}}
<div class="nk-block mb-4">
    <div class="card">
        <div class="card-inner">
            <p class="section-title mb-3">Active Users by Type (Last 30 Days)</p>
            <div id="chart-daily-users"></div>
        </div>
    </div>
</div>

{{-- ===================== SECTION 5: STREAMING ===================== --}}
<div class="nk-block mb-4">
    <p class="section-title">Streaming Activity (All Time)</p>
    <div class="row g-3">
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon" style="background:#fce4ec;color:#c62828"><em class="icon ni ni-signal"></em></div>
                    <div>
                        <div class="stat-value" id="stat-radio">—</div>
                        <div class="stat-label">Radio Streams</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-warning-dim text-warning"><em class="icon ni ni-music"></em></div>
                    <div>
                        <div class="stat-value" id="stat-items">—</div>
                        <div class="stat-label">Item Streams</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-success-dim text-success"><em class="icon ni ni-list"></em></div>
                    <div>
                        <div class="stat-value" id="stat-playlists">—</div>
                        <div class="stat-label">Playlist Streams</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="stat-icon bg-primary-dim text-primary"><em class="icon ni ni-folder"></em></div>
                    <div>
                        <div class="stat-value" id="stat-collections">—</div>
                        <div class="stat-label">Collection Streams</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===================== SECTION 6: BANNER CLICKS ===================== --}}
<div class="nk-block mb-4">
    <div class="card">
        <div class="card-inner">
            <p class="section-title mb-3">Banner Clicks</p>
            <div class="table-responsive">
                <table class="table table-bordered click-table" id="banner-clicks-table">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Image</th>
                            <th>Status</th>
                            <th>Clicks</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody id="banner-clicks-body">
                        <tr><td colspan="6" class="text-center text-muted">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- ===================== SECTION 7: POPUP CLICKS ===================== --}}
<div class="nk-block mb-4">
    <div class="card">
        <div class="card-inner">
            <p class="section-title mb-3">Pop Announcement Clicks</p>
            <div class="table-responsive">
                <table class="table table-bordered click-table" id="popup-clicks-table">
                    <thead class="table-light">
                        <tr>
                            <th>No.</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Status</th>
                            <th>Clicks</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody id="popup-clicks-body">
                        <tr><td colspan="6" class="text-center text-muted">Loading…</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const csrfToken = '{{ csrf_token() }}';

    function post(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(data || {}),
        }).then(r => r.json());
    }

    // Load engagement stats (sections 1-5)
    post('{{ route("admin.dashboard.getEngagementStats") }}').then(function (data) {
        document.getElementById('stat-total-active').textContent = data.total_active  ?? '—';
        document.getElementById('stat-free').textContent         = data.free_users    ?? '—';
        document.getElementById('stat-trial').textContent        = data.trial_users   ?? '—';
        document.getElementById('stat-paid').textContent         = data.paid_users    ?? '—';
        document.getElementById('stat-new-today').textContent    = data.new_users_today  ?? '—';
        document.getElementById('stat-new-month').textContent    = data.new_users_month  ?? '—';
        document.getElementById('stat-subs-today').textContent   = data.subs_today    ?? '—';
        document.getElementById('stat-subs-month').textContent   = data.subs_month    ?? '—';
        document.getElementById('stat-radio').textContent        = data.stream_radio        ?? '—';
        document.getElementById('stat-items').textContent        = data.stream_items        ?? '—';
        document.getElementById('stat-playlists').textContent    = data.stream_playlists    ?? '—';
        document.getElementById('stat-collections').textContent  = data.stream_collections  ?? '—';
    });

    // Load daily user chart
    post('{{ route("admin.dashboard.getDailyUserStats") }}').then(function (data) {
        if (typeof ApexCharts === 'undefined') return;
        var options = {
            chart: { type: 'area', height: 280, toolbar: { show: false }, sparkline: { enabled: false } },
            series: [
                { name: 'Free',  data: data.free_data  || [] },
                { name: 'Trial', data: data.trial_data || [] },
                { name: 'Paid',  data: data.paid_data  || [] },
            ],
            xaxis: { categories: data.xAxis || [], labels: { rotate: -45, style: { fontSize: '11px' } } },
            colors: ['#3c58d0', '#e65100', '#2e7d32'],
            fill: { opacity: 0.15 },
            stroke: { curve: 'smooth', width: 2 },
            legend: { position: 'top' },
            tooltip: { x: { show: true } },
            dataLabels: { enabled: false },
        };
        new ApexCharts(document.getElementById('chart-daily-users'), options).render();
    });

    // Load banner click stats
    post('{{ route("admin.dashboard.getBannerClickStats") }}').then(function (data) {
        var tbody = document.getElementById('banner-clicks-body');
        if (!data.banners || data.banners.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No banners found.</td></tr>';
            return;
        }
        tbody.innerHTML = data.banners.map(function (b, i) {
            var statusBadge = b.status == 10
                ? '<span class="badge badge-status-active">Active</span>'
                : '<span class="badge badge-status-suspended">Suspended</span>';
            var img = b.image_path
                ? '<img src="' + b.image_path + '" class="banner-thumb" alt="">'
                : '<span class="text-muted">—</span>';
            return '<tr>'
                + '<td>' + (i + 1) + '</td>'
                + '<td>' + img + '</td>'
                + '<td>' + statusBadge + '</td>'
                + '<td><strong>' + (b.clicks || 0) + '</strong></td>'
                + '<td>' + (b.created_at || '—') + '</td>'
                + '</tr>';
        }).join('');
    });

    // Load popup click stats
    post('{{ route("admin.dashboard.getPopAnnouncementClickStats") }}').then(function (data) {
        var tbody = document.getElementById('popup-clicks-body');
        if (!data.popups || data.popups.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center text-muted">No announcements found.</td></tr>';
            return;
        }
        tbody.innerHTML = data.popups.map(function (p, i) {
            var statusBadge = p.status == 10
                ? '<span class="badge badge-status-active">Active</span>'
                : '<span class="badge badge-status-suspended">Suspended</span>';
            var img = p.image_path
                ? '<img src="' + p.image_path + '" class="banner-thumb" alt="">'
                : '<span class="text-muted">—</span>';
            return '<tr>'
                + '<td>' + (i + 1) + '</td>'
                + '<td>' + img + '</td>'
                + '<td>' + (p.title || '—') + '</td>'
                + '<td>' + statusBadge + '</td>'
                + '<td><strong>' + (p.clicks || 0) + '</strong></td>'
                + '<td>' + (p.created_at || '—') + '</td>'
                + '</tr>';
        }).join('');
    });
})();
</script>
