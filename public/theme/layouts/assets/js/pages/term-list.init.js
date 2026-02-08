console.log("term.init.js loaded");

// ────────────────────────────────────────────────────────────────
// Dependencies check
// ────────────────────────────────────────────────────────────────
try {
    if (typeof axios === 'undefined') throw new Error("Axios missing");
    if (typeof Swal === 'undefined') throw new Error("SweetAlert2 missing");
    if (typeof bootstrap === 'undefined') throw new Error("Bootstrap missing");
    if (typeof List === 'undefined') throw new Error("List.js missing");
} catch (err) {
    console.error("Dependency error:", err.message);
}

// CSRF token
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content || '';

// ────────────────────────────────────────────────────────────────
// Helpers
// ────────────────────────────────────────────────────────────────
function debounce(func, wait = 300) {
    let timeout;
    return (...args) => {
        clearTimeout(timeout);
        timeout = setTimeout(() => func(...args), wait);
    };
}

function updateRemoveButtonVisibility() {
    const checked = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
    document.getElementById("remove-actions")?.classList.toggle("d-none", checked === 0);
}

// ────────────────────────────────────────────────────────────────
// Select All Checkbox
// ────────────────────────────────────────────────────────────────
const checkAll = document.getElementById("checkAll");
if (checkAll) {
    checkAll.addEventListener("change", function () {
        document.querySelectorAll('tbody input[name="chk_child"]').forEach(chk => {
            chk.checked = this.checked;
            chk.closest("tr").classList.toggle("table-active", this.checked);
        });
        updateRemoveButtonVisibility();
    });
}

// Individual checkboxes
function initCheckboxes() {
    document.querySelectorAll('tbody input[name="chk_child"]').forEach(chk => {
        chk.addEventListener("change", function () {
            this.closest("tr").classList.toggle("table-active", this.checked);
            updateRemoveButtonVisibility();

            const all = document.querySelectorAll('tbody input[name="chk_child"]');
            const checked = document.querySelectorAll('tbody input[name="chk_child"]:checked');
            if (checkAll) checkAll.checked = all.length > 0 && all.length === checked.length;
        });
    });
}

// ────────────────────────────────────────────────────────────────
// Edit handler
// ────────────────────────────────────────────────────────────────
function handleEditClick(e) {
    e.preventDefault();
    const tr = e.target.closest("tr");
    if (!tr) return;

    const id     = tr.querySelector(".id")?.getAttribute("data-id");
    const term   = tr.querySelector(".term")?.innerText.trim();
    const status = tr.querySelector(".status")?.getAttribute("data-status") === "1";

    if (!id || !term) return;

    document.getElementById("edit-id-field").value = id;
    document.getElementById("edit-term").value = term;
    document.getElementById("editStatus").checked = status;

    const modal = new bootstrap.Modal(document.getElementById("editModal"));
    modal.show();
}

// ────────────────────────────────────────────────────────────────
// Delete handler (single)
// ────────────────────────────────────────────────────────────────
function handleRemoveClick(e) {
    e.preventDefault();
    const tr = e.target.closest("tr");
    if (!tr) return;

    const id = tr.querySelector(".id")?.getAttribute("data-id");
    if (!id) return;

    // Replace delete button to prevent duplicate listeners
    const oldBtn = document.getElementById("delete-record");
    if (oldBtn) {
        const newBtn = oldBtn.cloneNode(true);
        oldBtn.parentNode.replaceChild(newBtn, oldBtn);

        newBtn.addEventListener("click", () => {
            axios.delete(`/term/${id}`)
                .then(() => {
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: "Deleted successfully",
                        showConfirmButton: false,
                        timer: 1800,
                        showCloseButton: true
                    });
                    location.reload();
                })
                .catch(err => {
                    Swal.fire({
                        icon: "error",
                        title: "Delete failed",
                        text: err.response?.data?.message || "Server error"
                    });
                });
        }, { once: true });
    }

    const modal = new bootstrap.Modal(document.getElementById("deleteRecordModal"));
    modal.show();
}

