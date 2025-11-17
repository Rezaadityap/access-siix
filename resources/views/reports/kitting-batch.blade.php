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
                    <div class="col-2 mt-3">
                        <div class="d-flex justify-between gap-2">
                            <label class="form-label mt-2">Date</label>
                            <input id="start_date" type="date" name="start_date" class="form-control" />
                        </div>
                    </div>
                    <div class="col-2 mt-3">
                        <div class="d-flex justify-between gap-2">
                            <label class="form-label mt-2">s/d</label>
                            <input id="end_date" type="date" name="end_date" class="form-control" />
                        </div>
                    </div>
                    <div class="col-2 position-relative mt-3">
                        <div class="d-flex justify-between gap-2">
                            <label class="form-label mt-2">Model</label>
                            <input id="model" type="text" name="model" class="form-control"
                                placeholder="Search Model" autocomplete="off">
                            <div id="suggestions_model" class="list-group position-absolute"
                                style="z-index:1050; display:none; max-height:200px; overflow:auto; width:100%;"></div>
                        </div>
                    </div>
                    <div class="col-2 position-relative mt-3">
                        <div class="d-flex justify-between gap-2">
                            <label class="form-label mt-2">PO</label>
                            <input id="po_number" type="text" name="po_number" class="form-control"
                                placeholder="Search PO Number" autocomplete="off">
                            <div id="suggestions_po" class="list-group position-absolute"
                                style="z-index:1050; display:none; max-height:200px; overflow:auto; width:100%;"></div>
                        </div>
                    </div>
                    <div class="col-2 mt-3">
                        <div class="d-flex justify-between gap-2">
                            <label class="form-label mt-2">Line</label>
                            <select name="line" id="line" class="form-control">
                                <option value="">-- Select Line --</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-2 position-relative mt-3">
                        <div class="d-flex justify-between gap-2">
                            <label class="form-label mt-2">Batch</label>
                            <input id="batch" type="text" name="batch" class="form-control"
                                placeholder="Search Batch" autocomplete="off">
                            <div id="suggestions_batch" class="list-group position-absolute"
                                style="z-index:1050; display:none; max-height:200px; overflow:auto; width:100%;"></div>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-6 mt-3">
                            <div class="d-flex flex-wrap gap-2">
                                <label class="form-label d-block">Source</label>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input source-checkbox" type="checkbox" id="chk_wh"
                                        value="wh" checked>
                                    <label class="form-check-label" for="chk_wh">WH</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input source-checkbox" type="checkbox" id="chk_smd"
                                        value="smd" checked>
                                    <label class="form-check-label" for="chk_smd">SMD</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input source-checkbox" type="checkbox" id="chk_sto"
                                        value="sto" checked>
                                    <label class="form-check-label" for="chk_sto">STO</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input source-checkbox" type="checkbox" id="chk_mar"
                                        value="mar" checked>
                                    <label class="form-check-label" for="chk_mar">MAR</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input source-checkbox" type="checkbox" id="chk_mismatch"
                                        value="mismatch" checked>
                                    <label class="form-check-label" for="chk_mismatch">Mismatch</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-6 d-flex justify-content-end align-items-start gap-2 mt-3 mb-3">
                            <button id="btnClearAll" type="button" class="btn btn-gradient-danger">
                                Reset
                            </button>
                            <button id="btnExport" type="button" class="btn btn-gradient-success">
                                Export Excel
                            </button>
                        </div>
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
            const modelInput = document.getElementById('model');
            const poInput = document.getElementById('po_number');
            const lineInput = document.getElementById('line');
            const container = document.getElementById('batchesContainer');
            const clearBtn = document.getElementById('btnClearAll');
            const exportBtn = document.getElementById('btnExport');
            const sourceCheckboxes = document.querySelectorAll('.source-checkbox');
            const suggestionsPoBox = document.getElementById('suggestions_po');
            const suggestionsModelBox = document.getElementById('suggestions_model');
            const batchInput = document.getElementById('batch');
            const suggestionsBatchBox = document.getElementById('suggestions_batch');

            function debounce(fn, delay) {
                let timer = null;
                return function(...args) {
                    clearTimeout(timer);
                    timer = setTimeout(() => fn.apply(this, args), delay);
                };
            }

            function loadData() {
                const start = startInput.value;
                const end = endInput.value;
                const po = poInput.value.trim();
                const model = modelInput.value.trim();
                const line = lineInput.value.trim();
                const batch = batchInput ? batchInput.value.trim() : '';

                const sources = Array.from(sourceCheckboxes)
                    .filter(ch => ch.checked)
                    .map(ch => ch.value);

                const params = new URLSearchParams();
                if (start) params.append('start_date', start);
                if (end) params.append('end_date', end);
                if (po) params.append('po_number', po);
                if (model) params.append('model', model);
                if (line) params.append('line', line);
                if (batch) params.append('batch', batch);
                if (sources.length) sources.forEach(s => params.append('sources[]', s));

                fetch("{{ route('reports.kitting.batches') }}?" + params.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(resp => {
                        if (!resp.ok) throw new Error('Network response was not ok');
                        return resp.text();
                    })
                    .then(html => container.innerHTML = html)
                    .catch(err => {
                        console.error(err);
                        container.innerHTML = '<div class="alert alert-danger">Gagal memuat data.</div>';
                    });
            }

            const loadDataDebounced = debounce(loadData, 400);

            // ---------- Suggestions (PO / Model) ----------
            function renderList(box, items, type) {
                box.innerHTML = '';
                if (!items || !items.length) {
                    box.style.display = 'none';
                    return;
                }
                items.forEach(it => {
                    const a = document.createElement('a');
                    a.href = '#';
                    a.className = 'list-group-item list-group-item-action';
                    a.textContent = it.label;
                    a.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (type === 'po') {
                            poInput.value = it.po_number;
                            suggestionsPoBox.style.display = 'none';
                        } else {
                            modelInput.value = it.model;
                            suggestionsModelBox.style.display = 'none';
                        }
                        fetchLinesDebounced(); // refresh line options relevant to selection
                        loadData(); // immediate load with chosen value
                    });
                    box.appendChild(a);
                });
                box.style.display = 'block';
            }

            function fetchPoSuggestions() {
                const q = poInput.value.trim();
                if (!q) {
                    suggestionsPoBox.style.display = 'none';
                    return;
                }
                fetch("{{ route('reports.batches.suggest.po') }}?q=" + encodeURIComponent(q), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json())
                    .then(data => renderList(suggestionsPoBox, data, 'po'))
                    .catch(err => {
                        console.error(err);
                        suggestionsPoBox.style.display = 'none';
                    });
            }

            function fetchModelSuggestions() {
                const q = modelInput.value.trim();
                if (!q) {
                    suggestionsModelBox.style.display = 'none';
                    return;
                }
                fetch("{{ route('reports.batches.suggest.model') }}?q=" + encodeURIComponent(q), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json())
                    .then(data => renderList(suggestionsModelBox, data, 'model'))
                    .catch(err => {
                        console.error(err);
                        suggestionsModelBox.style.display = 'none';
                    });
            }

            function renderList(box, items, type) {
                box.innerHTML = '';
                if (!items || !items.length) {
                    box.style.display = 'none';
                    return;
                }
                items.forEach(it => {
                    const a = document.createElement('a');
                    a.href = '#';
                    a.className = 'list-group-item list-group-item-action';
                    a.textContent = it.label;
                    a.addEventListener('click', function(e) {
                        e.preventDefault();
                        if (type === 'po') {
                            poInput.value = it.po_number;
                            suggestionsPoBox.style.display = 'none';
                        } else if (type === 'model') {
                            modelInput.value = it.model;
                            suggestionsModelBox.style.display = 'none';
                        } else if (type === 'batch') {
                            batchInput.value = it.batch;
                            suggestionsBatchBox.style.display = 'none';
                        }
                        fetchLinesDebounced();
                        loadData(); // immediate load with chosen value
                    });
                    box.appendChild(a);
                });
                box.style.display = 'block';
            }

            function fetchBatchSuggestions() {
                const q = batchInput.value.trim();
                if (!q) {
                    suggestionsBatchBox.style.display = 'none';
                    return;
                }
                fetch("{{ route('reports.batches.suggest.batch') }}?q=" + encodeURIComponent(q), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json())
                    .then(data => renderList(suggestionsBatchBox, data, 'batch'))
                    .catch(err => {
                        console.error(err);
                        suggestionsBatchBox.style.display = 'none';
                    });
            }
            const fetchBatchSuggestionsDebounced = debounce(fetchBatchSuggestions, 250);
            const fetchPoSuggestionsDebounced = debounce(fetchPoSuggestions, 250);
            const fetchModelSuggestionsDebounced = debounce(fetchModelSuggestions, 250);

            // ---------- Lines select population ----------
            function populateLineSelect(items, selected) {
                lineInput.innerHTML = '';
                const placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = '-- Select Line --';
                lineInput.appendChild(placeholder);

                items.forEach(it => {
                    const opt = document.createElement('option');
                    opt.value = it.line;
                    opt.textContent = it.label;
                    if (selected && selected === it.line) opt.selected = true;
                    lineInput.appendChild(opt);
                });
            }

            function fetchLines(q = '') {
                const params = new URLSearchParams();
                if (q) params.append('q', q);
                const modelVal = modelInput.value.trim();
                const poVal = poInput.value.trim();
                if (modelVal) params.append('model', modelVal);
                if (poVal) params.append('po', poVal);

                fetch("{{ route('reports.batches.suggest.line') }}?" + params.toString(), {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        const prev = lineInput.value || '';
                        populateLineSelect(data, prev);
                    })
                    .catch(err => {
                        console.error('fetchLines error', err);
                        populateLineSelect([], '');
                    });
            }

            const fetchLinesDebounced = debounce(fetchLines, 300);

            // ---------- Event hooks ----------
            startInput.addEventListener('change', loadData);
            endInput.addEventListener('change', loadData);

            sourceCheckboxes.forEach(ch => {
                ch.addEventListener('change', function() {
                    fetchLinesDebounced();
                    loadData();
                });
            });

            poInput.addEventListener('input', function() {
                fetchPoSuggestionsDebounced();
                fetchLinesDebounced();
                loadDataDebounced();
            });

            modelInput.addEventListener('input', function() {
                fetchModelSuggestionsDebounced();
                fetchLinesDebounced();
                loadDataDebounced();
            });

            lineInput.addEventListener('change', function() {
                loadData();
            });

            batchInput.addEventListener('input', function() {
                fetchBatchSuggestionsDebounced();
                loadDataDebounced();
            });

            // reset
            clearBtn.addEventListener('click', function() {
                startInput.value = '';
                endInput.value = '';
                poInput.value = '';
                modelInput.value = '';
                lineInput.value = '';
                batchInput.value = '';
                sourceCheckboxes.forEach(ch => ch.checked = true);
                suggestionsPoBox.style.display = 'none';
                suggestionsModelBox.style.display = 'none';
                suggestionsBatchBox.style.display = 'none';
                fetchLines();
                loadData();
            });

            // export button
            exportBtn.addEventListener('click', function() {
                const start = startInput.value;
                const end = endInput.value;
                const po = poInput ? poInput.value.trim() : '';
                const model = modelInput ? modelInput.value.trim() : '';
                const line = lineInput ? lineInput.value.trim() : '';
                const batch = batchInput ? batchInput.value.trim() : '';
                const sources = Array.from(sourceCheckboxes).filter(ch => ch.checked).map(c => c.value);
                const params = new URLSearchParams();
                if (start) params.append('start_date', start);
                if (end) params.append('end_date', end);
                if (po) params.append('po_number', po);
                if (model) params.append('model', model);
                if (line) params.append('line', line);
                if (batch) params.append('batch', batch);
                if (sources.length) sources.forEach(s => params.append('sources[]', s));
                const url = "{{ route('reports.batches.export') }}" + (params.toString() ? '?' + params
                    .toString() : '');
                window.location.href = url;
            });

            // hide suggestion boxes when clicking outside
            document.addEventListener('click', function(e) {
                if (!suggestionsPoBox.contains(e.target) && e.target !== poInput) suggestionsPoBox.style
                    .display = 'none';
                if (!suggestionsModelBox.contains(e.target) && e.target !== modelInput) suggestionsModelBox
                    .style.display = 'none';
                if (!suggestionsBatchBox.contains(e.target) && e.target !== batchInput) suggestionsBatchBox
                    .style.display = 'none';
            });

            // initial load
            fetchLines();
            loadData();
        });
    </script>
@endpush
