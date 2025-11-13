@extends('layouts.app')
@section('title')
    Record History
@endsection

@section('content')
    <main class="main" id="main">
        <div class="pagetitle">
            <h1>Record History</h1>
            <nav>
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('kitting.prod1') }}">Kitting Production 1</a></li>
                    <li class="breadcrumb-item active">Record History</li>
                </ol>
            </nav>
        </div>
        <div class="flex-grow-1 container-p-y">
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-2">
                        <div class="card-body">
                            <h5 class="card-title mb-0">History Record</h5>

                            {{-- Filter Form --}}
                            <form method="GET" action="{{ route('op-kitting.history') }}"
                                class="d-flex align-items-center my-2 gap-2">
                                <input type="date" name="date" class="form-control" value="{{ $date }}"
                                    onchange="this.form.submit()">

                                <select name="model" id="modelSelect" class="form-control" onchange="this.form.submit()"
                                    {{ $models->isEmpty() ? 'disabled' : '' }}>
                                    <option value="">Select Model</option>
                                    @foreach ($models as $model)
                                        <option value="{{ $model }}"
                                            {{ $selectedModel == $model ? 'selected' : '' }}>
                                            {{ $model }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                            <div class="d-flex align-items-center gap-3 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="2" id="chkDeleteStatus1">
                                    <label class="form-check-label" for="chkDeleteStatus1">
                                        Deleted
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="chkUpdateStatus2">
                                    <label class="form-check-label" for="chkUpdateStatus2">
                                        Updated
                                    </label>
                                </div>
                            </div>

                            {{-- Table --}}
                            <div class="table-responsive mt-3">
                                <table
                                    class="table table-bordered table-sm text-center align-middle sticky-header history-material-table"
                                    id="recordTable">
                                    <thead class="align-middle text-center">
                                        <tr>
                                            <th>No</th>
                                            <th>PO Number</th>
                                            <th>PO Item</th>
                                            <th>Part Number</th>
                                            <th>Part Description</th>
                                            <th>Required Qty</th>
                                            <th>Remarks</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse ($record as $index => $item)
                                            <tr id="row-{{ $item->id }}"
                                                @if ($item->status == 1) class="table-success"
                                        @elseif($item->status == 2)
                                            class="table-danger" @endif>
                                                <td>{{ $index + 1 }}</td>
                                                <td>{{ $item->recordMaterialTrans->po_number }}</td>
                                                <td>{{ $item->po_item }}</td>
                                                <td>{{ $item->material }}</td>
                                                <td>{{ $item->material_desc }}</td>
                                                <td>{{ $item->rec_qty }}</td>
                                                <td>{{ $item->remarks }}</td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm btn-delete"
                                                        style="padding: 0.1rem 0.35rem; font-size: 0.7rem;"
                                                        data-id="{{ $item->id }}"
                                                        data-po="{{ $item->recordMaterialTrans->po_number }}"
                                                        data-po-item="{{ $item->po_item }}"
                                                        data-material="{{ $item->material }}"
                                                        data-material-desc="{{ $item->material_desc }}"
                                                        data-qty="{{ $item->rec_qty }}">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="7" class="text-center text-muted">No records found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal --}}
        <div class="modal fade" id="replaceModal" tabindex="-1" aria-labelledby="replaceModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <form id="replaceForm">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="replaceModalLabel">Replace Record</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="id" id="recordId">
                            <div class="mb-3">
                                <label>PO Item</label>
                                <input type="text" name="po_item" id="newPoItem" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Part Number</label>
                                <input type="text" name="material" id="newMaterial" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Part Description</label>
                                <input type="text" name="material_desc" id="newMaterialDesc" class="form-control"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label>Required Qty</label>
                                <input type="number" name="rec_qty" id="newQty" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label>Note Remarks</label>
                                <input type="text" name="remarks" id="remarks" class="form-control"
                                    placeholder="Input note remarks" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger btn-sm" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary btn-sm">Submit</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </main>
@endsection

