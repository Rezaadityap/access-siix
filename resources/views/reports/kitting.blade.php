@extends('layouts.app')
@section('title', 'Reports Kitting')
@section('content')
    <main class="main" id="main">
        <div class="pagetitle">
            <h1>Reports Kitting</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Reports Kitting</li>
                </ol>
            </nav>
        </div>

        <div class="card mb-4">
            <div class="card-body pb-0">
                <form method="POST" action="{{ route('reports.kitting') }}" class="row align-items-end mb-0"
                    id="filterForm">
                    @csrf
                    <div class="col-sm-6 col-md-6">
                        <label class="form-label mt-2">Start date</label>
                        <input id="start_date" type="date" name="start_date" value="{{ $start }}"
                            class="form-control" required />
                    </div>
                    <div class="col-sm-6 col-md-6">
                        <label class="form-label">End date</label>
                        <input id="end_date" type="date" name="end_date" value="{{ $end }}"
                            class="form-control" required />
                    </div>
                    <div class="col-md-12 col-sm-12 mt-3">
                        <button id="btnSelectAll" type="button"
                            class="btn btn-gradient-purple mb-3 flex-fill {{ $records->count() ? '' : 'd-none' }}">
                            Select All
                        </button>

                        <button id="btnClearAll" type="button"
                            class="btn btn-danger mb-3 flex-fill {{ $records->count() ? '' : 'd-none' }}">
                            Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header gradient-purple fw-bold text-center">List Model</div>
                    <div class="card-body mt-3">
                        <div id="tableLoader" class="d-none text-muted">Loading...</div>
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead>
                                    <tr>
                                        <th>Model</th>
                                        <th>PO Number</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody id="tableBody">
                                    {{-- render awal dari server --}}
                                    @include('reports.kitting-rows', ['records' => $records])
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4">
                <div class="card shadow-lg border-0 rounded-4">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bolder gradient-text-primary text-center">
                            PT. SIIX EMS INDONESIA
                        </h5>
                        <hr class="mt-0 mb-4 text-muted">
                        <form>
                            <div class="col-12 mb-2">
                                <label for="model" class="form-label small text-muted mb-1">Model</label>
                                <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0"
                                    placeholder="Model" disabled id="model">
                            </div>
                            <div class="col-12 mb-2">
                                <label for="poNumbers" class="form-label small text-muted mb-1">PO Numbers</label>
                                <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0"
                                    placeholder="PO Numbers" id="poNumbers" disabled>
                            </div>

                            <div class="row mb-2">
                                <div class="col-sm-6">
                                    <label for="smtLine" class="form-label small text-muted mb-1">SMT Line</label>
                                    <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0"
                                        id="smtLine" placeholder="SMT Line" disabled>
                                </div>
                                <div class="col-sm-6">
                                    <label for="infoDate" class="form-label small text-muted mb-1">DATE</label>
                                    <input type="date" class="form-control form-control-sm rounded-3 bg-light border-0"
                                        placeholder="Date" id="infoDate" disabled>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-sm-6">
                                    <label for="infoLotSize" class="form-label small text-muted mb-1">LOT
                                        SIZE</label>
                                    <input type="number" class="form-control form-control-sm rounded-3 bg-light border-0"
                                        placeholder="Lot Size" id="infoLotSize" disabled>
                                </div>
                                <div class="col-sm-6">
                                    <label for="infoActual" class="form-label small text-muted mb-1">ACTUAL LOT
                                        SIZE</label>
                                    <input type="number" class="form-control form-control-sm rounded-3 bg-light border-0"
                                        placeholder="Act Lot Size" id="infoActual" disabled>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card p-3">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" id="btnExportExcel" class="btn btn-sm btn-gradient-success w-auto">
                            Export Excel
                        </button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm text-center align-middle history-material-table"
                            id="recordMaterials">
                            <thead class="align-middle text-center">
                                <tr>
                                    <th>Date</th>
                                    <th>Line</th>
                                    <th>Supplier</th>
                                    <th>Model</th>
                                    <th>PO</th>
                                    <th>Lot</th>
                                    <th>Item</th>
                                    <th>Description</th>
                                    <th>Usage</th>
                                    <th>Unit Price</th>
                                    <th>Qty Total</th>
                                    <th>Qty Lcr</th>
                                    <th>Amount Lcr</th>
                                    <th>Qty Loss</th>
                                    <th>Amount Loss</th>
                                    <th>Percentage</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