// ────────────────────────────────────────────────────────────────
// Bulk delete
// ────────────────────────────────────────────────────────────────
window.deleteMultiple = function () {
    const ids = Array.from(
        document.querySelectorAll('tbody input[name="chk_child"]:checked')
    ).map(chk => chk.closest("tr").querySelector(".id").getAttribute("data-id"));

    if (ids.length === 0) {
        Swal.fire("No terms selected", "", "info");
        return;
    }

    Swal.fire({
        title: `Delete ${ids.length} term(s)?`,
        text: "This cannot be undone.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, delete"
    }).then(result => {
        if (!result.isConfirmed) return;

        Promise.all(ids.map(id => axios.delete(`/term/${id}`)))
            .then(() => {
                Swal.fire("Deleted!", "", "success");
                location.reload();
            })
            .catch(err => {
                Swal.fire("Error", err.response?.data?.message || "Failed", "error");
            });
    });
};

// ────────────────────────────────────────────────────────────────
// List.js search
// ────────────────────────────────────────────────────────────────
let termList;
if (document.getElementById('termList')) {
    termList = new List('termList', {
        valueNames: ['term', 'datereg', 'status'],
        page: 1000,
        pagination: false
    });

    termList.on('searchComplete', () => {
        const noresult = document.querySelector('.noresult');
        if (noresult) {
            noresult.style.display = termList.visibleItems.length === 0 ? 'block' : 'none';
        }
    });
}

const searchInput = document.querySelector(".search");
if (searchInput && termList) {
    searchInput.addEventListener("input", debounce(() => {
        termList.search(searchInput.value.trim());
    }, 300));
}

// ────────────────────────────────────────────────────────────────
// Add form
// ────────────────────────────────────────────────────────────────
document.getElementById("add-term-form")?.addEventListener("submit", function(e) {
    e.preventDefault();
    const errorEl = document.getElementById("alert-error-msg");
    errorEl?.classList.add("d-none");

    const formData = new FormData(this);
    const payload = {
        term: formData.get("term")?.trim(),
        status: formData.get("status") === "on"
    };

    if (!payload.term) {
        errorEl.innerHTML = "Term name is required";
        errorEl?.classList.remove("d-none");
        return;
    }

    axios.post('/term', payload)
        .then(() => location.reload())
        .catch(err => {
            errorEl.innerHTML = err.response?.data?.message || "Failed to add term";
            errorEl?.classList.remove("d-none");
        });
});

// ────────────────────────────────────────────────────────────────
// Edit form
// ────────────────────────────────────────────────────────────────
document.getElementById("edit-term-form")?.addEventListener("submit", function(e) {
    e.preventDefault();
    const errorEl = document.getElementById("edit-alert-error-msg");
    errorEl?.classList.add("d-none");

    const formData = new FormData(this);
    const id = formData.get("id");
    const payload = {
        term: formData.get("term")?.trim(),
        status: formData.get("status") === "on"
    };

    if (!id || !payload.term) {
        errorEl.innerHTML = "Invalid data";
        errorEl?.classList.remove("d-none");
        return;
    }

    axios.put(`/term/${id}`, payload)
        .then(() => location.reload())
        .catch(err => {
            errorEl.innerHTML = err.response?.data?.message || "Failed to update term";
            errorEl?.classList.remove("d-none");
        });
});

// ────────────────────────────────────────────────────────────────
// Event delegation
// ────────────────────────────────────────────────────────────────
document.addEventListener('click', e => {
    if (e.target.closest('.edit-item-btn')) {
        handleEditClick(e);
    }
    if (e.target.closest('.remove-item-btn')) {
        handleRemoveClick(e);
    }
});

// ────────────────────────────────────────────────────────────────
// Modal reset
// ────────────────────────────────────────────────────────────────
document.getElementById("addTermModal")?.addEventListener("hidden.bs.modal", () => {
    document.getElementById("add-term-form")?.reset();
    document.getElementById("addStatus")?.checked = true;
});

document.getElementById("editModal")?.addEventListener("hidden.bs.modal", () => {
    document.getElementById("edit-term-form")?.reset();
});

// ────────────────────────────────────────────────────────────────
// Init
// ────────────────────────────────────────────────────────────────
document.addEventListener("DOMContentLoaded", () => {
    initCheckboxes();
    updateRemoveButtonVisibility();
    console.log("Term JS initialized");
});
