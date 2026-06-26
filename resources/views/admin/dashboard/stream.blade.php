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

    .bx {
        display: inline-block;
        border-radius: 4px;
        padding: 2px 8px;
        font-size: .75rem;
        font-weight: 600;
    }

    .bx-inactive { background: #fce4ec; color: #b71c1c; }

    .dt-buttons { display: none; }

    .dt-length select { margin-right: 8px; }
</style>

<div class="nk-block-head nk-block-head-sm">
    <div class="nk-block-between flex-wrap gap-2">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title" id="stream-page-title">Streams</h3>
        </div>
    </div>
</div>

<div id="stream-content">
    <div class="text-muted py-4 text-center">Loading…</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var csrf      = '{{ csrf_token() }}';
    var ACTIVE    = '{{ $activePage ?? 'radio' }}';

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

    function makeButtons(key) {
        var chkId    = 'exportSelected-' + key;
        var copyCls  = 'buttons-copy-'   + key;
        var excelCls = 'buttons-excel-'  + key;
        var csvCls   = 'buttons-csv-'    + key;
        var pdfCls   = 'buttons-pdf-'    + key;

        function exportOpts() {
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
            { extend: 'copyHtml5',  className: 'd-none ' + copyCls,  exportOptions: exportOpts() },
            { text: '<i class="fa fa-copy"></i>',       className: 'btn btn-light',   titleAttr: 'Copy',           action: visibleAction(copyCls) },
            { extend: 'excelHtml5', className: 'd-none ' + excelCls, exportOptions: exportOpts() },
            { text: '<i class="fa fa-file-excel"></i>', className: 'btn btn-success', titleAttr: 'Export to EXCEL', action: visibleAction(excelCls) },
            { extend: 'csvHtml5',   className: 'd-none ' + csvCls,   exportOptions: exportOpts() },
            { text: '<i class="fa fa-file-csv"></i>',   className: 'btn btn-info',    titleAttr: 'Export to CSV',   action: visibleAction(csvCls) },
            { extend: 'pdfHtml5',   className: 'd-none ' + pdfCls,   exportOptions: exportOpts() },
            { text: '<i class="fa fa-file-pdf"></i>',   className: 'btn btn-danger',  titleAttr: 'Export to PDF',   action: visibleAction(pdfCls) },
        ];
    }

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

    function makeDT(id, data, columns, columnDefs, orderCol) {
        var key = id.replace(/-table$/, '').replace(/-/g, '');
        if ($.fn.DataTable.isDataTable('#' + id)) {
            $('#' + id).DataTable().destroy();
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
            drawCallback : function () {
                var api  = this.api();
                var info = api.page.info();
                api.column(0, { page: 'current' }).nodes().each(function (cell, i) {
                    cell.innerHTML = info.start + i + 1;
                });
            },
        });
    }

    function noColDef() {
        return { targets: 0, orderable: false, searchable: false, render: function () { return ''; } };
    }

    /* ── Route to correct page ───────────────────────────────────────── */

    var match = ACTIVE.match(/^(items|plists|colls)-t(\d+)$/);

    if (ACTIVE === 'radio') {
        initRadioPage();
    } else if (match) {
        initTypePage(match[1], parseInt(match[2]));
    } else {
        $('#stream-content').html('<div class="text-danger py-3">Unknown page.</div>');
    }

    /* ── Radio page ──────────────────────────────────────────────────── */

    function initRadioPage() {
        document.getElementById('stream-page-title').textContent = 'Radio Streams';

        $('#stream-content').html(
            '<div class="listing-filter" style="grid-template-columns:1fr 3fr">' +
            '<input type="text" class="form-control form-control-sm" placeholder="Search date range…" id="radio-date" style="background:#fff">' +
            '<input type="text" class="form-control form-control-sm" placeholder="Search user / email…" id="radio-search">' +
            '</div>' +
            '<div class="card card-bordered card-preview"><div class="card-inner">' +
            '<table class="table" style="width:100%" id="radio-table">' +
            '<thead><tr><th>No.</th><th>User</th><th>Email</th><th>Played At</th></tr></thead>' +
            '<tbody></tbody>' +
            '</table></div></div>'
        );

        var dtRadio = null, radioDateRange = '';

        function loadRadioTable() {
            post('{{ route("admin.dashboard.getRadioStreamTable") }}', { date_range: radioDateRange })
                .then(function (d) {
                    dtRadio = makeDT('radio-table', d.logs, [
                        { data: null        },
                        { data: 'user'      },
                        { data: 'email'     },
                        { data: 'played_at' },
                    ], [
                        noColDef(),
                        { targets: 1, render: function (data) {
                            return data === 'Guest'
                                ? '<span class="bx bx-inactive">Guest</span>'
                                : esc(data);
                        }},
                    ], 3);
                });
        }

        $('#radio-date').flatpickr({ mode: 'range', disableMobile: true,
            onClose: function (sel, dateStr) { radioDateRange = dateStr; loadRadioTable(); }
        });

        var radioTimer;
        $('#radio-search').on('input', function () {
            var v = $(this).val(); clearTimeout(radioTimer);
            radioTimer = setTimeout(function () { if (dtRadio) dtRadio.search(v).draw(); }, 400);
        });

        loadRadioTable();
    }

    /* ── Per-type page (items / plists / colls) ──────────────────────── */

    function initTypePage(prefix, typeId) {
        var TYPE_NAMES = { 'Song': 'Music' };

        var CFG = {
            items: {
                endpoint : '{{ route("admin.dashboard.getItemStreams") }}',
                dataKey  : 'items',
                thead    : '<th>No.</th><th>Title</th><th>Plays</th>',
                cols     : [{ data: null }, { data: 'title' }, { data: 'total' }],
                defs     : [noColDef(), { targets: 2, render: function (d) { return '<strong>' + (d || 0) + '</strong>'; } }],
                orderCol : 2,
                suffix   : 'Streams',
            },
            plists: {
                endpoint : '{{ route("admin.dashboard.getPlaylistStreams") }}',
                dataKey  : 'playlists',
                thead    : '<th>No.</th><th>Playlist Name</th><th>Plays</th>',
                cols     : [{ data: null }, { data: 'name' }, { data: 'total' }],
                defs     : [noColDef(), { targets: 2, render: function (d) { return '<strong>' + (d || 0) + '</strong>'; } }],
                orderCol : 2,
                suffix   : 'Playlists',
            },
            colls: {
                endpoint : '{{ route("admin.dashboard.getCollectionStreams") }}',
                dataKey  : 'collections',
                thead    : '<th>No.</th><th>Collection Name</th><th>Plays</th>',
                cols     : [{ data: null }, { data: 'name' }, { data: 'total' }],
                defs     : [noColDef(), { targets: 2, render: function (d) { return '<strong>' + (d || 0) + '</strong>'; } }],
                orderCol : 2,
                suffix   : 'Collections',
            },
        };

        var cfg = CFG[prefix];

        post(cfg.endpoint, {}).then(function (d) {
            var type     = (d.types || []).find(function (t) { return t.id == typeId; });
            var typeName = type ? (TYPE_NAMES[type.name] || type.name) : '';
            document.getElementById('stream-page-title').textContent = typeName + ' ' + cfg.suffix;

            var tableId  = prefix + 't' + typeId + 'table';
            var searchId = prefix + 't' + typeId + 'search';

            $('#stream-content').html(
                '<div class="listing-filter" style="grid-template-columns:1fr 3fr">' +
                '<input type="text" class="form-control form-control-sm" placeholder="Search…" id="' + searchId + '">' +
                '<div></div>' +
                '</div>' +
                '<div class="card card-bordered card-preview"><div class="card-inner">' +
                '<table class="table" style="width:100%" id="' + tableId + '">' +
                '<thead><tr>' + cfg.thead + '</tr></thead><tbody></tbody>' +
                '</table></div></div>'
            );

            var rows = (d[cfg.dataKey] || []).filter(function (r) { return r.type_id == typeId; });
            var dt   = makeDT(tableId, rows, cfg.cols, cfg.defs, cfg.orderCol);

            var timer;
            $('#' + searchId).on('input', function () {
                var v = $(this).val(); clearTimeout(timer);
                timer = setTimeout(function () { if (dt) dt.search(v).draw(); }, 400);
            });

        }).catch(function () {
            $('#stream-content').html('<div class="text-danger py-3">Failed to load.</div>');
        });
    }
});
</script>
