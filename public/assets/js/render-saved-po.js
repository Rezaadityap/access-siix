function renderSavedPO() {
    console.log("%c[renderSavedPO] → Dipanggil", "color:cyan;");

    const saved = localStorage.getItem("currentPO");
    if (!saved) {
        console.log("[renderSavedPO] Tidak ada data di localStorage");
        $("#recordMaterials tbody").html(`
            <tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>
        `);
        return;
    }

    const savedPOs = JSON.parse(saved);
    const poList = Array.isArray(savedPOs) ? savedPOs : [savedPOs];
    console.log("[renderSavedPO] PO list:", poList);

    let allLines = [];

    const fetchPromises = poList.map((po) => {
        const po_number = po.po_number;
        return $.ajax({
            url: `/record_material/by-po/${po_number}`,
            method: "GET",
            success: function (res) {
                if (
                    res.status === "success" &&
                    res.data &&
                    res.data.length > 0
                ) {
                    console.log(
                        `[renderSavedPO] Data diterima untuk PO ${po_number}:`,
                        res.data
                    );
                    allLines.push(...res.data);
                } else {
                    console.warn(
                        `[renderSavedPO] Tidak ada data untuk PO ${po_number}`
                    );
                }
            },
            error: function (xhr) {
                console.error(
                    `[AJAX Error] Gagal ambil data untuk PO ${po_number}`,
                    xhr
                );
            },
        });
    });

    Promise.all(fetchPromises).then(() => {
        if (allLines.length === 0) {
            console.warn("[renderSavedPO] Tidak ada data valid dari semua PO");
            $("#recordMaterials tbody").html(`
                <tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>
            `);
            return;
        }

        const grouped = {};
        allLines.forEach((item) => {
            const key = item.material;
            const totalQty = parseFloat(item.total_qty) || 0;
            const receiveQty = parseFloat(item.receive_qty) || 0;
            const smdQty = parseFloat(item.smd_qty) || 0;
            const stoQty = parseFloat(item.sto_qty) || 0;
            const marQty = parseFloat(item.mar_qty) || 0;
            const mmQty = parseFloat(item.mm_qty || 0);

            if (!grouped[key]) {
                grouped[key] = {
                    ...item,
                    rec_qty: totalQty,
                    receive_qty: receiveQty,
                    smd_qty: smdQty,
                    sto_qty: stoQty,
                    mar_qty: marQty,
                    mm_qty: mmQty,
                };
            } else {
                grouped[key].rec_qty += totalQty;
                grouped[key].receive_qty += receiveQty;
                grouped[key].smd_qty += smdQty;
                grouped[key].sto_qty += stoQty;
                grouped[key].mar_qty += marQty;
                grouped[key].mm_qty += mmQty;
            }
        });

        let lotSize = document.getElementById("infoLotSize")?.value || 0;
        let rows = "";
        Object.values(grouped).forEach((item, i) => {
            const material = item.material;

            // Ambil jumlah scan dari API
            const smdCount = item.smd_scans ?? 0;
            const whCount = item.wh_scans ?? 0;
            const stoCount = item.sto_scans ?? 0;

            const totalScan = smdCount + whCount + stoCount;

            const accumulateScan =
                (item.smd_qty ?? 0) +
                (item.receive_qty ?? 0) +
                (item.sto_qty ?? 0);

            const status =
                (item.rec_qty - item.smd_qty - item.receive_qty) * -1 +
                    item.sto_qty <
                    0 || item.rec_qty === 0
                    ? '<span class="text-danger fw-bold">Shortage</span>'
                    : '<span class="text-success fw-bold">PASS</span>';

            rows += `
        <tr>
            <td>${i + 1}</td>
            <td>${item.material}</td>
            <td>${item.material_desc || item.part_description}</td>
            <td>${item.rec_qty || 0}</td>
            <td>${item.satuan}</td>
            <td>${status}</td>
            <td>${item.smd_qty}</td>
            <td>${(item.rec_qty - item.smd_qty) * -1}</td>
            <td>${item.receive_qty}</td>
            <td>${(item.rec_qty - item.smd_qty - item.receive_qty) * -1}</td>
            <td>${item.sto_qty}</td>
            <td>${
                (item.rec_qty - item.smd_qty - item.receive_qty) * -1 +
                item.sto_qty
            }</td>
            <td>${item.smd_qty + item.receive_qty + item.sto_qty}</td>
            <td>${item.mar_qty}</td>
            <td>${(item.rec_qty / parseFloat(lotSize || 0)) * item.cavity}</td>
            <td>${totalScan}</td>
            <td>${accumulateScan - item.rec_qty - item.mar_qty - totalScan}</td>
            <td>${item.unit_price * item.rec_qty || 0}</td>
            <td>${item.mm_qty || 0}</td>
        </tr>`;
        });

        $("#recordMaterials tbody").html(rows);
        if (window.__reapplySort) window.__reapplySort();
        console.log("[renderSavedPO] Table updated successfully.");
    });
}
(function () {
    const table = document.getElementById("recordMaterials");
    if (!table) return;

    function getText(tr, idx) {
        return tr.children[idx]?.textContent.trim() ?? "";
    }
    function toNum(v) {
        const n = parseFloat(v.replace(/,/g, ""));
        return Number.isFinite(n) ? n : NaN;
    }
    function sortBy(col, type, dir) {
        const tbody = table.tBodies[0];
        if (!tbody) return;
        const rows = Array.from(tbody.rows);

        rows.sort((r1, r2) => {
            const a = getText(r1, col);
            const b = getText(r2, col);

            if (type === "number") {
                const na = toNum(a),
                    nb = toNum(b);
                if (isNaN(na) && isNaN(nb)) return 0;
                if (isNaN(na)) return 1;
                if (isNaN(nb)) return -1;
                return dir === "asc" ? na - nb : nb - na;
            } else if (type === "status") {
                const order = { Shortage: 1, PASS: 2 };
                const va = order[a.toUpperCase()] || 99;
                const vb = order[b.toUpperCase()] || 99;
                return dir === "asc" ? va - vb : vb - va;
            } else {
                const aa = a.toLowerCase();
                const bb = b.toLowerCase();
                return dir === "asc"
                    ? aa.localeCompare(bb, "en", { numeric: true })
                    : bb.localeCompare(aa, "en", { numeric: true });
            }
        });

        rows.forEach((r) => tbody.appendChild(r));
        reindexTable();
        table.dataset.sortCol = String(col);
        table.dataset.sortDir = dir;
    }

    function reindexTable() {
        const tbody = table.tBodies[0];
        if (!tbody) return;
        Array.from(tbody.rows).forEach((tr, idx) => {
            const firstCell = tr.querySelector("td");
            if (firstCell) firstCell.textContent = idx + 1;
        });
    }

    // pasang listener ke th yang sortable
    table.querySelectorAll("thead th").forEach((th, i) => {
        if (!th.classList.contains("sortable")) return;
        th.addEventListener("click", () => {
            const curCol = parseInt(table.dataset.sortCol ?? "-1", 10);
            const curDir = table.dataset.sortDir ?? "asc";
            const newDir = curCol === i && curDir === "asc" ? "desc" : "asc";
            const type = th.dataset.type || "string";

            sortBy(i, type, newDir);

            // update indikator panah
            table
                .querySelectorAll("thead th")
                .forEach((h) => h.classList.remove("sort-asc", "sort-desc"));
            th.classList.add(newDir === "asc" ? "sort-asc" : "sort-desc");
        });
    });

    // fungsi untuk dipanggil ulang setelah tabel di-render
    window.__reapplySort = function () {
        const col = parseInt(table.dataset.sortCol ?? "-1", 10);
        const dir = table.dataset.sortDir;
        if (col >= 0 && dir) {
            const th = table.querySelectorAll("thead th")[col];
            const type = th?.dataset?.type || "string";
            sortBy(col, type, dir);
            table
                .querySelectorAll("thead th")
                .forEach((h) => h.classList.remove("sort-asc", "sort-desc"));
            if (th) th.classList.add(dir === "asc" ? "sort-asc" : "sort-desc");
        }
    };
})();
