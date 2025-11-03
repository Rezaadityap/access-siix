@extends('layouts.app')

@section('title')
    OP Kitting
@endsection

@section('content')
    <main id="main" class="main">
        <section class="section dashboard">
            <div class="container">
                <div class="row">
                    <div class="pagetitle">
                        <h1>OP Kitting</h1>
                        <nav>
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                                <li class="breadcrumb-item active">OP Kitting</li>
                            </ol>
                        </nav>
                    </div><!-- End Page Title -->
                    <div class="col-lg-6">
                        <div class="card shadow-lg border-0 rounded-4 card-hover-effect">
                            <div class="card-body p-4">
                                <h4 class="card-title text-center mb-4 fw-bolder gradient-text-primary">
                                    Input & Scan
                                </h4>
                                <div class="d-grid gap-3">
                                    <button class="btn btn-gradient-info py-2 fw-bold rounded-3 shadow-sm"
                                        data-bs-toggle="modal" data-bs-target="#poNumber">
                                        <i class="bi bi-plus-circle me-2"></i> New PO Number
                                    </button>
                                    <button class="btn btn-gradient-secondary py-2 fw-bold rounded-3 shadow-sm"
                                        data-bs-toggle="modal" data-bs-target="#searchPO">
                                        <i class="bi bi-search me-2"></i> Search PO Number
                                    </button>
                                    <button class="btn btn-gradient-success py-2 fw-bold rounded-3 shadow-sm"
                                        data-bs-target="#scanSmd" data-bs-toggle="modal">
                                        <i class="bi bi-bookshelf me-2"></i> Scan Rack SMD Production
                                    </button>
                                    <button class="btn btn-gradient-primary py-2 fw-bold rounded-3 shadow-sm"
                                        data-bs-toggle="modal" data-bs-target="#scanMaterial">
                                        <i class="bi bi-qr-code-scan me-2"></i> Scan WH Material
                                    </button>
                                    <button class="btn btn-gradient-danger py-2 fw-bold rounded-3 shadow-sm"
                                        data-bs-toggle="modal" data-bs-target="#scanSto">
                                        <i class="bi bi-box-seam me-2"></i> Scan STO Material
                                    </button>
                                    <button class="btn btn-gradient-warning py-2 fw-bold rounded-3 shadow-sm"
                                        data-bs-toggle="modal" data-bs-target="#scanMar">
                                        <i class="bi bi-caret-right-square me-2"></i> Scan Material After Running
                                    </button>
                                    <a href="{{ route('op-kitting.history') }}"
                                        class="btn btn-gradient-custom py-2 fw-bold rounded-3 shadow-sm">
                                        <i class="bi bi-clock-history me-2"></i> History Record
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 mx-auto">
                        <div class="card shadow-lg border-0 rounded-4">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h5 class="card-title fw-bolder gradient-text-primary mb-0 d-flex align-items-center">
                                        Record Material Information
                                    </h5>
                                    <button class="btn btn-sm btn-danger text-white rounded-pill px-4 shadow-sm"
                                        id="deletePO">
                                        Delete
                                    </button>
                                </div>
                                <hr class="mt-0 mb-4 text-muted">
                                <form>
                                    <div id="infoContainer"></div>
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <label for="infoProduction" class="form-label small text-muted mb-1">PRODUCTION
                                                AREA</label>
                                            <input type="text"
                                                class="form-control form-control-sm rounded-3 bg-light border-0"
                                                placeholder="Production Area" disabled id="infoProduction">
                                        </div>
                                        <div class="col-6">
                                            <label for="infoLine" class="form-label small text-muted mb-1">LINE AREA</label>
                                            <input type="text"
                                                class="form-control form-control-sm rounded-3 bg-light border-0"
                                                placeholder="Line Area" id="infoLine" disabled>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-6">
                                            <label for="infoChecker"
                                                class="form-label small text-muted mb-1">Checker</label>
                                            <input type="text"
                                                class="form-control form-control-sm rounded-3 bg-light border-0"
                                                value="{{ Auth::user()->name }}">
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="infoDate" class="form-label small text-muted mb-1">DATE</label>
                                            <input type="date"
                                                class="form-control form-control-sm rounded-3 bg-light border-0"
                                                placeholder="Date" id="infoDate" disabled>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-6">
                                            <label for="infoLotSize" class="form-label small text-muted mb-1">LOT
                                                SIZE</label>
                                            <input type="number"
                                                class="form-control form-control-sm rounded-3 bg-light border-0"
                                                placeholder="Lot Size" id="infoLotSize" disabled>
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="infoActual" class="form-label small text-muted mb-1">ACTUAL LOT
                                                SIZE</label>
                                            <input type="number"
                                                class="form-control form-control-sm rounded-3 bg-light border-0"
                                                placeholder="Act Lot Size" id="infoActual" disabled>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Table Record --}}
            @include('table.table-record')
            {{-- Table History --}}
            @include('table.table-history-batch')
        </section>
        {{-- Modal Import PO --}}
        @include('modal.import-po')
        {{-- Modal search record material --}}
        @include('modal.search-record')
        {{-- Scan Rack SMD --}}
        @include('modal.scan-rack-smd')
        {{-- Modal scan WH Material --}}
        @include('modal.scan-material')
        {{-- Modal Scan STO --}}
        @include('modal.scan-sto')
        {{-- Modal Scan MAR --}}
        @include('modal.scan-mar')
    </main>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function updateInfoFields(data) {
            if (!data || (Array.isArray(data) && data.length === 0)) return;

            const newPOs = Array.isArray(data) ? data : [data];

            let saved = localStorage.getItem('currentPO');
            let existingPOs = saved ? JSON.parse(saved) : [];

            if (!Array.isArray(existingPOs)) existingPOs = [existingPOs];

            // Gabungkan data tanpa duplikasi
            newPOs.forEach(po => {
                if (!existingPOs.some(e => e.po_number === po.po_number)) {
                    existingPOs.push(po);
                }
            });

            localStorage.setItem('currentPO', JSON.stringify(existingPOs));

            $('#infoContainer').empty();

            // Render setiap PO ke dalam input baru
            existingPOs.forEach((po, index) => {
                let infoRow = '';
                if (index === 0) {
                    infoRow = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">PO Number</label>
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${po.po_number}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Model</label>
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${po.model}" readonly>
                    </div>
                </div>
                `;
                } else {
                    infoRow = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${po.po_number}" readonly>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${po.model}" readonly>
                    </div>
                </div>
                `;
                }
                $('#infoContainer').append(infoRow);
            });

            // Karena field lain sama, ambil dari data pertama saja
            const base = existingPOs.find(po => po.infoActual) || existingPOs[0];
            console.log('[RESTORE] Menggunakan PO:', base?.po_number, 'dengan infoActual:', base?.infoActual);

            // Isi field info
            $('#infoProduction').val(base.area || '-');
            $('#infoLine').val(base.line || '-');
            $('#infoLotSize').val(base.lot_size || '-');
            document.getElementById('infoActual').value = base?.infoActual || base?.act_lot_size || '';
            $('#infoDate').val(base.date || '-');

            renderSavedPO();

            if (existingPOs && existingPOs.length > 0) {
                const poNumbers = existingPOs.map(po => po.po_number);
                loadHistoryData(poNumbers);
            }
        }

        function loadHistoryData(poNumbers) {
            if (!Array.isArray(poNumbers)) poNumbers = [poNumbers];

            fetch(`/record-material/history?po=${encodeURIComponent(poNumbers.join(','))}`)
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        fillTable('#recordScanSMD', data.smd);
                        fillTable('#recordScanWH', data.wh);
                        fillTable('#recordScanSTO', data.sto);
                        fillTable('#recordScanMAR', data.mar);
                    }
                })
                .catch(err => console.error('Error loading history:', err));
        }

        function fillTable(tableId, rows) {
            const tbody = document.querySelector(`${tableId} tbody`);
            tbody.innerHTML = '';

            if (!rows || rows.length === 0) {
                tbody.innerHTML = `<tr><td colspan="5">No data found</td></tr>`;
                return;
            }

            rows.forEach((row, index) => {
                const tr = `
            <tr>
                <td>${index + 1}</td>
                <td>${row.scan_code || '-'}</td>
                <td>${row.material || '-'}</td>
                <td>${row.qty || 0}</td>
                <td>${row.batch_description || '-'}</td>
            </tr>
        `;
                tbody.insertAdjacentHTML('beforeend', tr);
            });
        }

        function renderSavedPO() {
            console.log('%c[renderSavedPO] → Dipanggil', 'color:cyan;');

            const saved = localStorage.getItem('currentPO');
            if (!saved) {
                console.log('[renderSavedPO] Tidak ada data di localStorage');
                $('#recordMaterials tbody').html(`
            <tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>
        `);
                return;
            }

            const savedPOs = JSON.parse(saved);
            const poList = Array.isArray(savedPOs) ? savedPOs : [savedPOs];
            console.log('[renderSavedPO] PO list:', poList);

            let allLines = [];

            const fetchPromises = poList.map(po => {
                const po_number = po.po_number;
                return $.ajax({
                    url: `/record_material/by-po/${po_number}`,
                    method: "GET",
                    success: function(res) {
                        if (res.status === 'success' && res.data && res.data.length > 0) {
                            console.log(
                                `[renderSavedPO] Data diterima untuk PO ${po_number}:`,
                                res.data);
                            allLines.push(...res.data);
                        } else {
                            console.warn(
                                `[renderSavedPO] Tidak ada data untuk PO ${po_number}`);
                        }
                    },
                    error: function(xhr) {
                        console.error(`[AJAX Error] Gagal ambil data untuk PO ${po_number}`,
                            xhr);
                    }
                });
            });

            // Tunggu semua request selesai
            Promise.all(fetchPromises).then(() => {
                if (allLines.length === 0) {
                    console.warn('[renderSavedPO] Tidak ada data valid dari semua PO');
                    $('#recordMaterials tbody').html(`
                <tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>
            `);
                    return;
                }

                const grouped = {};
                allLines.forEach(item => {
                    const key = item.material;

                    // Ambil total_qty & receive_qty, jika tidak ada, default 0
                    const totalQty = parseFloat(item.total_qty) || 0;
                    const receiveQty = parseFloat(item.receive_qty) || 0;
                    const smdQty = parseFloat(item.smd_qty) || 0;
                    const stoQty = parseFloat(item.sto_qty) || 0;
                    const marQty = parseFloat(item.mar_qty) || 0;

                    if (!grouped[key]) {
                        grouped[key] = {
                            ...item,
                            rec_qty: totalQty,
                            receive_qty: receiveQty,
                            smd_qty: smdQty,
                            sto_qty: stoQty,
                            mar_qty: marQty
                        };
                    } else {
                        grouped[key].rec_qty += totalQty;
                        grouped[key].receive_qty += receiveQty;
                        grouped[key].smd_qty += smdQty;
                        grouped[key].sto_qty += stoQty;
                        grouped[key].mar_qty += marQty;
                    }
                });

                console.log('[Grouped Combined Data]:', grouped);

                let lotSize = document.getElementById('infoLotSize').value;
                let rows = '';
                Object.values(grouped).forEach((item, i) => {
                    const material = item.material;

                    const smdCount = $(`#recordScanSMD tbody tr td:nth-child(3):contains("${material}")`)
                        .length;
                    const whCount = $(`#recordScanWH tbody tr td:nth-child(3):contains("${material}")`)
                        .length;
                    const stoCount = $(`#recordScanSTO tbody tr td:nth-child(3):contains("${material}")`)
                        .length;

                    const totalScan = smdCount + whCount + stoCount;

                    rows += `
                <tr>
                    <td>${i + 1}</td>
                    <td>${item.material}</td>
                    <td>${item.material_desc || item.part_description}</td>
                    <td>${item.rec_qty || 0}</td>
                    <td>${item.satuan}</td>
                    <td class="${
                       (((item.rec_qty - item.smd_qty) - item.receive_qty) * -1) + item.sto_qty < 0
                            ? 'text-danger fw-bold'
                            : 'text-success fw-bold'
                        }">
                        ${
                            (((item.rec_qty - item.smd_qty) - item.receive_qty) * -1) + item.sto_qty < 0
                                ? 'Shortage'
                                : 'PASS'
                        }
                    </td>                      
                    <td>${item.smd_qty}</td>    
                    <td>${(item.rec_qty - item.smd_qty) * -1}</td>
                    <td>${item.receive_qty}</td>       
                    <td>${((item.rec_qty - item.smd_qty) - item.receive_qty) * -1}</td>
                    <td>${item.sto_qty}</td>
                    <td>${(((item.rec_qty - item.smd_qty) - item.receive_qty) * -1) + item.sto_qty}</td>
                    <td>${item.mar_qty ? item.mar_qty - item.rec_qty : 0}</td>
                    <td>${item.mar_qty}</td>
                    <td>${item.rec_qty - parseFloat(lotSize || 0)}</td>
                    <td>${totalScan}</td>
                </tr>
                `;
                });

                $('#recordMaterials tbody').html(rows);
                console.log('[renderSavedPO] Table updated successfully.');
            });
        }
        $(document).ready(function() {
            let uploadedFiles = [];
            console.log('[INIT] Render InfoContainer from:', '$(document).ready)');

            // Upload multiple
            $(document).on('change', '.import-file', function() {
                const files = this.files;
                if (!files.length) return;

                const uploadData = new FormData();
                uploadData.append('_token', "{{ csrf_token() }}");
                for (let i = 0; i < files.length; i++) {
                    uploadData.append('files[]', files[i]);
                }

                $.ajax({
                    url: "{{ route('record-material.upload') }}",
                    method: "POST",
                    data: uploadData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        if (!response.files || !response.files.length) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'No valid files!',
                                text: 'No files were processed by the server.'
                            });
                            return;
                        }

                        response.files.forEach(file => {
                            const {
                                model,
                                po_number,
                                path
                            } = file;
                            const fileData = {
                                model,
                                po_number,
                                path
                            };

                            uploadedFiles.push(fileData);

                            const newRow = `
                        <tr data-path="${path}">
                            <td><input type="text" class="form-control po_number" value="${po_number}" readonly></td>
                            <td><input type="text" class="form-control model" value="${model}" readonly></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-danger remove-row">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                            $('#importTable tbody').append(newRow);
                        });
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Upload Failed',
                            text: 'Could not upload one or more files.'
                        });
                    }
                });

                // reset input agar bisa upload file lain
                $(this).val('');
            });

            // Hapus file
            $(document).on('click', '.remove-row', function(e) {
                e.preventDefault();
                const row = $(this).closest('tr');
                const filePath = row.data('path');

                Swal.fire({
                    title: 'Are you sure?',
                    text: "This file will be removed from the list.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        uploadedFiles = uploadedFiles.filter(f => f.path !== filePath);
                        row.remove();

                        Swal.fire({
                            icon: 'success',
                            title: 'Deleted!',
                            text: 'The file has been removed.',
                            timer: 1000,
                            showConfirmButton: false
                        });
                    }
                });
            });

            // Submit form ke backend
            $('#importFormWrapper').on('submit', function(e) {
                e.preventDefault();

                if (uploadedFiles.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Files!',
                        text: 'Please upload at least one file before submitting.'
                    });
                    return;
                }

                const forms = uploadedFiles.map(file => ({
                    po_number: file.po_number,
                    model: file.model,
                    file_path: file.path,
                    lot_size: $('#lot_size').val(),
                    line: $('#lineArea').val(),
                }));

                Swal.fire({
                    title: 'Are you sure?',
                    text: 'This data will be saved to the database.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, save it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "{{ route('record-material.store') }}",
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                forms: forms
                            },
                            success: function(response) {
                                if (response.status === 'success') {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: response.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        updateInfoFields(response.data);
                                        $('#searchRecords').DataTable().ajax
                                            .reload(null, false);

                                        $('#poNumber').modal('hide');
                                        $('#importTable tbody').empty();
                                        $('#importFormWrapper')[0].reset();
                                        uploadedFiles = [];
                                    });
                                } else if (response.status === 'duplicate') {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Duplicate PO Number!',
                                        text: response.message
                                    });
                                }
                            },
                            error: function(xhr) {
                                const response = xhr.responseJSON;
                                if (xhr.status === 422 && response?.status ===
                                    'duplicate') {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Duplicate PO Number!',
                                        text: response.message
                                    });
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Failed!',
                                        text: response?.message ||
                                            'An error occurred while saving data.'
                                    });
                                }
                            }
                        });
                    }
                });
            });

            function checkInfoFields() {
                const poNumber = $('#infoPONumber').val()?.trim();
                const model = $('#infoModel').val()?.trim();

                console.log('[checkInfoFields] poNumber:', poNumber, 'model:', model);

                if (!poNumber || !model || poNumber === '-' || model === '-') {
                    console.warn('[checkInfoFields] Data incomplete — localStorage dihapus');
                    localStorage.removeItem('currentPO');
                    $('#recordMaterials tbody').html(`
                <tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>
            `);
                }
            }

            document.addEventListener('DOMContentLoaded', () => {
                const savedPO = localStorage.getItem('currentPO');
                if (savedPO) {
                    const info = JSON.parse(savedPO);
                    const poList = Array.isArray(info) ? info : [info];

                    // Bersihkan container
                    $('#infoContainer').empty();

                    // Render ulang semua PO ke dalam input field baru
                    poList.forEach((po, index) => {
                        let infoRow = '';
                        if (index === 0) {
                            infoRow = `
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1">PO Number</label>
                                <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${po.po_number}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1">Model</label>
                                <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${po.model}" readonly>
                            </div>
                        </div>
                    `;
                        } else {
                            infoRow = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${po.po_number}" readonly>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${po.model}" readonly>
                    </div>
                </div>
                `;
                        }
                        $('#infoContainer').append(infoRow);
                    });

                    // Field lainnya dari PO pertama
                    const base = poList[0];
                    $('#infoProduction').val(base.area || '-');
                    $('#infoLine').val(base.line || '-');
                    $('#infoLotSize').val(base.lot_size || '-');
                    $('#infoActual').val(base.infoActual || base.act_lot_size || '0');
                    $('#infoDate').val(base.date || '-');

                    renderSavedPO();
                    if (existingPOs && existingPOs.length > 0) {
                        const poNumbers = existingPOs.map(po => po.po_number);
                        loadHistoryData(poNumbers);
                    }
                } else {
                    $('#recordMaterials tbody').html(`
            <tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>
        `);
                }
            });

            $('#deletePO').on('click', function() {
                const savedPO = JSON.parse(localStorage.getItem('currentPO') || '[]');

                if (savedPO.length === 0) {
                    Swal.fire({
                        icon: 'info',
                        title: 'No saved purchase order',
                        text: 'No data to delete!',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }

                const poNumbers = savedPO.map(po => po.po_number).join(', ');

                Swal.fire({
                    title: 'Delete Data?',
                    html: `Are you sure you want to delete the following PO data from the database?<br><b>${poNumbers}</b>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/record_material/delete-po',
                            type: 'POST',
                            data: {
                                po_numbers: savedPO.map(po => po.po_number),
                                _token: "{{ csrf_token() }}",
                            },
                            beforeSend: function() {
                                Swal.fire({
                                    title: 'Deleting...',
                                    text: 'Please wait a moment.',
                                    allowOutsideClick: false,
                                    didOpen: () => Swal.showLoading()
                                });
                            },
                            success: function(res) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Succeeded',
                                    text: 'PO data has been successfully deleted from the database.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                // Hapus localStorage & reset tampilan
                                localStorage.removeItem('currentPO');
                                $('#recordMaterials tbody').html(
                                    `<tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>`
                                );
                                $('#recordScanWH tbody').html(
                                    `<tr><td colspan="5" class="text-center text-muted">No Data Found</td></tr>`
                                );
                                $('#recordScanSMD tbody').html(
                                    `<tr><td colspan="5" class="text-center text-muted">No Data Found</td></tr>`
                                );
                                $('#recordScanMAR tbody').html(
                                    `<tr><td colspan="5" class="text-center text-muted">No Data Found</td></tr>`
                                );
                                $('#infoContainer').empty();
                                $('#infoProduction, #infoLine, #infoLotSize, #infoActual, #infoDate')
                                    .val('');

                                $('#searchRecords .btn-custom').each(function() {
                                    $(this).prop('disabled', false)
                                        .text('Select')
                                        .removeClass('btn-danger')
                                        .addClass('btn-primary');
                                });
                            },
                            error: function(err) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Failed',
                                    text: 'An error occurred while deleting the PO from the database.'
                                });
                                console.error('Failed to delete PO in the database:',
                                    err
                                    .responseText);
                            }
                        });
                    }
                });
            });
        });
        document.addEventListener('DOMContentLoaded', () => {
            const saved = localStorage.getItem('currentPO');
            if (saved) {
                const savedPOs = JSON.parse(saved);
                const poList = Array.isArray(savedPOs) ? savedPOs : [savedPOs];

                // Render input info container
                $('#infoContainer').empty();
                poList.forEach((po, index) => {
                    let infoRow = '';
                    if (index === 0) {
                        infoRow = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-1">PO Number</label>
                            <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${po.po_number}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-1">Model</label>
                            <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${po.model}" readonly>
                        </div>
                    </div>
                `;
                    } else {
                        infoRow = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${po.po_number}" readonly>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${po.model}" readonly>
                        </div>
                    </div>
                `;
                    }
                    $('#infoContainer').append(infoRow);
                });

                // Field lainnya dari PO pertama
                const base = poList[0];
                $('#infoProduction').val(base.area || '-');
                $('#infoLine').val(base.line || '-');
                $('#infoLotSize').val(base.lot_size || '-');
                $('#infoActual').val(base.infoActual || base.act_lot_size || '0');
                $('#infoDate').val(base.date || '-');

                // Render tabel record_material_lines
                renderSavedPO();

                if (poList && poList.length > 0) {
                    const poNumbers = poList.map(po => po.po_number);
                    loadHistoryData(poNumbers);
                }
            } else {
                $('#recordMaterials tbody').html(`
            <tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>
        `);
            }
        });

        // Get search record
        $(document).ready(function() {
            let today = new Date().toISOString().split('T')[0];
            $('#searchDate').val(today);

            let table = $('#searchRecords').DataTable({
                serverSide: false,
                dom: 'rtip',
                ajax: {
                    url: "{{ route('record-material.getSearch') }}",
                    data: function(d) {
                        d.date = $('#searchDate').val();
                    },
                    dataSrc: function(json) {
                        return json.length ? json : [];
                    },
                    error: function(err) {
                        console.error('Gagal load data:', err);
                    }
                },
                columns: [{
                        data: null,
                        render: (data, type, row, meta) => meta.row + 1
                    },
                    {
                        data: 'po_number'
                    },
                    {
                        data: 'area'
                    },
                    {
                        data: 'line'
                    },
                    {
                        data: 'model'
                    },
                    {
                        data: 'date'
                    },
                    {
                        data: 'lot_size'
                    },
                    {
                        data: 'act_lot_size'
                    },
                    {
                        data: null,
                        render: function(data, type, row) {
                            return `<button type="button" class="btn btn-primary btn-custom">Select</button>`;
                        }
                    }
                ],
                language: {
                    emptyTable: "No data found"
                },
                pageLength: 5,
            });

            $('#searchDate').on('change', function() {
                table.ajax.reload();
            });

            $('#tableSearch').on('keyup', function() {
                table.search(this.value).draw();
            });

            table.on('draw', function() {
                let saved;
                try {
                    saved = JSON.parse(localStorage.getItem('currentPO') || '[]');
                } catch (e) {
                    console.warn('[renderSavedPO] JSON parse error di localStorage.currentPO:', e);
                    saved = [];
                }

                const savedPOs = Array.isArray(saved) ? saved : [saved];
                console.log('[renderSavedPO] PO list:', savedPOs);

                $('#searchRecords tbody tr').each(function() {
                    const rowData = table.row(this).data();

                    if (!rowData || !rowData.po_number) {
                        console.warn('[renderSavedPO] rowData kosong atau invalid:', rowData);
                        return;
                    }

                    const poExist = Array.isArray(savedPOs) && savedPOs.some(po => po && po
                        .po_number === rowData.po_number);
                    const btn = $(this).find('.btn-custom');

                    if (poExist) {
                        btn.text('Cancel');
                        btn.removeClass('btn-primary').addClass('btn-danger');
                    } else {
                        btn.text('Select');
                        btn.removeClass('btn-danger').addClass('btn-primary');
                    }
                });
            });


            // reload otomatis saat tanggal berubah
            $('#searchDate').on('change', function() {
                table.ajax.reload();
            });

            // event tombol select / cancel
            $('#searchRecords').on('click', '.btn-custom', function() {
                let rowData = table.row($(this).parents('tr')).data();
                const btn = $(this);

                // Ambil semua PO yang tersimpan
                let saved = localStorage.getItem('currentPO');
                let pos = saved ? JSON.parse(saved) : [];
                if (!Array.isArray(pos)) pos = [pos];

                // === Jika tombol saat ini "Cancel" ===
                if (btn.text() === 'Cancel') {
                    Swal.fire({
                        title: 'Cancel this PO?',
                        text: `PO ${rowData.po_number} will be removed.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, cancel it!',
                        cancelButtonText: 'No',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Hapus hanya PO yang diklik dari localStorage
                            pos = pos.filter(p => p.po_number !== rowData.po_number);

                            if (pos.length > 0) {
                                localStorage.setItem('currentPO', JSON.stringify(pos));
                            } else {
                                localStorage.removeItem('currentPO');
                            }

                            // Update tampilan info dan tabel
                            if (pos.length > 0) {
                                updateInfoFields(pos);
                            } else {
                                // Kosongkan info kalau sudah tidak ada PO
                                $('#infoContainer').empty();
                                $('#infoPONumber').val('');
                                $('#infoProduction').val('');
                                $('#infoLine').val('');
                                $('#infoModel').val('');
                                $('#infoLotSize').val('');
                                $('#infoActual').val('');
                                $('#infoDate').val('');
                                $('#recordMaterials tbody').html(`
                        <tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>
                    `);
                            }

                            // Kembalikan tombol ke "Select"
                            btn.text('Select');
                            btn.removeClass('btn-danger').addClass('btn-primary');

                            Swal.fire({
                                icon: 'success',
                                title: 'Cancelled!',
                                text: `PO ${rowData.po_number} has been removed.`,
                                timer: 1200,
                                showConfirmButton: false
                            });
                        }
                    });
                    return;
                }

                // === Jika tombol saat ini "Select" ===
                Swal.fire({
                    title: 'Are you sure?',
                    text: `You are selecting PO: ${rowData.po_number}`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, select it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Tambahkan PO baru ke localStorage
                        updateInfoFields(rowData);

                        // Tutup modal
                        $('#modalPONumber').modal('hide');

                        // Ubah tombol menjadi Cancel
                        btn.text('Cancel');
                        btn.removeClass('btn-primary').addClass('btn-danger');

                        Swal.fire({
                            icon: 'success',
                            title: 'PO Selected!',
                            text: `PO ${rowData.po_number} has been added.`,
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            // Fungsi utama scan handler
            function initScanHandler({
                inputId,
                tableBodyId,
                modalId,
                submitBtnId,
                type,
                submitUrl
            }) {
                let scannedData = [];

                // Parsing barcode seperti [)>@SIIX20@25XEMMKD00@1123-09973@50.000@@
                function parseScanCode(scanText) {
                    if (!scanText) return null;
                    const parts = scanText.trim().split('@');
                    if (parts.length < 5) return null;
                    return {
                        batch: parts[2].trim(),
                        material: parts[3].trim(),
                        qty: parseInt(parts[4].split('.')[0]) || 0,
                        description: scanText.trim()
                    };
                }

                // Fungsi utama untuk handle hasil scan banyak barcode
                function handleScanInput(scanText) {
                    if (!scanText) return;

                    // Bersihkan semua karakter aneh di awal/akhir
                    let cleaned = scanText
                        .replace(/\r?\n|\r/g, ' ') // hapus enter / newline
                        .replace(/\s+/g, ' ') // ubah spasi ganda jadi satu
                        .trim();

                    // Pisahkan setiap barcode dengan regex yang cari "[)>@" diikuti karakter lain sampai " @@"
                    const regex = /\[>\)@[^\@]+@[^\@]+@[^\@]+@\d+\.\d+@@/g;
                    const codes = cleaned.match(regex);

                    if (!codes) return;

                    // Parse tiap barcode
                    codes.forEach(code => {
                        const parsed = parseScanCode(code);
                        if (parsed) {
                            const exist = scannedData.some(d => d.batch === parsed.batch && d.material ===
                                parsed.material);
                            if (!exist) scannedData.push(parsed);
                        }
                    });

                    renderTable();
                }

                // Render hasil scan ke tabel
                function renderTable() {
                    const tbody = document.getElementById(tableBodyId);
                    tbody.innerHTML = '';
                    if (scannedData.length === 0) {
                        tbody.innerHTML =
                            `<tr><td colspan="6" class="text-center text-muted">No data found</td></tr>`;
                        return;
                    }
                    scannedData.forEach((d, i) => {
                        tbody.insertAdjacentHTML('beforeend', `
                    <tr class="text-muted" style="font-size: 1rem;">
                        <td>${i + 1}</td>
                        <td>${d.batch}</td>
                        <td>${d.material}</td>
                        <td>${d.qty}</td>
                        <td>${d.description}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-danger delete-btn">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                `);
                    });
                }

                // Fokus otomatis saat modal dibuka
                document.getElementById(modalId).addEventListener('shown.bs.modal', () => {
                    setTimeout(() => document.getElementById(inputId).focus(), 100);
                });

                // Event scan barcode (Enter) — revised to support multiple barcodes in one input
                const input = document.getElementById(inputId);
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        const raw = this.value || '';
                        if (!raw.trim()) return;

                        const currentPO = JSON.parse(localStorage.getItem('currentPO') || '[]');
                        if (!currentPO.length) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'No PO Selected!',
                                text: 'Please select or import file before scanning.'
                            });
                            this.value = '';
                            return;
                        }

                        // Bersihkan dan pecah jadi beberapa kode (regex non-greedy sampai @@)
                        const cleaned = raw.replace(/\r?\n|\r/g, ' ').replace(/\s+/g, ' ').trim();
                        const codeRegex =
                            /\[\)\>@[\s\S]*?@@/g;
                        const codes = cleaned.match(codeRegex) || [];

                        if (codes.length === 0) {
                            // kalau tidak match pattern barcode, coba parse langsung (backward compatibility)
                            const single = parseScanCode(cleaned);
                            if (!single) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Invalid Format!',
                                    text: 'Invalid barcode format.'
                                });
                                this.value = '';
                                return;
                            }
                            codes.push(cleaned);
                        }

                        // Proses satu-per-satu (serial-ish) agar AJAX cek PO & batch bekerja per barcode
                        // memproses tiap code untuk setiap code lakukan cek material ke all PO.
                        const processNext = (index) => {
                            if (index >= codes.length) {
                                // semua selesai
                                input.value = '';
                                input.focus();
                                return;
                            }

                            const codeStr = codes[index].trim();
                            const parsed = parseScanCode(codeStr);
                            if (!parsed) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Invalid Format!',
                                    text: `Invalid barcode format: ${codeStr}`
                                });
                                // lanjut ke berikutnya
                                return processNext(index + 1);
                            }

                            let found = false;
                            let checkedCount = 0;
                            const totalPO = currentPO.length;

                            for (const po of currentPO) {
                                $.ajax({
                                    url: '/record_material/check-material',
                                    method: 'POST',
                                    data: {
                                        _token: '{{ csrf_token() }}',
                                        po_number: po.po_number,
                                        material: parsed.material
                                    },
                                    success: function(res) {
                                        checkedCount++;

                                        if (res.status === 'ok' && !found) {
                                            found = true;

                                            // Cek duplikat lokal by batch
                                            if (scannedData.some(item => item.batch ===
                                                    parsed.batch)) {
                                                Swal.fire({
                                                    icon: 'warning',
                                                    title: 'Duplicate Scan!',
                                                    text: 'This batch is already scanned!'
                                                });
                                                // lanjut ke code berikutnya
                                                if (checkedCount === totalPO) processNext(
                                                    index + 1);
                                                return;
                                            }

                                            // cek batch di DB
                                            $.ajax({
                                                url: '/record_material/check-batch',
                                                method: 'POST',
                                                data: {
                                                    _token: '{{ csrf_token() }}',
                                                    batch: parsed.batch,
                                                    type: type
                                                },
                                                success: function(batchRes) {
                                                    if (batchRes.status ===
                                                        'duplicate') {
                                                        Swal.fire({
                                                            icon: 'warning',
                                                            title: 'Duplicate Batch!',
                                                            text: `Batch ${parsed.batch} already exists in DB.`
                                                        });
                                                        // lanjut ke code berikutnya
                                                        processNext(index + 1);
                                                    } else {
                                                        scannedData.push(
                                                            parsed);
                                                        renderTable();
                                                        // lanjut ke code berikutnya
                                                        processNext(index + 1);
                                                    }
                                                },
                                                error: function() {
                                                    Swal.fire({
                                                        icon: 'error',
                                                        title: 'Error!',
                                                        text: 'Failed to check batch in database.'
                                                    });
                                                    // lanjut
                                                    processNext(index + 1);
                                                }
                                            });
                                        }

                                        if (checkedCount === totalPO && !found) {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Material Not Found!',
                                                text: `Material ${parsed.material} not found in any PO.`
                                            });
                                            processNext(index + 1);
                                        }
                                    },
                                    error: function() {
                                        checkedCount++;
                                        if (checkedCount === totalPO && !found) {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error!',
                                                text: 'Failed to check material in PO.'
                                            });
                                            processNext(index + 1);
                                        }
                                    }
                                });
                            } // end for currentPO
                        }; // end processNext

                        // mulai proses dari index 0
                        processNext(0);
                    }
                });

                // Tombol hapus baris
                document.getElementById(tableBodyId).addEventListener('click', function(e) {
                    if (e.target.closest('.delete-btn')) {
                        const row = e.target.closest('tr');
                        const index = row.rowIndex - 1;
                        const batchName = scannedData[index]?.batch || 'Unknown Batch';
                        Swal.fire({
                            title: 'Are you sure?',
                            text: `Batch "${batchName}" will be removed.`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, delete it!',
                            cancelButtonText: 'Cancel'
                        }).then(result => {
                            if (result.isConfirmed) {
                                scannedData.splice(index, 1);
                                renderTable();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Deleted!',
                                    text: `Batch "${batchName}" removed.`,
                                    timer: 1200,
                                    showConfirmButton: false
                                });
                            }
                        });
                    }
                });

                // Tombol submit
                $(`#${submitBtnId}`).on('click', function() {
                    if (scannedData.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Data!',
                            text: 'Please scan at least one material before submitting.'
                        });
                        return;
                    }

                    const currentPO = JSON.parse(localStorage.getItem('currentPO') || '[]');
                    if (!currentPO.length) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No PO Selected!',
                            text: 'Please select a PO before submitting.'
                        });
                        return;
                    }

                    let actualLotSize = null;
                    if (type === 'MAR') {
                        actualLotSize = document.getElementById('actualLotSize')?.value.trim() || null;
                        if (!actualLotSize || actualLotSize <= 0) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Missing Lot Size!',
                                text: 'Please input actual lot size before submitting.'
                            });
                            return;
                        }
                    }

                    Swal.fire({
                        title: 'Submit scanned data?',
                        text: 'This will save all scanned materials.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, submit!'
                    }).then(result => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: submitUrl,
                                method: 'POST',
                                data: {
                                    _token: '{{ csrf_token() }}',
                                    po_list: currentPO,
                                    scanned: scannedData,
                                    ...(type === 'MAR' ? {
                                        actual_lot_size: actualLotSize
                                    } : {})
                                },
                                success: function(res) {
                                    if (res.status === 'success') {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success!',
                                            text: res.message,
                                            timer: 1500,
                                            showConfirmButton: false
                                        });
                                        const currentPO = JSON.parse(localStorage
                                            .getItem('currentPO') || '[]');
                                        const poNumbers = currentPO.map(p => p
                                            .po_number);

                                        loadHistoryData(poNumbers);
                                        renderSavedPO?.();
                                        scannedData = [];
                                        renderTable();
                                        $(`#${modalId}`).modal('hide');
                                        if (type === 'MAR') {
                                            document.getElementById('infoActual')
                                                .value = actualLotSize;

                                            // ambil currentPO
                                            let currentPO = JSON.parse(localStorage
                                                .getItem('currentPO') || '[]');
                                            if (!Array.isArray(currentPO)) currentPO = [
                                                currentPO
                                            ];

                                            // update PO aktif (misal yang terakhir)
                                            if (currentPO.length > 0) {
                                                currentPO[currentPO.length - 1]
                                                    .infoActual = actualLotSize;
                                            }

                                            // simpan ulang
                                            localStorage.setItem('currentPO', JSON
                                                .stringify(currentPO));
                                        }
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Failed!',
                                            text: res.message ||
                                                'Error saving data.'
                                        });
                                    }
                                },
                                error: function(xhr) {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error!',
                                        text: xhr.responseJSON?.message ||
                                            'Unexpected error occurred.'
                                    });
                                }
                            });
                        }
                    });
                });
            }

            // Inisialisasi untuk beberapa modal
            initScanHandler({
                inputId: 'whBarcodeInput',
                tableBodyId: 'materialTableBody',
                modalId: 'scanMaterial',
                submitBtnId: 'submitScanWH',
                type: 'WH',
                submitUrl: '/record_material/store-wh'
            });

            initScanHandler({
                inputId: 'smdBarcodeInput',
                tableBodyId: 'smdTableBody',
                modalId: 'scanSmd',
                submitBtnId: 'submitScanSmd',
                type: 'SMD',
                submitUrl: '/record_material/store-smd'
            });

            initScanHandler({
                inputId: 'stoBarcodeInput',
                tableBodyId: 'stoTableBody',
                modalId: 'scanSto',
                submitBtnId: 'submitScanSto',
                type: 'STO',
                submitUrl: '/record_material/store-sto'
            });

            initScanHandler({
                inputId: 'marBarcodeInput',
                tableBodyId: 'marTableBody',
                modalId: 'scanMar',
                submitBtnId: 'submitScanMar',
                type: 'MAR',
                submitUrl: '/record_material/store-mar'
            });

        });

        // Button collapse di table history batch
        document.addEventListener('DOMContentLoaded', function() {
            const collapseEl = document.getElementById('collapseHistory');
            const toggleBtn = document.getElementById('toggleHistoryBtn');
            const icon = toggleBtn.querySelector('i');

            collapseEl.addEventListener('shown.bs.collapse', function() {
                icon.classList.remove('bi-chevron-down');
                icon.classList.add('bi-chevron-up');
                toggleBtn.innerHTML = `<i class="bi bi-chevron-up me-1"></i> Collapse`;
            });

            collapseEl.addEventListener('hidden.bs.collapse', function() {
                icon.classList.remove('bi-chevron-up');
                icon.classList.add('bi-chevron-down');
                toggleBtn.innerHTML = `<i class="bi bi-chevron-down me-1"></i> Expand`;
            });
        });

        // Button copy batch
        document.addEventListener('DOMContentLoaded', function() {
            // Ambil semua tombol copy batch
            const copyButtons = document.querySelectorAll('.copyBatchBtn');

            copyButtons.forEach(copy => {
                copy.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Ambil tabel terdekat
                    const table = copy.closest('.col-md-6').querySelector('table');
                    if (!table) return;

                    // Ambil semua teks dari kolom terakhir (Batch Description)
                    const batchDescriptions = [];
                    table.querySelectorAll('tbody tr').forEach(row => {
                        const lastCell = row.querySelector('td:last-child');
                        if (lastCell && lastCell.textContent.trim() !== "No data found") {
                            batchDescriptions.push(lastCell.textContent.trim());
                        }
                    });

                    if (batchDescriptions.length > 0) {
                        // Copy ke clipboard
                        navigator.clipboard.writeText(batchDescriptions.join('\n'));
                    }

                    // Animasi “Copied”
                    const icon = copy.querySelector('i');
                    const span = copy.querySelector('span');

                    icon.classList.replace('bi-clipboard', 'bi-check2');
                    span.textContent = 'Copied';

                    setTimeout(() => {
                        icon.classList.replace('bi-check2', 'bi-clipboard');
                        span.textContent = 'Copy batch';
                    }, 1000);
                });
            });
        });
    </script>
@endsection
