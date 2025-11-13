// Button collapse di table history batch
document.addEventListener("DOMContentLoaded", function () {
    const collapseEl = document.getElementById("collapseHistory");
    const toggleBtn = document.getElementById("toggleHistoryBtn");
    const icon = toggleBtn.querySelector("i");

    collapseEl.addEventListener("shown.bs.collapse", function () {
        icon.classList.remove("bi-chevron-down");
        icon.classList.add("bi-chevron-up");
        toggleBtn.innerHTML = `<i class="bi bi-chevron-up me-1"></i> Collapse`;
    });

    collapseEl.addEventListener("hidden.bs.collapse", function () {
        icon.classList.remove("bi-chevron-up");
        icon.classList.add("bi-chevron-down");
        toggleBtn.innerHTML = `<i class="bi bi-chevron-down me-1"></i> Expand`;
    });
});

// Button copy batch
document.addEventListener("DOMContentLoaded", function () {
    // Ambil semua tombol copy batch
    const copyButtons = document.querySelectorAll(".copyBatchBtn");

    copyButtons.forEach((copy) => {
        copy.addEventListener("click", function (e) {
            e.preventDefault();

            // Ambil tabel terdekat
            const table = copy.closest("#copy").querySelector("table");
            if (!table) return;

            // Ambil semua teks dari kolom terakhir (Batch Description)
            const batchDescriptions = [];
            table.querySelectorAll("tbody tr").forEach((row) => {
                const lastCell = row.querySelector("td:last-child");
                if (
                    lastCell &&
                    lastCell.textContent.trim() !== "No data found"
                ) {
                    batchDescriptions.push(lastCell.textContent.trim());
                }
            });

            if (batchDescriptions.length > 0) {
                // Copy ke clipboard
                navigator.clipboard.writeText(batchDescriptions.join("\n"));
            }

            // Animasi “Copied”
            const icon = copy.querySelector("i");
            const span = copy.querySelector("span");

            icon.classList.replace("bi-clipboard", "bi-check2");
            span.textContent = "Copied";

            setTimeout(() => {
                icon.classList.replace("bi-check2", "bi-clipboard");
                span.textContent = "Copy batch";
            }, 1000);
        });
    });
});
