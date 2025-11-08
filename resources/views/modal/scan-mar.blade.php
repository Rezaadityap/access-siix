<!-- Modal Scan STO -->
<div class="modal fade" id="scanMar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-warning text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-upc-scan me-2"></i>Scan Material After Running</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="scanMaterialAfterRunning">
                    <div class="form-block border p-3 rounded mb-3 bg-light">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="input-group shadow-sm rounded-3">
                                    <span class="input-group-text bg-warning text-white fw-bold border-0 p-3"
                                        id="sto-barcode-icon">
                                        <i class="bi bi-upc-scan fs-4"></i>
                                    </span>
                                    <input type="text" id="marBarcodeInput"
                                        class="form-control form-control-lg border-0 ps-3 py-3"
                                        placeholder="Scan Material After Running"
                                        aria-label="Scan Material After Running" aria-describedby="mar-barcode-icon"
                                        autofocus>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="d-flex align-items-center">
                                    <label for="actualLotSize" class="fw-bold" style="width: 150px;">Actual Lot
                                        Size</label>
                                    <div class="p-2">:</div>
                                    <input type="number" id="actualLotSize" class="form-control"
                                        placeholder="Enter the actual lot size">
                                </div>
                            </div>

                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <label for="cavity" class="fw-bold" style="width: 200px;">Cavity</label>
                                    <div class="p-2">:</div>
                                    <input type="number" id="cavity" class="form-control"
                                        placeholder="Enter the cavity">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="d-flex align-items-center">
                                    <label for="change_model" class="fw-bold" style="width: 200px">Change Model</label>
                                    <div class="p-2">:</div>
                                    <input type="number" id="change_model" class="form-control"
                                        placeholder="Enter the change model">
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle text-center mb-0">
                                        <thead class="table-warning">
                                            <tr class="text-muted">
                                                <th>NO</th>
                                                <th>SCAN CODE</th>
                                                <th>MATERIAL CODE</th>
                                                <th>QTY</th>
                                                <th>BATCH DESCRIPTION</th>
                                                <th>ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody id="marTableBody">
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
                        <button type="button" id="submitScanMar"
                            class="btn btn-warning text-white px-4 shadow-sm">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
