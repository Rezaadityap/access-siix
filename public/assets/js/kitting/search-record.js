$(document).ready(function () {
    let today = new Date().toISOString().split("T")[0];
    $("#searchDate").val(today);

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
            const checker_name = $(this).data("checker_name") ?? "-";
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
                    checker_name,
                });
            }
        });
        return arr;
    }

    window.getCurrentPOFromDOM = getCurrentPOFromDOM;

    function addPOsToDOM(polist) {
        if (!Array.isArray(polist)) polist = [polist];
        const existing = getCurrentPOFromDOM();
        const existingPOs = existing.map((p) => p.po_number);
        polist.forEach((p, idx) => {
            if (!p || !p.po_number) return;
            if (
                existing.some(
                    (e) =>
                        e.po_number === p.po_number &&
                        String(e.group_id) === String(p.group_id)
                )
            )
                return;

            const checkerNameSafe = (p.checker_name ?? p.checker ?? "-")
                .toString()
                .replace(/"/g, "&quot;");

            let infoRow = "";
            if (existing.length === 0) {
                infoRow = `
                <div class="row mb-3" data-group_id="${
                    p.group_id ?? ""
                }" data-checker_name="${checkerNameSafe}" ${
                    p.area ? `data-area="${p.area}"` : ""
                } ${p.line ? `data-line="${p.line}"` : ""} ${
                    p.lot_size ? `data-lot_size="${p.lot_size}"` : ""
                } ${
                    p.act_lot_size
                        ? `data-act_lot_size="${p.act_lot_size}"`
                        : ""
                } ${p.date ? `data-date="${p.date}"` : ""}>
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">PO Number</label>
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${
                            p.po_number
                        }" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Model</label>
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${
                            p.model || ""
                        }" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Checker</label>
                        <input type="text" id="infoCheckerRow" class="form-control form-control-sm rounded-3 bg-light border-0" value="${
                            checkerNameSafe || "-"
                        }" readonly>
                    </div>
                </div>`;
            } else {
                infoRow = `
                <div class="row mb-3" data-group_id="${
                    p.group_id ?? ""
                }" data-checker_name="${checkerNameSafe}" ${
                    p.area ? `data-area="${p.area}"` : ""
                } ${p.line ? `data-line="${p.line}"` : ""} ${
                    p.lot_size ? `data-lot_size="${p.lot_size}"` : ""
                } ${
                    p.act_lot_size
                        ? `data-act_lot_size="${p.act_lot_size}"`
                        : ""
                } ${p.date ? `data-date="${p.date}"` : ""}>
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${
                            p.po_number
                        }" readonly>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${
                            p.model || ""
                        }" readonly>
                    </div>
                    <div class="col-md-4">
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${
                            checkerNameSafe || "-"
                        }" readonly>
                    </div>
                </div>`;
            }
            $("#infoContainer").append(infoRow);
            existing.push({
                po_number: p.po_number,
                model: p.model,
                area: p.area,
                line: p.line,
                lot_size: p.lot_size,
                act_lot_size: p.act_lot_size,
                date: p.date,
                group_id: p.group_id,
                checker_name: checkerNameSafe,
            });
        });
    }

    function removePOFromDOM(po_number, group_id) {
        if (!po_number) return;

        const targetPo = String(po_number);
        const targetG =
            group_id === undefined || group_id === null
                ? null
                : String(group_id);

        $("#infoContainer .row").each(function () {
            const po = $(this).find("input").eq(0).val() || "";
            const gidAttr = $(this).data("group_id");
            const gid =
                gidAttr === undefined || gidAttr === null
                    ? null
                    : String(gidAttr);

            if (po === targetPo) {
                if (targetG === null || gid === targetG) {
                    $(this).remove();
                }
            }
        });
    }

    function refreshTableButtons(table) {
        const existing = getCurrentPOFromDOM();
        $("#searchRecords tbody tr").each(function () {
            const rowData = table.row(this).data();
            if (!rowData || !rowData.po_number) return;

            const btn = $(this).find(".btn-custom");

            const existsSameGroup = existing.some((e) => {
                const eg =
                    e.group_id === undefined || e.group_id === null
                        ? ""
                        : String(e.group_id);
                const rg =
                    rowData.group_id === undefined || rowData.group_id === null
                        ? ""
                        : String(rowData.group_id);
                return (
                    String(e.po_number) === String(rowData.po_number) &&
                    eg === rg
                );
            });

            if (existsSameGroup) {
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

    table.on("draw", function () {
        refreshTableButtons(table);
    });

    // Click handler for Select / Cancel
    $("#searchRecords").on("click", ".btn-custom", function () {
        let rowData = table.row($(this).parents("tr")).data();
        const btn = $(this);

        if (!rowData || !rowData.po_number) return;

        if (btn.text().trim() === "Cancel") {
            Swal.fire({
                title: "Cancel this PO?",
                text: `PO ${rowData.po_number} will be removed from the selected group.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, cancel it!",
                cancelButtonText: "No",
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    removePOFromDOM(rowData.po_number, rowData.group_id);

                    const remaining = getCurrentPOFromDOM();
                    if (remaining.length > 0) {
                        if (typeof updateInfoFields === "function")
                            updateInfoFields(remaining);
                    } else {
                        $("#infoContainer").empty();
                        $(
                            "#infoPONumber, #infoProduction, #infoLine, #infoModel, #infoLotSize, #infoActual, #infoDate, #infoChecker"
                        ).val("");
                        $("#recordMaterials tbody").html(
                            `<tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>`
                        );

                        if (typeof loadHistoryData === "function") {
                            try {
                                loadHistoryData([]);
                            } catch (e) {
                                console.warn(
                                    "loadHistoryData([]) error after cancel:",
                                    e
                                );
                            }
                        }
                    }

                    $("#searchRecords tbody tr").each(function () {
                        const d = table.row(this).data();
                        if (!d) return;
                        const thisG =
                            d.group_id === undefined || d.group_id === null
                                ? null
                                : String(d.group_id);
                        const targetG =
                            rowData.group_id === undefined ||
                            rowData.group_id === null
                                ? null
                                : String(rowData.group_id);
                        if (
                            String(d.po_number) === String(rowData.po_number) &&
                            thisG === targetG
                        ) {
                            const b = $(this).find(".btn-custom");
                            b.text("Select")
                                .removeClass("btn-danger")
                                .addClass("btn-primary");
                        }
                    });

                    Swal.fire({
                        icon: "success",
                        title: "Cancelled!",
                        text: `PO ${
                            rowData.po_number
                        } has been removed from group ${
                            rowData.group_id || "-"
                        }.`,
                        timer: 1200,
                        showConfirmButton: false,
                    });

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

            const toAdd = sameGroupRows.map((r) => ({
                po_number: r.po_number,
                model: r.model,
                area: r.area,
                line: r.line,
                lot_size: r.lot_size,
                act_lot_size: r.act_lot_size,
                date: r.date,
                group_id: r.group_id,
                checker_name: r.checker_name ?? r.checker ?? "-",
            }));

            addPOsToDOM(toAdd);

            const pos = getCurrentPOFromDOM();
            // update info fields first
            if (typeof updateInfoFields === "function") updateInfoFields(pos);

            const poNumbers = pos.map((p) => ({
                po_number: p.po_number,
                group_id: p.group_id,
            }));
            if (poNumbers.length > 0) {
                $("#recordMaterials tbody").html(`
                    <tr><td colspan="20" class="text-center text-muted">Loading material details...</td></tr>
                `);

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

    // Save
    $("#nextProcess").on("click", function (e) {
        e.preventDefault();

        // ambil semua group_id dari infoContainer (unique)
        const groupIds = $("#infoContainer .row")
            .map(function () {
                const gid = $(this).data("group_id");
                return gid ? gid : null;
            })
            .get()
            .filter(Boolean)
            .filter((v, i, a) => a.indexOf(v) === i);

        // cek jika tidak ada PO yg dipilih
        if (!groupIds.length) {
            Swal.fire({
                icon: "info",
                title: "No data recorded",
                text: "Please select record first!",
                confirmButtonText: "OK",
            });
            return;
        }

        // konfirmasi simpan
        Swal.fire({
            title: "Are you sure you want to save?",
            text: "All records will be saved. Continue?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, continue",
            cancelButtonText: "Cancel",
            reverseButtons: true,
        }).then((result) => {
            if (!result.isConfirmed) return;

            // ambil CSRF
            const csrf =
                document.querySelector('meta[name="csrf-token"]')?.content ||
                "";

            // kirim ke server
            $.ajax({
                url: "/record_material/save-record",
                method: "POST",
                data: {
                    _token: csrf,
                    group_ids: groupIds,
                },
                success: function (res) {
                    if (res && res.status === "success") {
                        doLocalReset();
                        Swal.fire({
                            icon: "success",
                            title: "Data saved successfully",
                            text: res.message || "Group status updated",
                            confirmButtonText: "OK",
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Update failed",
                            text:
                                res?.message ||
                                "Failed to change group status on server.",
                            confirmButtonText: "OK",
                        });
                    }
                },
                error: function (xhr) {
                    console.error("mark-next error:", xhr);
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text:
                            xhr.responseJSON?.message ||
                            "An error occurred while contacting the server.",
                        confirmButtonText: "OK",
                    });
                },
            });
        });
    });

    function doLocalReset() {
        try {
            localStorage.removeItem("currentPO");
        } catch (err) {
            console.warn("Gagal remove currentPO:", err);
        }

        $("#infoContainer").empty();
        $(
            "#infoProduction, #infoLine, #infoChecker, #infoDate, #infoLotSize, #infoActual"
        ).val("");

        const tableBodies = [
            "#recordMaterials tbody",
            "#recordScanSMD tbody",
            "#recordScanWH tbody",
            "#recordScanSTO tbody",
            "#recordScanMAR tbody",
            "#recordScanMismatch tbody",
        ];
        tableBodies.forEach((sel) => {
            try {
                const $tb = $(sel);
                if ($tb.length) {
                    const colspan = sel.includes("recordMaterials") ? 20 : 5;
                    $tb.html(
                        `<tr><td colspan="${colspan}" class="text-center text-muted">No Data Found</td></tr>`
                    );
                }
            } catch (e) {
                /* ignore */
            }
        });

        if (typeof updateInfoFields === "function") {
            try {
                updateInfoFields([]);
            } catch (e) {
                console.warn("updateInfoFields([]) error:", e);
            }
        }

        if (typeof loadHistoryData === "function") {
            try {
                loadHistoryData([]);
            } catch (e) {
                console.warn("loadHistoryData([]) error:", e);
            }
        }

        if (window.MaterialCache && "loadedForPOs" in window.MaterialCache) {
            try {
                window.MaterialCache.loadedForPOs = null;
                window.MaterialCache.set = new Set();
            } catch (e) {
                /* ignore */
            }
        }

        try {
            if (typeof refreshTableButtons === "function") {
                refreshTableButtons(window.table || table);
            } else {
                // fallback: reload DataTable untuk forcing redraw
                if (typeof table !== "undefined" && table.ajax) {
                    table.ajax.reload(null, false);
                }
            }
        } catch (err) {
            console.warn("refreshTableButtons error:", err);
        }
    }
    function buildLineOptions($select, selected) {
        if (!$select || !$select.length) return;
        $select.empty();

        for (let i = 1; i <= 19; i++) {
            const num = i.toString().padStart(2, "0");
            const val = `SMT-${num}`;

            const opt = $("<option>").val(val).text(val);

            if (selected && String(selected).toUpperCase() === val)
                opt.prop("selected", true);

            $select.append(opt);
        }
    }
    $(function () {
        buildLineOptions($("#edit_line"));

        $(document).on("click", "#btnEditInfo", function (e) {
            e.preventDefault();

            const $rows = $("#infoContainer .row");

            if (!$rows.length) {
                Swal.fire({
                    icon: "info",
                    title: "No data recorded",
                    text: "Please select record first!",
                    confirmButtonText: "OK",
                });
                return;
            }

            const $first = $rows.first();
            const groupId = $first.data("group_id") ?? "";
            const currentLine =
                $first.data("line") ?? $("#infoLine").val() ?? "";
            const currentLot =
                $first.data("lot_size") ?? $("#infoLotSize").val() ?? "";

            $("#edit_group_id").val(groupId);
            buildLineOptions($("#edit_line"), currentLine);
            $("#edit_lot_size").val(currentLot);

            const modalEl = document.getElementById("editInformation");
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });

        $(document).on("submit", "#formEditInfo", function (e) {
            e.preventDefault();

            const groupId = $("#edit_group_id").val();
            const line = $("#edit_line").val();
            const lotSize = $("#edit_lot_size").val();

            if (!groupId) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Group ID not found.",
                });
                return;
            }

            Swal.fire({
                title: "Save changes?",
                text: `Line → ${line}\nLot Size → ${lotSize}`,
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Save",
                cancelButtonText: "Cancel",
                reverseButtons: true,
            }).then(function (ans) {
                if (!ans.isConfirmed) return;

                const csrf = $('meta[name="csrf-token"]').attr("content") || "";

                $.ajax({
                    url: "/record_material/update-info",
                    method: "POST",
                    data: {
                        _token: csrf,
                        group_id: groupId,
                        line: line,
                        lot_size: lotSize,
                    },
                    success: function (res) {
                        if (res && res.status === "success") {
                            // tutup modal
                            const modalEl =
                                document.getElementById("editInformation");
                            bootstrap.Modal.getInstance(modalEl)?.hide();

                            $("#infoContainer .row").each(function () {
                                const $r = $(this);
                                if (
                                    String($r.data("group_id")) ===
                                    String(groupId)
                                ) {
                                    $r.attr("data-line", line);
                                    $r.data("line", line);
                                    $r.attr("data-lot_size", lotSize);
                                    $r.data("lot_size", lotSize);
                                }
                            });

                            $("#infoLine").val(line);
                            $("#infoLotSize").val(lotSize);

                            if (typeof updateInfoFields === "function") {
                                try {
                                    updateInfoFields(getCurrentPOFromDOM());
                                } catch (e) {
                                    /* ignore */
                                }
                            }

                            try {
                                if (
                                    $.fn.DataTable &&
                                    $.fn.DataTable.isDataTable("#searchRecords")
                                ) {
                                    const dt = $("#searchRecords").DataTable();
                                    const infoActualVal =
                                        $("#infoActual").val();
                                    const rowsApi = dt.rows(function (
                                        idx,
                                        data,
                                        node
                                    ) {
                                        return (
                                            data &&
                                            String(data.group_id) ===
                                                String(groupId)
                                        );
                                    });

                                    rowsApi.every(function () {
                                        const d = this.data();
                                        d.line = line;
                                        d.lot_size = lotSize;
                                        d.act_lot_size =
                                            d.act_lot_size ||
                                            infoActualVal ||
                                            d.act_lot_size;
                                        this.data(d);
                                    });

                                    dt.draw(false);
                                }
                            } catch (e) {
                                console.warn(
                                    "sync searchRecords after edit error:",
                                    e
                                );
                            }

                            Swal.fire({
                                icon: "success",
                                title: "Updated",
                                text: res.message || "Info updated.",
                            });
                        } else {
                            Swal.fire({
                                icon: "error",
                                title: "Failed",
                                text: res?.message || "Failed to save.",
                            });
                        }
                    },
                    error: function (xhr) {
                        console.error("Update info error:", xhr);
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: xhr.responseJSON?.message || "Server error",
                        });
                    },
                });
            });
        });
    });
    $(document).on("click", "#deletePO", function (e) {
        e.preventDefault();

        // Ambil semua group_id unik dari DOM
        const groupIds = $("#infoContainer .row")
            .map(function () {
                const gid = $(this).data("group_id");
                return gid ? gid : null;
            })
            .get()
            .filter(Boolean)
            .filter((v, i, a) => a.indexOf(v) === i);

        if (!groupIds.length) {
            Swal.fire({
                icon: "info",
                title: "No data recorded",
                text: "Please select record first!",
                confirmButtonText: "OK",
            });
            return;
        }

        // Tampilkan konfirmasi delete
        Swal.fire({
            title: "Delete Record?",
            text: "All PO(s) and related data in this group will be permanently deleted.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "Cancel",
            reverseButtons: true,
        }).then((result) => {
            if (!result.isConfirmed) return;

            // CSRF
            const csrf = $('meta[name="csrf-token"]').attr("content") || "";

            // Kirim AJAX delete
            $.ajax({
                url: "/record_material/delete-po",
                method: "POST",
                data: {
                    _token: csrf,
                    group_ids: groupIds,
                },
                success: function (res) {
                    if (res && res.status === "success") {
                        // Reset UI
                        if (typeof doLocalReset === "function") {
                            doLocalReset();
                        }

                        try {
                            if (
                                $.fn.DataTable &&
                                $.fn.DataTable.isDataTable("#searchRecords")
                            ) {
                                const dt = $("#searchRecords").DataTable();
                                const idsSet = new Set(
                                    groupIds.map((g) => String(g))
                                );
                                dt.rows(function (idx, data, node) {
                                    return (
                                        data &&
                                        idsSet.has(String(data.group_id))
                                    );
                                }).remove();
                                dt.draw(false);
                            } else if (window.table && window.table.ajax) {
                                try {
                                    window.table.ajax.reload(null, false);
                                } catch (e) {}
                            }
                        } catch (e) {
                            console.warn(
                                "sync searchRecords after delete error:",
                                e
                            );
                        }

                        Swal.fire({
                            icon: "success",
                            title: "Deleted!",
                            text:
                                res.message || "Records successfully deleted.",
                            confirmButtonText: "OK",
                        });
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Delete Failed",
                            text: res?.message || "Unable to delete data.",
                            confirmButtonText: "OK",
                        });
                    }
                },
                error: function (xhr) {
                    Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text:
                            xhr.responseJSON?.message ||
                            "Server error occurred.",
                    });
                },
            });
        });
    });
});