@push('script')
    <script>
        (function() {
            // helper
            function $(sel, ctx) {
                return (ctx || document).querySelector(sel);
            }

            function $all(sel, ctx) {
                return Array.prototype.slice.call((ctx || document).querySelectorAll(sel));
            }

            function hasSwal() {
                return (typeof window.Swal !== 'undefined' && typeof window.Swal.fire === 'function');
            }

            var form = document.getElementById('filterForm');
            var start = document.getElementById('start_date');
            var end = document.getElementById('end_date');
            var tbody = document.getElementById('tableBody');
            var loader = document.getElementById('tableLoader');

            // CSS.escape polyfill
            if (typeof window.CSS === 'undefined' || !CSS.escape) {
                window.CSS = window.CSS || {};
                CSS.escape = function(sel) {
                    return String(sel).replace(/([ #;?%&,.+*~\':"!^$\[\]\(\)=>|\/@])/g, '\\$1');
                };
            }

            // debounce
            var tDebounce;

            function debounce(fn, delay) {
                return function() {
                    var args = arguments;
                    clearTimeout(tDebounce);
                    tDebounce = setTimeout(function() {
                        fn.apply(null, args);
                    }, delay || 400);
                };
            }

            function isValidRange() {
                return start && end && start.value && end.value && start.value <= end.value;
            }

            function getCsrf() {
                var meta = document.querySelector('meta[name="csrf-token"]');
                return meta ? meta.getAttribute('content') : '';
            }

            function getSelectedGroupIds() {
                if (!tbody) return [];
                var set = {};
                $all('.row-check:checked', tbody).forEach(function(cb) {
                    var g = cb.getAttribute('data-group');
                    if (g !== null && g !== '') set[g] = true;
                });
                return Object.keys(set);
            }

            function updateCardFromSelection() {
                var modelEl = document.getElementById('model');
                var poEl = document.getElementById('poNumbers');
                var lineEl = document.getElementById('smtLine');
                var dateEl = document.getElementById('infoDate');
                var lotEl = document.getElementById('infoLotSize');
                var actLotEl = document.getElementById('infoActual');

                if (!tbody) return;

                var checked = $all('.row-check:checked', tbody);
                if (!checked.length) {
                    if (modelEl) modelEl.value = '';
                    if (poEl) poEl.value = '';
                    if (lineEl) lineEl.value = '';
                    if (dateEl) dateEl.value = '';
                    if (lotEl) lotEl.value = '';
                    if (actLotEl) actLotEl.value = '';
                    return;
                }

                var models = [];
                var pos = [];

                checked.forEach(function(cb) {
                    var tr = cb.closest('tr');
                    if (!tr) return;
                    var tdModel = tr.cells && tr.cells[0];
                    var tdPO = tr.cells && tr.cells[1];
                    var tdDate = tr.cells && tr.cells[2];

                    if (tdModel) {
                        var text = tdModel.textContent.replace(/\s+/g, ' ').trim().replace(/^on\s*/i, '');
                        if (text) models.push(text);
                    }
                    if (tdPO) {
                        var po = tdPO.textContent.replace(/\s+/g, ' ').trim();
                        if (po) pos.push(po);
                    }
                    if (dateEl && tdDate && !dateEl.value) {
                        var d = tdDate.textContent.replace(/\s+/g, ' ').trim();
                        dateEl.value = d; // YYYY-MM-DD
                    }
                });

                // unique
                var uniqueModels = Array.from(new Set(models));
                var uniquePOs = Array.from(new Set(pos));
                if (modelEl) modelEl.value = uniqueModels.join(' - ');
                if (poEl) poEl.value = uniquePOs.join(' - ');

                var first = checked[0];
                if (first) {
                    var rid = first.getAttribute('data-id');
                    if (lineEl) lineEl.value = first.getAttribute('data-line') || (window.RECORDS_INDEX && window
                        .RECORDS_INDEX[rid] && window.RECORDS_INDEX[rid].line) || '';
                    if (lotEl) lotEl.value = first.getAttribute('data-lot_size') || (window.RECORDS_INDEX && window
                        .RECORDS_INDEX[rid] && window.RECORDS_INDEX[rid].lot_size) || '';
                    if (actLotEl) actLotEl.value = first.getAttribute('data-act_lot_size') || (window.RECORDS_INDEX &&
                        window.RECORDS_INDEX[rid] && window.RECORDS_INDEX[rid].act_lot_size) || '';
                }
            }

            function toggleGroupButtons(count) {
                var btnSelect = document.getElementById('btnSelectAll');
                var btnClear = document.getElementById('btnClearAll');
                if (!btnSelect || !btnClear) return;
                if (count > 0) {
                    btnSelect.classList.remove('d-none');
                    btnClear.classList.remove('d-none');
                } else {
                    btnSelect.classList.add('d-none');
                    btnClear.classList.add('d-none');
                }
            }

            async function submitAjax() {
                if (!form || !isValidRange()) return;
                if (loader) loader.classList.remove('d-none');
                var fd = new FormData(form);
                try {
                    var res = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: fd
                    });
                    if (!res.ok) throw new Error('Request gagal');
                    var data = await res.json();
                    tbody.innerHTML = data.rows || '';
                    toggleGroupButtons(data.count || 0);
                    updateCardFromSelection();
                    await refreshMaterialDetails();
                } catch (e) {
                    console.error(e);
                } finally {
                    if (loader) loader.classList.add('d-none');
                }
            }

            async function refreshMaterialDetails() {
                var groupIds = getSelectedGroupIds();
                var tgtBody = document.querySelector('#recordMaterials tbody');
                if (!tgtBody) return;

                if (!groupIds.length) {
                    tgtBody.innerHTML =
                        '<tr><td colspan="13" class="text-muted text-center">No material detail.</td></tr>';
                    return;
                }

                try {
                    var res = await fetch("{{ route('reports.kitting.materials') }}", {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': getCsrf()
                        },
                        body: JSON.stringify({
                            group_ids: groupIds
                        })
                    });
                    if (!res.ok) throw new Error('Failed to load material details');
                    var data = await res.json();
                    tgtBody.innerHTML = data.rows || '';
                } catch (e) {
                    console.error(e);
                    tgtBody.innerHTML =
                        '<tr><td colspan="13" class="text-danger text-center">Error loading material detail.</td></tr>';
                }
            }

            function init() {
                if (!form || !start || !end || !tbody) return;

                start.addEventListener('change', debounce(submitAjax, 400));
                end.addEventListener('change', debounce(submitAjax, 400));

                var btnSelect = document.getElementById('btnSelectAll');
                var btnClear = document.getElementById('btnClearAll');

                if (btnSelect) {
                    btnSelect.addEventListener('click', function() {
                        var boxes = $all('.row-check', tbody);

                        function doSelectAll() {
                            boxes.forEach(function(cb) {
                                cb.checked = true;
                            });
                            updateCardFromSelection();
                            refreshMaterialDetails();
                        }
                        if (hasSwal()) {
                            Swal.fire({
                                icon: 'question',
                                title: 'Select all row?',
                                text: 'This will select ' + boxes.length + ' rows.',
                                showCancelButton: true,
                                confirmButtonText: 'Yes, select all',
                                cancelButtonText: 'Cancel'
                            }).then(function(res) {
                                if (res.isConfirmed) doSelectAll();
                            });
                        } else {
                            doSelectAll();
                        }
                    });
                }

                if (btnClear) {
                    btnClear.addEventListener('click', function() {
                        var boxes = $all('.row-check', tbody);

                        function doClear() {
                            boxes.forEach(function(cb) {
                                cb.checked = false;
                            });
                            updateCardFromSelection();
                            refreshMaterialDetails();
                        }
                        if (hasSwal()) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Reset all selected?',
                                text: 'This will remove the selection from the ' + boxes.length +
                                    ' row.',
                                showCancelButton: true,
                                confirmButtonText: 'Yes, reset',
                                cancelButtonText: 'Cancel'
                            }).then(function(res) {
                                if (res.isConfirmed) doClear();
                            });
                        } else {
                            doClear();
                        }
                    });
                }

                var lastState = new WeakMap();

                function rememberState(ev) {
                    var cb = ev.target.closest('.row-check');
                    if (!cb) return;
                    lastState.set(cb, cb.checked);
                }
                tbody.addEventListener('pointerdown', rememberState);
                tbody.addEventListener('mousedown', rememberState);
                tbody.addEventListener('touchstart', rememberState, {
                    passive: true
                });

                tbody.addEventListener('click', function(ev) {
                    var cb = ev.target.closest('.row-check');
                    if (!cb) return;

                    var wasChecked = lastState.has(cb) ? lastState.get(cb) : cb.checked;
                    var willCheck = !wasChecked;
                    var group = cb.getAttribute('data-group') || '';
                    var groupBoxes = $all('.row-check[data-group="' + CSS.escape(group) + '"]', tbody);
                    var count = groupBoxes.length;

                    function applyCheck(val) {
                        groupBoxes.forEach(function(el) {
                            el.checked = val;
                        });
                        updateCardFromSelection();
                        refreshMaterialDetails();
                    }

                    if (!hasSwal()) {
                        applyCheck(!wasChecked);
                        return;
                    }

                    ev.preventDefault();

                    if (willCheck) {
                        Swal.fire({
                            icon: 'question',
                            title: 'Select this model?',
                            text: 'This action will select ' + count + ' rows with the same Group ID.',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, select all',
                            cancelButtonText: 'Cancel'
                        }).then(function(res) {
                            if (res.isConfirmed) applyCheck(true);
                            else cb.checked = wasChecked;
                        });
                    } else {
                        Swal.fire({
                            icon: 'question',
                            title: 'Are you sure?',
                            text: 'This action will clear the selection of ' + count +
                                ' rows in this group.',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, cancel it.',
                            cancelButtonText: 'Cancel'
                        }).then(function(res) {
                            if (res.isConfirmed) applyCheck(false);
                            else cb.checked = wasChecked;
                        });
                    }

                    lastState.delete(cb);
                });

                @if ($start && $end && !$records->count())
                    submitAjax();
                @endif
            }

            // EXPORT EXCEL (event delegation aman saat tbody di-re-render)
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('#btnExportExcel');
                if (!btn) return;

                var ids = getSelectedGroupIds();
                if (!ids.length) {
                    if (hasSwal()) {
                        Swal.fire({
                            icon: 'info',
                            title: 'No selection',
                            text: 'Silakan pilih minimal satu model (group) terlebih dahulu.'
                        });
                    }
                    return;
                }

                var originalHTML = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = 'Exporting...';

                var url = "{{ route('reports.kitting.export') }}";
                var token = getCsrf();

                fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': token,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            group_ids: ids
                        })
                    })
                    .then(function(res) {
                        if (!res.ok) return res.text().then(function(t) {
                            throw new Error(t || 'Export gagal');
                        });
                        var disposition = res.headers.get('Content-Disposition') || '';
                        var filename = 'Reports_Kitting_' + new Date().toISOString().replace(/[:.]/g, '-') +
                            '.xlsx';
                        var rx = /filename\*?=([^;]+)/i.exec(disposition);
                        if (rx) {
                            var raw = rx[1].trim().replace(/^UTF-8''/i, '').replace(/(^"|"$)/g, '');
                            try {
                                filename = decodeURIComponent(raw);
                            } catch (e) {
                                filename = raw;
                            }
                        }
                        return res.blob().then(function(blob) {
                            return {
                                blob: blob,
                                filename: filename
                            };
                        });
                    })
                    .then(function(paket) {
                        var a = document.createElement('a');
                        a.href = URL.createObjectURL(paket.blob);
                        a.download = paket.filename;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                        URL.revokeObjectURL(a.href);
                    })
                    .catch(function(err) {
                        console.error(err);
                        if (hasSwal()) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Export gagal',
                                text: err && err.message ? err.message.substring(0, 500) :
                                    'Terjadi kesalahan saat mengekspor Excel.'
                            });
                        } else {
                            alert('Export gagal');
                        }
                    })
                    .finally(function() {
                        btn.disabled = false;
                        btn.innerHTML = originalHTML;
                    });
            });

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                init();
            }
        })();
    </script>
@endpush
