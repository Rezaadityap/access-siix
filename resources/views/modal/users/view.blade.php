<!-- Modern profile-card modal style (similar to reference image) -->
<div class="modal fade" id="userViewModal" tabindex="-1" aria-labelledby="userViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content border-0 shadow-lg" style="border-radius:14px; overflow:hidden;">
            <div class="modal-body p-0">
                <div id="userViewLoader" class="d-flex justify-content-center align-items-center p-5">
                    <div class="spinner-border" role="status"></div>
                </div>

                <div id="userViewError" class="d-none text-center text-danger py-4">
                    Unable to load user details.
                </div>

                <div id="userViewContent" class="d-none">
                    <div class="profile-wrapper">

                        <!-- LEFT (PHOTO) -->
                        <div class="profile-left">
                            <div class="photo-frame">
                                <img id="userViewPhoto" src="" alt="User Photo">
                            </div>
                        </div>

                        <!-- RIGHT (DETAILS) -->
                        <div class="profile-right">
                            <h2 id="userViewName" class="user-name">-</h2>

                            <div class="info-row"><strong>NIK:</strong> <span id="detailNik">-</span></div>
                            <div class="info-row"><strong>Email:</strong> <span id="detailEmail">-</span></div>
                            <div class="info-row"><strong>Department:</strong> <span id="detailDepartment">-</span>
                            </div>
                            <div class="info-row"><strong>Section:</strong> <span id="detailSection">-</span></div>
                            <div class="profile-actions mt-4">
                                <button type="button" class="btn btn-gradient-secondary"
                                    data-bs-dismiss="modal">Close</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
