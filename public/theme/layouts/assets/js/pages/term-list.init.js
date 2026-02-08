// term.init.js
// Updated to support status field (active/inactive)
// Compatible with your original Blade UI structure

console.log("term.init.js loaded");

// ─── CSRF Token ────────────────────────────────────────────────────────
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
if (csrfToken) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
} else {
    console.warn("CSRF token meta tag not found");
}

// ─── Debounce helper ───────────────────────────────────────────────────
function debounce(func, wait = 300) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// ─── Check All Checkbox ────────────────────────────────────────────────
const checkAll = document.getElementById("checkAll");
if (checkAll) {
    checkAll.addEventListener("change", function () {
        const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
        checkboxes.forEach(cb => {
            cb.checked = this.checked;
            const row = cb.closest("tr");
            row?.classList.toggle("table-active", this.checked);
        });
        toggleBulkDeleteButton();
    });
}

// Update bulk delete button visibility
function toggleBulkDeleteButton() {
    const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
    const removeBtn = document.getElementById("remove-actions");
    if (removeBtn) {
        removeBtn.classList.toggle("d-none", checkedCount === 0);
    }
}

// Individual checkbox change
document.addEventListener("change", function (e) {
    if (e.target.name === "chk_child") {
        const row = e.target.closest("tr");
        row?.classList.toggle("table-active", e.target.checked);
        toggleBulkDeleteButton();

        // Update "select all" state
        const allCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]');
        const allChecked = Array.from(allCheckboxes).every(cb => cb.checked);
        if (checkAll) checkAll.checked = allChecked && allCheckboxes.length > 0;
    }
});

// ─── Live Search with List.js ──────────────────────────────────────────
let termList;
const termListContainer = document.getElementById("termList");

if (termListContainer) {
    try {
        termList = new List('termList', {
            valueNames: ['term', 'datereg'],
            page: 1000,           // large number = effectively no pagination
            pagination: false,
            listClass: 'list'
        });

        // Show/hide no results message
        termList.on('searchComplete', function () {
            const noResult = document.querySelector('.noresult');
            if (noResult) {
                noResult.style.display = termList.visibleItems.length === 0 ? 'table-row' : 'none';
            }
        });
    } catch (err) {
        console.error("List.js failed to initialize:", err);
    }
}

// Search input handler
const searchInput = document.querySelector(".search-box input.search");
if (searchInput && termList) {
    searchInput.addEventListener("input", debounce(function () {
        termList.search(searchInput.value, ['term']);
    }, 250));
}

// ─── Add Term Form ─────────────────────────────────────────────────────
const addForm = document.getElementById("add-term-form");
if (addForm) {
    addForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        const errorMsg = document.getElementById("alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");

        const termValue = document.getElementById("term")?.value.trim();
        const statusChecked = document.getElementById("addStatus")?.checked ?? true;

        if (!termValue) {
            if (errorMsg) {
                errorMsg.textContent = "Term name is required";
                errorMsg.classList.remove("d-none");
            }
            return;
        }

        try {
            const response = await axios.post('/term', {
                term: termValue,
                status: statusChecked
            });

            if (response.data.success) {
                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: response.data.message || "Term created successfully",
                    timer: 2000,
                    showConfirmButton: false
                });
                bootstrap.Modal.getInstance(document.getElementById("addTermModal"))?.hide();
                location.reload();
            }
        } catch (error) {
            const msg = error.response?.data?.message || "Failed to create term";
            if (errorMsg) {
                errorMsg.textContent = msg;
                errorMsg.classList.remove("d-none");
            }
            console.error("Add term error:", error);
        }
    });
}

// ─── Edit Term ─────────────────────────────────────────────────────────
document.addEventListener("click", function (e) {
    const editBtn = e.target.closest(".edit-item-btn");
    if (editBtn) {
        e.preventDefault();
        const row = editBtn.closest("tr");
        if (!row) return;

        const id = row.querySelector(".id")?.getAttribute("data-id");
        const termText = row.querySelector(".term")?.textContent.trim();
        const statusBadge = row.querySelector(".badge");
        const isActive = statusBadge?.textContent.trim() === "Active";

        document.getElementById("edit-id-field").value = id || "";
        document.getElementById("edit-term").value = termText || "";
        document.getElementById("editStatus").checked = isActive;

        bootstrap.Modal.getOrCreateInstance(document.getElementById("editModal")).show();
    }
});

