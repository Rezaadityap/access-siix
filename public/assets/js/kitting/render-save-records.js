function renderSavedPOFromList(poList) {
    console.log("%c[renderSavedPOFromList] → Dipanggil", "color:lightgreen;", {
        poList,
    });

    // early guard
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
    // GLOBAL BUFFER (debounce + merge)
    // ----------------------
    // buffer map keyed by po_number -> keep most specific (prefer non-empty group_id)
    window.__renderSavedBuffer = window.__renderSavedBuffer || new Map();
    window.__renderSavedBufferTimeout =
        window.__renderSavedBufferTimeout || null;

    incoming.forEach((p) => {
        const existing = window.__renderSavedBuffer.get(p.po_number);
        if (!existing) {
            window.__renderSavedBuffer.set(p.po_number, {
                po_number: p.po_number,
                group_id: p.group_id ?? null,
            });
        } else {
            // prefer specific group_id
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

    // schedule actual fetch after short debounce window (merge multiple calls)
    if (window.__renderSavedBufferTimeout) {
        clearTimeout(window.__renderSavedBufferTimeout);
    }

    window.__renderSavedBufferTimeout = setTimeout(async () => {
        // take snapshot and clear buffer
        const toFetchList = Array.from(window.__renderSavedBuffer.values());
        window.__renderSavedBuffer.clear();
        window.__renderSavedBufferTimeout = null;

        if (!toFetchList.length) {
            $("#recordMaterials tbody").html(`
                <tr><td colspan="20" class="text-center text-muted">No Data Found</td></tr>
            `);
            return;
        }

        // Build unique signature for this batch
        const signature = JSON.stringify(toFetchList);

        // prevent multiple identical batches in-flight
        window.__renderSavedInFlight = window.__renderSavedInFlight || {};
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
            const ajaxPromises = toFetchList.map((po) => {
                const po_number = po.po_number;
                let url = `/record_material/by-po/${encodeURIComponent(
                    po_number
                )}`;
                if (po.group_id)
                    url += `?group_id=${encodeURIComponent(po.group_id)}`;

                return $.ajax({
                    url,
                    method: "GET",
                })
                    .then((res) => {
                        if (
                            res &&
                            res.status === "success" &&
                            Array.isArray(res.data) &&
                            res.data.length
                        ) {
                            console.log(
                                `[renderSavedPOFromList] Data diterima untuk PO ${po_number} (group ${
                                    po.group_id ?? "-"
                                }) :`,
                                res.data.length
                            );
                            res.data.forEach((item) => {
                                const uniqueKey = item.rml_id
                                    ? `rml:${item.rml_id}`
                                    : `mat:${item.material}::g:${
                                          item.group_id ?? po.group_id ?? ""
                                      }::po:${item.po_number ?? po_number}`;
                                if (!fetchedKeys.has(uniqueKey)) {
                                    fetchedKeys.add(uniqueKey);
                                    allLines.push(item);
                                }
                            });
                        } else {
                            console.warn(
                                `[renderSavedPOFromList] Tidak ada data untuk PO ${po_number} (group ${
                                    po.group_id ?? "-"
                                })`
                            );
                        }
                    })
                    .catch((xhr) => {
                        console.error(
                            `[AJAX Error] Gagal ambil data untuk PO ${po_number} (group ${
                                po.group_id ?? "-"
                            })`,
                            xhr
                        );
                    });
            });

            await Promise.all(ajaxPromises);

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

            // group by material + group_id so same material but different group stays separate
            const grouped = {};
            allLines.forEach((item) => {
                const gid =
                    item.group_id ??
                    item.groupId ??
                    item.group ??
                    item.po_number ??
                    "";
                const key = `${item.material}::${gid}`;
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
                <td>${
                    (item.rec_qty - item.smd_qty - item.receive_qty) * -1
                }</td>
                <td>${item.sto_qty}</td>
                <td>${
                    (item.rec_qty - item.smd_qty - item.receive_qty) * -1 +
                    item.sto_qty
                }</td>
                <td>${item.smd_qty + item.receive_qty + item.sto_qty}</td>
                <td>${item.mar_qty}</td>
                <td>${
                    (item.rec_qty / parseFloat(lotSize || 0)) *
                    item.cavity *
                    item.change_model
                }</td>
                <td>${totalScan}</td>
                <td>${
                    accumulateScan - item.rec_qty - item.mar_qty - totalScan
                }</td>
                <td>${item.unit_price * item.rec_qty || 0}</td>
                <td>${item.mm_qty || 0}</td>
            </tr>`;
            });

            $("#recordMaterials tbody").html(rows);
            if (window.__reapplySort) window.__reapplySort();
            console.log("[renderSavedPOFromList] Table updated successfully.", {
                count: Object.keys(grouped).length,
                allLinesCount: allLines.length,
            });
        } catch (err) {
            window.__renderSavedInFlight[signature] = false;
            console.error("[renderSavedPOFromList] Fetch error:", err);
        }
    }, 60);
}
