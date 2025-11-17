function loadHistoryData(poNumbers) {
    if (!poNumbers) {
        poNumbers = getCurrentPOFromDOM().map((p) => ({
            po_number: p.po_number,
            group_id: p.group_id ?? null,
        }));
    } else if (!Array.isArray(poNumbers)) {
        if (typeof poNumbers === "string") {
            const dom = getCurrentPOFromDOM().find(
                (d) => d.po_number === poNumbers
            );
            poNumbers = [
                {
                    po_number: poNumbers,
                    group_id: dom ? dom.group_id : null,
                },
            ];
        } else if (poNumbers.po_number) {
            // object passed
            poNumbers = [
                {
                    po_number: poNumbers.po_number,
                    group_id: poNumbers.group_id ?? null,
                },
            ];
        }
    } else {
        // array: normalize each item
        poNumbers = poNumbers.map((p) => {
            if (typeof p === "string") {
                const dom = getCurrentPOFromDOM().find(
                    (d) => d.po_number === p
                );
                return {
                    po_number: p,
                    group_id: dom ? dom.group_id : null,
                };
            }
            return {
                po_number: p.po_number,
                group_id: p.group_id ?? null,
            };
        });
    }

    // remove invalid
    poNumbers = poNumbers.filter((p) => p.po_number);

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

    // show loading
    [
        "#recordScanSMD",
        "#recordScanWH",
        "#recordScanSTO",
        "#recordScanMAR",
        "#recordScanMismatch",
    ].forEach((tid) => showLoading(tid));

    const poList = poNumbers.map((p) => p.po_number).join(",");
    const groupList = poNumbers.map((p) => p.group_id ?? "").join(",");

    const url = `/record-material/history?po=${encodeURIComponent(
        poList
    )}&group_id=${encodeURIComponent(groupList)}`;

    return fetch(url, { credentials: "same-origin" })
        .then((res) =>
            res.ok ? res.json() : Promise.reject(`HTTP ${res.status}`)
        )
        .then(async (data) => {
            if (data.status === "success") {
                // preload
                try {
                    const csrf = document.querySelector(
                        'meta[name="csrf-token"]'
                    )?.content;
                    await preloadMaterialsForPOs(poNumbers, csrf);
                } catch (_) {}

                fillTable("#recordScanSMD", data.smd);
                fillTable("#recordScanWH", data.wh);
                fillTable("#recordScanSTO", data.sto);
                fillTable("#recordScanMAR", data.mar);
                fillTable("#recordScanMismatch", data.mm);

                return data;
            }

            [
                "#recordScanSMD",
                "#recordScanWH",
                "#recordScanSTO",
                "#recordScanMAR",
                "#recordScanMismatch",
            ].forEach((tid) =>
                showError(tid, data.message || "No history data")
            );

            return data;
        })
        .catch((err) => {
            console.error("Error loading history:", err);
            [
                "#recordScanSMD",
                "#recordScanWH",
                "#recordScanSTO",
                "#recordScanMAR",
                "#recordScanMismatch",
            ].forEach((tid) => showError(tid, "Failed to load history"));
            return Promise.reject(err);
        });
}

function escapeHtml(str) {
    return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#39;");
}
window.escapeHtml = escapeHtml;

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
window.fillTable = fillTable;
function showLoading(tableId) {
    const tbody = document.querySelector(`${tableId} tbody`);
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">Loading...</td></tr>`;
}
window.showLoading = showLoading;

function showError(tableId, msg) {
    const tbody = document.querySelector(`${tableId} tbody`);
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger">${String(
        msg
    )}</td></tr>`;
}
window.showError = showError;

function showNoData(tableId) {
    const tbody = document.querySelector(`${tableId} tbody`);
    if (!tbody) return;
    tbody.innerHTML = `<tr><td colspan="5" class="text-center text-muted">No data found</td></tr>`;
}
window.showNoData = showNoData;
