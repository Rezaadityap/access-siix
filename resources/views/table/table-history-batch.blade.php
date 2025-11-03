<div class="container-xxl flex-grow-1 container-p-y mt-3">
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center my-2">
                        <h5 class="card-title mb-0">History Record Batch</h5>
                        <button id="toggleHistoryBtn" class="btn btn-sm btn-primary" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapseHistory" aria-expanded="false"
                            aria-controls="collapseHistory">
                            <i class="bi bi-chevron-down me-1"></i> Expand
                        </button>
                    </div>
                    <div class="row collapse" style="margin-right: -8px; margin-left: -8px;" id="collapseHistory">
                        <div class="col-md-6 px-2">
                            <div class="border rounded p-3 h-100">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between items-center">
                                            <h6 class="fw-bold gradient-text-primary mb-2" style="font-size: 0.84em">SMD
                                                Record
                                            </h6>
                                            <a href="#"
                                                class="copyBatchBtn text-secondary text-decoration-none small d-flex align-items-center">
                                                <i class="bi bi-clipboard me-1" style="font-size: 0.9em;"></i>
                                                <span>Copy batch</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table
                                        class="table table-bordered table-sm text-center align-middle sticky-header history-material-table"
                                        id="recordScanSMD">
                                        <thead class="align-middle text-center">
                                            <tr>
                                                <th>No</th>
                                                <th>Scan Code</th>
                                                <th>Material Code</th>
                                                <th>Qty</th>
                                                <th>Batch Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="5">No data found</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 px-2">
                            <div class="border rounded p-3 h-100">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between items-center">
                                            <h6 class="fw-bold gradient-text-primary mb-2" style="font-size: 0.84em">WH
                                                Record
                                            </h6>
                                            <a href="#"
                                                class="copyBatchBtn text-secondary text-decoration-none small d-flex align-items-center">
                                                <i class="bi bi-clipboard me-1" style="font-size: 0.9em;"></i>
                                                <span>Copy batch</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table
                                        class="table table-bordered table-sm text-center align-middle sticky-header history-material-table"
                                        id="recordScanWH">
                                        <thead class="align-middle text-center">
                                            <tr>
                                                <th>No</th>
                                                <th>Scan Code</th>
                                                <th>Material Code</th>
                                                <th>Qty</th>
                                                <th>Batch Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="5">No data found</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 px-2 mt-3">
                            <div class="border rounded p-3 h-100">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between items-center">
                                            <h6 class="fw-bold gradient-text-primary mb-2" style="font-size: 0.84em">STO
                                                Record
                                            </h6>
                                            <a href="#"
                                                class="copyBatchBtn text-secondary text-decoration-none small d-flex align-items-center">
                                                <i class="bi bi-clipboard me-1" style="font-size: 0.9em;"></i>
                                                <span>Copy batch</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table
                                        class="table table-bordered table-sm text-center align-middle sticky-header history-material-table"
                                        id="recordScanSTO">
                                        <thead class="align-middle text-center">
                                            <tr>
                                                <th>No</th>
                                                <th>Scan Code</th>
                                                <th>Material Code</th>
                                                <th>Qty</th>
                                                <th>Batch Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="5">No data found</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 px-2 mt-3">
                            <div class="border rounded p-3 h-100">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between items-center">
                                            <h6 class="fw-bold gradient-text-primary mb-2" style="font-size: 0.84em">
                                                Material After Running
                                                Record
                                            </h6>
                                            <a href="#"
                                                class="copyBatchBtn text-secondary text-decoration-none small d-flex align-items-center">
                                                <i class="bi bi-clipboard me-1" style="font-size: 0.9em;"></i>
                                                <span>Copy batch</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table
                                        class="table table-bordered table-sm text-center align-middle sticky-header history-material-table"
                                        id="recordScanMAR">
                                        <thead class="align-middle text-center">
                                            <tr>
                                                <th>No</th>
                                                <th>Scan Code</th>
                                                <th>Material Code</th>
                                                <th>Qty</th>
                                                <th>Batch Description</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="5">No data found</td>
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
    </div>
</div>
