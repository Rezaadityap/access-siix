document.addEventListener("DOMContentLoaded", function () {
    // di atas initScanHandler
    const MaterialCache = {
        set: new Set(),
        loadedForPOs: null,
    };

    async function preloadMaterialsForPOs(poList, csrf) {
        const po_numbers = (poList || [])
            .map((p) => p.po_number)
            .filter(Boolean);
        // kalau sudah sama, skip
        const signature = JSON.stringify(po_numbers.sort());
        if (!po_numbers.length) {
            MaterialCache.set = new Set();
            MaterialCache.loadedForPOs = signature;
            return;
        }
        if (MaterialCache.loadedForPOs === signature) return;

        try {
            const res = await $.post("/record_material/check-material", {
                _token: csrf,
                po_numbers,
            });
            if (res.status === "success") {
                MaterialCache.set = new Set(res.materials || []);
                MaterialCache.loadedForPOs = signature;
            } else {
                // jika gagal, tetap kosong tetapi set signature agar tidak retry berulang-ulang
                MaterialCache.set = new Set();
                MaterialCache.loadedForPOs = signature;
            }
        } catch (e) {
            console.warn("preloadMaterialsForPOs error:", e);
            // jangan ubah loadedForPOs agar bisa retry nanti
        }
    }

    // NEW: helper cek duplikat DB (dipakai saat scan & saat submit)
    async function checkDBDuplicates(batches, type, csrf) {
        if (!batches?.length) return new Set();
        try {
            const res = await $.post("/record_material/check-batch", {
                _token: csrf,
                batches,
                type,
            });
            if (res.status === "success") {
                return new Set(res.duplicates || []);
            }
        } catch (e) {
            console.warn("checkDBDuplicates error:", e);
        }
        return new Set();
    }

    function initScanHandler({
        inputId,
        tableBodyId,
        modalId,
        submitBtnId,
        type,
        submitUrl,
    }) {
        // --- safety checks & element resolution ---
        let scannedData = [];
        const $tbody = document.getElementById(tableBodyId);
        const $input = document.getElementById(inputId);
        const modalEl = document.getElementById(modalId);
        const $submitBtn = document.getElementById(submitBtnId);
        const csrf =
            document.querySelector('meta[name="csrf-token"]')?.content ||
            "{{ csrf_token() }}";

        if (!$tbody) {
            console.warn(
                `[initScanHandler] tableBody "${tableBodyId}" not found — handler disabled.`
            );
            return;
        }
        if (!$input) {
            console.warn(
                `[initScanHandler] input "${inputId}" not found — handler disabled.`
            );
            return;
        }
        if (!modalEl) {
            console.warn(
                `[initScanHandler] modal "${modalId}" not found — continuing but modal events will be skipped.`
            );
            // we continue because submit button might still exist (some pages have modal elsewhere)
        }
        if (!$submitBtn) {
            console.warn(
                `[initScanHandler] submit button "${submitBtnId}" not found — submit handler will be skipped.`
            );
        }

        // cache untuk deteksi duplikat di sesi ini (cepat)
        const SessionBatchSet = new Set();

        function parseScanCode(scanText) {
            if (!scanText) return null;
            const parts = scanText.trim().split("@");
            if (parts.length < 5) return null;
            const qtyRaw = parseInt(parts[4].split(".")[0], 10);
            return {
                batch: parts[2].trim(),
                material: parts[3].trim(),
                qty: Number.isFinite(qtyRaw) ? qtyRaw : 0,
                description: scanText.trim(),
            };
        }

        function renderTable() {
            if (!scannedData.length) {
                $tbody.innerHTML = `<tr><td colspan="6" class="text-center text-muted">No data found</td></tr>`;
                return;
            }
            const frag = document.createDocumentFragment();
            scannedData.forEach((d, i) => {
                const tr = document.createElement("tr");
                tr.className = "text-muted";
                tr.style.fontSize = "1rem";
                tr.innerHTML = `
                <td>${i + 1}</td>
                <td>${d.batch}</td>
                <td>${d.material}</td>
                <td>${d.qty}</td>
                <td>${d.description}</td>
                <td><button type="button" class="btn btn-sm btn-danger delete-btn"><i class="bi bi-trash"></i></button></td>`;
                frag.appendChild(tr);
            });
            $tbody.innerHTML = "";
            $tbody.appendChild(frag);
        }

        // Fokus saat modal muncul + preload material untuk PO aktif
        if (modalEl) {
            modalEl.addEventListener("shown.bs.modal", async () => {
                setTimeout(() => $input.focus(), 50);
                const currentPO = getCurrentPOFromDOM();
                if (currentPO.length)
                    await preloadMaterialsForPOs(currentPO, csrf);
            });
        }

        // ENTER handler: sekarang async, validasi lokal + cek duplikat DB sekali
        // --- REPLACEMENT: input handling (Enter / input / paste) ---
        // central processing function (reused by keydown, input, paste)
        async function processScannedText(rawText) {
            const raw = (rawText || "").trim();
            if (!raw) return;

            const currentPO = getCurrentPOFromDOM();
            if (!currentPO.length) {
                Swal.fire({
                    icon: "warning",
                    title: "No PO Selected!",
                    text: "Please select or import file before scanning.",
                });
                return;
            }

            const cleaned = raw
                .replace(/\r?\n|\r/g, " ")
                .replace(/\s+/g, " ")
                .trim();

            const codeRegex = /\[\)\>@[\s\S]*?@@/g;
            const codes = cleaned.match(codeRegex) || [cleaned];

            const t0 = performance.now();

            let added = 0,
                skippedDupSession = 0,
                skippedMaterial = 0,
                skippedDupDB = 0;

            // kumpulkan kandidat yang lolos validasi lokal dulu
            const candidates = [];
            for (const codeStr of codes) {
                const parsed = parseScanCode(codeStr);
                if (!parsed) continue;

                // duplikat di sesi (kecuali MAR boleh duplikat)
                if (type !== "MAR" && SessionBatchSet.has(parsed.batch)) {
                    skippedDupSession++;
                    continue;
                }

                // ensure material cache loaded
                if (!MaterialCache.set.size) {
                    try {
                        await preloadMaterialsForPOs(
                            JSON.parse(
                                localStorage.getItem("currentPO") || "[]"
                            ),
                            csrf
                        );
                    } catch (err) {
                        // ignore preload error
                    }
                }

                if (!MaterialCache.set.has(parsed.material)) {
                    skippedMaterial++;
                    continue;
                }

                candidates.push(parsed);
            }

            // cek duplikat DB sekali untuk semua kandidat (kecuali MAR)
            let dupDBSet = new Set();
            if (type !== "MAR" && candidates.length) {
                const batchesToCheck = candidates.map((c) => c.batch);
                dupDBSet = await checkDBDuplicates(batchesToCheck, type, csrf);
            }

            // finalisasi penambahan data
            for (const parsed of candidates) {
                if (type !== "MAR" && dupDBSet.has(parsed.batch)) {
                    skippedDupDB++;
                    continue;
                }
                scannedData.push(parsed);
                SessionBatchSet.add(parsed.batch);
                added++;
            }

            renderTable();
            const dt = (performance.now() - t0).toFixed(1);
            console.log(
                `Scan processed: +${added}, dupSession:${skippedDupSession}, dupDB:${skippedDupDB}, notInPO:${skippedMaterial} in ${dt}ms`
            );

            // feedback ringan tanpa modal blocking
            const msg = [
                skippedDupSession
                    ? `Skipped session duplicates: ${skippedDupSession}`
                    : null,
                skippedDupDB ? `Skipped DB duplicates: ${skippedDupDB}` : null,
                skippedMaterial ? `Not in PO: ${skippedMaterial}` : null,
            ]
                .filter(Boolean)
                .join(" | ");
            if (msg) {
                Swal.fire({
                    icon: "info",
                    title: "Scan Notice",
                    text: msg,
                    timer: 1800,
                    showConfirmButton: false,
                    toast: true,
                    position: "top-end",
                });
            }
        }

        // keydown: tetap tetap support Enter (legacy)
        $input.addEventListener("keydown", async function (e) {
            if (e.key !== "Enter") return;
            e.preventDefault();
            const raw = this.value || "";
            this.value = "";
            await processScannedText(raw);
            this.focus();
        });

        // debounced input: proses otomatis saat pattern terpenuhi atau heuristik
        const inputDebounced = (function () {
            let t = null;
            return function () {
                clearTimeout(t);
                t = setTimeout(async () => {
                    const val = $input.value || "";
                    // heuristik: jika ada pattern kode, atau panjang > 20 & mengandung '@', proses
                    const cleaned = val
                        .replace(/\r?\n|\r/g, " ")
                        .replace(/\s+/g, " ")
                        .trim();
                    const codeRegex = /\[\)\>@[\s\S]*?@@/g;
                    const hasPattern = !!cleaned.match(codeRegex);
                    const heuristic =
                        cleaned.length > 20 && cleaned.includes("@");
                    if (hasPattern || heuristic) {
                        $input.value = "";
                        await processScannedText(cleaned);
                        $input.focus();
                    }
                }, 120); // delay kecil supaya paste/typing selesai
            };
        })();
        $input.addEventListener("input", inputDebounced);

        // paste: proses segera (scanner that pastes)
        $input.addEventListener("paste", function (e) {
            // baca clipboard langsung jika tersedia
            const clipboardData = e.clipboardData || window.clipboardData;
            const pasted = clipboardData
                ? clipboardData.getData("Text") || ""
                : "";
            setTimeout(async () => {
                const val = pasted || $input.value || "";
                if (val) {
                    $input.value = "";
                    await processScannedText(val);
                    $input.focus();
                }
            }, 20);
        });

        // hapus baris
        $tbody.addEventListener("click", function (e) {
            const btn = e.target.closest(".delete-btn");
            if (!btn) return;
            const row = btn.closest("tr");
            const idx = Array.from($tbody.children).indexOf(row);
            const batchName = scannedData[idx]?.batch || "Unknown";
            Swal.fire({
                title: "Are you sure?",
                text: `Batch "${batchName}" will be removed.`,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Yes, delete it!",
            }).then((r) => {
                if (r.isConfirmed) {
                    SessionBatchSet.delete(scannedData[idx]?.batch);
                    scannedData.splice(idx, 1);
                    renderTable();
                    Swal.fire({
                        icon: "success",
                        title: "Deleted!",
                        timer: 800,
                        showConfirmButton: false,
                    });
                }
            });
        });

        // submit: tetap cek duplikat DB secara batch untuk jaga-jaga (idempotent)
        if ($submitBtn) {
            $(`#${submitBtnId}`).on("click", async function () {
                if (!scannedData.length) {
                    Swal.fire({
                        icon: "warning",
                        title: "No Data!",
                        text: "Please scan at least one material before submitting.",
                    });
                    return;
                }

                const currentPO = getCurrentPOFromDOM();
                if (!currentPO.length) {
                    Swal.fire({
                        icon: "warning",
                        title: "No PO Selected!",
                        text: "Please select a PO before submitting.",
                    });
                    return;
                }

                // Validasi khusus MAR
                let actualLotSize = null,
                    cavity = null,
                    changeModel = null;
                if (type === "MAR") {
                    const lotRaw =
                        document
                            .getElementById("actualLotSize")
                            ?.value.trim() || null;
                    const cavRaw =
                        document.getElementById("cavity")?.value.trim() || null;
                    changeModel =
                        document.getElementById("change_model")?.value.trim() ||
                        null;

                    actualLotSize = lotRaw !== null ? Number(lotRaw) : null;
                    cavity = cavRaw !== null ? Number(cavRaw) : null;

                    if (!Number.isFinite(actualLotSize) || actualLotSize <= 0)
                        return Swal.fire({
                            icon: "warning",
                            title: "Missing Lot Size!",
                        });
                    if (!Number.isFinite(cavity) || cavity <= 0)
                        return Swal.fire({
                            icon: "warning",
                            title: "Missing Cavity!",
                        });
                    if (!changeModel)
                        return Swal.fire({
                            icon: "warning",
                            title: "Missing change model!",
                        });
                }

                // 1x call untuk cek seluruh batch di DB (safety net)
                const batches = scannedData.map((s) => s.batch);
                let duplicates = [];
                try {
                    const res = await $.post("/record_material/check-batch", {
                        _token: csrf,
                        batches,
                        type,
                    });
                    if (res.status === "success")
                        duplicates = res.duplicates || [];
                } catch (e) {
                    return Swal.fire({
                        icon: "error",
                        title: "Error!",
                        text: "Failed to verify batches.",
                    });
                }

                let payloadData = scannedData;
                if (type !== "MAR" && duplicates.length) {
                    const dupSet = new Set(duplicates);
                    payloadData = scannedData.filter(
                        (s) => !dupSet.has(s.batch)
                    );

                    if (duplicates.length) {
                        await Swal.fire({
                            icon: "info",
                            title: "Duplicate batches skipped",
                            text: `${duplicates.length} duplicate batch(es) excluded.`,
                            timer: 1200,
                            showConfirmButton: false,
                        });
                    }
                }

                if (!payloadData.length) {
                    Swal.fire({
                        icon: "warning",
                        title: "No new data to save",
                        text: "All scanned batches are duplicates.",
                    });
                    return;
                }

                Swal.fire({
                    title: "Submit scanned data?",
                    text: `You are about to save ${payloadData.length} scanned batch(es).`,
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Yes, submit!",
                }).then((result) => {
                    if (!result.isConfirmed) return;

                    // submit final
                    $.ajax({
                        url: submitUrl,
                        method: "POST",
                        data: {
                            _token: csrf,
                            po_list: currentPO,
                            scanned: payloadData,
                            ...(type === "MAR"
                                ? {
                                      actual_lot_size: actualLotSize,
                                      cavity,
                                      changeModel,
                                  }
                                : {}),
                        },
                        success: async function (res) {
                            if (res.status === "success") {
                                Swal.fire({
                                    icon: "success",
                                    title: "Success!",
                                    text: res.message,
                                    timer: 1100,
                                    showConfirmButton: false,
                                });

                                const poNumbers = currentPO.map(
                                    (p) => p.po_number
                                );
                                if (typeof loadHistoryData === "function")
                                    loadHistoryData(poNumbers);

                                // try to render saved PO lines
                                if (poNumbers.length) {
                                    try {
                                        await preloadMaterialsForPOs(
                                            currentPO,
                                            csrf
                                        );
                                    } catch (err) {}
                                    if (
                                        typeof window.renderSavedPOFromList ===
                                        "function"
                                    ) {
                                        try {
                                            window.renderSavedPOFromList(
                                                poNumbers
                                            );
                                        } catch (err) {
                                            console.error(
                                                "Error calling renderSavedPOFromList after submit:",
                                                err
                                            );
                                            if (
                                                typeof renderSavedPO ===
                                                "function"
                                            )
                                                renderSavedPO();
                                        }
                                    } else if (
                                        typeof renderSavedPO === "function"
                                    ) {
                                        try {
                                            renderSavedPO();
                                        } catch (err) {
                                            console.error(
                                                "renderSavedPO fallback error:",
                                                err
                                            );
                                        }
                                    } else {
                                        console.warn(
                                            "No renderSavedPOFromList or renderSavedPO available after submit."
                                        );
                                    }
                                }

                                scannedData = [];
                                SessionBatchSet.clear();
                                renderTable();
                                if (modalEl) $(`#${modalId}`).modal("hide");

                                // special MAR update: persist actual to localPO and re-render info
                                if (type === "MAR") {
                                    if (document.getElementById("infoActual"))
                                        document.getElementById(
                                            "infoActual"
                                        ).value = actualLotSize;
                                    let updatedPO = JSON.parse(
                                        localStorage.getItem("currentPO") ||
                                            "[]"
                                    );
                                    updatedPO.forEach((po) => {
                                        po.infoActual = actualLotSize;
                                        po.cavity = cavity;
                                        po.changeModel = changeModel;
                                    });
                                    localStorage.setItem(
                                        "currentPO",
                                        JSON.stringify(updatedPO)
                                    );
                                    try {
                                        await preloadMaterialsForPOs(
                                            updatedPO,
                                            csrf
                                        );
                                    } catch (err) {}
                                    if (typeof updateInfoFields === "function")
                                        updateInfoFields(updatedPO);
                                }
                            } else {
                                Swal.fire({
                                    icon: "error",
                                    title: "Failed!",
                                    text: res.message || "Error saving data.",
                                });
                            }
                        },
                        error: function (xhr) {
                            Swal.fire({
                                icon: "error",
                                title: "Error!",
                                text:
                                    xhr.responseJSON?.message ||
                                    "Unexpected error occurred.",
                            });
                        },
                    });
                });
            });
        } // end if $submitBtn
    } // end initScanHandler

    // -------------------------
    // Helper: baca current PO dari DOM (#infoContainer)
    // -------------------------
    function getCurrentPOFromDOM() {
        const arr = [];
        $("#infoContainer .row").each(function () {
            const $inputs = $(this).find("input");
            const po_number = $inputs.eq(0).val() || "";
            const model = $inputs.eq(1).val() || "";
            const area =
                $(this).data("area") ?? $(this).attr("data-area") ?? "";
            const line =
                $(this).data("line") ?? $(this).attr("data-line") ?? "";
            const lot_size =
                $(this).data("lot_size") ?? $(this).attr("data-lot_size") ?? "";
            const act_lot_size =
                $(this).data("act_lot_size") ??
                $(this).attr("data-act_lot_size") ??
                "";
            const date =
                $(this).data("date") ?? $(this).attr("data-date") ?? "";
            const group_id =
                $(this).data("group_id") ?? $(this).attr("data-group_id") ?? "";
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
                });
            }
        });
        return arr;
    }

    // Inisialisasi untuk semua modal
    initScanHandler({
        inputId: "whBarcodeInput",
        tableBodyId: "materialTableBody",
        modalId: "scanMaterial",
        submitBtnId: "submitScanWH",
        type: "WH",
        submitUrl: "/record_material/store-wh",
    });
    initScanHandler({
        inputId: "smdBarcodeInput",
        tableBodyId: "smdTableBody",
        modalId: "scanSmd",
        submitBtnId: "submitScanSmd",
        type: "SMD",
        submitUrl: "/record_material/store-smd",
    });
    initScanHandler({
        inputId: "stoBarcodeInput",
        tableBodyId: "stoTableBody",
        modalId: "scanSto",
        submitBtnId: "submitScanSto",
        type: "STO",
        submitUrl: "/record_material/store-sto",
    });
    initScanHandler({
        inputId: "marBarcodeInput",
        tableBodyId: "marTableBody",
        modalId: "scanMar",
        submitBtnId: "submitScanMar",
        type: "MAR",
        submitUrl: "/record_material/store-mar",
    });
    initScanHandler({
        inputId: "mismatchInput",
        tableBodyId: "mismatchTableBody",
        modalId: "scanMismatchModal",
        submitBtnId: "submitScanMismatch",
        type: "MM",
        submitUrl: "/record_material/store-mismatch",
    });
});
