document.addEventListener("DOMContentLoaded", () => {
    const saved = localStorage.getItem("currentPO");
    if (saved) {
        const savedPOs = JSON.parse(saved);
        const poList = Array.isArray(savedPOs) ? savedPOs : [savedPOs];

        // Render input info container
        $("#infoContainer").empty();
        poList.forEach((po, index) => {
            let infoRow = "";
            if (index === 0) {
                infoRow = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-1">PO Number</label>
                            <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${po.po_number}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted mb-1">Model</label>
                            <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${po.model}" readonly>
                        </div>
                    </div>
                `;
            } else {
                infoRow = `
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${po.po_number}" readonly>
                        </div>
                        <div class="col-md-6">
                            <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${po.model}" readonly>
                        </div>
                    </div>
                `;
            }
            $("#infoContainer").append(infoRow);
        });

        // Field lainnya dari PO pertama
        const base = poList[0];
        $("#infoProduction").val(base.area || "-");
        $("#infoLine").val(base.line || "-");
        $("#infoLotSize").val(base.lot_size || "-");
        $("#infoActual").val(base.infoActual || base.act_lot_size || "0");
        $("#infoDate").val(base.date || "-");

        // Render tabel record_material_lines
        if (typeof renderSavedPO === "function") renderSavedPO();

        if (poList && poList.length > 0) {
            const poNumbers = poList.map((po) => po.po_number);
            if (typeof loadHistoryData === "function")
                loadHistoryData(poNumbers);
        }
    } else {
        $("#recordMaterials tbody").html(`
            <tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>
        `);
    }

    // -----------------------------
    // Delete PO
    // -----------------------------
    $("#deletePO").on("click", function () {
        const savedPO = JSON.parse(localStorage.getItem("currentPO") || "[]");

        if (savedPO.length === 0) {
            Swal.fire({
                icon: "info",
                title: "No saved purchase order",
                text: "No data to delete!",
                confirmButtonColor: "#3085d6",
            });
            return;
        }

        const poNumbers = savedPO.map((po) => po.po_number).join(", ");

        Swal.fire({
            title: "Delete Data?",
            html: `Are you sure you want to delete the following PO data from the database?<br><b>${poNumbers}</b>`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, Delete!",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/record_material/delete-po",
                    type: "POST",
                    data: {
                        po_numbers: savedPO.map((po) => po.po_number),
                        _token: "{{ csrf_token() }}",
                    },
                    beforeSend: function () {
                        Swal.fire({
                            title: "Deleting...",
                            text: "Please wait a moment.",
                            allowOutsideClick: false,
                            didOpen: () => Swal.showLoading(),
                        });
                    },
                    success: function (res) {
                        Swal.fire({
                            icon: "success",
                            title: "Succeeded",
                            text: "PO data has been successfully deleted from the database.",
                            timer: 1500,
                            showConfirmButton: false,
                        });

                        // Hapus localStorage & reset tampilan
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
                    },
                    error: function (err) {
                        Swal.fire({
                            icon: "error",
                            title: "Failed",
                            text: "An error occurred while deleting the PO from the database.",
                        });
                        console.error(
                            "Failed to delete PO in the database:",
                            err.responseText
                        );
                    },
                });
            }
        });
    });
});
