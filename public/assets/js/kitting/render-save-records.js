function invalidateRenderSavedCacheFor(poList) {
    if (!poList) return;
    if (!Array.isArray(poList)) poList = [poList];

    const normalized = poList
        .map((p) => {
            if (typeof p === "string") return { po_number: p, group_id: null };
            return {
                po_number: p.po_number,
                group_id: p.group_id ?? p.groupId ?? null,
            };
        })
        .filter((p) => p.po_number);

    if (!normalized.length) return;

    try {
        const cache = window.__renderSavedCache;
        if (cache && typeof cache.delete === "function") {
            normalized.forEach((p) => {
                const key = `${p.po_number}::${p.group_id ?? ""}`;
                if (cache.has(key)) {
                    cache.delete(key);
                    console.log(
                        "[invalidateRenderSavedCache] deleted cache key:",
                        key
                    );
                }
            });
        } else if (cache && typeof cache === "object") {
            // fallback for object-shaped cache
            Object.keys(cache).forEach((k) => {
                normalized.forEach((p) => {
                    if (k === `${p.po_number}::${p.group_id ?? ""}`) {
                        try {
                            delete cache[k];
                        } catch (e) {
                            cache[k] = undefined;
                        }
                        console.log(
                            "[invalidateRenderSavedCache] deleted cache key (obj):",
                            k
                        );
                    }
                });
            });
        }
    } catch (e) {
        console.warn("invalidateRenderSavedCache error (cache):", e);
    }

    try {
        const inflight = window.__renderSavedInFlight;
        if (!inflight) return;

        if (
            inflight &&
            typeof inflight === "object" &&
            !("size" in inflight && typeof inflight.delete === "function")
        ) {
            Object.keys(inflight).forEach((sig) => {
                normalized.forEach((p) => {
                    if (sig && String(sig).indexOf(p.po_number) !== -1) {
                        try {
                            delete inflight[sig];
                        } catch (e) {
                            inflight[sig] = false;
                        }
                        console.log(
                            "[invalidateRenderSavedCache] cleared in-flight sig:",
                            sig
                        );
                    }
                });
            });
        } else if (inflight && typeof inflight.has === "function") {
            Array.from(inflight).forEach((entry) => {
                normalized.forEach((p) => {
                    if (String(entry).indexOf(p.po_number) !== -1) {
                        try {
                            inflight.delete(entry);
                        } catch (e) {}
                        console.log(
                            "[invalidateRenderSavedCache] deleted in-flight entry:",
                            entry
                        );
                    }
                });
            });
        } else if (inflight && typeof inflight.delete === "function") {
            try {
                Array.from(inflight.keys()).forEach((k) => {
                    normalized.forEach((p) => {
                        if (String(k).indexOf(p.po_number) !== -1) {
                            try {
                                inflight.delete(k);
                            } catch (e) {}
                            console.log(
                                "[invalidateRenderSavedCache] deleted in-flight key (map):",
                                k
                            );
                        }
                    });
                });
            } catch (e) {}
        }
    } catch (e) {
        console.warn("invalidateRenderSavedCache error (inflight):", e);
    }
}
window.invalidateRenderSavedCacheFor = invalidateRenderSavedCacheFor;

