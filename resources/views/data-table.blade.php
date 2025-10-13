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
                                    <h5 class="card-title">Selected File</h5>
                                </div>
                                <div>
                                    <a href="#" id="btn-slideshow" class="btn btn-sm btn-info text-white disabled">
                                        <i class="bi bi-play-btn"></i> Slideshow
                                    </a>
                                    <button id="btnResetSelection" class="btn btn-sm btn-warning text-white"><i
                                            class="bi bi-x-circle"></i>
                                        Reset Selection</button>
                                </div>
                            </div>
                            <table class="table selected-files">
                                <thead>
                                    <tr>
                                        <th>Filename</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="text-center no-file-row">
                                        <td colspan="4">No file selected</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="row">
                                <div class="col-lg-6">
                                    <h5 class="card-title">Selected PIC</h5>
                                    <table class="table" id="selectedPicTable">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Department</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="text-center">
                                                <td colspan="3">No PIC selected</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-lg-6">
                                    <h5 class="card-title">Selected Absent</h5>
                                    <table class="table" id="selectedAbsentTable">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Department</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="text-center">
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
            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <div class="card-body">
                            <div
                                class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
                                <div>
                                    <h5 class="card-title">Work Instruction Document</h5>
                                </div>
                                <div>
                                    <a href="#" class="btn btn-sm btn-secondary"><i class="bi bi-arrow-repeat"></i>
                                        Sync Folder</a>
                                </div>
                            </div>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item">
                                        <a href="{{ route('wi-document.index') }}">Home</a>
                                    </li>
                                    @if (!empty($breadcrumb))
                                        @foreach ($breadcrumb as $crumb)
                                            <li class="breadcrumb-item">
                                                <a href="{{ route('wi-document.index', ['path' => $crumb['path']]) }}">
                                                    {{ $crumb['name'] }}
                                                </a>
                                            </li>
                                        @endforeach
                                    @endif
                                </ol>
                            </nav>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Size</th>
                                        <th>Modified</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($items as $item)
                                        @if ($item['type'] === 'folder')
                                            <tr>
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
                                            <tr>
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
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-person-lines-fill"></i> Select PIC & Absent Employees</span>
                        </div>
                        <div id="employeeList">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-lg-6">
                                        <div class="border rounded p-2">
                                            <h6 class="fw-bold mb-3 text-center text-success">PIC List</h6>
                                            <table class="table table-sm align-middle" id="tablePic">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Photo</th>
                                                        <th>NIK</th>
                                                        <th>Name</th>
                                                        <th>Department</th>
                                                        <th style="width: 180px">Action</th>
                                                    </tr>
                                                </thead>
                                                {{-- <tbody id="picList">
                                                    @foreach ($employees as $employee)
                                                        <tr data-id="{{ $employee->id }}">
                                                            <td>
                                                                <img src="{{ 'http://192.168.61.8/photos/employee/' . $employee->photo }}"
                                                                    alt="Photo" width="45" height="45"
                                                                    class="rounded-circle" loading="lazy">
                                                                <img src="{{ asset('assets/img/' . $employee->photo) }}"
                                                                    alt="Photo" width="45" height="45"
                                                                    class="rounded-circle" loading="lazy">
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
                                                </tbody> --}}
                                            </table>
                                        </div>
                                    </div>
                                    <div class="col-lg-6">
                                        <div class="border rounded p-2">
                                            <h6 class="fw-bold mb-3 text-center text-danger">Absent List</h6>
                                            <table id="tableAbsent" class="table table-sm align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Photo</th>
                                                        <th>Nik</th>
                                                        <th>Name</th>
                                                        <th>Department</th>
                                                        <th style="width: 180px">Action</th>
                                                    </tr>
                                                </thead>
                                                {{-- <tbody id="absentList">
                                                    @foreach ($employees as $employee)
                                                        <tr data-id="{{ $employee->id }}">
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
                                                </tbody> --}}
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
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0">
                            <canvas id="pdfCanvas" style="width: 100%;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fullscreen Content -->
            <div id="fullscreenContainer">
                <div class="container-fluid h-100 p-0">
                    <div class="d-flex h-100">

                        <!-- PIC LINE -->
                        <div class="sidebar-section">
                            <div class="card h-100 border-0 rounded-0">
                                <div class="card-header text-center bg-white border-bottom">
                                    <img src="{{ asset('assets/img/siix-access-banner.png') }}" alt="Siix Banner"
                                        class="img-fluid mb-3">
                                    <strong>PIC Line</strong>
                                </div>
                                <div class="card-body p-2 d-flex flex-column bg-white justify-content-center">
                                    <table class="table table-sm mb-0 table-borderless">
                                        <tbody id="fullscreenPICBody">
                                            <!-- Rendered by JS -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- PDF CANVAS SLIDESHOW -->
                        <div class="flex-grow-1 d-flex flex-column main-canvas-area">
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
                                <div class="card-header text-center bg-white border-bottom">
                                    <img src="{{ asset('assets/img/siix-access-banner.png') }}" alt="Siix Banner"
                                        class="img-fluid mb-3">
                                    <strong>Absent</strong>
                                </div>
                                <div class="card-body p-2 d-flex flex-column bg-white justify-content-center">
                                    <table class="table table-sm mb-0 table-borderless">
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
            // Global Variable
            window.selectedPic = window.selectedPic || new Map();
            window.selectedAbsent = window.selectedAbsent || new Map();

            document.addEventListener('DOMContentLoaded', function() {
                // // ================== VARIABEL ==================
                // window.selectedPic = new Set();
                // window.selectedAbsent = new Set();

                // const picList = document.getElementById('picList');
                // const absentList = document.getElementById('absentList');
                // const selectedPicTable = document.querySelector('#selectedPicTable tbody');
                // const selectedAbsentTable = document.querySelector('#selectedAbsentTable tbody');
                const selectedFileTableBody = document.querySelector('.selected-files tbody');
                // const btnReset = document.getElementById('btn-reset');

                const toggleBtn = document.getElementById('toggleBtn');
                const toggleIcon = toggleBtn ? toggleBtn.querySelector('i') : null;
                const toggleText = toggleBtn ? toggleBtn.firstChild : null;
                const collapseEl = document.getElementById('collapseExample');

                // Helper
                function refreshEmptyState(table, message) {
                    if (table.children.length === 0) {
                        table.innerHTML = `<tr class="text-center"><td colspan="3">${message}</td></tr>`;
                    }
                }

                // function addToSelectedTable(tableBody, name, dept, id, type) {
                //     const emptyRow = tableBody.querySelector('.text-center');
                //     if (emptyRow) emptyRow.remove();

                //     if (tableBody.querySelector(`tr[data-id="${id}"]`)) {
                //         return;
                //     }

                //     const row = document.createElement('tr');
                //     row.dataset.id = id;
                //     row.innerHTML = `
        //             <td>${name}</td>
        //             <td>${dept}</td>
        //             <td>
        //                 <button class="btn btn-danger btn-sm remove-${type}" data-id="${id}">
        //                     <i class="bi bi-x-circle"></i> Remove
        //                 </button>
        //             </td>
        //         `;
                //     tableBody.appendChild(row);
                // }


                function updateFileEmptyRow() {
                    const emptyRow = selectedFileTableBody.querySelector('.no-file-row');
                    const hasFiles = selectedFileTableBody.querySelectorAll('tr:not(.no-file-row)').length > 0;
                    if (hasFiles && emptyRow) emptyRow.remove();
                    else if (!hasFiles && !emptyRow) {
                        const row = document.createElement('tr');
                        row.classList.add('text-center', 'no-file-row');
                        row.innerHTML = `<td colspan="4">No file selected</td>`;
                        selectedFileTableBody.appendChild(row);
                    }
                }



                // // Event ketika select PIC
                // if (picList) {
                //     picList.addEventListener('click', (e) => {
                //         if (!e.target.classList.contains('select-pic')) return;

                //         const tr = e.target.closest('tr');
                //         const id = tr.dataset.id;
                //         const display_name = tr.children[2].textContent.trim();
                //         const Departement = tr.children[3].textContent.trim();

                //         if (selectedAbsent.has(id)) {
                //             alert("This employee is already selected as Absent!");
                //             return;
                //         }

                //         if (!selectedPic.has(id)) {
                //             selectedPic.set(id, {
                //                 id,
                //                 display_name,
                //                 Departement,
                //                 nik: tr.children[1].textContent.trim(),
                //                 img: tr.querySelector('img')?.src || ''
                //             });
                //             addToSelectedTable(selectedPicTable, display_name, Departement, id, 'pic');
                //             e.target.innerHTML = '<i class="bi bi-check-circle"></i> Selected';
                //             e.target.disabled = true;

                //             const absentRow = absentList?.querySelector(`tr[data-id="${id}"] .select-absent`);
                //             if (absentRow) {
                //                 absentRow.innerHTML = '<i class="bi bi-x-circle"></i> In PIC';
                //                 absentRow.disabled = true;
                //             }
                //             syncSelectionButtons();
                //             updateSlideshowButtonState();
                //         }
                //     });
                // }

                // // Event ketika select absent
                // if (absentList) {
                //     absentList.addEventListener('click', (e) => {
                //         if (!e.target.classList.contains('select-absent')) return;

                //         const tr = e.target.closest('tr');
                //         const id = tr.dataset.id;
                //         const display_name = tr.children[2].textContent.trim();
                //         const Departement = tr.children[3].textContent.trim();

                //         if (selectedPic.has(id)) {
                //             alert("This employee is already selected as PIC!");
                //             return;
                //         }

                //         if (!selectedAbsent.has(id)) {
                //             selectedAbsent.set(id, {
                //                 id,
                //                 display_name,
                //                 Departement,
                //                 nik: tr.children[1].textContent.trim(),
                //                 img: tr.querySelector('img')?.src || ''
                //             });
                //             addToSelectedTable(selectedAbsentTable, display_name, Departement, id, 'absent');
                //             e.target.innerHTML = '<i class="bi bi-check-circle"></i> Selected';
                //             e.target.disabled = true;

                //             const picRow = picList?.querySelector(`tr[data-id="${id}"] .select-pic`);
                //             if (picRow) {
                //                 picRow.innerHTML = '<i class="bi bi-x-circle"></i> In Absent';
                //                 picRow.disabled = true;
                //             }
                //             syncSelectionButtons();
                //             updateSlideshowButtonState();
                //         }
                //     });
                // }

                // // Event ketika remove PIC dan Absent
                // document.addEventListener('click', function(e) {
                //     // Hapus PIC
                //     if (e.target.closest('.remove-pic')) {
                //         const btn = e.target.closest('.remove-pic');
                //         const id = btn.dataset.id;

                //         // Hapus dari Map
                //         window.selectedPic.delete(id);

                //         // Hapus baris di tabel Selected PIC
                //         btn.closest('tr').remove();

                //         // Kalau tabel kosong, munculkan placeholder lagi
                //         const tableBody = document.querySelector('#selectedPicTable tbody');
                //         if (tableBody.children.length === 0) {
                //             tableBody.innerHTML = `
        //             <tr class="text-center">
        //                 <td colspan="3">No PIC selected</td>
        //             </tr>`;
                //         }

                //         // Ubah tombol Select di daftar utama (PIC List)
                //         const selectBtn = document.querySelector(`#picList tr[data-id="${id}"] .select-pic`);
                //         if (selectBtn) {
                //             selectBtn.disabled = false;
                //             selectBtn.innerHTML = 'Select';
                //         }

                //         // Juga ubah tombol Absent agar bisa dipilih lagi (jika sebelumnya dinonaktifkan)
                //         const absentBtn = document.querySelector(
                //             `#absentList tr[data-id="${id}"] .select-absent`);
                //         if (absentBtn) {
                //             absentBtn.disabled = false;
                //             absentBtn.innerHTML = 'Select';
                //         }
                //     }

                //     // Hapus Absent
                //     if (e.target.closest('.remove-absent')) {
                //         const btn = e.target.closest('.remove-absent');
                //         const id = btn.dataset.id;

                //         // Hapus dari Map
                //         window.selectedAbsent.delete(id);

                //         // Hapus baris di tabel Selected Absent
                //         btn.closest('tr').remove();

                //         // Kalau tabel kosong, munculkan placeholder lagi
                //         const tableBody = document.querySelector('#selectedAbsentTable tbody');
                //         if (tableBody.children.length === 0) {
                //             tableBody.innerHTML = `
        //             <tr class="text-center">
        //                 <td colspan="3">No Absent selected</td>
        //             </tr>`;
                //         }

                //         // Ubah tombol Select di daftar utama (Absent List)
                //         const selectBtn = document.querySelector(
                //             `#absentList tr[data-id="${id}"] .select-absent`);
                //         if (selectBtn) {
                //             selectBtn.disabled = false;
                //             selectBtn.innerHTML = 'Select';
                //         }

                //         // Juga ubah tombol PIC agar bisa dipilih lagi
                //         const picBtn = document.querySelector(`#picList tr[data-id="${id}"] .select-pic`);
                //         if (picBtn) {
                //             picBtn.disabled = false;
                //             picBtn.innerHTML = 'Select';
                //         }
                //     }

                //     // Setelah remove, update tombol slideshow juga
                //     if (typeof updateSlideshowButtonState === 'function') {
                //         updateSlideshowButtonState();
                //     }
                //     syncSelectionButtons();
                // });

                // function syncSelectionButtons() {
                //     // Sinkron tombol PIC
                //     document.querySelectorAll('#picList .select-pic').forEach(btn => {
                //         const id = btn.closest('tr').dataset.id;

                //         if (window.selectedPic.has(id)) {
                //             btn.innerHTML = '<i class="bi bi-check-circle"></i> Selected';
                //             btn.disabled = true;
                //         } else if (window.selectedAbsent.has(id)) {
                //             btn.innerHTML = '<i class="bi bi-x-circle"></i> In Absent';
                //             btn.disabled = true;
                //         } else {
                //             btn.innerHTML = 'Select';
                //             btn.disabled = false;
                //         }
                //     });

                //     // Sinkron tombol Absent
                //     document.querySelectorAll('#absentList .select-absent').forEach(btn => {
                //         const id = btn.closest('tr').dataset.id;

                //         if (window.selectedAbsent.has(id)) {
                //             btn.innerHTML = '<i class="bi bi-check-circle"></i> Selected';
                //             btn.disabled = true;
                //         } else if (window.selectedPic.has(id)) {
                //             btn.innerHTML = '<i class="bi bi-x-circle"></i> In PIC';
                //             btn.disabled = true;
                //         } else {
                //             btn.innerHTML = 'Select';
                //             btn.disabled = false;
                //         }
                //     });
                // }

                $(document).ready(function() {
                    // ================================
                    // 1️⃣ GLOBAL STATE
                    // ================================
                    window.selectedPic = new Map();
                    window.selectedAbsent = new Map();

                    // ================================
                    // 2️⃣ DATA TABLES
                    // ================================
                    const tablePic = $('#tablePic').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '{{ route('employeesPic.index') }}',
                        columns: [{
                                data: 'photo',
                                name: 'photo',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'nik',
                                name: 'nik'
                            },
                            {
                                data: 'name',
                                name: 'name'
                            },
                            {
                                data: 'department',
                                name: 'department'
                            },
                            {
                                data: 'action',
                                name: 'action',
                                orderable: false,
                                searchable: false
                            }
                        ]
                    });

                    const tableAbsent = $('#tableAbsent').DataTable({
                        processing: true,
                        serverSide: true,
                        ajax: '{{ route('employeesAbsent.index') }}',
                        columns: [{
                                data: 'photo',
                                name: 'photo',
                                orderable: false,
                                searchable: false
                            },
                            {
                                data: 'nik',
                                name: 'nik'
                            },
                            {
                                data: 'name',
                                name: 'name'
                            },
                            {
                                data: 'department',
                                name: 'department'
                            },
                            {
                                data: 'action',
                                name: 'action',
                                orderable: false,
                                searchable: false
                            }
                        ]
                    });

                    // ================================
                    // 3️⃣ FUNGSI TAMBAH KE TABEL TUJUAN
                    // ================================
                    function addToSelectedTable(tableBody, name, dept, id, type) {
                        const emptyRow = tableBody.find('.text-center');
                        if (emptyRow.length) emptyRow.remove();

                        const rowHtml = `
            <tr data-id="${id}">
                <td>${name}</td>
                <td>${dept}</td>
                <td>
                    <button class="btn btn-danger btn-sm remove-${type}">Remove</button>
                </td>
            </tr>
        `;
                        tableBody.append(rowHtml);
                    }

                    // ================================
                    // 4️⃣ FUNGSI SINKRON TOMBOL
                    // ================================
                    function syncSelectionButtons() {
                        // Tombol PIC
                        $('#tablePic button.select-pic').each(function() {
                            const tr = $(this).closest('tr');
                            const data = tablePic.row(tr).data();
                            if (!data) return;

                            const id = data.id;

                            if (window.selectedPic.has(id)) {
                                $(this).html('<i class="bi bi-check-circle"></i> Selected').prop(
                                    'disabled', true);
                            } else if (window.selectedAbsent.has(id)) {
                                $(this).html('<i class="bi bi-x-circle"></i> In Absent').prop(
                                    'disabled', true);
                            } else {
                                $(this).html('Select').prop('disabled', false);
                            }
                        });

                        // Tombol Absent
                        $('#tableAbsent button.select-absent').each(function() {
                            const tr = $(this).closest('tr');
                            const data = tableAbsent.row(tr).data();
                            if (!data) return;

                            const id = data.id;

                            if (window.selectedAbsent.has(id)) {
                                $(this).html('<i class="bi bi-check-circle"></i> Selected').prop(
                                    'disabled', true);
                            } else if (window.selectedPic.has(id)) {
                                $(this).html('<i class="bi bi-x-circle"></i> In PIC').prop('disabled',
                                    true);
                            } else {
                                $(this).html('Select').prop('disabled', false);
                            }
                        });
                    }

                    // ================================
                    // 5️⃣ EVENT: SELECT PIC
                    // ================================
                    $('#tablePic').on('click', '.select-pic', function() {
                        const tr = $(this).closest('tr');
                        const data = tablePic.row(tr).data();
                        if (!data) return;

                        const id = data.id;
                        const name = data.name;
                        const department = data.department;

                        if (window.selectedAbsent.has(id)) {
                            alert('This employee is already selected as Absent!');
                            return;
                        }

                        if (!window.selectedPic.has(id)) {
                            window.selectedPic.set(id, {
                                id,
                                name,
                                department
                            });
                            addToSelectedTable($('#selectedPicTable tbody'), name, department, id,
                                'pic');
                        }

                        syncSelectionButtons();
                    });

                    // ================================
                    // 6️⃣ EVENT: SELECT ABSENT
                    // ================================
                    $('#tableAbsent').on('click', '.select-absent', function() {
                        const tr = $(this).closest('tr');
                        const data = tableAbsent.row(tr).data();
                        if (!data) return;

                        const id = data.id;
                        const name = data.name;
                        const department = data.department;

                        if (window.selectedPic.has(id)) {
                            alert('This employee is already selected as PIC!');
                            return;
                        }

                        if (!window.selectedAbsent.has(id)) {
                            window.selectedAbsent.set(id, {
                                id,
                                name,
                                department
                            });
                            addToSelectedTable($('#selectedAbsentTable tbody'), name, department, id,
                                'absent');
                        }

                        syncSelectionButtons();
                    });

                    // ================================
                    // 7️⃣ EVENT: REMOVE PIC
                    // ================================
                    $('#selectedPicTable').on('click', '.remove-pic', function() {
                        const tr = $(this).closest('tr');
                        const id = tr.data('id');

                        tr.remove();
                        window.selectedPic.delete(id);

                        if ($('#selectedPicTable tbody tr').length === 0) {
                            $('#selectedPicTable tbody').html(
                                '<tr class="text-center"><td colspan="3">No PIC selected</td></tr>');
                        }

                        syncSelectionButtons();
                    });

                    // ================================
                    // 8️⃣ EVENT: REMOVE ABSENT
                    // ================================
                    $('#selectedAbsentTable').on('click', '.remove-absent', function() {
                        const tr = $(this).closest('tr');
                        const id = tr.data('id');

                        tr.remove();
                        window.selectedAbsent.delete(id);

                        if ($('#selectedAbsentTable tbody tr').length === 0) {
                            $('#selectedAbsentTable tbody').html(
                                '<tr class="text-center"><td colspan="3">No Absent selected</td></tr>'
                            );
                        }

                        syncSelectionButtons();
                    });

                    // ================================
                    // 9️⃣ SINKRONISASI SAAT DATATABLES REDRAW
                    // ================================
                    tablePic.on('draw', syncSelectionButtons);
                    tableAbsent.on('draw', syncSelectionButtons);

                    // Pertama kali sinkron
                    syncSelectionButtons();
                });


                const baseUrl = "{{ url('wi-documents/view') }}";
                // Event ketika memilih file
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
                   <td>
                        <a href="#" class="btn btn-sm btn-danger remove-file" data-path="${path}">Remove</a>
                        <button type="button" class="btn btn-sm btn-primary view-file" data-path="${baseUrl}/${encodeURIComponent(path)}">View</button>
                    </td>`;
                            selectedFileTableBody.appendChild(row);
                        } else {
                            const existing = selectedFileTableBody.querySelector(
                                `tr[data-path="${path}"]`);
                            if (existing) existing.remove();
                            updateFileEmptyRow();
                        }
                    });
                });

                selectedFileTableBody.addEventListener('click', function(e) {
                    if (e.target.closest('.remove-file')) {
                        e.preventDefault();
                        const btn = e.target.closest('.remove-file');
                        const path = btn.dataset.path;
                        const row = selectedFileTableBody.querySelector(`tr[data-path="${path}"]`);
                        const cb = document.querySelector(`.select-file[data-path="${path}"]`);

                        if (row) row.remove();
                        if (cb) cb.checked = false;
                        updateFileEmptyRow();
                    }
                });

                updateFileEmptyRow();

                //             // 🔟 EVENT: RESET SEMUA SELECTION
                //             // ================================
                //             $('#btn-reset').on('click', function(e) {
                //                 e.preventDefault();

                //                 // Kosongkan data
                //                 window.selectedPic.clear();
                //                 window.selectedAbsent.clear();

                //                 // Reset tampilan tabel PIC dan Absent terpilih
                //                 $('#selectedPicTable tbody').html(`
        //     <tr class="text-center">
        //         <td colspan="3">No PIC selected</td>
        //     </tr>
        // `);

                //                 $('#selectedAbsentTable tbody').html(`
        //     <tr class="text-center">
        //         <td colspan="3">No Absent selected</td>
        //     </tr>
        // `);

                //                 // Jika kamu juga punya tabel file
                //                 if ($('#selectedFileTable tbody').length) {
                //                     $('#selectedFileTable tbody').html(`
        //         <tr class="text-center no-file-row">
        //             <td colspan="4">No file selected</td>
        //         </tr>
        //     `);
                //                     // Reset checkbox file
                //                     $('.select-file').prop('checked', false);
                //                 }

                //                 // Reset tombol di DataTables
                //                 $('#tablePic button.select-pic').each(function() {
                //                     $(this).text('Select').prop('disabled', false);
                //                 });

                //                 $('#tableAbsent button.select-absent').each(function() {
                //                     $(this).text('Select').prop('disabled', false);
                //                 });

                //                 // Sinkron ulang status
                //                 syncSelectionButtons();

                //                 // Notifikasi kecil (optional)
                //                 toastr.info('All selections have been reset.');
                //             });

                // Event reset selection
                // btnReset.addEventListener('click', function(e) {
                //     e.preventDefault();

                //     selectedPic.clear();
                //     selectedAbsent.clear();

                //     selectedPicTable.innerHTML =
                //         `<tr class="text-center"><td colspan="3">No PIC selected</td></tr>`;
                //     selectedAbsentTable.innerHTML =
                //         `<tr class="text-center"><td colspan="3">No Absent selected</td></tr>`;
                //     selectedFileTableBody.innerHTML =
                //         `<tr class="text-center no-file-row"><td colspan="4">No file selected</td></tr>`;

                //     document.querySelectorAll('.select-file').forEach(cb => cb.checked = false);

                //     if (picList) {
                //         picList.querySelectorAll('.select-pic').forEach(btn => {
                //             btn.textContent = 'Select';
                //             btn.disabled = false;
                //         });
                //     }

                //     if (absentList) {
                //         absentList.querySelectorAll('.select-absent').forEach(btn => {
                //             btn.textContent = 'Select';
                //             btn.disabled = false;
                //         });
                //     }
                //     syncSelectionButtons();
                // });
                // ===============================
                // ================================
                // 🔟 EVENT: RESET SEMUA SELECTION
                // ================================
                $('#btnResetSelection').on('click', function(e) {
                    e.preventDefault();

                    // Kosongkan data
                    window.selectedPic.clear();
                    window.selectedAbsent.clear();

                    // Reset tampilan tabel PIC dan Absent terpilih
                    $('#selectedPicTable tbody').html(`
        <tr class="text-center">
            <td colspan="3">No PIC selected</td>
        </tr>
    `);

                    $('#selectedAbsentTable tbody').html(`
        <tr class="text-center">
            <td colspan="3">No Absent selected</td>
        </tr>
    `);

                    // Jika kamu juga punya tabel file
                    if ($('#selectedFileTable tbody').length) {
                        $('#selectedFileTable tbody').html(`
            <tr class="text-center no-file-row">
                <td colspan="4">No file selected</td>
            </tr>
        `);
                        // Reset checkbox file
                        $('.select-file').prop('checked', false);
                    }

                    // Reset tombol di DataTables
                    $('#tablePic button.select-pic').each(function() {
                        $(this).text('Select').prop('disabled', false);
                    });

                    $('#tableAbsent button.select-absent').each(function() {
                        $(this).text('Select').prop('disabled', false);
                    });

                    // Sinkron ulang status
                    syncSelectionButtons();

                    // Notifikasi kecil (optional)
                    toastr.info('All selections have been reset.');
                });

            });

            // PDF Modal dengan bootstrap
            document.addEventListener('DOMContentLoaded', function() {
                const pdfModalEl = document.getElementById('pdfModal');
                const pdfModal = new bootstrap.Modal(pdfModalEl);
                const canvas = document.getElementById('pdfCanvas');
                const ctx = canvas.getContext('2d');

                let pdfDoc = null;

                document.addEventListener('click', function(e) {
                    const btn = e.target.closest('.view-pdf') || e.target.closest('.view-file');
                    if (!btn) return;
                    e.preventDefault();

                    const pdfUrl = btn.dataset.path;

                    // Load PDF menggunakan PDF.js
                    pdfjsLib.getDocument(pdfUrl).promise.then(pdf => {
                        pdfDoc = pdf;
                        renderPage(1); // tampilkan halaman pertama
                    });

                    pdfModal.show();
                });

                function renderPage(pageNum) {
                    pdfDoc.getPage(pageNum).then(page => {
                        const viewport = page.getViewport({
                            scale: 1.5
                        });
                        canvas.height = viewport.height;
                        canvas.width = viewport.width;

                        const renderContext = {
                            canvasContext: ctx,
                            viewport: viewport
                        };
                        page.render(renderContext);
                    });
                }

                // Kosongkan canvas saat modal ditutup
                pdfModalEl.addEventListener('hidden.bs.modal', function() {
                    ctx.clearRect(0, 0, canvas.width, canvas.height);
                    pdfDoc = null;
                });
            });

            // Disabled and Active button slideshow
            // document.addEventListener('DOMContentLoaded', function() {
            //     const selectedFileTableBody = document.querySelector('.selected-files tbody');
            //     const btnSlideshow = document.getElementById('btn-slideshow');

            //     window.updateSlideshowButtonState = function() {
            //         const selectedFileTableBody = document.querySelector('.selected-files tbody');
            //         const btnSlideshow = document.getElementById('btn-slideshow');

            //         const hasFiles = selectedFileTableBody.querySelectorAll('tr:not(.no-file-row)').length > 0;
            //         const hasPics = window.selectedPic && window.selectedPic.size > 0;
            //         const hasAbsents = window.selectedAbsent && window.selectedAbsent.size > 0;

            //         if (hasFiles && hasPics && hasAbsents) {
            //             btnSlideshow.classList.remove('disabled');
            //         } else {
            //             btnSlideshow.classList.add('disabled');
            //         }
            //     };


            //     document.querySelectorAll('.select-file').forEach(cb => {
            //         cb.addEventListener('change', updateSlideshowButtonState);
            //     });
            //     document.addEventListener('click', e => {
            //         if (e.target.classList.contains('remove') || e.target.classList.contains('remove-file')) {
            //             updateSlideshowButtonState();
            //         }
            //     });
            //     document.getElementById('btn-reset').addEventListener('click', updateSlideshowButtonState);
            // });

            // Slidshow PDF
            // document.addEventListener('DOMContentLoaded', function() {
            //     const btnSlideshow = document.getElementById('btn-slideshow');
            //     const fullscreenContainer = document.getElementById('fullscreenContainer');
            //     const pdfCanvas = document.getElementById('fullscreen-pdf-canvas');
            //     const ctx = pdfCanvas.getContext('2d');

            //     const picBody = document.getElementById('fullscreenPICBody');
            //     const absentBody = document.getElementById('fullscreenAbsentBody');

            //     let selectedFiles = [];
            //     let currentIndex = 0;
            //     let slideshowInterval = null;
            //     let isTransitioning = false;
            //     let currentDirection = 'right';

            //     async function renderPDF(url, direction = 'right') {
            //         if (isTransitioning) return;
            //         isTransitioning = true;
            //         currentDirection = direction;

            //         try {
            //             // Reset semua class animasi
            //             pdfCanvas.classList.remove('slide-right-enter', 'slide-right-exit', 'slide-right-active');
            //             pdfCanvas.classList.remove('slide-left-enter', 'slide-left-exit', 'slide-left-active');

            //             // Animasi keluar
            //             pdfCanvas.classList.add(direction === 'right' ? 'slide-right-exit' : 'slide-left-exit');

            //             // Tunggu animasi keluar selesai
            //             await new Promise(resolve => setTimeout(resolve, 450));

            //             // Render PDF baru
            //             pdfCanvas.style.opacity = '0';
            //             const loadingTask = pdfjsLib.getDocument(url);
            //             const pdfDoc = await loadingTask.promise;
            //             const page = await pdfDoc.getPage(1);
            //             const viewport = page.getViewport({
            //                 scale: 1.5
            //             });

            //             pdfCanvas.width = viewport.width;
            //             pdfCanvas.height = viewport.height;

            //             const renderContext = {
            //                 canvasContext: ctx,
            //                 viewport
            //             };

            //             await page.render(renderContext).promise;

            //             // Setup untuk animasi masuk
            //             pdfCanvas.classList.remove(direction === 'right' ? 'slide-right-exit' : 'slide-left-exit');
            //             pdfCanvas.classList.add(direction === 'right' ? 'slide-right-enter' : 'slide-left-enter');

            //             // Trigger reflow dan animasi masuk
            //             requestAnimationFrame(() => {
            //                 requestAnimationFrame(() => {
            //                     pdfCanvas.style.opacity = '1';
            //                     pdfCanvas.classList.remove(direction === 'right' ?
            //                         'slide-right-enter' : 'slide-left-enter');
            //                     pdfCanvas.classList.add(direction === 'right' ?
            //                         'slide-right-active' : 'slide-left-active');
            //                 });
            //             });

            //             // Reset status setelah animasi selesai
            //             setTimeout(() => {
            //                 isTransitioning = false;
            //                 pdfCanvas.classList.remove('slide-right-active', 'slide-left-active');
            //             }, 800);

            //         } catch (err) {
            //             console.error("Gagal render PDF:", err);
            //             isTransitioning = false;
            //         }
            //     }

            //     function nextSlide() {
            //         if (selectedFiles.length <= 1 || isTransitioning) return;

            //         currentIndex = (currentIndex + 1) % selectedFiles.length;
            //         renderPDF(selectedFiles[currentIndex], 'right');
            //     }

            //     function previousSlide() {
            //         if (selectedFiles.length <= 1 || isTransitioning) return;

            //         currentIndex = (currentIndex - 1 + selectedFiles.length) % selectedFiles.length;
            //         renderPDF(selectedFiles[currentIndex], 'left');
            //     }

            //     btnSlideshow.addEventListener('click', async function(e) {
            //         e.preventDefault();
            //         if (this.classList.contains('disabled')) return;

            //         selectedFiles = Array.from(document.querySelectorAll('.selected-files tbody tr'))
            //             .filter(row => !row.classList.contains('no-file-row'))
            //             .map(row => {
            //                 const path = row.dataset.path || row.querySelector('.remove-file')?.dataset
            //                     .path;
            //                 return "{{ url('wi-documents/view') }}/" + encodeURIComponent(path);
            //             });

            //         if (!selectedFiles.length) {
            //             alert("No files selected.");
            //             return;
            //         }

            //         // Bersihkan konten sidebar
            //         picBody.innerHTML = '';
            //         absentBody.innerHTML = '';

            //         // PIC
            //         window.selectedPic.forEach(({
            //             nik,
            //             display_name,
            //             img
            //         }) => {
            //             picBody.innerHTML += `
    //                 <tr><td class="text-center">
    //                     <div>
    //                     <img src="${img}" class="img-fluid pic-absent-img mb-2" style="border-radius: 15px;">
    //                     <strong style="font-size: 0.8rem; display:block;">${display_name}</strong>
    //                     <small>${nik}</small>
    //                     </div>
    //                 </td></tr>`;
            //         });
            //         // Absent
            //         window.selectedAbsent.forEach(({
            //             nik,
            //             display_name,
            //             img
            //         }) => {
            //             absentBody.innerHTML += `
    //                 <tr><td class="text-center">
    //                     <div>
    //                     <img src="${img}" class="img-fluid pic-absent-img mb-2" style="border-radius: 15px;">
    //                     <strong style="font-size: 0.8rem; display:block;">${display_name}</strong>
    //                     <small>${nik}</small>
    //                     </div>
    //                 </td></tr>`;
            //         });


            //         fullscreenContainer.style.display = 'block';
            //         try {
            //             if (!document.fullscreenElement) await fullscreenContainer.requestFullscreen();
            //         } catch (err) {
            //             console.error("Fullscreen gagal:", err);
            //         }

            //         currentIndex = 0;

            //         // Reset animasi untuk pertama kali
            //         pdfCanvas.classList.remove('slide-right-enter', 'slide-right-exit',
            //             'slide-right-active');
            //         pdfCanvas.classList.remove('slide-left-enter', 'slide-left-exit', 'slide-left-active');
            //         pdfCanvas.style.opacity = "1";

            //         await renderPDF(selectedFiles[currentIndex], 'right');

            //         if (selectedFiles.length > 1) {
            //             if (slideshowInterval) clearInterval(slideshowInterval);
            //             slideshowInterval = setInterval(nextSlide, 5000);
            //         }
            //     });

            //     document.addEventListener('fullscreenchange', () => {
            //         if (!document.fullscreenElement) {
            //             fullscreenContainer.style.display = 'none';
            //             clearInterval(slideshowInterval);
            //             ctx.clearRect(0, 0, pdfCanvas.width, pdfCanvas.height);
            //             isTransitioning = false;
            //         }
            //     });
            // });
        </script>
    @endpush
@endsection
