(function () {
    const modalEl = document.getElementById("userViewModal");
    const loader = document.getElementById("userViewLoader");
    const content = document.getElementById("userViewContent");
    const errorBox = document.getElementById("userViewError");

    const photo = document.getElementById("userViewPhoto");
    const nameEl = document.getElementById("userViewName");
    const nikEl = document.getElementById("detailNik");
    const emailEl = document.getElementById("detailEmail");
    const deptEl = document.getElementById("detailDepartment");
    const sectionEl = document.getElementById("detailSection");

    let bsModal = null;
    if (typeof bootstrap !== "undefined")
        bsModal = new bootstrap.Modal(modalEl);

    function reset() {
        loader.classList.remove("d-none");
        content.classList.add("d-none");
        errorBox.classList.add("d-none");

        photo.src = "";
        nameEl.textContent = "-";
        nikEl.textContent = "-";
        emailEl.textContent = "-";
        deptEl.textContent = "-";
        sectionEl.textContent = "-";
    }

    const fallbackAvatar = (name) => {
        const i = (name || "U")
            .split(" ")
            .map((s) => s[0])
            .slice(0, 2)
            .join("")
            .toUpperCase();
        const svg = `<svg xmlns='http://www.w3.org/2000/svg' width='300' height='300'><rect width='100%' height='100%' fill='%23444444'/><text x='50%' y='50%' dy='.1em' font-family='Arial' font-size='80' fill='white' text-anchor='middle'>${i}</text></svg>`;
        return "data:image/svg+xml;utf8," + encodeURIComponent(svg);
    };

    async function openUserModal(id) {
        reset();
        if (bsModal) bsModal.show();

        try {
            const res = await fetch(window.USER_SHOW_URL + "/" + id);
            if (!res.ok) throw new Error("Bad response");
            const data = await res.json();

            nameEl.textContent = data.name || "-";
            nikEl.textContent = data.nik || "-";
            emailEl.textContent = data.email || "-";

            if (data.employee) {
                deptEl.textContent = data.employee.department || "-";
                sectionEl.textContent = data.employee.section || "-";
                photo.src = data.employee.photo || fallbackAvatar(data.name);
            } else {
                deptEl.textContent = "-";
                sectionEl.textContent = "-";
                photo.src = fallbackAvatar(data.name);
            }

            loader.classList.add("d-none");
            content.classList.remove("d-none");
        } catch (err) {
            loader.classList.add("d-none");
            errorBox.classList.remove("d-none");
        }
    }

    document.addEventListener("click", (e) => {
        const btn = e.target.closest(".btn-modern-view");
        if (!btn) return;
        openUserModal(btn.dataset.userId || btn.id);
    });
})();
