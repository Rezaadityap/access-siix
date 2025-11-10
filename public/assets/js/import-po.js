$(document).ready(function () {
    let uploadedFiles = [];

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    const uploadUrl = document
        .querySelector('meta[name="upload-url"]')
        .getAttribute("content");
    const storeUrl = document
        .querySelector('meta[name="store-url"]')
        .getAttribute("content");

    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": csrfToken,
        },
    });

    // Upload multiple
    $(document).on("change", ".import-file", function () {
        const files = this.files;
        if (!files.length) return;

        const uploadData = new FormData();
        uploadData.append("_token", csrfToken);
        for (let i = 0; i < files.length; i++) {
            uploadData.append("files[]", files[i]);
        }

        $.ajax({
            url: uploadUrl,
            method: "POST",
            data: uploadData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (!response.files || !response.files.length) {
                    Swal.fire({
                        icon: "warning",
                        title: "No valid files!",
                        text: "No files were processed by the server.",
                    });
                    return;
                }

                $(
                    '#importTable tbody tr:contains("No files uploaded")'
                ).remove();

                response.files.forEach((file) => {
                    const { model, po_number, path } = file;

                    // Cegah duplikasi path
                    if (uploadedFiles.some((f) => f.path === path)) return;

                    const fileData = { model, po_number, path };
                    uploadedFiles.push(fileData);

                    const newRow = `
                        <tr data-path="${path}">
                            <td><input type="text" class="form-control po_number" value="${po_number}" readonly></td>
                            <td><input type="text" class="form-control model" value="${model}" readonly></td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-danger remove-row" title="Remove this file">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                    $("#importTable tbody").append(newRow);
                });

                // Fokus ke tabel biar user bisa langsung lihat hasil upload
                $("#importTable")
                    .closest(".modal-body")
                    .animate(
                        { scrollTop: $("#importTable").offset().top },
                        500
                    );
            },
            error: function () {
                Swal.fire({
                    icon: "error",
                    title: "Upload Failed",
                    text: "Could not upload one or more files.",
                });
            },
        });

        // Reset input agar bisa upload file lain
        $(this).val("");
    });

    // Hapus file dari tabel + list array
    $(document).on("click", ".remove-row", function (e) {
        e.preventDefault();
        const row = $(this).closest("tr");
        const filePath = row.data("path");

        Swal.fire({
            title: "Are you sure?",
            text: "This file will be removed from the list.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                uploadedFiles = uploadedFiles.filter(
                    (f) => f.path !== filePath
                );
                row.remove();

                if ($("#importTable tbody tr").length === 0) {
                    $("#importTable tbody").html(
                        `<tr><td colspan="3" class="text-center text-muted">No files uploaded</td></tr>`
                    );
                }

                Swal.fire({
                    icon: "success",
                    title: "Deleted!",
                    text: "The file has been removed.",
                    timer: 1000,
                    showConfirmButton: false,
                });
            }
        });
    });

    // Submit form
    $("#importFormWrapper").on("submit", function (e) {
        e.preventDefault();

        if (uploadedFiles.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "No Files!",
                text: "Please upload at least one file before submitting.",
            });
            return;
        }

        const forms = uploadedFiles.map((file) => ({
            po_number: file.po_number,
            model: file.model,
            file_path: file.path,
            lot_size: $("#lot_size").val(),
            line: $("#lineArea").val(),
        }));

        Swal.fire({
            title: "Are you sure?",
            text: "This data will be saved to the database.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Yes, save it!",
            cancelButtonText: "Cancel",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: storeUrl,
                    method: "POST",
                    data: {
                        _token: csrfToken,
                        forms: forms,
                    },
                    success: function (response) {
                        if (response.status === "success") {
                            Swal.fire({
                                icon: "success",
                                title: "Success!",
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false,
                            }).then(() => {
                                updateInfoFields(response.data);
                                $("#searchRecords")
                                    .DataTable()
                                    .ajax.reload(null, false);

                                $("#poNumber").modal("hide");
                                $("#importTable tbody").empty();
                                $("#importFormWrapper")[0].reset();
                                uploadedFiles = [];
                            });
                        } else if (response.status === "duplicate") {
                            Swal.fire({
                                icon: "warning",
                                title: "Duplicate PO Number!",
                                text: response.message,
                            });
                        }
                    },
                    error: function (xhr) {
                        const response = xhr.responseJSON;
                        if (
                            xhr.status === 422 &&
                            response?.status === "duplicate"
                        ) {
                            Swal.fire({
                                icon: "warning",
                                title: "Duplicate PO Number!",
                                text: response.message,
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Failed!",
                                text:
                                    response?.message ||
                                    "An error occurred while saving data.",
                            });
                        }
                    },
                });
            }
        });
    });
    // reset po number
    $("#poNumber").on("show.bs.modal", function (e) {
        const currentPO = localStorage.getItem("currentPO");
        if (currentPO) {
            e.preventDefault();

            Swal.fire({
                title: "Active PO Number!",
                html: `You need to clear the PO number. Do you want to clear?`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                cancelButtonColor: "#6c757d",
                confirmButtonText: "Yes, Reset!",
                cancelButtonText: "Cancel",
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: "Cleaning",
                        text: "Please wait a moment.",
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading(),
                    });

                    setTimeout(() => {
                        Swal.fire({
                            icon: "success",
                            title: "Succeeded",
                            text: "PO data has been successfully cleaned.",
                            timer: 800,
                            showConfirmButton: false,
                        });

                        localStorage.removeItem("currentPO");
                        $("#recordMaterials tbody").html(
                            `<tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>`
                        );
                        $(
                            "#recordScanWH tbody, #recordScanSMD tbody, #recordScanMAR tbody"
                        ).html(
                            `<tr><td colspan="5" class="text-center text-muted">No Data Found</td></tr>`
                        );
                        $("#infoContainer").empty();
                        $(
                            "#infoProduction, #infoLine, #infoLotSize, #infoActual, #infoDate"
                        ).val("");

                        $("#searchRecords .btn-custom").each(function () {
                            $(this)
                                .prop("disabled", false)
                                .text("Select")
                                .removeClass("btn-danger")
                                .addClass("btn-primary");
                        });

                        console.log(
                            "[resetPoButton] Semua localStorage dihapus."
                        );

                        $("#poNumber").modal("show");
                    }, 800);
                } else {
                    Swal.close();
                }
            });
        }
    });
});
