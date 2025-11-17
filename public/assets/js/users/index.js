(function () {
    const dept = document.getElementById("departmentSelect");
    const form = document.getElementById("filterForm");
    const searchInput = document.getElementById("searchInput");

    if (dept && form) {
        dept.addEventListener("change", function () {
            form.submit();
        });
    }

    if (searchInput && form) {
        let debounceTimer = null;
        const DEBOUNCE_MS = 300;

        searchInput.addEventListener("input", function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                form.submit();
            }, DEBOUNCE_MS);
        });

        searchInput.addEventListener("keydown", function (e) {
            if (e.key === "Enter") {
                e.preventDefault();
                clearTimeout(debounceTimer);
                form.submit();
            }
        });
    }
})();
