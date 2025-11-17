// -------------------------
// updateInfoFields (global)
// -------------------------
window.updateInfoFields = function (data) {
    if (!data || (Array.isArray(data) && data.length === 0)) return;

    const newPOs = Array.isArray(data) ? data : [data];

    function getUserNameById(id) {
        if (!id) return "-";
        if (!window.users || !Array.isArray(window.users)) return "-";
        const u = window.users.find((x) => String(x.id) === String(id));
        return u ? u.name : "-";
    }

    let existingPOs = [];

    $("#infoContainer .row").each(function () {
        const po_number = $(this).find("input").eq(0).val();
        const model = $(this).find("input").eq(1).val();
        const area = $(this).data("area");
        const line = $(this).data("line");
        const lot_size = $(this).data("lot_size");
        const act_lot_size = $(this).data("act_lot_size");
        const date = $(this).data("date");
        const checker_name =
            $(this).data("checker_name") ??
            getUserNameById($(this).data("user_id"));

        if (po_number) {
            existingPOs.push({
                po_number,
                model: model || "-",
                area: area || "-",
                line: line || "-",
                lot_size: lot_size ?? "-",
                act_lot_size: act_lot_size ?? "0",
                date: date || "-",
                checker_name: checker_name || "-",
                group_id: $(this).data("group_id") ?? null,
            });
        }
    });

    newPOs.forEach((po) => {
        if (!po || !po.po_number) return;

        if (
            !existingPOs.some(
                (e) =>
                    e.po_number === po.po_number &&
                    String(e.group_id) === String(po.group_id)
            )
        ) {
            const checkerId = po.checker_id ?? po.user_id ?? po.checker ?? null;
            const checkerNameFromPayload = po.checker_name ?? null;
            const resolvedCheckerName =
                checkerNameFromPayload ??
                (checkerId ? getUserNameById(checkerId) : "-");

            existingPOs.push({
                po_number: po.po_number ?? "-",
                model: po.model ?? "-",
                area: po.area ?? "-",
                line: po.line ?? "-",
                lot_size: po.lot_size ?? "-",
                act_lot_size: po.act_lot_size ?? "0",
                date: po.date ?? "-",
                checker_name: resolvedCheckerName,
                group_id: po.group_id ?? null,
            });
        }
    });

    $("#infoContainer").empty();

    existingPOs.forEach((po, index) => {
        const safeCheckerName = (po.checker_name ?? "-")
            .toString()
            .replace(/"/g, "&quot;");
        let infoRow = "";
        if (index === 0) {
            infoRow = `
  <div class="row mb-3" data-group_id="${
      po.group_id ?? ""
  }" data-checker_name="${safeCheckerName}" data-area="${po.area}" data-line="${
                po.line
            }" data-lot_size="${po.lot_size}" data-act_lot_size="${
                po.act_lot_size
            }" data-date="${po.date}">
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
  <div class="row mb-3" data-group_id="${
      po.group_id ?? ""
  }" data-checker_name="${safeCheckerName}" data-area="${po.area}" data-line="${
                po.line
            }" data-lot_size="${po.lot_size}" data-act_lot_size="${
                po.act_lot_size
            }" data-date="${po.date}">
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

    let base = null;
    if (newPOs && newPOs.length) {
        const firstNew = newPOs[0];
        base =
            existingPOs.find((e) => e.po_number === firstNew.po_number) ||
            firstNew;
    }
    base = base || existingPOs[0];

    console.log("[updateInfoFields] Menampilkan PO:", base?.po_number);

    $("#infoProduction").val(base.area || "-");
    $("#infoLine").val(base.line || "-");
    $("#infoLotSize").val(base.lot_size || "-");
    $("#infoActual").val(base.act_lot_size || "0");
    $("#infoDate").val(base.date || "-");
    $("#infoChecker").val(base.checker_name || "-");

    // render saved PO details
    const poObjs = existingPOs
        .map((po) => ({
            po_number: po.po_number,
            group_id: po.group_id ?? null,
        }))
        .filter((p) => p.po_number);

    if (poObjs.length) {
        $("#recordMaterials tbody").html(`
        <tr><td colspan="20" class="text-center text-muted">Loading material details...</td></tr>
    `);

        if (typeof window.renderSavedPOFromList === "function") {
            try {
                window.renderSavedPOFromList(poObjs);
            } catch (err) {
                console.error(
                    "[updateInfoFields] Error calling renderSavedPOFromList:",
                    err
                );
                $("#recordMaterials tbody").html(`
                <tr><td colspan="20" class="text-center text-danger">Failed to load material details</td></tr>
            `);
            }
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
