$(document).ready(function () {
    let today = new Date().toISOString().split("T")[0];
    $("#searchDate").val(today);

    // Helpers: read current PO list from DOM (#infoContainer)
    function getCurrentPOFromDOM() {
        const arr = [];
        $("#infoContainer .row").each(function () {
            const $inputs = $(this).find("input");
            const po_number = $inputs.eq(0).val() || "";
            const model = $inputs.eq(1).val() || "";
            const area = $(this).data("area");
            const line = $(this).data("line");
            const lot_size = $(this).data("lot_size");
            const act_lot_size = $(this).data("act_lot_size");
            const date = $(this).data("date");
            const group_id = $(this).data("group_id");
            if (po_number) {
                arr.push({
                    po_number,
                    model,
                    area,
                    line,
                    lot_size,
                    act_lot_size,
                    date,
                    group_id,
                });
            }
        });
        return arr;
    }

    function addPOsToDOM(polist) {
        if (!Array.isArray(polist)) polist = [polist];
        const existing = getCurrentPOFromDOM();
        const existingPOs = existing.map((p) => p.po_number);
        polist.forEach((p, idx) => {
            if (!p || !p.po_number) return;
            if (existingPOs.includes(p.po_number)) return;

            let infoRow = "";
            if (existing.length === 0) {
                infoRow = `
                <div class="row mb-3" data-group_id="${p.group_id ?? ""}" ${
                    p.area ? `data-area="${p.area}"` : ""
                } ${p.line ? `data-line="${p.line}"` : ""} ${
                    p.lot_size ? `data-lot_size="${p.lot_size}"` : ""
                } ${
                    p.act_lot_size
                        ? `data-act_lot_size="${p.act_lot_size}"`
                        : ""
                } ${p.date ? `data-date="${p.date}"` : ""}>
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">PO Number</label>
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${
                            p.po_number
                        }" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Model</label>
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${
                            p.model || ""
                        }" readonly>
                    </div>
                </div>`;
            } else {
                // subsequent rows no labels (same as original)
                infoRow = `
                <div class="row mb-3" data-group_id="${p.group_id ?? ""}" ${
                    p.area ? `data-area="${p.area}"` : ""
                } ${p.line ? `data-line="${p.line}"` : ""} ${
                    p.lot_size ? `data-lot_size="${p.lot_size}"` : ""
                } ${
                    p.act_lot_size
                        ? `data-act_lot_size="${p.act_lot_size}"`
                        : ""
                } ${p.date ? `data-date="${p.date}"` : ""}>
                    <div class="col-md-6">
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${
                            p.po_number
                        }" readonly>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${
                            p.model || ""
                        }" readonly>
                    </div>
                </div>`;
            }
            $("#infoContainer").append(infoRow);
            existing.push(p);
        });
    }

    // Remove PO by po_number from DOM
    function removePOFromDOM(po_number) {
        if (!po_number) return;
        $("#infoContainer .row").each(function () {
            const po = $(this).find("input").eq(0).val();
            if (po === po_number) $(this).remove();
        });
    }

    // Update buttons in the table row depending on whether PO exists in DOM
    function refreshTableButtons(table) {
        let existing = getCurrentPOFromDOM();
        const existingPOs = existing.map((p) => p.po_number);
        $("#searchRecords tbody tr").each(function () {
            const rowData = table.row(this).data();
            if (!rowData || !rowData.po_number) return;
            const btn = $(this).find(".btn-custom");
            const poExist = existingPOs.includes(rowData.po_number);
            if (poExist) {
                btn.text("Cancel")
                    .removeClass("btn-primary")
                    .addClass("btn-danger");
            } else {
                btn.text("Select")
                    .removeClass("btn-danger")
                    .addClass("btn-primary");
            }
        });
    }

    let table = $("#searchRecords").DataTable({
        serverSide: false,
        dom: "rtip",
        ajax: {
            url: "/record_material/getSearchRecord",
            data: function (d) {
                d.start_date = $("#startDate").val();
                d.end_date = $("#endDate").val();
            },
            dataSrc: function (json) {
                return json.length ? json : [];
            },
            error: function (err) {
                console.error("Gagal load data:", err);
            },
        },
        columns: [
            { data: null, render: (data, type, row, meta) => meta.row + 1 },
            { data: "po_number" },
            { data: "area" },
            { data: "line" },
            { data: "model" },
            { data: "date" },
            { data: "lot_size" },
            { data: "act_lot_size" },
            {
                data: null,
                render: function (data, type, row) {
                    return `<button type="button" class="btn btn-primary btn-custom">Select</button>`;
                },
            },
        ],
        language: { emptyTable: "No data found" },
        pageLength: 5,
    });

    $("#startDate, #endDate").on("change", function () {
        table.ajax.reload();
    });

    $("#tableSearch").on("keyup", function () {
        table.search(this.value).draw();
    });

    // On draw, update button texts based on DOM PO list
    table.on("draw", function () {
        refreshTableButtons(table);
    });

    // Click handler for Select / Cancel
    $("#searchRecords").on("click", ".btn-custom", function () {
        let rowData = table.row($(this).parents("tr")).data();
        const btn = $(this);

        if (!rowData || !rowData.po_number) return;

        // If currently 'Cancel'
        if (btn.text().trim() === "Cancel") {
            Swal.fire({
                title: "Cancel this PO?",
                text: `PO ${rowData.po_number} will be removed.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, cancel it!",
                cancelButtonText: "No",
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    // remove only that PO from DOM
                    removePOFromDOM(rowData.po_number);

                    // After removal, if nothing left, clear related UI (same behavior as before)
                    const remaining = getCurrentPOFromDOM();
                    if (remaining.length > 0) {
                        // update info fields using the remaining array
                        if (typeof updateInfoFields === "function")
                            updateInfoFields(remaining);
                    } else {
                        $("#infoContainer").empty();
                        $(
                            "#infoPONumber, #infoProduction, #infoLine, #infoModel, #infoLotSize, #infoActual, #infoDate"
                        ).val("");
                        $("#recordMaterials tbody").html(
                            `<tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>`
                        );
                    }

                    // toggle button look
                    btn.text("Select")
                        .removeClass("btn-danger")
                        .addClass("btn-primary");

                    Swal.fire({
                        icon: "success",
                        title: "Cancelled!",
                        text: `PO ${rowData.po_number} has been removed.`,
                        timer: 1200,
                        showConfirmButton: false,
                    });

                    // refresh all buttons
                    refreshTableButtons(table);
                }
            });
            return;
        }

        // If currently 'Select'
        Swal.fire({
            title: "Are you sure?",
            text: `You are selecting PO: ${rowData.po_number}`,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Yes, select it!",
            cancelButtonText: "Cancel",
            reverseButtons: true,
        }).then((result) => {
            if (!result.isConfirmed) return;

            // Ambil semua rows yang memiliki group_id yang sama
            const sameGroupRows = table
                .rows()
                .data()
                .toArray()
                .filter((r) => r.group_id === rowData.group_id);

            // Build array of PO objects to add (maintain same structure the controller provides)
            const toAdd = sameGroupRows.map((r) => ({
                po_number: r.po_number,
                model: r.model,
                area: r.area,
                line: r.line,
                lot_size: r.lot_size,
                act_lot_size: r.act_lot_size,
                date: r.date,
                group_id: r.group_id,
            }));

            // Add to DOM (avoid duplicates)
            addPOsToDOM(toAdd);

            // Prepare pos array (current state) and update info fields
            const pos = getCurrentPOFromDOM();
            // update info fields first
            if (typeof updateInfoFields === "function") updateInfoFields(pos);

            // --- NEW: render material lines for selected PO(s) ---
            const poNumbers = pos.map((p) => p.po_number).filter(Boolean);
            if (poNumbers.length > 0) {
                // show temporary loading row
                $("#recordMaterials tbody").html(`
        <tr><td colspan="20" class="text-center text-muted">Loading material details...</td></tr>
    `);

                // preferensi memanggil fungsi dari global/window
                if (typeof window.renderSavedPOFromList === "function") {
                    try {
                        window.renderSavedPOFromList(poNumbers);
                    } catch (err) {
                        console.error(
                            "[Select] Error calling renderSavedPOFromList:",
                            err
                        );
                        $("#recordMaterials tbody").html(`
                <tr><td colspan="20" class="text-center text-danger">Failed to load material details</td></tr>
            `);
                    }
                } else if (typeof renderSavedPOFromList === "function") {
                    try {
                        renderSavedPOFromList(poNumbers);
                    } catch (err) {
                        console.error(
                            "[Select] Error calling renderSavedPOFromList:",
                            err
                        );
                        $("#recordMaterials tbody").html(`
                <tr><td colspan="20" class="text-center text-danger">Failed to load material details</td></tr>
            `);
                    }
                } else {
                    console.warn(
                        "[Select] renderSavedPOFromList not found (global)."
                    );
                }
            }
            // --- END NEW ---

            // Close modal
            $("#searchPO").modal("hide");

            // Update buttons for all rows with same group
            $("#searchRecords tbody tr").each(function () {
                const data = table.row(this).data();
                if (data && data.group_id === rowData.group_id) {
                    const button = $(this).find(".btn-custom");
                    button
                        .text("Cancel")
                        .removeClass("btn-primary")
                        .addClass("btn-danger");
                }
            });

            Swal.fire({
                icon: "success",
                title: "Group Selected!",
                text: `All POs in group ${rowData.group_id} have been added.`,
                timer: 1500,
                showConfirmButton: false,
            });
        });
    });
});
