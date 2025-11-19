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

                let duplicatesSkipped = 0;

                response.files.forEach((file) => {
                    const model = (file.model || "").toString().trim();
                    const po_number = (file.po_number || "").toString().trim();
                    const path = file.path;

                    if (!po_number) {
                        // jika PO kosong, tetep masukan
                        uploadedFiles.push({ model, po_number, path });
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
                        return;
                    }

                    const key = `${po_number}::${model}`.toLowerCase();

                    const exists = uploadedFiles.some((f) => {
                        const existingKey = `${(f.po_number || "")
                            .toString()
                            .trim()}::${(f.model || "")
                            .toString()
                            .trim()}`.toLowerCase();
                        return existingKey === key;
                    });

                    if (exists) {
                        duplicatesSkipped++;
                        return;
                    }

                    // tambahkan file baru (first-seen wins)
                    uploadedFiles.push({ model, po_number, path });

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

                if (duplicatesSkipped > 0) {
                    Swal.fire({
                        icon: "info",
                        title: "Duplicates removed",
                        text: `${duplicatesSkipped} duplicate file(s) (same PO + Model) were skipped and not added to the import list.`,
                        timer: 1800,
                        showConfirmButton: false,
                        toast: true,
                        position: "top-end",
                    });
                }

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
                                try {
                                    const data = Array.isArray(response.data)
                                        ? response.data
                                        : [];

                                    // build safe poList (only items with po_number)
                                    const domPOs = getCurrentPOFromDOM();
                                    const poList = data
                                        .map((r) => ({
                                            po_number: r?.po_number,
                                            group_id:
                                                r?.group_id ??
                                                r?.groupId ??
                                                domPOs.find(
                                                    (d) =>
                                                        d.po_number ===
                                                        r?.po_number
                                                )?.group_id ??
                                                null,
                                        }))
                                        .filter((p) => p.po_number);

                                    if (poList.length) {
                                        window.renderSavedPOFromList(poList);
                                    }

                                    if (poList.length) {
                                        if (
                                            typeof renderSavedPOFromList ===
                                            "function"
                                        ) {
                                            renderSavedPOFromList(poList);
                                        } else {
                                            console.warn(
                                                "renderSavedPOFromList not defined"
                                            );
                                        }
                                    } else {
                                        console.warn(
                                            "[AJAX success] response.data kosong atau tidak berformat sesuai"
                                        );
                                    }

                                    if (
                                        typeof updateInfoFields === "function"
                                    ) {
                                        updateInfoFields(data);
                                    } else {
                                        console.warn(
                                            "updateInfoFields not defined"
                                        );
                                    }

                                    if (typeof loadHistoryData === "function") {
                                        // kirim semua PO & group_id
                                        const hist = data.map((d) => ({
                                            po_number: d.po_number,
                                            group_id: d.group_id ?? null,
                                        }));
                                        loadHistoryData(hist);
                                    }

                                    // reload datatable only if initialized
                                    if (
                                        $.fn.DataTable &&
                                        $.fn.DataTable.isDataTable(
                                            "#searchRecords"
                                        )
                                    ) {
                                        $("#searchRecords")
                                            .DataTable()
                                            .ajax.reload(null, false);
                                    }

                                    if (window.__reapplySort)
                                        window.__reapplySort();
                                } catch (err) {
                                    console.error(
                                        "Error handling success response:",
                                        err
                                    );
                                } finally {
                                    // cleanup UI upload
                                    $("#poNumber").modal("hide");
                                    $("#importTable tbody").empty();
                                    $("#importFormWrapper")[0].reset();
                                    uploadedFiles = [];
                                }
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
});
