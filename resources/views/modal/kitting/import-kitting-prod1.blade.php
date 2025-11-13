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
                                    <input type="file" class="form-control import-file" accept=".txt, .csv" multiple>
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
                                <div class="col-6">
                                    <label class="form-label">LOT SIZE</label>
                                    <input type="number" id="lot_size" class="form-control"
                                        placeholder="Enter Lot Size" required>
                                </div>
                                <div class="col-6">
                                    <label class="form-label">LINE AREA</label>
                                    <select id="lineArea" class="form-control" required>
                                        <option value="">Select Line</option>
                                        <option value="SMT-01">SMT-01</option>
                                        <option value="SMT-02">SMT-02</option>
                                        <option value="SMT-03">SMT-03</option>
                                        <option value="SMT-04">SMT-04</option>
                                        <option value="SMT-05">SMT-05</option>
                                        <option value="SMT-06">SMT-06</option>
                                        <option value="SMT-07">SMT-07</option>
                                        <option value="SMT-08">SMT-08</option>
                                        <option value="SMT-09">SMT-09</option>
                                        <option value="SMT-10">SMT-10</option>
                                        <option value="SMT-11">SMT-11</option>
                                        <option value="SMT-12">SMT-12</option>
                                        <option value="SMT-13">SMT-13</option>
                                        <option value="SMT-14">SMT-14</option>
                                        <option value="SMT-15">SMT-15</option>
                                        <option value="SMT-16">SMT-16</option>
                                        <option value="SMT-17">SMT-17</option>
                                        <option value="SMT-18">SMT-18</option>
                                        <option value="SMT-19">SMT-19</option>
                                    </select>
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
