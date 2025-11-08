function loadHistoryData(poNumbers) {
    if (!Array.isArray(poNumbers)) poNumbers = [poNumbers];

    fetch(
        `/record-material/history?po=${encodeURIComponent(poNumbers.join(","))}`
    )
        .then((response) => response.json())
        .then((data) => {
            if (data.status === "success") {
                fillTable("#recordScanSMD", data.smd);
                fillTable("#recordScanWH", data.wh);
                fillTable("#recordScanSTO", data.sto);
                fillTable("#recordScanMAR", data.mar);
                fillTable("#recordScanMismatch", data.mm);
            }
        })
        .catch((err) => console.error("Error loading history:", err));
}

function fillTable(tableId, rows) {
    const tbody = document.querySelector(`${tableId} tbody`);
    tbody.innerHTML = "";

    if (!rows || rows.length === 0) {
        tbody.innerHTML = `<tr><td colspan="5">No data found</td></tr>`;
        return;
    }

    rows.forEach((row, index) => {
        const tr = `
        <tr>
            <td>${index + 1}</td>
            <td>${row.scan_code || "-"}</td>
            <td>${row.material || "-"}</td>
            <td>${row.qty || 0}</td>
            <td>${row.batch_description || "-"}</td>
        </tr>
        `;
        tbody.insertAdjacentHTML("beforeend", tr);
    });
}
