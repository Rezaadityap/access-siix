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