@push('script')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let selectedId = null;

            const chkDeleteStatus1 = document.getElementById('chkDeleteStatus1');
            const chkUpdateStatus2 = document.getElementById('chkUpdateStatus2');

            // Fungsi filter dan nomor urut
            function filterRows() {
                const rows = document.querySelectorAll('#recordTable tbody tr');
                let counter = 1; // reset nomor urut

                rows.forEach(row => {
                    // Ambil status dari class
                    let status = null;
                    if (row.classList.contains('table-success')) status = 1; // Updated
                    else if (row.classList.contains('table-danger')) status = 2; // Deleted

                    let show = true; // default tampil semua

                    if (chkDeleteStatus1.checked && !chkUpdateStatus2.checked) show = (status === 2);
                    else if (!chkDeleteStatus1.checked && chkUpdateStatus2.checked) show = (status === 1);
                    else if (chkDeleteStatus1.checked && chkUpdateStatus2.checked) show = (status === 1 ||
                        status === 2);
                    else show = true;

                    if (show) {
                        row.style.display = '';
                        row.cells[0].innerText = counter++;
                    } else {
                        row.style.display = 'none';
                    }
                });
            }

            // Event listener checkbox
            chkDeleteStatus1.addEventListener('change', filterRows);
            chkUpdateStatus2.addEventListener('change', filterRows);

            // Klik tombol delete/replace
            function setupDeleteButtons(container = document) {
                container.querySelectorAll('.btn-delete').forEach(btn => {
                    btn.addEventListener('click', function() {
                        selectedId = this.dataset.id;
                        const po = this.dataset.po;
                        const material = this.dataset.material;
                        const poItem = this.dataset.poItem;
                        const materialDesc = this.dataset.materialDesc;
                        const qty = this.dataset.qty;

                        Swal.fire({
                            title: 'Are you sure you want to replace this record?',
                            text: `PO: ${po} | Material: ${material}`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, replace data',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                // isi modal
                                document.getElementById('recordId').value = selectedId;
                                document.getElementById('newPoItem').value = poItem;
                                document.getElementById('newMaterial').value = material;
                                document.getElementById('newMaterialDesc').value =
                                    materialDesc;
                                document.getElementById('newQty').value = qty;

                                // tampilkan modal
                                const modal = new bootstrap.Modal(document.getElementById(
                                    'replaceModal'));
                                modal.show();
                            }
                        });
                    });
                });
            }

            // Setup awal tombol delete
            setupDeleteButtons();

            // Submit modal replace
            document.getElementById('replaceForm').addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);

                // tampilkan alert konfirmasi dulu
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to replace this record!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, replace it',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // lakukan fetch setelah user konfirmasi
                        fetch(`/op-kitting/history/replace/${selectedId}`, {
                                method: 'POST',
                                body: formData,
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                                }
                            })
                            .then(res => res.json())
                            .then(result => {
                                if (result.success) {
                                    const modal = bootstrap.Modal.getInstance(document
                                        .getElementById('replaceModal'));
                                    modal.hide();
                                    Swal.fire('Success!', result.message, 'success');

                                    const oldRow = document.getElementById(`row-${selectedId}`);
                                    const newData = result.data;

                                    // update warna row lama jadi merah
                                    oldRow.classList.remove('table-success', 'table-danger');
                                    oldRow.classList.add('table-danger');

                                    // buat row baru (hijau)
                                    const newRow = document.createElement('tr');
                                    newRow.id = `row-${newData.id}`;
                                    newRow.classList.add('table-success');
                                    newRow.innerHTML = `
                        <td></td>
                        <td>${newData.record_material_trans.po_number}</td>
                        <td>${newData.po_item}</td>
                        <td>${newData.material}</td>
                        <td>${newData.material_desc}</td>
                        <td>${newData.rec_qty}</td>
                        <td>${newData.remarks ?? ''}</td>
                        <td>
                            <button type="button" class="btn btn-danger btn-sm btn-delete"
                                style="padding: 0.1rem 0.35rem; font-size: 0.7rem;"
                                data-id="${newData.id}"
                                data-po="${newData.record_material_trans.po_number}"
                                data-po-item="${newData.po_item}"
                                data-material="${newData.material}"
                                data-material-desc="${newData.material_desc}"
                                data-qty="${newData.rec_qty}">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    `;

                                    oldRow.after(newRow);
                                    setupDeleteButtons(newRow);
                                    filterRows();
                                } else {
                                    Swal.fire('Error!', result.message, 'error');
                                }
                            })
                            .catch(err => {
                                console.error(err);
                                Swal.fire('Error', 'An error occurred while processing',
                                    'error');
                            });
                    }
                });
            });
        });
    </script>
@endpush
