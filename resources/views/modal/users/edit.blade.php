<div class="modal fade" id="userEditModal" tabindex="-1" aria-labelledby="userEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userEditModalLabel">Edit User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="userEditForm" enctype="multipart/form-data" novalidate>
                @csrf
                @method('PUT')

                <div class="modal-body">
                    <div id="editFormAlert" class="alert d-none" role="alert"></div>

                    <div class="row g-3">
                        <div class="col-md-12">
                            <div class="mb-2">
                                <label for="editName" class="form-label">Full name</label>
                                <input type="text" id="editName" name="name" class="form-control" />
                                <div class="invalid-feedback" id="error_name"></div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label for="editNik" class="form-label">NIK</label>
                                    <input type="text" id="editNik" name="nik" class="form-control" />
                                    <div class="invalid-feedback" id="error_nik"></div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label for="editEmail" class="form-label">Email</label>
                                    <input type="email" id="editEmail" name="email" class="form-control" />
                                    <div class="invalid-feedback" id="error_email"></div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label for="editDepartment" class="form-label">Department</label>
                                    <input type="text" id="editDepartment" name="department" class="form-control" />
                                    <div class="invalid-feedback" id="error_department"></div>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <label for="editSection" class="form-label">Section</label>
                                    <input type="text" id="editSection" name="section" class="form-control" />
                                    <div class="invalid-feedback" id="error_section"></div>
                                </div>
                            </div>

                            <div class="mb-2">
                                <label for="editLevel" class="form-label">Level</label>
                                <select id="editLevel" name="level_id" class="form-select">
                                    <option value="">-- Select level --</option>
                                    @foreach ($levels as $lvl)
                                        <option value="{{ $lvl->id }}">{{ $lvl->level_name }}</option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="error_level_id"></div>
                            </div>

                        </div>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-gradient-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gradient-primary" id="saveEditBtn">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