const editForm = document.getElementById("edit-term-form");
if (editForm) {
    editForm.addEventListener("submit", async function (e) {
        e.preventDefault();

        const errorMsg = document.getElementById("edit-alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");

        const id = document.getElementById("edit-id-field")?.value;
        const termValue = document.getElementById("edit-term")?.value.trim();
        const statusChecked = document.getElementById("editStatus")?.checked ?? false;

        if (!id || !termValue) {
            if (errorMsg) {
                errorMsg.textContent = "Missing required fields";
                errorMsg.classList.remove("d-none");
            }
            return;
        }

        try {
            const response = await axios.put(`/term/${id}`, {
                term: termValue,
                status: statusChecked
            });

            if (response.data.success) {
                Swal.fire({
                    icon: "success",
                    title: "Success",
                    text: response.data.message || "Term updated successfully",
                    timer: 2000,
                    showConfirmButton: false
                });
                bootstrap.Modal.getInstance(document.getElementById("editModal"))?.hide();
                location.reload();
            }
        } catch (error) {
            const msg = error.response?.data?.message || "Failed to update term";
            if (errorMsg) {
                errorMsg.textContent = msg;
                errorMsg.classList.remove("d-none");
            }
            console.error("Update error:", error);
        }
    });
}

// ─── Single Delete ─────────────────────────────────────────────────────
let deleteTargetId = null;

document.addEventListener("click", function (e) {
    const removeBtn = e.target.closest(".remove-item-btn");
    if (removeBtn) {
        e.preventDefault();
        const row = removeBtn.closest("tr");
        deleteTargetId = row?.querySelector(".id")?.getAttribute("data-id");

        if (deleteTargetId) {
            bootstrap.Modal.getOrCreateInstance(document.getElementById("deleteRecordModal")).show();
        }
    }
});

const confirmDeleteBtn = document.getElementById("delete-record");
if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener("click", async function () {
        if (!deleteTargetId) return;

        try {
            const response = await axios.delete(`/term/${deleteTargetId}`);

            if (response.data.success) {
                Swal.fire({
                    icon: "success",
                    title: "Deleted",
                    text: response.data.message || "Term deleted successfully",
                    timer: 2000,
                    showConfirmButton: false
                });
                bootstrap.Modal.getInstance(document.getElementById("deleteRecordModal"))?.hide();
                location.reload();
            }
        } catch (error) {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: error.response?.data?.message || "Failed to delete term"
            });
        }
    });
}

// ─── Bulk Delete (deleteMultiple) ──────────────────────────────────────
window.deleteMultiple = async function () {
    const checked = document.querySelectorAll('tbody input[name="chk_child"]:checked');
    if (checked.length === 0) {
        Swal.fire("Info", "Please select at least one term", "info");
        return;
    }

    const ids = Array.from(checked).map(cb => cb.closest("tr").querySelector(".id")?.getAttribute("data-id"));

    Swal.fire({
        title: "Are you sure?",
        text: `You are about to delete ${ids.length} term(s)`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete them!"
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await Promise.all(
                    ids.map(id => axios.delete(`/term/${id}`))
                );
                Swal.fire("Deleted!", "Selected terms have been deleted.", "success");
                location.reload();
            } catch (error) {
                Swal.fire("Error", "Some or all deletions failed", "error");
                console.error("Bulk delete error:", error);
            }
        }
    });
};

// ─── Modal cleanup (optional) ──────────────────────────────────────────
document.getElementById("addTermModal")?.addEventListener("hidden.bs.modal", function () {
    document.getElementById("add-term-form")?.reset();
    document.getElementById("addStatus")?.checked = true;
});

document.getElementById("editModal")?.addEventListener("hidden.bs.modal", function () {
    document.getElementById("edit-term-form")?.reset();
});

console.log("Term management initialized");
