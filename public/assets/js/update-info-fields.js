window.updateInfoFields = function (data) {
    if (!data || (Array.isArray(data) && data.length === 0)) return;

    const newPOs = Array.isArray(data) ? data : [data];

    let saved = localStorage.getItem("currentPO");
    let existingPOs = saved ? JSON.parse(saved) : [];

    if (!Array.isArray(existingPOs)) existingPOs = [existingPOs];

    newPOs.forEach((po) => {
        if (!existingPOs.some((e) => e.po_number === po.po_number)) {
            existingPOs.push(po);
        }
    });

    localStorage.setItem("currentPO", JSON.stringify(existingPOs));

    $("#infoContainer").empty();

    existingPOs.forEach((po, index) => {
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
                </div>`;
        } else {
            infoRow = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${po.po_number}" readonly>
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control form-control-sm rounded-3 bg-light border-0" value="${po.model}" readonly>
                    </div>
                </div>`;
        }
        $("#infoContainer").append(infoRow);
    });

    const base = existingPOs.find((po) => po.infoActual) || existingPOs[0];
    console.log(
        "[RESTORE] Menggunakan PO:",
        base?.po_number,
        "dengan infoActual:",
        base?.infoActual
    );

    $("#infoProduction").val(base.area || "-");
    $("#infoLine").val(base.line || "-");
    $("#infoLotSize").val(base.lot_size || "-");
    document.getElementById("infoActual").value =
        base?.infoActual || base?.act_lot_size || "0";
    $("#infoDate").val(base.date || "-");

    // Pastikan fungsi ini juga global kalau dipakai di file lain
    if (typeof renderSavedPO === "function") renderSavedPO();

    if (
        existingPOs &&
        existingPOs.length > 0 &&
        typeof loadHistoryData === "function"
    ) {
        const poNumbers = existingPOs.map((po) => po.po_number);
        loadHistoryData(poNumbers);
    }
};
