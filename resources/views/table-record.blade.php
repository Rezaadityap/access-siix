<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row">
        <div class="col-md-12">
            <div class="card mb-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center my-2">
                        <h5 id="recordMaterial" class="card-title mb-0">Record Material</h5>
                        <button class="btn btn-success btn-sm" onclick="downloadExcel()">
                            <i class="fas fa-file-excel me-1"></i> Download Excel
                        </button>
                    </div>
                    <div class="row text-center">
                        <div class="col-md-12">
                            <div class="table-responsive">
                                <table
                                    class="table table-bordered table-sm text-center align-middle sticky-header custom-material-table"
                                    id="recordMaterials">
                                    <thead class="align-middle text-center">
                                        <tr>
                                            <th class="text-nowrap">No</th>
                                            <th>Part Number</th>
                                            <th>Part Description</th>
                                            <th class="text-nowrap">Required Qty</th>
                                            <th class="text-nowrap">Unit</th>
                                            <th style="background-color: #cfe2ff;">Qty Received<br>(WH)</th>
                                            <th style="background-color: #d1f2eb;">Balance<br>Stock</th>
                                            <th style="background-color: #d1f2eb;">Status<br>Stock</th>
                                            <th style="background-color: #d1f2eb;">Calculate<br>Stock</th>
                                            <th style="background-color: #f8d7da;">Actual From<br>STO</th>
                                            <th style="background-color: #f8d7da;">Status After<br>STO</th>
                                            <th style="background-color: #f8d7da;">BAL Qty<br>After STO</th>
                                            <th style="background-color: #fff3cd;">Actual<br>Qty Usage</th>
                                            <th style="background-color: #fff3cd;">Qty After<br>Running</th>
                                            <th style="background-color: #fff3cd;">Qty <br> Counting</th>
                                            <th style="background-color: #ff3700ff;">Qty Part LCR</th>
                                            <th style="background-color: #ff3700ff;">Qty Sample</th>
                                            <th style="background-color: #ff3700ff;">Qty <br> Loss & Scrap</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
