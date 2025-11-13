@extends('layouts.app')

@section('title', 'Reports Batches')

@section('content')
    <main class="main" id="main">
        <div class="pagetitle">
            <h1>Reports Batches</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">Reports Batches</li>
                </ol>
            </nav>
        </div>

        <div class="card mb-4">
            <div class="card-body pb-0">
                <form id="filterForm" class="row align-items-end mb-0" onsubmit="return false;">
                    @csrf
                    <div class="col-sm-6 col-md-6">
                        <label class="form-label mt-2">Start date</label>
                        <input id="start_date" type="date" name="start_date" class="form-control" />
                    </div>
                    <div class="col-sm-6 col-md-6">
                        <label class="form-label">End date</label>
                        <input id="end_date" type="date" name="end_date" class="form-control" />
                    </div>

                    <div class="col-6 mt-3">
                        <label class="form-label d-block">Source</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input source-checkbox" type="checkbox" id="chk_wh" value="wh"
                                checked>
                            <label class="form-check-label" for="chk_wh">WH</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input source-checkbox" type="checkbox" id="chk_smd" value="smd"
                                checked>
                            <label class="form-check-label" for="chk_smd">SMD</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input source-checkbox" type="checkbox" id="chk_sto" value="sto"
                                checked>
                            <label class="form-check-label" for="chk_sto">STO</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input source-checkbox" type="checkbox" id="chk_mar" value="mar"
                                checked>
                            <label class="form-check-label" for="chk_mar">MAR</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input source-checkbox" type="checkbox" id="chk_mismatch"
                                value="mismatch" checked>
                            <label class="form-check-label" for="chk_mismatch">Mismatch</label>
                        </div>
                    </div>

                    <div class="col-6 mt-3">
                        <label class="form-label">PO Number</label>
                        <input id="po_number" type="text" name="po_number" class="form-control"
                            placeholder="Search PO Number">
                    </div>

                    <div class="col-md-12 col-sm-12 mt-3">
                        <button id="btnClearAll" type="button" class="btn btn-gradient-danger mb-3 flex-fill">
                            Reset
                        </button>
                        <button id="btnExport" type="button" class="btn btn-gradient-success mb-3">Export Excel</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="batchesContainer">
            @include('partials.kitting-batch-rows', ['batches' => $batches ?? collect()])
        </div>
    </main>

@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const startInput = document.getElementById('start_date');
            const endInput = document.getElementById('end_date');
            const poInput = document.getElementById('po_number');
            const container = document.getElementById('batchesContainer');
            const clearBtn = document.getElementById('btnClearAll');
            const sourceCheckboxes = document.querySelectorAll('.source-checkbox');

            // debounce helper
            function debounce(fn, delay) {
                let timer = null;
                return function(...args) {
                    clearTimeout(timer);
                    timer = setTimeout(() => fn.apply(this, args), delay);
                };
            }

            // fungsi untuk request data
            function loadData() {
                const start = startInput.value;
                const end = endInput.value;
                const po = poInput.value.trim();

                // ambil sources yang dicentang
                const sources = Array.from(sourceCheckboxes)
                    .filter(ch => ch.checked)
                    .map(ch => ch.value);

                // susun query params
                const params = new URLSearchParams();
                if (start) params.append('start_date', start);
                if (end) params.append('end_date', end);
                if (po) params.append('po_number', po);

                // jika ada sources, tambahkan sebagai multiple params
                if (sources.length) {
                    sources.forEach(s => params.append('sources[]', s));
                }

                fetch("{{ route('reports.kitting.batches') }}?" + params.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(resp => {
                        if (!resp.ok) throw new Error('Network response was not ok');
                        return resp.text();
                    })
                    .then(html => {
                        container.innerHTML = html;
                    })
                    .catch(err => {
                        console.error(err);
                        container.innerHTML = '<div class="alert alert-danger">Gagal memuat data.</div>';
                    });
            }

            const loadDataDebounced = debounce(loadData, 400);

            // trigger otomatis ketika salah satu input berubah
            startInput.addEventListener('change', loadData);
            endInput.addEventListener('change', loadData);
            sourceCheckboxes.forEach(ch => ch.addEventListener('change', loadData));

            // PO input -> tanpa tombol, otomatis (debounced)
            poInput.addEventListener('input', loadDataDebounced);

            // tombol reset mengosongkan input, centang semua source, dan reload semua data
            clearBtn.addEventListener('click', function() {
                startInput.value = '';
                endInput.value = '';
                poInput.value = '';
                sourceCheckboxes.forEach(ch => ch.checked = true);
                loadData();
            });

            // muat data awal (full) saat halaman dibuka
            loadData();

            function buildParams() {
                const start = startInput.value;
                const end = endInput.value;
                const po = poInput ? poInput.value.trim() : '';
                const sources = Array.from(sourceCheckboxes).filter(ch => ch.checked).map(c => c.value);

                const params = new URLSearchParams();
                if (start) params.append('start_date', start);
                if (end) params.append('end_date', end);
                if (po) params.append('po_number', po);
                if (sources.length) sources.forEach(s => params.append('sources[]', s));
                return params.toString();
            }

            const exportBtn = document.getElementById('btnExport');
            exportBtn.addEventListener('click', function() {
                const qs = buildParams();
                const url = "{{ route('reports.batches.export') }}" + (qs ? '?' + qs : '');
                window.location.href = url;
            });
        });
    </script>
@endpush
