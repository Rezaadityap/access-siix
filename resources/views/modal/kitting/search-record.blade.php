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
                                <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap">
                                    <div class="d-flex align-items-center gap-2">
                                        <label class="fw-semibold">Date Range:</label>
                                        <input type="date" id="startDate" class="form-control form-control-sm"
                                            style="width: 150px;">
                                        <span class="fw-bold">to</span>
                                        <input type="date" id="endDate" class="form-control form-control-sm"
                                            style="width: 150px;">
                                    </div>

                                    <div class="d-flex align-items-center">
                                        <label for="tableSearch" class="fw-semibold mb-0">Search:</label>
                                        <input type="search" id="tableSearch" class="form-control form-control-sm"
                                            placeholder="Search data..." style="width: 200px;">
                                    </div>
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
