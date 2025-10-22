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
                                    <button class="btn btn-gradient-info py-3 fw-bold rounded-3 shadow-sm"
                                        data-bs-toggle="modal" data-bs-target="#poNumber">
                                        <i class="bi bi-plus-circle me-2"></i> New PO Number
                                    </button>
                                    <button class="btn btn-gradient-secondary py-3 fw-bold rounded-3 shadow-sm"
                                        data-bs-toggle="modal" data-bs-target="#searchPO">
                                        <i class="bi bi-search me-2"></i> Search PO Number
                                    </button>
                                    <button class="btn btn-gradient-primary py-3 fw-bold rounded-3 shadow-sm"
                                        data-bs-toggle="modal" data-bs-target="#scanMaterial">
                                        <i class="bi bi-qr-code-scan me-2"></i> Scan WH Material
                                    </button>
                                    <button class="btn btn-gradient-success py-3 fw-bold rounded-3 shadow-sm">
                                        <i class="bi bi-arrow-repeat me-2"></i> Scan Balance Production
                                    </button>
                                    <button class="btn btn-gradient-danger py-3 fw-bold rounded-3 shadow-sm">
                                        <i class="bi bi-box-seam me-2"></i> Scan STO Material
                                    </button>
                                    <button class="btn btn-gradient-warning py-3 fw-bold rounded-3 shadow-sm">
                                        <i class="bi bi-clock-history me-2"></i> Scan Material After Running
                                    </button>
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
                                    <button class="btn btn-sm btn-primary text-white rounded-pill px-4 shadow-sm"
                                        id="nextPOButton">
                                        Next <i class="bi bi-arrow-right-short"></i>
                                    </button>
                                </div>
                                <hr class="mt-0 mb-4 text-muted">
                                <form>
                                    <div class="row">
                                        <div class="col-12 mb-3">
                                            <label for="infoPONumber" class="form-label small text-muted mb-1">PO
                                                NUMBER</label>
                                            <input type="text"
                                                class="form-control form-control-sm rounded-3 bg-light border-0"
                                                placeholder="PO Number" id="infoPONumber" disabled>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-6">
                                            <label for="infoProduction" class="form-label small text-muted mb-1">PRODUCTION
                                                AREA</label>
                                            <input type="text"
                                                class="form-control form-control-sm rounded-3 bg-light border-0"
                                                placeholder="Production Area" disabled id="infoProduction">
                                        </div>
                                        <div class="col-sm-6">
                                            <label for="infoLine" class="form-label small text-muted mb-1">LINE AREA</label>
                                            <input type="text"
                                                class="form-control form-control-sm rounded-3 bg-light border-0"
                                                placeholder="Line Area" id="infoLine" disabled>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-sm-6">
                                            <label for="infoModel" class="form-label small text-muted mb-1">MODEL</label>
                                            <input type="text"
                                                class="form-control form-control-sm rounded-3 bg-light border-0"
                                                placeholder="Model" id="infoModel" disabled>
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
            @include('table-record')
        </section>
        {{-- Modal import po number --}}
        <div class="modal fade" id="poNumber" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Import Record Material</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="importFormWrapper">
                            <div id="formContainer">
                                <div class="form-block border p-3 rounded mb-3">
                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label class="form-label">IMPORT FILE</label>
                                            <input type="file" class="form-control import-file" accept=".txt, .csv"
                                                multiple>
                                        </div>
                                        <div class="col-lg-12">
                                            <table class="table border rounded" id="importTable">
                                                <thead>
                                                    <tr>
                                                        <th>PO NUMBER</th>
                                                        <th>MODEL</th>
                                                        <th>ACTION</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label">LOT SIZE</label>
                                            <input type="number" id="lot_size" class="form-control"
                                                placeholder="Enter Lot Size" required>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label">ACTUAL LOT SIZE</label>
                                            <input type="number" class="form-control" id="actual_lot_size"
                                                placeholder="Enter Actual Lot Size" required>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label">LINE AREA</label>
                                            <input type="text" class="form-control" placeholder="Enter Line Area"
                                                required id="lineArea">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        {{-- Modal search record material --}}
        <div class="modal fade" id="searchPO" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Search Record Material</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="searchPONumber">
                            <div id="formContainer">
                                <div class="form-block border p-3 rounded mb-3">
                                    <div class="row g-3">
                                        <div class="col-lg-6 text-end">
                                            <input type="date" id="searchDate" class="form-control">
                                        </div>
                                        <div class="col-lg-12">
                                            <table class="table border rounded search-material-table" id="searchRecords">
                                                <thead>
                                                    <tr class="align-middle text-center">
                                                        <th>NO</th>
                                                        <th>PO NUMBER</th>
                                                        <th>PRODUCTION AREA</th>
                                                        <th>LINE AREA</th>
                                                        <th>MODEL</th>
                                                        <th>DATE RUN</th>
                                                        <th>LOT SIZE</th>
                                                        <th>ACTUAL LOT SIZE</th>
                                                        <th>ACTION</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @include('modal.scan-material')
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

            // Ambil semua PO number
            const poNumbers = existingPOs.map(po => po.po_number).join(' - ');

            // Karena field lain sama, ambil dari data pertama saja
            const base = existingPOs[0];

            // Isi field info
            $('#infoPONumber').val(poNumbers);
            $('#infoProduction').val(base.area || '-');
            $('#infoLine').val(base.line || '-');
            $('#infoModel').val(base.model || '-');
            $('#infoLotSize').val(base.lot_size || '-');
            $('#infoActual').val(base.act_lot_size || '-');
            $('#infoDate').val(base.date || '-');

            renderSavedPO();
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
                    const totalQty = parseInt(item.total_qty) || 0;
                    const receiveQty = parseInt(item.receive_qty) || 0;

                    if (!grouped[key]) {
                        grouped[key] = {
                            ...item,
                            rec_qty: totalQty,
                            receive_qty: receiveQty
                        };
                    } else {
                        grouped[key].rec_qty += totalQty;
                        grouped[key].receive_qty += receiveQty;
                    }
                });

                console.log('[Grouped Combined Data]:', grouped);

                let rows = '';
                Object.values(grouped).forEach((item, i) => {
                    rows += `
                <tr>
                    <td>${i + 1}</td>
                    <td>${item.material}</td>
                    <td>${item.material_desc || item.part_description}</td>
                    <td>${item.rec_qty || 0}</td>
                    <td>${item.satuan}</td>
                    <td>${item.receive_qty}</td>
                </tr>
            `;
                });

                $('#recordMaterials tbody').html(rows);
                console.log('[renderSavedPO] Table updated successfully.');
            });
        }
        $(document).ready(function() {
            let uploadedFiles = [];

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

                const models = uploadedFiles.map(f => f.model).filter(m => m && m.trim() !== '');
                const uniqueModels = [...new Set(models)];
                if (uniqueModels.length > 1) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Inconsistent Models!',
                        text: 'Uploaded files have different models. Please ensure all files have the same model.'
                    });
                    return;
                }

                const forms = uploadedFiles.map(file => ({
                    po_number: file.po_number,
                    model: file.model,
                    file_path: file.path,
                    lot_size: $('#lot_size').val(),
                    act_lot_size: $('input[placeholder="Enter Actual Lot Size"]').val(),
                    line: $('input[placeholder="Enter Line Area"]').val(),
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
                    const base = Array.isArray(info) ? info[0] : info;

                    $('#infoPONumber').val(Array.isArray(info) ? info.map(p => p.po_number).join(' - ') :
                        base.po_number);
                    $('#infoProduction').val(base.area || '-');
                    $('#infoLine').val(base.line || '-');
                    $('#infoModel').val(base.model || '-');
                    $('#infoLotSize').val(base.lot_size || '-');
                    $('#infoActual').val(base.act_lot_size || '-');
                    $('#infoDate').val(base.date || '-');

                    renderSavedPO();
                } else {
                    $('#recordMaterials tbody').html(`
            <tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>
        `);
                }
            });

            $('#nextPOButton, #logoutButton').on('click', function() {
                console.log('[Button] Next/Logout diklik — localStorage dihapus');

                // Hapus localStorage
                localStorage.removeItem('currentPO');

                // Kosongkan tabel
                $('#recordMaterials tbody').html(`
        <tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>
    `);

                // Kosongkan field info
                $('#infoPONumber').val('');
                $('#infoProduction').val('');
                $('#infoLine').val('');
                $('#infoModel').val('');
                $('#infoLotSize').val('');
                $('#infoActual').val('');
                $('#infoDate').val('');

                // Reset semua tombol Select/Selected
                $('#searchRecords .btn-custom').each(function() {
                    $(this).prop('disabled', false); // aktifkan lagi
                    $(this).text('Select'); // ubah teks kembali
                });
            });
        });
        document.addEventListener('DOMContentLoaded', () => {
            const savedPO = localStorage.getItem('currentPO');
            if (savedPO) {
                const info = JSON.parse(savedPO);
                $('#infoPONumber').val(info.po_number);
                $('#infoProduction').val(info.area);
                $('#infoLine').val(info.line);
                $('#infoModel').val(info.model);
                $('#infoLotSize').val(info.lot_size);
                $('#infoActual').val(info.act_lot_size);
                $('#infoDate').val(info.date);
            }
        });
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
                        if (json.length === 0) {
                            return [];
                        } else {
                            return json;
                        }
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

            // event tombol select
            $('#searchRecords').on('click', '.btn-custom', function() {
                let rowData = table.row($(this).parents('tr')).data();
                console.log('PO terpilih:', rowData);

                const btn = $(this); // tombol yang diklik

                // Ambil model yang sudah ada
                const currentModel = $('#infoModel').val()?.trim();

                if (currentModel && currentModel !== '-' && rowData.model !== currentModel) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Inconsistent Model!',
                        text: `Selected PO model (${rowData.model}) does not match the existing model (${currentModel}).`
                    });
                    return; // batalkan pemilihan
                }

                // Konfirmasi jika model sama atau belum ada PO
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
                        // Update localStorage dan form utama
                        updateInfoFields(rowData);

                        // Tutup modal PO selection
                        $('#modalPONumber').modal('hide');

                        // Ubah tombol menjadi disabled dan ganti teks
                        btn.prop('disabled', true);
                        btn.text('Selected');

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

        //Scan WH Material 
        document.addEventListener('DOMContentLoaded', function() {
            // Variabel global untuk menyimpan hasil scan
            let scannedData = [];

            // Parsing barcode string seperti [)>@SIIX20@25XEMMKD00@1123-09973@50.000@@
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

            // Render data hasil scan ke tabel
            function renderTable() {
                const tbody = document.getElementById('materialTableBody');
                tbody.innerHTML = '';

                if (scannedData.length === 0) {
                    tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">No data found</td></tr>`;
                    return;
                }

                scannedData.forEach((d, i) => {
                    const row = `
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
            `;
                    tbody.insertAdjacentHTML('beforeend', row);
                });
            }

            // Event Enter di input scan
            const whInput = document.getElementById('whBarcodeInput');
            whInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const scanText = this.value.trim();
                    const parsed = parseScanCode(scanText);

                    if (!parsed) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Format!',
                            text: 'Invalid barcode format',
                            confirmButtonColor: '#3085d6',
                        }).then(() => {
                            document.getElementById('whBarcodeInput').value = '';
                        });
                        return;
                    }

                    if (scannedData.some(item => item.batch === parsed.batch)) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Duplicate Scan!',
                            text: 'The batch has already been scanned!',
                            confirmButtonColor: '#f39c12',
                        }).then(() => {
                            document.getElementById('whBarcodeInput').value = '';
                        });
                        return;
                    }

                    scannedData.push(parsed);
                    renderTable();
                    this.value = '';
                    this.focus();
                }
            });

            // Hapus baris scan
            document.getElementById('materialTableBody').addEventListener('click', function(e) {
                if (e.target.closest('.delete-btn')) {
                    const row = e.target.closest('tr');
                    const index = row.rowIndex - 1;
                    const batchName = scannedData[index]?.batch || 'Unknown Batch';

                    Swal.fire({
                        title: 'Are you sure?',
                        text: `Batch "${batchName}" will be removed from the scan list.`,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete it!',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            scannedData.splice(index, 1);
                            renderTable();

                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: `Batch "${batchName}" removed success.`,
                                timer: 1200,
                                showConfirmButton: false
                            });
                        }
                    });
                }
            });

            // Fokus otomatis saat modal dibuka
            document.getElementById('scanMaterial').addEventListener('shown.bs.modal', () => {
                setTimeout(() => document.getElementById('whBarcodeInput').focus(), 100);
            });
        });
    </script>
@endsection
