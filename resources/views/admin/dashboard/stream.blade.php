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

    .dt-length select { margin-right: 8px; }
</style>

<div class="nk-block-head nk-block-head-sm mb-4">
    <div class="nk-block-between flex-wrap gap-2">
        <div class="nk-block-head-content">
            <h3 class="nk-block-title page-title" id="stream-page-title">Streams</h3>
        </div>
    </div>
</div>

<div id="stream-content">
    <div class="text-muted py-4 text-center">Loading…</div>
</div>

<div class="modal fade" id="stream-detail-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="stream-detail-title">Stream Detail</h5>
                <a href="#" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <em class="icon ni ni-cross"></em>
                </a>
            </div>
            <div class="modal-body">
                <div class="listing-filter mb-2">
                    <input type="text" class="form-control form-control-sm" placeholder="Search date range…" id="stream-detail-date" style="background:#fff">
                </div>
                <div class="card card-bordered card-preview">
                    <div class="card-inner">
                        <table class="table" style="width:100%" id="stream-detail-table">
                            <thead><tr><th></th><th>No.</th><th>User</th><th>Email</th><th>Played At</th></tr></thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
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

    /* Build a descriptive, collision-safe export file name, e.g. "Radio_Streams_260730_143012" */
    function exportFileName(label) {
        var d = new Date();
        function p(n) { return (n < 10 ? '0' : '') + n; }
        var stamp = String(d.getFullYear()).slice(2) + p(d.getMonth() + 1) + p(d.getDate()) +
            '_' + p(d.getHours()) + p(d.getMinutes()) + p(d.getSeconds());
        return (label || 'Streams').replace(/\s+/g, '_') + '_' + stamp;
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

    function makeButtons(key, excludeLastCol, label) {
        var chkId    = 'exportSelected-' + key;
        var copyCls  = 'buttons-copy-'   + key;
        var excelCls = 'buttons-excel-'  + key;
        var csvCls   = 'buttons-csv-'    + key;
        var pdfCls   = 'buttons-pdf-'    + key;
        var fileName = function () { return exportFileName(label || key); };

        function exportOpts() {
            var rowNum = 0;
            return {
                modifier: { page: 'all' },
                orthogonal: 'export',
                rows: function (idx, data, node) {
                    if ($('#' + chkId).is(':checked')) {
                        return $(node).find('.select-row').is(':checked');
                    }
                    return true;
                },
                columns: excludeLastCol
                    ? ':not(:first-child):not(:last-child)'
                    : ':not(:first-child)',
                format: {
                    /* column index here is the real table column index (checkbox=0,
                       No.=1), not a position within the exported subset. */
                    header: function (data, column) {
                        if (column === 1) rowNum = 0;
                        return data;
                    },
                    body: function (data, row, column) {
                        return column === 1 ? ++rowNum : data;
                    },
                },
            };
        }

        function visibleAction(proxyClass) {
            /* This table's data lives fully in memory (no serverSide), and the hidden
               proxy button's exportOptions already use modifier:{page:'all'}, so the
               export already covers every row regardless of on-screen pagination —
               no need to flip page length to "All" and back. */
            return function () {
                $('.' + proxyClass).click();
            };
        }

        return [
            { extend: 'copyHtml5',  className: 'd-none ' + copyCls,  exportOptions: exportOpts() },
            { text: '<i class="fa fa-copy"></i>',       className: 'btn btn-light',   titleAttr: 'Copy',           action: visibleAction(copyCls) },
            { extend: 'excelHtml5', className: 'd-none ' + excelCls, title: fileName, exportOptions: exportOpts() },
            { text: '<i class="fa fa-file-excel"></i>', className: 'btn btn-success', titleAttr: 'Export to EXCEL', action: visibleAction(excelCls) },
            { extend: 'csvHtml5',   className: 'd-none ' + csvCls,   title: fileName, exportOptions: exportOpts() },
            { text: '<i class="fa fa-file-csv"></i>',   className: 'btn btn-info',    titleAttr: 'Export to CSV',   action: visibleAction(csvCls) },
            { extend: 'pdfHtml5',   className: 'd-none ' + pdfCls,   title: fileName, exportOptions: exportOpts(), orientation: 'landscape',
                customize: function (doc) {
                    var tableIndex = doc.content.findIndex(function (item) { return item.table; });
                    if (tableIndex > -1) {
                        var body     = doc.content[tableIndex].table.body;
                        var colCount = body[0].length;
                        doc.content[tableIndex].table.widths = Array(colCount).fill('*');
                        body.forEach(function (row) {
                            if (row[colCount - 1]) row[colCount - 1].alignment = 'center';
                        });
                    }
                } },
            { text: '<i class="fa fa-file-pdf"></i>',   className: 'btn btn-danger',  titleAttr: 'Export to PDF',   action: visibleAction(pdfCls) },
        ];
    }

    function makeInitComplete(key) {
        var chkId = 'exportSelected-' + key;
        return function () {
            var api = this.api();
            var $container = $(api.table().container());
            $container.find('.export-check-wrapper').remove();
            $container.find('.dt-buttons').append(
                '<div class="my-3 export-check-wrapper">' +
                '<input type="checkbox" id="' + chkId + '" name="' + chkId + '">' +
                '<label for="' + chkId + '" class="ms-1">Export ONLY selected rows</label>' +
                '</div>'
            );
            $container.find('.dataTables_length select').addClass('custom-dropdown');

            var $selectAllTh = $(api.table().header()).find('th').eq(0).addClass('text-center');
            if (!$selectAllTh.find('.select-all-rows').length) {
                $selectAllTh.html('<input type="checkbox" class="select-all-rows">');
            }
            $selectAllTh.off('change.selectAll').on('change.selectAll', '.select-all-rows', function () {
                $(api.table().body()).find('.select-row').prop('checked', $(this).is(':checked'));
            });
        };
    }

    function makeDT(id, data, columns, columnDefs, orderCol, excludeLastCol, label) {
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
            buttons      : makeButtons(key, excludeLastCol, label),
            language     : DT_LANG,
            createdRow   : function (row) { $(row).addClass('nk-tb-item'); },
            initComplete : makeInitComplete(key),
            drawCallback : function () {
                var api  = this.api();
                var info = api.page.info();
                api.column(1, { page: 'current' }).nodes().each(function (cell, i) {
                    cell.innerHTML = info.start + i + 1;
                });
                $(api.table().header()).find('.select-all-rows').prop('checked', false);
            },
        });
    }

    function selectColDef() {
        return {
            targets: 0, orderable: false, searchable: false, className: 'text-center',
            render: function () { return '<input type="checkbox" class="select-row">'; },
        };
    }

    function noColDef() {
        return { targets: 1, orderable: false, searchable: false, render: function () { return ''; } };
    }

    function actionColDef(target, contentType, idField, titleField) {
        return {
            targets: target, orderable: false, searchable: false,
            render: function (data, type, row) {
                return '<button type="button" class="btn btn-sm btn-outline-primary btn-stream-detail" ' +
                    'data-content-type="' + contentType + '" data-id="' + row[idField] + '" ' +
                    'data-title="' + esc(row[titleField]) + '">' +
                    '<em class="icon ni ni-eye me-1"></em>Detail</button>';
            },
        };
    }

    /* ── Stream detail modal (who streamed a specific item/playlist/collection) ── */

    var streamDetailModalEl     = document.getElementById('stream-detail-modal');
    var streamDetailModal       = new bootstrap.Modal(streamDetailModalEl);
    var streamDetailDT          = null;
    var streamModalShown        = false;
    var streamPendingLogs       = null;
    var streamDetailLabel       = 'Stream Detail';
    var streamDetailContentType = null;
    var streamDetailId          = null;
    var streamDetailDateRange   = '';

    $('#stream-detail-date').flatpickr({ mode: 'range', disableMobile: true,
        onClose: function (sel, dateStr) { streamDetailDateRange = dateStr; loadStreamDetail(); }
    });

    function loadStreamDetail() {
        if (!streamDetailContentType || !streamDetailId) return;
        post('{{ route("admin.dashboard.getStreamDetail") }}', {
            content_type: streamDetailContentType,
            id          : streamDetailId,
            date_range  : streamDetailDateRange,
        }).then(function (d) {
            if (streamModalShown) {
                buildStreamDetailTable(d.logs);
            } else {
                streamPendingLogs = d.logs;
            }
        });
    }

    function buildStreamDetailTable(logs) {
        streamDetailDT = makeDT('stream-detail-table', logs, [
            { data: null        },
            { data: null        },
            { data: 'user'      },
            { data: 'email'     },
            { data: 'played_at' },
        ], [
            selectColDef(),
            noColDef(),
            { targets: 2, render: function (data, type) {
                if (type !== 'display') return data;
                return data === 'Guest'
                    ? '<span class="bx bx-inactive">Guest</span>'
                    : esc(data);
            }},
        ], 4, false, streamDetailLabel);
    }

    streamDetailModalEl.addEventListener('shown.bs.modal', function () {
        streamModalShown = true;
        if (streamPendingLogs) {
            buildStreamDetailTable(streamPendingLogs);
            streamPendingLogs = null;
        }
    });
    streamDetailModalEl.addEventListener('hidden.bs.modal', function () {
        streamModalShown = false;
    });

    $('#stream-content').on('click', '.btn-stream-detail', function (e) {
        e.preventDefault();
        var $btn = $(this);

        streamDetailContentType = $btn.data('content-type');
        streamDetailId          = $btn.data('id');
        streamDetailLabel       = $btn.data('title') || 'Stream Detail';
        streamDetailDateRange   = '';
        var dateFp = $('#stream-detail-date')[0]._flatpickr;
        if (dateFp) dateFp.clear();

        document.getElementById('stream-detail-title').textContent = streamDetailLabel;
        streamDetailModal.show();

        loadStreamDetail();
    });

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
            '<thead><tr><th></th><th>No.</th><th>User</th><th>Email</th><th>Played At</th></tr></thead>' +
            '<tbody></tbody>' +
            '</table></div></div>'
        );

        var dtRadio = null, radioDateRange = '';

        function loadRadioTable() {
            post('{{ route("admin.dashboard.getRadioStreamTable") }}', { date_range: radioDateRange })
                .then(function (d) {
                    dtRadio = makeDT('radio-table', d.logs, [
                        { data: null        },
                        { data: null        },
                        { data: 'user'      },
                        { data: 'email'     },
                        { data: 'played_at' },
                    ], [
                        selectColDef(),
                        noColDef(),
                        { targets: 2, render: function (data, type) {
                            if (type !== 'display') return data;
                            return data === 'Guest'
                                ? '<span class="bx bx-inactive">Guest</span>'
                                : esc(data);
                        }},
                    ], 4, false, 'Radio Streams');
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
                thead    : '<th></th><th>No.</th><th>Title</th><th>Plays</th><th>Action</th>',
                cols     : [{ data: null }, { data: null }, { data: 'title' }, { data: 'total' }, { data: null }],
                defs     : [selectColDef(), noColDef(), { targets: 3, render: function (d, type) { return type === 'display' ? '<strong>' + (d || 0) + '</strong>' : (d || 0); } },
                            actionColDef(4, 2, 'item_id', 'title')],
                orderCol : 3,
                suffix   : 'Streams',
            },
            plists: {
                endpoint : '{{ route("admin.dashboard.getPlaylistStreams") }}',
                dataKey  : 'playlists',
                thead    : '<th></th><th>No.</th><th>Playlist Name</th><th>Plays</th><th>Action</th>',
                cols     : [{ data: null }, { data: null }, { data: 'name' }, { data: 'total' }, { data: null }],
                defs     : [selectColDef(), noColDef(), { targets: 3, render: function (d, type) { return type === 'display' ? '<strong>' + (d || 0) + '</strong>' : (d || 0); } },
                            actionColDef(4, 3, 'playlist_id', 'name')],
                orderCol : 3,
                suffix   : 'Playlists',
            },
            colls: {
                endpoint : '{{ route("admin.dashboard.getCollectionStreams") }}',
                dataKey  : 'collections',
                thead    : '<th></th><th>No.</th><th>Collection Name</th><th>Plays</th><th>Action</th>',
                cols     : [{ data: null }, { data: null }, { data: 'name' }, { data: 'total' }, { data: null }],
                defs     : [selectColDef(), noColDef(), { targets: 3, render: function (d, type) { return type === 'display' ? '<strong>' + (d || 0) + '</strong>' : (d || 0); } },
                            actionColDef(4, 4, 'collection_id', 'name')],
                orderCol : 3,
                suffix   : 'Collections',
            },
        };

        var cfg = CFG[prefix];

        var tableId  = prefix + 't' + typeId + 'table';
        var searchId = prefix + 't' + typeId + 'search';
        var dateId   = prefix + 't' + typeId + 'date';

        $('#stream-content').html(
            '<div class="listing-filter" style="grid-template-columns:1fr 3fr">' +
            '<input type="text" class="form-control form-control-sm" placeholder="Search date range…" id="' + dateId + '" style="background:#fff">' +
            '<input type="text" class="form-control form-control-sm" placeholder="Search…" id="' + searchId + '">' +
            '</div>' +
            '<div class="card card-bordered card-preview"><div class="card-inner">' +
            '<table class="table" style="width:100%" id="' + tableId + '">' +
            '<thead><tr>' + cfg.thead + '</tr></thead><tbody></tbody>' +
            '</table></div></div>'
        );

        var dt = null, typeDateRange = '';

        function loadTypeTable() {
            post(cfg.endpoint, { date_range: typeDateRange }).then(function (d) {
                var type     = (d.types || []).find(function (t) { return t.id == typeId; });
                var typeName = type ? (TYPE_NAMES[type.name] || type.name) : '';
                document.getElementById('stream-page-title').textContent = typeName + ' ' + cfg.suffix;

                var rows = (d[cfg.dataKey] || []).filter(function (r) { return r.type_id == typeId; });
                dt = makeDT(tableId, rows, cfg.cols, cfg.defs, cfg.orderCol, true, typeName + ' ' + cfg.suffix);
            }).catch(function () {
                $('#stream-content').html('<div class="text-danger py-3">Failed to load.</div>');
            });
        }

        $('#' + dateId).flatpickr({ mode: 'range', disableMobile: true,
            onClose: function (sel, dateStr) { typeDateRange = dateStr; loadTypeTable(); }
        });

        var timer;
        $('#' + searchId).on('input', function () {
            var v = $(this).val(); clearTimeout(timer);
            timer = setTimeout(function () { if (dt) dt.search(v).draw(); }, 400);
        });

        loadTypeTable();
    }
});
</script>
