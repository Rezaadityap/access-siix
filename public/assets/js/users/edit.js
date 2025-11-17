document.addEventListener("DOMContentLoaded", function () {
    const userShowBase = window.USER_SHOW_URL || "/users";
    const userViewModal = new bootstrap.Modal(
        document.getElementById("userEditModal")
    );
    const form = document.getElementById("userEditForm");
    const saveBtn = document.getElementById("saveEditBtn");

    const editName = document.getElementById("editName");
    const editNik = document.getElementById("editNik");
    const editEmail = document.getElementById("editEmail");
    const editDepartment = document.getElementById("editDepartment");
    const editSection = document.getElementById("editSection");
    const editLevel = document.getElementById("editLevel");
    const editFormAlert = document.getElementById("editFormAlert");

    let currentUserId = null;

    function clearErrors() {
        ["name", "nik", "email", "department", "section", "level_id"].forEach(
            (field) => {
                const el = document.getElementById("error_" + field);
                if (el) {
                    el.textContent = "";
                    el.parentElement
                        ?.querySelectorAll(".is-invalid")
                        .forEach((i) => i.classList.remove("is-invalid"));
                }
            }
        );
        editFormAlert.classList.add("d-none");
        editFormAlert.classList.remove("alert-danger", "alert-success");
    }

    document.addEventListener("click", function (e) {
        const btn = e.target.closest(".btn-gradient-success, .btn-edit-user");
        if (!btn) return;

        let uid = btn.getAttribute("data-user-id") || btn.id;
        if (!uid) return;

        currentUserId = uid;
        clearErrors();
        editFormAlert.classList.add("d-none");

        editName.value = "";
        editNik.value = "";
        editEmail.value = "";
        editDepartment.value = "";
        editSection.value = "";
        editLevel.value = "";

        userViewModal.show();

        // fetch user data
        fetch(`${userShowBase}/${uid}`, {
            headers: { Accept: "application/json" },
        })
            .then(async (res) => {
                if (!res.ok) throw res;
                const data = await res.json();

                // Fill form fields
                editName.value = data.name ?? "";
                editNik.value = data.nik ?? "";
                editEmail.value = data.email ?? "";
                if (data.employee) {
                    editDepartment.value = data.employee.department ?? "";
                    editSection.value = data.employee.section ?? "";
                } else {
                    editDepartment.value = "";
                    editSection.value = "";
                }

                if (
                    typeof data.level_id !== "undefined" &&
                    data.level_id !== null
                ) {
                    editLevel.value = data.level_id;
                } else {
                    editLevel.value = "";
                }
            })
            .catch((err) => {
                console.error(err);
                editFormAlert.textContent = "Gagal memuat data user.";
                editFormAlert.classList.remove("d-none");
                editFormAlert.classList.add("alert", "alert-danger");
            });
    });

    // Submit handler
    form.addEventListener("submit", function (e) {
        e.preventDefault();
        if (!currentUserId) {
            return;
        }

        clearErrors();
        saveBtn.disabled = true;
        saveBtn.textContent = "Saving...";

        const url = `${userShowBase}/${currentUserId}`;
        const token =
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content") ||
            form.querySelector('input[name="_token"]')?.value ||
            "";

        const fd = new FormData();
        fd.append("_method", "PUT");
        if (token) fd.append("_token", token);
        fd.append("name", editName.value);
        fd.append("nik", editNik.value);
        fd.append("email", editEmail.value);
        fd.append("department", editDepartment.value);
        fd.append("section", editSection.value);
        fd.append("level_id", editLevel.value || "");

        fetch(url, {
            method: "POST",
            headers: {
                Accept: "application/json",
            },
            body: fd,
        })
            .then(async (res) => {
                saveBtn.disabled = false;
                saveBtn.textContent = "Save changes";

                if (res.status === 422) {
                    // validation errors
                    const json = await res.json();
                    const errors = json.errors || {};
                    Object.keys(errors).forEach((key) => {
                        const el = document.getElementById("error_" + key);
                        if (el) {
                            el.textContent = errors[key].join(" ");
                            const input = document.querySelector(
                                `[name="${key}"]`
                            );
                            if (input) input.classList.add("is-invalid");
                        }
                    });
                    editFormAlert.textContent =
                        json.message || "Validation error";
                    editFormAlert.classList.remove("d-none");
                    editFormAlert.classList.add("alert", "alert-danger");
                    return;
                }

                if (!res.ok) {
                    const txt = await res.text();
                    editFormAlert.textContent = "Update gagal. " + (txt || "");
                    editFormAlert.classList.remove("d-none");
                    editFormAlert.classList.add("alert", "alert-danger");
                    return;
                }

                const result = await res.json();
                editFormAlert.textContent = result.message || "Updated";
                editFormAlert.classList.remove("d-none");
                editFormAlert.classList.add("alert", "alert-success");

                function findUserRow(userId) {
                    const el = document.querySelector(
                        `#usersTable [data-user-id="${userId}"]`
                    );
                    if (el) return el.closest("tr");

                    const trs = document.querySelectorAll(
                        "#usersTable tbody tr"
                    );
                    for (const tr of trs) {
                        if (tr.dataset && tr.dataset.userId === String(userId))
                            return tr;
                    }
                    return null;
                }

                // later when update succeeded:
                const tr = findUserRow(currentUserId);
                if (tr) {
                    const tds = tr.querySelectorAll("td");
                    if (tds.length >= 4) {
                        tds[1].textContent =
                            result.user.nik ?? tds[1].textContent;
                        tds[2].textContent =
                            result.user.name ?? tds[2].textContent;
                        tds[3].textContent =
                            result.user.email ?? tds[3].textContent;
                    }
                } else {
                    setTimeout(() => location.reload(), 800);
                }

                setTimeout(() => {
                    const modalEl = document.getElementById("userEditModal");
                    const bsModal = bootstrap.Modal.getInstance(modalEl);
                    bsModal?.hide();
                }, 700);
            })
            .catch((err) => {
                console.error(err);
                saveBtn.disabled = false;
                saveBtn.textContent = "Save changes";
                editFormAlert.textContent =
                    "Terjadi kesalahan saat menghubungi server.";
                editFormAlert.classList.remove("d-none");
                editFormAlert.classList.add("alert", "alert-danger");
            });
    });
});
