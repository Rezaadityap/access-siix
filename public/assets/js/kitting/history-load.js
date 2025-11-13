/**
 * loadHistoryData
 * - poNumbers: array of PO numbers (or array of objects with po_number) or single string/obj
 * - returns a Promise that resolves when table rendering is done
 */
function loadHistoryData(poNumbers) {
    // normalize to array of strings
    if (!poNumbers) {
        // try read from DOM as fallback
        poNumbers = getCurrentPOFromDOM().map((p) => p.po_number);
    } else if (!Array.isArray(poNumbers)) {
        // if passed array of objects {po_number} or string
        if (typeof poNumbers === "string") poNumbers = [poNumbers];
        else if (poNumbers && poNumbers.po_number)
            poNumbers = [poNumbers.po_number];
        else poNumbers = Array.from(poNumbers); // best effort
    }

    // flatten possible array of objects
    poNumbers = poNumbers
        .map((p) => (typeof p === "string" ? p : p?.po_number))
        .filter(Boolean);

    // convenience: if nothing, clear history tables and resolve
    if (!poNumbers.length) {
        [
            "#recordScanSMD",
            "#recordScanWH",
            "#recordScanSTO",
            "#recordScanMAR",
            "#recordScanMismatch",
        ].forEach((tid) => showNoData(tid));
        return Promise.resolve();
    }

    // show loading for each table
    [
        "#recordScanSMD",
        "#recordScanWH",
        "#recordScanSTO",
        "#recordScanMAR",
        "#recordScanMismatch",
    ].forEach((tid) => showLoading(tid));

    const url = `/record-material/history?po=${encodeURIComponent(
        poNumbers.join(",")
    )}`;

    return fetch(url, { credentials: "same-origin" })
        .then((res) => {
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then(async (data) => {
            if (data.status === "success") {
                // optionally ensure material cache is up-to-date for displayed POs
                try {
                    const csrf =
                        document.querySelector('meta[name="csrf-token"]')
                            ?.content || "{{ csrf_token() }}";
                    await preloadMaterialsForPOs(
                        poNumbers.map((n) => ({ po_number: n })),
                        csrf
                    );
                } catch (e) {
                    // ignore preload errors, it's non-fatal for history render
                }

                fillTable("#recordScanSMD", data.smd);
                fillTable("#recordScanWH", data.wh);
                fillTable("#recordScanSTO", data.sto);
                fillTable("#recordScanMAR", data.mar);
                fillTable("#recordScanMismatch", data.mm);

                return data;
            } else {
                const err = data.message || "No history data";
                [
                    "#recordScanSMD",
                    "#recordScanWH",
                    "#recordScanSTO",
                    "#recordScanMAR",
                    "#recordScanMismatch",
                ].forEach((tid) => showError(tid, err));
                // still resolve so callers don't hang
                return Promise.resolve(data);
            }
        })
        .catch((err) => {
            console.error("Error loading history:", err);
            [
                "#recordScanSMD",
                "#recordScanWH",
                "#recordScanSTO",
                "#recordScanMAR",
                "#recordScanMismatch",
            ].forEach((tid) =>
                showError(tid, err.message || "Failed to load history")
            );
            return Promise.reject(err);
        });
}

/**
 * fillTable
 * - tableId: selector to table (e.g. "#recordScanSMD")
 * - rows: array of objects: { scan_code, material, qty, batch_description } (can be empty)
 */
function fillTable(tableId, rows) {
    const tbody = document.querySelector(`${tableId} tbody`);
    if (!tbody) {
        console.warn("fillTable: table body not found for", tableId);
        return;
    }
    tbody.innerHTML = "";

    if (!rows || rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No data found</td></tr>`;
        return;
    }

    const frag = document.createDocumentFragment();
    rows.forEach((row, index) => {
        const tr = document.createElement("tr");
        tr.innerHTML = `
            <td>${index + 1}</td>
            <td>${escapeHtml(row.scan_code ?? "-")}</td>
            <td>${escapeHtml(row.material ?? "-")}</td>
            <td>${Number(row.qty || 0)}</td>
            <td>${escapeHtml(row.batch_description ?? "-")}</td>
        `;
        frag.appendChild(tr);
    });
    tbody.appendChild(frag);
}

/* small helpers for UX */
function showLoading(tableId) {
    const tbody = document.querySelector(`${tableId} tbody`);
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>`;
}
function showError(tableId, msg) {
    const tbody = document.querySelector(`${tableId} tbody`);
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">${escapeHtml(
        String(msg)
    )}</td></tr>`;
}
function showNoData(tableId) {
    const tbody = document.querySelector(`${tableId} tbody`);
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No data found</td></tr>`;
}

/* tiny HTML escape to avoid accidental injection when using innerHTML */
function escapeHtml(str) {
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}
