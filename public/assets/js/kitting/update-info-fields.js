// -------------------------
// updateInfoFields (global)
// -------------------------
window.updateInfoFields = function (data) {
    if (!data || (Array.isArray(data) && data.length === 0)) return;

    const newPOs = Array.isArray(data) ? data : [data];

    // Ambil PO yang sudah tampil di infoContainer sebelumnya
    // supaya ketika ada import baru, data lama tetap ditampilkan
    let existingPOs = [];

    $("#infoContainer .row").each(function () {
        const po_number = $(this).find("input").eq(0).val();
        const model = $(this).find("input").eq(1).val();
        // also try read data-* attributes in case you store more metadata there
        const area = $(this).data("area");
        const line = $(this).data("line");
        const lot_size = $(this).data("lot_size");
        const act_lot_size = $(this).data("act_lot_size");
        const date = $(this).data("date");
        if (po_number) {
            existingPOs.push({
                po_number,
                model: model || "-",
                area: area || "-",
                line: line || "-",
                lot_size: lot_size ?? "-",
                act_lot_size: act_lot_size ?? "0",
                date: date || "-",
            });
        }
    });

    // Tambahkan PO baru (hindari duplikasi)
    newPOs.forEach((po) => {
        if (!po || !po.po_number) return;
        if (!existingPOs.some((e) => e.po_number === po.po_number)) {
            existingPOs.push({
                po_number: po.po_number ?? "-",
                model: po.model ?? "-",
                area: po.area ?? "-",
                line: po.line ?? "-",
                lot_size: po.lot_size ?? "-",
                act_lot_size: po.act_lot_size ?? "0",
                date: po.date ?? "-",
            });
        }
    });

    // Render ulang semua PO ke infoContainer
    $("#infoContainer").empty();

    existingPOs.forEach((po, index) => {
        let infoRow = "";
        if (index === 0) {
            infoRow = `
                <div class="row mb-3" data-area="${po.area}" data-line="${po.line}" data-lot_size="${po.lot_size}" data-act_lot_size="${po.act_lot_size}" data-date="${po.date}">
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">PO Number</label>
                        <input type="text"
                            class="form-control form-control-sm rounded-3 bg-light border-0"
                            value="${po.po_number}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small text-muted mb-1">Model</label>
                        <input type="text"
                            class="form-control form-control-sm rounded-3 bg-light border-0"
                            value="${po.model}" readonly>
                    </div>
                </div>`;
        } else {
            infoRow = `
                <div class="row mb-3" data-area="${po.area}" data-line="${po.line}" data-lot_size="${po.lot_size}" data-act_lot_size="${po.act_lot_size}" data-date="${po.date}">
                    <div class="col-md-6">
                        <input type="text"
                            class="form-control form-control-sm rounded-3 bg-light border-0"
                            value="${po.po_number}" readonly>
                    </div>
                    <div class="col-md-6">
                        <input type="text"
                            class="form-control form-control-sm rounded-3 bg-light border-0"
                            value="${po.model}" readonly>
                    </div>
                </div>`;
        }
        $("#infoContainer").append(infoRow);
    });

    const base = newPOs[0] || existingPOs[0];

    console.log("[updateInfoFields] Menampilkan PO:", base?.po_number);

    $("#infoProduction").val(base.area || "-");
    $("#infoLine").val(base.line || "-");
    $("#infoLotSize").val(base.lot_size || "-");
    $("#infoActual").val(base.act_lot_size || "0");
    $("#infoDate").val(base.date || "-");

    // --- NEW: render saved PO details (material lines) using the PO numbers shown ---
    const poNumbers = existingPOs.map((po) => po.po_number).filter(Boolean);

    if (poNumbers.length) {
        $("#recordMaterials tbody").html(`
            <tr><td colspan="20" class="text-center text-muted">Loading material details...</td></tr>
        `);

        if (typeof window.renderSavedPOFromList === "function") {
            try {
                window.renderSavedPOFromList(poNumbers);
            } catch (err) {
                console.error(
                    "[updateInfoFields] Error calling renderSavedPOFromList:",
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
                    "[updateInfoFields] Error calling renderSavedPOFromList:",
                    err
                );
                $("#recordMaterials tbody").html(`
                    <tr><td colspan="20" class="text-center text-danger">Failed to load material details</td></tr>
                `);
            }
        } else {
            console.warn(
                "[updateInfoFields] renderSavedPOFromList not found (global)."
            );
            // fallback: show hint to user (optional)
            $("#recordMaterials tbody").html(`
                <tr><td colspan="20" class="text-center text-muted">No material renderer found</td></tr>
            `);
        }
    } else {
        $("#recordMaterials tbody").html(`
            <tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>
        `);
    }

    // Jika ada fungsi loadHistoryData, panggil dengan semua PO yang tampil (tetap panggil)
    if (existingPOs.length > 0 && typeof loadHistoryData === "function") {
        const poNums = existingPOs.map((po) => po.po_number);
        loadHistoryData(poNums);
    }
};
// End updateInfoFields
