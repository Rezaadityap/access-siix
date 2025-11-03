<!-- Modal Scan SMD -->
<div class="modal fade" id="scanSmd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-upc-scan me-2"></i>Scan Rack SMD Production</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="scanRackSMD">
                    <div class="form-block border p-3 rounded mb-3 bg-light">
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="input-group mb-4 shadow-sm rounded-3">
                                    <span class="input-group-text bg-success text-white fw-bold border-0 p-3"
                                        id="wh-barcode-icon">
                                        <i class="bi bi-upc-scan fs-4"></i>
                                    </span>
                                    <input type="text" id="smdBarcodeInput"
                                        class="form-control form-control-lg border-0 ps-3 py-3"
                                        placeholder="Scan Rack SMD Production" aria-label="Scan Rack SMD Production"
                                        aria-describedby="smd-barcode-icon" autofocus>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle text-center mb-0">
                                        <thead class="table-success">
                                            <tr class="text-muted">
                                                <th>NO</th>
                                                <th>SCAN CODE</th>
                                                <th>MATERIAL CODE</th>
                                                <th>QTY</th>
                                                <th>BATCH DESCRIPTION</th>
                                                <th>ACTION</th>
                                            </tr>
                                        </thead>
                                        <tbody id="smdTableBody">
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
                        <button type="button" id="submitScanSmd" class="btn btn-success px-4 shadow-sm">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
