<!-- Modal Edit Information -->
<div class="modal fade" id="editInformation" tabindex="-1" aria-labelledby="editInformationLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form id="formEditInfo">
                <div class="modal-header gradient-success text-white">
                    <h5 class="modal-title" id="editInformationLabel">Edit Info (Line & Lot Size)</h5>
                    <button type="button" class="btn-close text-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="edit_group_id" name="group_id" value="">
                    <div class="mb-3">
                        <label for="edit_line" class="form-label small">Line</label>
                        <select id="edit_line" name="line" class="form-control form-control-sm">
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_lot_size" class="form-label small">Lot Size</label>
                        <input type="number" id="edit_lot_size" name="lot_size" class="form-control form-control-sm"
                            placeholder="Lot Size" min="0" step="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-gradient-secondary"
                        data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-gradient-success">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
