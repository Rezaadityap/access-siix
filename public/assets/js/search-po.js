$(document).ready(function () {
    let today = new Date().toISOString().split("T")[0];
    $("#searchDate").val(today);

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
        let saved;
        try {
            saved = JSON.parse(localStorage.getItem("currentPO") || "[]");
        } catch (e) {
            console.warn("[renderSavedPO] JSON parse error:", e);
            saved = [];
        }

        const savedPOs = Array.isArray(saved) ? saved : [saved];
        console.log("[renderSavedPO] PO list:", savedPOs);

        $("#searchRecords tbody tr").each(function () {
            const rowData = table.row(this).data();
            if (!rowData || !rowData.po_number) return;

            const poExist = savedPOs.some(
                (po) => po && po.po_number === rowData.po_number
            );
            const btn = $(this).find(".btn-custom");

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
    });

    $("#searchRecords").on("click", ".btn-custom", function () {
        let rowData = table.row($(this).parents("tr")).data();
        const btn = $(this);

        let saved = localStorage.getItem("currentPO");
        let pos = saved ? JSON.parse(saved) : [];
        if (!Array.isArray(pos)) pos = [pos];

        // Cancel
        if (btn.text() === "Cancel") {
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
                    pos = pos.filter((p) => p.po_number !== rowData.po_number);
                    if (pos.length > 0) {
                        localStorage.setItem("currentPO", JSON.stringify(pos));
                    } else {
                        localStorage.removeItem("currentPO");
                    }

                    if (pos.length > 0) {
                        updateInfoFields(pos);
                    } else {
                        $("#infoContainer").empty();
                        $(
                            "#infoPONumber, #infoProduction, #infoLine, #infoModel, #infoLotSize, #infoActual, #infoDate"
                        ).val("");
                        $("#recordMaterials tbody").html(
                            `<tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>`
                        );
                    }

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
                }
            });
            return;
        }

        // Select
        Swal.fire({
            title: "Are you sure?",
            text: `You are selecting PO: ${rowData.po_number}`,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Yes, select it!",
            cancelButtonText: "Cancel",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                // Ambil semua data di tabel yang punya group_id sama
                const sameGroupRows = table
                    .rows()
                    .data()
                    .toArray()
                    .filter((r) => r.group_id === rowData.group_id);

                // Ambil dari localStorage
                let saved = localStorage.getItem("currentPO");
                let pos = saved ? JSON.parse(saved) : [];
                if (!Array.isArray(pos)) pos = [pos];

                // Tambahkan semua PO dalam group ini (hindari duplikat)
                sameGroupRows.forEach((r) => {
                    if (!pos.some((p) => p.po_number === r.po_number)) {
                        pos.push(r);
                    }
                });

                // Simpan kembali ke localStorage
                localStorage.setItem("currentPO", JSON.stringify(pos));

                // Update tampilan info dan tabel
                updateInfoFields(pos);
                $("#searchPO").modal("hide");

                // Update tombol jadi Cancel untuk semua PO dalam group ini
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
            }
        });
    });
});