function renderSavedPOFromList(poList) {
    console.log("%c[renderSavedPOFromList] → Dipanggil", "color:lightgreen;", {
        poList,
    });

    if (!poList) {
        console.warn("[renderSavedPOFromList] poList kosong");
        return;
    }

    if (!Array.isArray(poList)) poList = [poList];
    const incoming = poList
        .map((p) => {
            if (typeof p === "string") return { po_number: p, group_id: null };
            return {
                po_number: p.po_number,
                group_id: p.group_id ?? p.groupId ?? p.group ?? null,
            };
        })
        .filter(Boolean);

    if (!incoming.length) {
        console.warn(
            "[renderSavedPOFromList] Tidak ada PO valid setelah normalisasi"
        );
        $("#recordMaterials tbody").html(`
            <tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>
        `);
        return;
    }

    // ----------------------
    // cache + buffer + in-flight
    // ----------------------
    window.__renderSavedBuffer = window.__renderSavedBuffer || new Map();
    window.__renderSavedBufferTimeout =
        window.__renderSavedBufferTimeout || null;
    window.__renderSavedCache = window.__renderSavedCache || new Map();
    window.__renderSavedInFlight = window.__renderSavedInFlight || {};

    incoming.forEach((p) => {
        const existing = window.__renderSavedBuffer.get(p.po_number);
        if (!existing) {
            window.__renderSavedBuffer.set(p.po_number, {
                po_number: p.po_number,
                group_id: p.group_id ?? null,
            });
        } else {
            const exG = existing.group_id;
            const newG = p.group_id ?? null;
            const exEmpty = exG === null || exG === "" || exG === undefined;
            const newEmpty = newG === null || newG === "" || newG === undefined;
            if (exEmpty && !newEmpty) {
                window.__renderSavedBuffer.set(p.po_number, {
                    po_number: p.po_number,
                    group_id: newG,
                });
            }
        }
    });

    if (window.__renderSavedBufferTimeout) {
        clearTimeout(window.__renderSavedBufferTimeout);
    }

    window.__renderSavedBufferTimeout = setTimeout(async () => {
        const toFetchList = Array.from(window.__renderSavedBuffer.values());
        window.__renderSavedBuffer.clear();
        window.__renderSavedBufferTimeout = null;

        if (!toFetchList.length) {
            $("#recordMaterials tbody").html(`
                <tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>
            `);
            return;
        }

        const signature = JSON.stringify(toFetchList);
        if (window.__renderSavedInFlight[signature]) {
            console.log(
                "[renderSavedPOFromList] Batch already in-flight -> skip",
                signature
            );
            return;
        }
        window.__renderSavedInFlight[signature] = true;

        let allLines = [];
        const fetchedKeys = new Set();

        try {
            async function fetchPO(po) {
                const key = `${po.po_number}::${po.group_id ?? ""}`;
                if (window.__renderSavedCache.has(key)) {
                    // return cached array copy
                    return window.__renderSavedCache.get(key);
                }

                let url = `/record_material/by-po/${encodeURIComponent(
                    po.po_number
                )}`;
                if (po.group_id)
                    url += `?group_id=${encodeURIComponent(po.group_id)}`;

                try {
                    const res = await $.ajax({ url, method: "GET" });
                    if (
                        res &&
                        res.status === "success" &&
                        Array.isArray(res.data) &&
                        res.data.length
                    ) {
                        window.__renderSavedCache.set(key, res.data);
                        return res.data;
                    } else {
                        window.__renderSavedCache.set(key, []);
                        return [];
                    }
                } catch (xhr) {
                    console.error(
                        `[AJAX Error] Gagal ambil data untuk PO ${
                            po.po_number
                        } (group ${po.group_id ?? "-"})`,
                        xhr
                    );
                    window.__renderSavedCache.set(key, []);
                    return [];
                }
            }

            // concurrency-limited fetch: chunking
            const concurrency = 5;
            for (let i = 0; i < toFetchList.length; i += concurrency) {
                const chunk = toFetchList.slice(i, i + concurrency);
                const results = await Promise.all(chunk.map(fetchPO));
                // results is array of arrays
                results.forEach((resArr, idx) => {
                    const po = chunk[idx];
                    if (!resArr || !resArr.length) {
                        console.warn(
                            `[renderSavedPOFromList] Tidak ada data untuk PO ${
                                po.po_number
                            } (group ${po.group_id ?? "-"})`
                        );
                        return;
                    }
                    resArr.forEach((item) => {
                        const uniqueKey = item.rml_id
                            ? `rml:${item.rml_id}`
                            : `mat:${item.material}::g:${
                                  item.group_id ?? po.group_id ?? ""
                              }::po:${item.po_number ?? po.po_number}`;
                        if (!fetchedKeys.has(uniqueKey)) {
                            fetchedKeys.add(uniqueKey);
                            allLines.push(item);
                        }
                    });
                });
            }

            window.__renderSavedInFlight[signature] = false;

            if (allLines.length === 0) {
                $("#recordMaterials tbody").html(`
                    <tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>
                `);
                console.warn(
                    "[renderSavedPOFromList] Tidak ada data valid dari semua PO"
                );
                return;
            }

            // group by material + group_id
            const grouped = {};
            // parse numbers once per item
            allLines.forEach((item) => {
                const gid =
                    item.group_id ??
                    item.groupId ??
                    item.group ??
                    item.po_number ??
                    "";
                const key = `${item.material}::${gid}`;

                const totalQty = Number(item.total_qty) || 0;
                const receiveQty = Number(item.receive_qty) || 0;
                const smdQty = Number(item.smd_qty) || 0;
                const stoQty = Number(item.sto_qty) || 0;
                const marQty = Number(item.mar_qty) || 0;
                const mmQty = Number(item.mm_qty) || 0;

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

            const lotSizeRaw = document.getElementById("infoLotSize")?.value;
            const lotSize = Number(lotSizeRaw) || 0;
            let rowsArr = [];
            const groupedVals = Object.values(grouped);
            for (let i = 0; i < groupedVals.length; i++) {
                const item = groupedVals[i];
                const smdCount = Number(item.smd_scans) || 0;
                const whCount = Number(item.wh_scans) || 0;
                const stoCount = Number(item.sto_scans) || 0;
                const totalScan = smdCount + whCount + stoCount;
                const accumulateScan =
                    (Number(item.smd_qty) || 0) +
                    (Number(item.receive_qty) || 0) +
                    (Number(item.sto_qty) || 0);
                const rec_qty = Number(item.rec_qty) || 0;
                const smd_qty = Number(item.smd_qty) || 0;
                const receive_qty = Number(item.receive_qty) || 0;
                const sto_qty = Number(item.sto_qty) || 0;
                const mar_qty = Number(item.mar_qty) || 0;
                const unit_price = Number(item.unit_price) || 0;
                const mm_qty = Number(item.mm_qty) || 0;

                const status =
                    (rec_qty - smd_qty - receive_qty) * -1 + sto_qty < 0 ||
                    rec_qty === 0
                        ? '<span class="text-danger fw-bold">Shortage</span>'
                        : '<span class="text-success fw-bold">PASS</span>';

                // safe mar calc: avoid division by zero
                let marCalc = 0;
                const cavity = Number(item.cavity) || 0;
                const changeModel = Number(item.change_model) || 0;
                if (lotSize > 0 && cavity > 0 && changeModel > 0) {
                    marCalc = (rec_qty / lotSize) * cavity * changeModel;
                }

                rowsArr.push(`
                    <tr>
                        <td>${i + 1}</td>
                        <td>${item.material}</td>
                        <td>${
                            item.material_desc || item.part_description || ""
                        }</td>
                        <td>${rec_qty}</td>
                        <td>${item.satuan || ""}</td>
                        <td>${status}</td>
                        <td>${smd_qty}</td>
                        <td>${(rec_qty - smd_qty) * -1}</td>
                        <td>${receive_qty}</td>
                        <td>${(rec_qty - smd_qty - receive_qty) * -1}</td>
                        <td>${sto_qty}</td>
                        <td>${
                            (rec_qty - smd_qty - receive_qty) * -1 + sto_qty
                        }</td>
                        <td>${smd_qty + receive_qty + sto_qty}</td>
                        <td>${mar_qty}</td>
                        <td>${marCalc}</td>
                        <td>${totalScan}</td>
                        <td>${
                            accumulateScan - rec_qty - mar_qty - totalScan
                        }</td>
                <td>${
                    (unit_price * rec_qty).toString().includes(".")
                        ? (
                              Math.floor(unit_price * rec_qty * 100) / 100
                          ).toFixed(2)
                        : unit_price * rec_qty
                }</td>
                        <td>${mm_qty}</td>
                    </tr>
                `);
            }

            $("#recordMaterials tbody").html(rowsArr.join(""));
            if (window.__reapplySort) window.__reapplySort();
            console.log("[renderSavedPOFromList] Table updated successfully.", {
                count: groupedVals.length,
                allLinesCount: allLines.length,
            });
        } catch (err) {
            window.__renderSavedInFlight[signature] = false;
            console.error("[renderSavedPOFromList] Fetch error:", err);
        }
    }, 120);
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
