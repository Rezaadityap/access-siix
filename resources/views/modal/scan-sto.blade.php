<!-- Modal Scan STO -->
<div class="modal fade" id="scanSto" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-upc-scan me-2"></i>Scan STO Material</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="scanStoMaterial">
                    <div class="form-block border p-3 rounded mb-3 bg-light">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="input-group mb-4 shadow-sm rounded-3">
                                    <span class="input-group-text bg-danger text-white fw-bold border-0 p-3"
                                        id="sto-barcode-icon">
                                        <i class="bi bi-upc-scan fs-4"></i>
                                    </span>
                                    <input type="text" id="stoBarcodeInput"
                                        class="form-control form-control-lg border-0 ps-3 py-3"
                                        placeholder="Scan STO Material" aria-label="Scan STO Material"
                                        aria-describedby="sto-barcode-icon" autofocus>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle text-center mb-0">
                                        <thead class="table-danger">
                                            <tr class="text-muted">
                                                <th>NO</th>
                                                <th>SCAN CODE</th>
                                                <th>MATERIAL CODE</th>
                                                <th>QTY</th>
                                                <th>BATCH DESCRIPTION</th>
                                                <th>ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody id="stoTableBody">
                                            <tr>
                                                <td colspan="6" class="text-muted">No data found</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="button" id="submitScanSto" class="btn btn-danger px-4 shadow-sm">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
