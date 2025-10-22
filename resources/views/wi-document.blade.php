@extends('layouts.app')
@section('title')
    WI Document
@endsection
@section('content')
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Work Instruction Document</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item active">WI Document</li>
                </ol>
            </nav>
        </div>
        <section class="section dashboard">
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div
                                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                                <div>
                                    <h5 class="card-title">File Selected</h5>
                                </div>
                                <div>
                                    <a href="#" id="btn-slideshow" class="btn btn-sm btn-primary text-white disabled">
                                        <i class="bi bi-play-btn"></i> Slideshow
                                    </a>
                                    <a href="#" id="btn-reset" class="btn btn-sm btn-warning text-white"><i
                                            class="bi bi-x-circle"></i>
                                        Reset Selection</a>
                                </div>
                            </div>
                            <table class="table selected-files">
                                <thead>
                                    <tr class="font-custom">
                                        <th>Filename</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="text-center no-file-row font-custom">
                                        <td colspan="4">No file selected</td>
                                    </tr>
                                </tbody>
                            </table>
                            <!-- Pastikan ada row yang valid -->
                            <div class="row" style="margin-right: -8px; margin-left: -8px;">
                                <div class="col-lg-6 mb-3 px-2">
                                    <div class="border rounded p-3 h-100">
                                        <h5 class="card-title text-center">PIC Selected</h5>
                                        <table class="table" id="selectedPicTable">
                                            <thead>
                                                <tr class="font-custom">
                                                    <th>Name</th>
                                                    <th>Department</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="text-center font-custom">
                                                    <td colspan="3">No PIC selected</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="col-lg-6 mb-3 px-2">
                                    <div class="border rounded p-3 h-100">
                                        <h5 class="card-title text-center">Absent Selected</h5>
                                        <table class="table" id="selectedAbsentTable">
                                            <thead>
                                                <tr class="font-custom">
                                                    <th>Name</th>
                                                    <th>Department</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr class="text-center font-custom">
                                                    <td colspan="3">No Absent selected</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div
                                class="d-flex flex-column flex-md-row justify-content-center align-items-start align-items-md-center">
                                <div>
                                    <h5 class="card-title">Work Instruction Document</h5>
                                </div>
                            </div>
                            {{-- <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <i class="bi bi-folder2"></i>
                                        <a href="{{ route('wi-document.index') }}">WI Document</a>
                                    </li>
                                    @if (!empty($breadcrumb))
                                        @foreach ($breadcrumb as $crumb)
                                            <li class="breadcrumb-item">
                                                <i class="bi bi-folder2"></i>
                                                <a href="{{ route('wi-document.index', ['path' => $crumb['path']]) }}">
                                                    {{ $crumb['name'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                </ol>
                            </nav> --}}
                            <table class="table" id="WiTable">
                                <thead>
                                    <tr class="font-custom">
                                        <th>Name</th>
                                        <th>Size</th>
                                        <th>Modified</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $item)
                                        @if ($item['type'] === 'folder')
                                            <tr class="font-custom">
                                                <td>
                                                    <a href="{{ route('wi-document.index', ['path' => $item['path']]) }}">
                                                        📁 {{ $item['name'] }}
                                                    </a>
                                                </td>
                                                <td>{{ $item['size'] }}</td>
                                                <td>{{ $item['modified'] }}</td>
                                                <td>-</td>
                                            </tr>
                                        @else
                                            <tr class="font-custom">
                                                <td>
                                                    <input type="checkbox" class="select-file"
                                                        data-name="{{ $item['name'] }}" data-path="{{ $item['path'] }}">
                                                    {{ $item['name'] }}
                                                </td>
                                                <td>{{ $item['size'] }}</td>
                                                <td>{{ $item['modified'] }}</td>
                                                <td>
                                                    <button class="btn btn-sm btn-primary view-pdf"
                                                        data-path="{{ route('wi-document.view', ['path' => $item['path']]) }}">
                                                        View
                                                    </button>
                                                </td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr class="text-center">
                                            <td colspan="4">No files or folders found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-header align-items-center text-center">
                            <span class="text-center fw-bold"><i class="bi bi-person-lines-fill"></i> Select PIC &
                                Absent
                                Employees</span>
                        </div>
                        <div id="employeeList">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="border rounded p-2">
                                            <table class="table table-sm align-middle" id="tablePic">
                                                <thead class="table-light">
                                                    <tr class="font-custom">
                                                        <th>Photo</th>
                                                        <th>NIK</th>
                                                        <th>Name</th>
                                                        <th>Department</th>
                                                        <th style="width: 200px">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="picList">
                                                    @foreach ($employees as $employee)
                                                        <tr data-id="{{ $employee->id }}" class="font-custom">
                                                            <td>
                                                                <img src="{{ 'http://192.168.61.8/photos/employee/' . $employee->photo }}"
                                                                    alt="Photo" width="45" height="45"
                                                                    class="rounded-circle" loading="lazy">
                                                                {{-- <img src="{{ asset('assets/img/' . $employee->photo) }}"
                                                                    alt="Photo" width="45" height="45"
                                                                    class="rounded-circle" loading="lazy"> --}}
                                                            </td>
                                                            <td>{{ $employee->nik ?? '-' }}</td>
                                                            <td>{{ $employee->name ?? '-' }}</td>
                                                            <td>{{ $employee->department ?? '-' }}</td>
                                                            <td class="action-col">
                                                                <button
                                                                    class="btn btn-sm btn-primary select-pic">Select</button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="border rounded p-2">
                                            <table id="tableAbsent" class="table table-sm align-middle">
                                                <thead class="table-light">
                                                    <tr class="font-custom">
                                                        <th>Photo</th>
                                                        <th>Nik</th>
                                                        <th>Name</th>
                                                        <th>Department</th>
                                                        <th style="width: 200px">Action</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="absentList">
                                                    @foreach ($employees as $employee)
                                                        <tr data-id="{{ $employee->id }}" class="font-custom">
                                                            <td>
                                                                <img src="{{ 'http://192.168.61.8/photos/employee/' . $employee->photo }}"
                                                                    loading="lazy" width="45" height="45"
                                                                    class="rounded-circle" alt="Photo">
                                                            </td>
                                                            <td>{{ $employee->nik ?? '-' }}</td>
                                                            <td>{{ $employee->name ?? '-' }}</td>
                                                            <td>{{ $employee->department ?? '-' }}</td>
                                                            <td>
                                                                <button
                                                                    class="btn btn-sm btn-primary select-absent">Select</button>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- PDF Viewer Modal -->
            <div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="pdfModalLabel">View Work Instruction</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <iframe id="pdfViewer" src="" frameborder="0"
                                style="width: 100%; height: 80vh;"></iframe>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fullscreen Content -->
            <div id="fullscreenContainer">
                <div class="container-fluid h-100 p-0">
                    <div class="d-flex flex-nowrap h-100">

                        <!-- PIC LINE -->
                        <div class="sidebar-section">
                            <div class="card h-100 border-0 rounded-0">
                                <div class="card-header text-center bg-white border-bottom p-2">
                                    <img src="{{ asset('assets/img/siix-access-banner.png') }}" alt="Siix Banner"
                                        class="sidebar-logo mb-2">
                                    <strong>PIC Line</strong>
                                </div>
                                <div class="card-body p-2 bg-white overflow-auto">
                                    <table class="table table-sm table-borderless mb-0 text-center align-middle">
                                        <tbody id="fullscreenPICBody">
                                            <!-- Rendered by JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- PDF (Center) -->
                        <div class="flex-grow-1 main-canvas-area">
                            <div class="card h-100 border-0 rounded-0">
                                <div class="card-body p-0 d-flex justify-content-center align-items-center position-relative"
                                    style="background-color: #F5F5F5;">
                                    <div
                                        class="canvas-container w-100 h-100 d-flex justify-content-center align-items-center">
                                        <canvas id="fullscreen-pdf-canvas"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ABSENT -->
                        <div class="sidebar-section">
                            <div class="card h-100 border-0 rounded-0">
                                <div class="card-header text-center bg-white border-bottom p-2">
                                    <img src="{{ asset('assets/img/siix-access-banner.png') }}" alt="Siix Banner"
                                        class="sidebar-logo mb-2">
                                    <strong>Absent</strong>
                                </div>
                                <div class="card-body p-2 bg-white overflow-auto">
                                    <table class="table table-sm table-borderless mb-0 text-center align-middle">
                                        <tbody id="fullscreenAbsentBody">
                                            <!-- Rendered by JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
    </main>
    @push('script')
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // ====== Fungsi Local Storage ======
                function saveToLocalStorage(key, data) {
                    localStorage.setItem(key, JSON.stringify(data));
                }

                function loadFromLocalStorage(key) {
                    const data = localStorage.getItem(key);
                    return data ? JSON.parse(data) : [];
                }

                // ====== Global State ======
                window.selectedPic = new Map(loadFromLocalStorage('selectedPic').map(item => [item.id, item]));
                window.selectedAbsent = new Map(loadFromLocalStorage('selectedAbsent').map(item => [item.id, item]));

                // ====== Elemen DOM ======
                const selectedPicTable = document.querySelector('#selectedPicTable tbody');
                const selectedAbsentTable = document.querySelector('#selectedAbsentTable tbody');
                const picList = document.getElementById('picList');
                const absentList = document.getElementById('absentList');
                const baseUrl = "{{ url('wi-documents/view') }}";
                const selectedFileTableBody = document.querySelector('.selected-files tbody');
                const btnReset = document.getElementById('btn-reset');

                // ====== Helper Function ======
                function refreshEmptyState(table, message) {
                    if (table.children.length === 0) {
                        table.innerHTML = `<tr class="text-center"><td colspan="3">${message}</td></tr>`;
                    }
                }

                function addToSelectedTable(tableBody, name, dept, id, type) {
                    const emptyRow = tableBody.querySelector('.text-center');
                    if (emptyRow) emptyRow.remove();
                    if (tableBody.querySelector(`tr[data-id="${id}"]`)) return;

                    const row = document.createElement('tr');
                    row.dataset.id = id;
                    row.innerHTML = `
                        <td>${name}</td>
                        <td>${dept}</td>
                        <td>
                            <button class="btn btn-danger btn-sm remove-${type}" data-id="${id}">
                                Remove
                            </button>
                        </td>
                    `;
                    tableBody.appendChild(row);
                }

                function syncSelectionButtons() {
                    document.querySelectorAll('#picList .select-pic').forEach(btn => {
                        const id = btn.closest('tr').dataset.id;
                        if (window.selectedPic.has(id)) {
                            btn.innerHTML = '<i class="bi bi-check-circle"></i> Selected';
                            btn.disabled = true;
                        } else if (window.selectedAbsent.has(id)) {
                            btn.innerHTML = '<i class="bi bi-x-circle"></i> In Absent';
                            btn.disabled = true;
                        } else {
                            btn.innerHTML = 'Select';
                            btn.disabled = false;
                        }
                    });

                    document.querySelectorAll('#absentList .select-absent').forEach(btn => {
                        const id = btn.closest('tr').dataset.id;
                        if (window.selectedAbsent.has(id)) {
                            btn.innerHTML = '<i class="bi bi-check-circle"></i> Selected';
                            btn.disabled = true;
                        } else if (window.selectedPic.has(id)) {
                            btn.innerHTML = '<i class="bi bi-x-circle"></i> In PIC';
                            btn.disabled = true;
                        } else {
                            btn.innerHTML = 'Select';
                            btn.disabled = false;
                        }
                    });
                }

                // ====== Restore dari Local Storage ======
                window.selectedPic.forEach(item => {
                    addToSelectedTable(selectedPicTable, item.display_name, item.Departement, item.id, 'pic');
                });
                window.selectedAbsent.forEach(item => {
                    addToSelectedTable(selectedAbsentTable, item.display_name, item.Departement, item.id,
                        'absent');
                });

                refreshEmptyState(selectedPicTable, 'No PIC selected');
                refreshEmptyState(selectedAbsentTable, 'No Absent selected');
                syncSelectionButtons();

                // ====== Event: Pilih PIC ======
                if (picList) {
                    picList.addEventListener('click', (e) => {
                        if (!e.target.classList.contains('select-pic')) return;

                        const tr = e.target.closest('tr');
                        const id = tr.dataset.id;
                        const display_name = tr.children[2].textContent.trim();
                        const Departement = tr.children[3].textContent.trim();
                        const nik = tr.children[1].textContent.trim();
                        const img = tr.querySelector('img')?.src || '';

                        if (selectedAbsent.has(id)) {
                            alert("This employee is already selected as Absent!");
                            return;
                        }

                        if (!selectedPic.has(id)) {
                            selectedPic.set(id, {
                                id,
                                display_name,
                                Departement,
                                nik,
                                img
                            });
                            addToSelectedTable(selectedPicTable, display_name, Departement, id, 'pic');
                            e.target.innerHTML = '<i class="bi bi-check-circle"></i> Selected';
                            e.target.disabled = true;

                            const absentRow = absentList?.querySelector(`tr[data-id="${id}"] .select-absent`);
                            if (absentRow) {
                                absentRow.innerHTML = '<i class="bi bi-x-circle"></i> In PIC';
                                absentRow.disabled = true;
                            }

                            saveToLocalStorage('selectedPic', Array.from(window.selectedPic.values()));
                            syncSelectionButtons();
                            if (typeof updateSlideshowButtonState === 'function') updateSlideshowButtonState();
                        }
                    });
                }

                // ====== Event: Pilih Absent ======
                if (absentList) {
                    absentList.addEventListener('click', (e) => {
                        if (!e.target.classList.contains('select-absent')) return;

                        const tr = e.target.closest('tr');
                        const id = tr.dataset.id;
                        const display_name = tr.children[2].textContent.trim();
                        const Departement = tr.children[3].textContent.trim();
                        const nik = tr.children[1].textContent.trim();
                        const img = tr.querySelector('img')?.src || '';

                        if (selectedPic.has(id)) {
                            alert("This employee is already selected as PIC!");
                            return;
                        }

                        if (!selectedAbsent.has(id)) {
                            selectedAbsent.set(id, {
                                id,
                                display_name,
                                Departement,
                                nik,
                                img
                            });
                            addToSelectedTable(selectedAbsentTable, display_name, Departement, id, 'absent');
                            e.target.innerHTML = '<i class="bi bi-check-circle"></i> Selected';
                            e.target.disabled = true;

                            const picRow = picList?.querySelector(`tr[data-id="${id}"] .select-pic`);
                            if (picRow) {
                                picRow.innerHTML = '<i class="bi bi-x-circle"></i> In Absent';
                                picRow.disabled = true;
                            }

                            saveToLocalStorage('selectedAbsent', Array.from(window.selectedAbsent.values()));
                            syncSelectionButtons();
                            if (typeof updateSlideshowButtonState === 'function') updateSlideshowButtonState();
                        }
                    });
                }

                // ====== Event: Remove PIC / Absent ======
                document.addEventListener('click', (e) => {
                    // Hapus PIC
                    if (e.target.closest('.remove-pic')) {
                        const id = e.target.closest('.remove-pic').dataset.id;
                        window.selectedPic.delete(id);
                        e.target.closest('tr').remove();
                        saveToLocalStorage('selectedPic', Array.from(window.selectedPic.values()));
                        refreshEmptyState(selectedPicTable, 'No PIC selected');
                        syncSelectionButtons();
                    }

                    // Hapus Absent
                    if (e.target.closest('.remove-absent')) {
                        const id = e.target.closest('.remove-absent').dataset.id;
                        window.selectedAbsent.delete(id);
                        e.target.closest('tr').remove();
                        saveToLocalStorage('selectedAbsent', Array.from(window.selectedAbsent.values()));
                        refreshEmptyState(selectedAbsentTable, 'No Absent selected');
                        syncSelectionButtons();
                    }

                    if (typeof updateSlideshowButtonState === 'function') updateSlideshowButtonState();
                });
                let selectedFiles = JSON.parse(localStorage.getItem('selectedFiles') || '[]');

                if (selectedFiles.length > 0) {
                    const emptyRow = selectedFileTableBody.querySelector('.no-file-row');
                    if (emptyRow) emptyRow.remove();

                    selectedFiles.forEach(file => {
                        const row = document.createElement('tr');
                        row.dataset.path = file.path;
                        row.innerHTML = `
            <td>${file.name}</td>
            <td>
                <a href="#" class="btn btn-sm btn-danger remove-file" data-path="${file.path}">Remove</a>
                <button type="button" class="btn btn-sm btn-primary view-file" data-path="${baseUrl}/${encodeURI(file.path)}">View</button>
            </td>`;
                        selectedFileTableBody.appendChild(row);

                        // Cek checkbox jika file masih ada di folder
                        const cb = document.querySelector(`.select-file[data-path="${file.path}"]`);
                        if (cb) cb.checked = true;
                    });
                }

                // 🔹 Fungsi update localStorage saat file berubah
                function saveSelectedFiles() {
                    const rows = Array.from(selectedFileTableBody.querySelectorAll('tr[data-path]')).map(row => ({
                        name: row.children[0].textContent.trim(),
                        path: row.dataset.path
                    }));
                    localStorage.setItem('selectedFiles', JSON.stringify(rows));
                    selectedFiles = rows;
                }

                // 🔹 Event ketika memilih file
                document.querySelectorAll('.select-file').forEach(cb => {
                    cb.addEventListener('change', function() {
                        const name = this.dataset.name;
                        const path = this.dataset.path;

                        if (this.checked) {
                            const emptyRow = selectedFileTableBody.querySelector('.no-file-row');
                            if (emptyRow) emptyRow.remove();

                            const row = document.createElement('tr');
                            row.dataset.path = path;
                            row.innerHTML = `
                <td>${name}</td>
                <td class="text-end">
                    <a href="#" class="btn btn-sm btn-danger remove-file" data-path="${path}">Remove</a>
                    <button type="button" class="btn btn-sm btn-primary view-file" data-path="${baseUrl}/${encodeURI(path)}">View</button>
                </td>`;
                            selectedFileTableBody.appendChild(row);
                        } else {
                            const existing = selectedFileTableBody.querySelector(
                                `tr[data-path="${path}"]`);
                            if (existing) existing.remove();
                            updateFileEmptyRow();
                        }

                        saveSelectedFiles();
                        updateSlideshowButtonState?.(); // tetap sinkron
                    });
                });

                // 🔹 Hapus file dari tabel dan localStorage
                selectedFileTableBody.addEventListener('click', function(e) {
                    if (e.target.closest('.remove-file')) {
                        e.preventDefault();
                        const btn = e.target.closest('.remove-file');
                        const path = btn.dataset.path;
                        const row = selectedFileTableBody.querySelector(`tr[data-path="${path}"]`);
                        const cb = document.querySelector(`.select-file[data-path="${path}"]`);

                        if (row) row.remove();
                        if (cb) cb.checked = false;

                        saveSelectedFiles();
                        updateFileEmptyRow();
                        updateSlideshowButtonState?.();
                    }
                });

                updateFileEmptyRow();

                btnReset.addEventListener('click', function(e) {
                    e.preventDefault(); // Hentikan navigasi default

                    // Clear localStorage
                    localStorage.removeItem('selectedFiles');
                    localStorage.removeItem('selectedPic');
                    localStorage.removeItem('selectedAbsent');

                    // Clear memory
                    selectedFiles = [];
                    selectedPic.clear();
                    selectedAbsent.clear();

                    // Reset file table
                    selectedFileTableBody.innerHTML =
                        `<tr class="text-center no-file-row font-custom"><td colspan="4">No file selected</td></tr>`;

                    // Reset PIC & Absent tables
                    selectedPicTable.innerHTML =
                        `<tr class="text-center font-custom"><td colspan="3">No PIC selected</td></tr>`;
                    selectedAbsentTable.innerHTML =
                        `<tr class="text-center font-custom"><td colspan="3">No Absent selected</td></tr>`;

                    // Reset semua checkbox file
                    document.querySelectorAll('.select-file').forEach(cb => {
                        cb.checked = false;
                        cb.disabled = false;
                    });

                    // Reset tombol PIC
                    if (picList) {
                        picList.querySelectorAll('.select-pic').forEach(btn => {
                            btn.innerHTML = 'Select';
                            btn.disabled = false;
                        });
                    }

                    // Reset tombol Absent
                    if (absentList) {
                        absentList.querySelectorAll('.select-absent').forEach(btn => {
                            btn.innerHTML = 'Select';
                            btn.disabled = false;
                        });
                    }

                    // Update empty row helper & slideshow
                    refreshEmptyState(selectedPicTable, 'No PIC selected');
                    refreshEmptyState(selectedAbsentTable, 'No Absent selected');
                    updateFileEmptyRow();
                    syncSelectionButtons();
                    updateSlideshowButtonState?.();
                });

                function updateFileEmptyRow() {
                    const emptyRow = selectedFileTableBody.querySelector('.no-file-row');
                    const hasFiles = selectedFileTableBody.querySelectorAll('tr:not(.no-file-row)').length > 0;
                    if (hasFiles && emptyRow) emptyRow.remove();
                    else if (!hasFiles && !emptyRow) {
                        const row = document.createElement('tr');
                        row.classList.add('text-center', 'no-file-row', 'font-custom');
                        row.innerHTML = `<td colspan="4">No file selected</td>`;
                        selectedFileTableBody.appendChild(row);
                    }
                }
            });
            document.addEventListener('DOMContentLoaded', function() {
                const pdfModalEl = document.getElementById('pdfModal');
                const pdfModal = new bootstrap.Modal(pdfModalEl);
                const pdfViewer = document.getElementById('pdfViewer');

                document.addEventListener('click', function(e) {
                    const btn = e.target.closest('.view-pdf') || e.target.closest('.view-file');
                    if (!btn) return;
                    e.preventDefault();

                    const pdfUrl = btn.dataset.path; // ambil URL dari data-path

                    // Tampilkan PDF langsung di iframe (bawaan browser)
                    pdfViewer.src = pdfUrl;
                    pdfModal.show();
                });

                // Kosongkan iframe saat modal ditutup
                pdfModalEl.addEventListener('hidden.bs.modal', function() {
                    pdfViewer.src = '';
                });
            });
            document.addEventListener('DOMContentLoaded', function() {
                const selectedFileTableBody = document.querySelector('.selected-files tbody');
                const btnSlideshow = document.getElementById('btn-slideshow');

                window.updateSlideshowButtonState = function() {
                    const selectedFileTableBody = document.querySelector('.selected-files tbody');
                    const btnSlideshow = document.getElementById('btn-slideshow');

                    const hasFiles = selectedFileTableBody.querySelectorAll('tr:not(.no-file-row)').length > 0;
                    const hasPics = window.selectedPic && window.selectedPic.size > 0;
                    const hasAbsents = window.selectedAbsent && window.selectedAbsent.size > 0;

                    if (hasFiles && hasPics) {
                        btnSlideshow.classList.remove('disabled');
                    } else {
                        btnSlideshow.classList.add('disabled');
                    }
                };


                document.querySelectorAll('.select-file').forEach(cb => {
                    cb.addEventListener('change', updateSlideshowButtonState);
                });
                document.addEventListener('click', e => {
                    if (e.target.classList.contains('remove') || e.target.classList.contains('remove-file')) {
                        updateSlideshowButtonState();
                    }
                });
                document.getElementById('btn-reset').addEventListener('click', updateSlideshowButtonState);
            });
            // Fullscreen PDF
            document.addEventListener('DOMContentLoaded', function() {
                const btnSlideshow = document.getElementById('btn-slideshow');
                const fullscreenContainer = document.getElementById('fullscreenContainer');
                const pdfCanvas = document.getElementById('fullscreen-pdf-canvas');
                const ctx = pdfCanvas.getContext('2d');

                const picBody = document.getElementById('fullscreenPICBody');
                const absentBody = document.getElementById('fullscreenAbsentBody');

                let selectedFiles = [];
                let currentIndex = 0;
                let slideshowInterval = null;
                let isTransitioning = false;
                let currentDirection = 'right';

                let pdfDoc = null;
                let currentPage = 1;
                let totalPages = 0;

                async function renderPDF(url, direction = 'right') {
                    if (isTransitioning) return;
                    isTransitioning = true;
                    currentDirection = direction;

                    try {
                        // 🔹 Animasi keluar
                        pdfCanvas.style.transform = direction === 'right' ? "translateX(-100%)" :
                            "translateX(100%)";
                        pdfCanvas.style.opacity = "0";

                        await new Promise(resolve => setTimeout(resolve, 600));

                        // 🔹 Muat PDF jika beda file
                        if (!pdfDoc || pdfDoc.url !== url) {
                            const loadingTask = pdfjsLib.getDocument(url);
                            pdfDoc = await loadingTask.promise;
                            pdfDoc.url = url; // simpan untuk identifikasi
                            totalPages = pdfDoc.numPages;
                            currentPage = 1;
                        }

                        // 🔹 Render halaman saat ini
                        const page = await pdfDoc.getPage(currentPage);
                        const containerWidth = pdfCanvas.parentElement.clientWidth;
                        const containerHeight = pdfCanvas.parentElement.clientHeight;

                        const unscaledViewport = page.getViewport({
                            scale: 1
                        });
                        const scale = Math.min(
                            containerWidth / unscaledViewport.width,
                            containerHeight / unscaledViewport.height
                        );
                        const viewport = page.getViewport({
                            scale
                        });

                        pdfCanvas.width = viewport.width;
                        pdfCanvas.height = viewport.height;

                        await page.render({
                            canvasContext: ctx,
                            viewport
                        }).promise;

                        // 🔹 Animasi masuk
                        pdfCanvas.style.transform = direction === 'right' ? "translateX(100%)" :
                            "translateX(-100%)";
                        pdfCanvas.style.opacity = "0";

                        pdfCanvas.offsetHeight; // reflow

                        pdfCanvas.style.transform = "translateX(0)";
                        pdfCanvas.style.opacity = "1";

                        await new Promise(resolve => setTimeout(resolve, 600));

                        isTransitioning = false;
                    } catch (err) {
                        console.error("Gagal render PDF:", err);
                        isTransitioning = false;
                    }
                }

                function nextSlide() {
                    if (selectedFiles.length === 0 || isTransitioning) return;

                    if (pdfDoc && currentPage < totalPages) {
                        // Halaman berikut dalam file yang sama
                        currentPage++;
                        renderPDF(selectedFiles[currentIndex], 'right');
                    } else {
                        // File berikut
                        currentIndex = (currentIndex + 1) % selectedFiles.length;
                        pdfDoc = null;
                        renderPDF(selectedFiles[currentIndex], 'right');
                    }
                }

                function previousSlide() {
                    if (selectedFiles.length === 0 || isTransitioning) return;

                    if (pdfDoc && currentPage > 1) {
                        currentPage--;
                        renderPDF(selectedFiles[currentIndex], 'left');
                    } else {
                        currentIndex = (currentIndex - 1 + selectedFiles.length) % selectedFiles.length;
                        pdfDoc = null;
                        renderPDF(selectedFiles[currentIndex], 'left');
                    }
                }

                btnSlideshow.addEventListener('click', async function(e) {
                    e.preventDefault();
                    if (this.classList.contains('disabled')) return;

                    selectedFiles = Array.from(document.querySelectorAll('.selected-files tbody tr'))
                        .filter(row => !row.classList.contains('no-file-row'))
                        .map(row => {
                            const path = row.dataset.path || row.querySelector('.remove-file')?.dataset
                                .path;
                            return "{{ url('wi-documents/view') }}/" + encodeURI(path);
                        });

                    if (!selectedFiles.length) {
                        alert("No files selected.");
                        return;
                    }

                    // Bersihkan konten sidebar
                    picBody.innerHTML = '';
                    absentBody.innerHTML = '';

                    // PIC
                    window.selectedPic.forEach(({
                        nik,
                        display_name,
                        img
                    }) => {
                        picBody.innerHTML += `
                            <tr><td class="text-center">
                                <div class="mb-3">
                                <img src="${img}" class="img-fluid pic-absent-img mb-2" style="border-radius: 15px;">
                                <strong style="font-size: 0.8rem; display:block;">${display_name}</strong>
                                <small>${nik}</small>
                                </div>
                            </td></tr>`;
                    });

                    // Absent
                    if (window.selectedAbsent && window.selectedAbsent.size > 0) {
                        window.selectedAbsent.forEach(({
                            nik,
                            display_name,
                            img
                        }) => {
                            absentBody.innerHTML += `
                            <tr><td class="text-center">
                                <div class="mb-3">
                                <img src="${img}" class="img-fluid pic-absent-img mb-2" style="border-radius: 15px;">
                                <strong style="font-size: 0.8rem; display:block;">${display_name}</strong>
                                <small>${nik}</small>
                                </div>
                            </td></tr>`;
                        });
                    } else {
                        absentBody.innerHTML = `
                        <tr><td class="text-center">No Absent selected</td></tr>`;
                    }

                    fullscreenContainer.style.display = 'block';
                    try {
                        if (!document.fullscreenElement) await fullscreenContainer.requestFullscreen();
                    } catch (err) {
                        console.error("Fullscreen gagal:", err);
                    }

                    currentIndex = 0;

                    // Reset animasi untuk pertama kali
                    pdfCanvas.classList.remove('slide-right-enter', 'slide-right-exit',
                        'slide-right-active');
                    pdfCanvas.classList.remove('slide-left-enter', 'slide-left-exit', 'slide-left-active');
                    pdfCanvas.style.opacity = "1";

                    await renderPDF(selectedFiles[currentIndex], 'right');

                    if (selectedFiles.length > 1) {
                        if (slideshowInterval) clearInterval(slideshowInterval);
                        slideshowInterval = setInterval(nextSlide, 5000);
                    }
                });

                document.addEventListener('fullscreenchange', () => {
                    if (!document.fullscreenElement) {
                        fullscreenContainer.style.display = 'none';
                        clearInterval(slideshowInterval);
                        ctx.clearRect(0, 0, pdfCanvas.width, pdfCanvas.height);
                        isTransitioning = false;
                    }
                });
            });
        </script>
    @endpush
@endsection
