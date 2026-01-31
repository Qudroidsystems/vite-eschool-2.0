console.log("schoolclass-list.init.js loaded at", new Date().toISOString());

// === CONFIG ===
const perPage = 100;
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
if (!csrfToken) console.warn("CSRF token missing");

axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

const routeUrls = window.routeUrls || {
    storeSchoolClass: '/schoolclass',
    updateSchoolClass: '/schoolclass/:id',
    destroySchoolClass: '/schoolclass/:id',
    getArms: '/schoolclass/:id/arms'
};

// === LIST.JS SETUP ===
const options = {
    valueNames: ['schoolclassid', 'schoolclass', 'arm', 'classcategory', 'datereg'],
    page: perPage,
    pagination: false
};

const schoolClassList = new List('schoolClassList', options);

schoolClassList.on("updated", function () {
    console.log("List updated - items:", schoolClassList.items.length);
    const noResult = document.querySelector(".noresult");
    if (noResult) {
        noResult.style.display = schoolClassList.matchingItems.length === 0 ? "block" : "none";
    }
    refreshCallbacks();
    ischeckboxcheck();
});

// === CHECKBOX LOGIC ===
function ischeckboxcheck() {
    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    checkboxes.forEach(cb => {
        cb.removeEventListener("change", handleCheckboxChange);
        cb.addEventListener("change", handleCheckboxChange);
    });
}

function handleCheckboxChange(e) {
    const row = e.target.closest("tr");
    row?.classList.toggle("table-active", e.target.checked);

    const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
    document.getElementById("remove-actions")?.classList.toggle("d-none", checkedCount === 0);

    const allCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    document.getElementById("checkAll").checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCount;
}

document.getElementById("checkAll")?.addEventListener("change", function () {
    document.querySelectorAll('tbody input[name="chk_child"]').forEach(cb => {
        cb.checked = this.checked;
        cb.closest("tr")?.classList.toggle("table-active", this.checked);
    });
    document.getElementById("remove-actions")?.classList.toggle("d-none", !this.checked);
});

// === REFRESH CALLBACKS (only attach once per button) ===
function refreshCallbacks() {
    console.log("Refreshing callbacks...");

    // Remove old listeners first
    document.querySelectorAll('.remove-item-btn').forEach(btn => {
        btn.removeEventListener("click", handleRemoveClick);
        btn.addEventListener("click", handleRemoveClick);
    });

    document.querySelectorAll('.edit-item-btn').forEach(btn => {
        btn.removeEventListener("click", handleEditClick);
        btn.addEventListener("click", handleEditClick);
    });
}

// === DELETE HANDLER ===
function handleRemoveClick(e) {
    e.preventDefault();
    e.stopPropagation();

    const row = e.target.closest("tr");
    const id = row?.querySelector(".id")?.dataset.id;
    if (!id) {
        console.error("No ID found for delete");
        return;
    }

    console.log("Delete clicked for ID:", id);

    const modalEl = document.getElementById("deleteRecordModal");
    if (!modalEl) {
        console.error("Delete modal not found");
        Swal.fire("Error", "Delete modal missing", "error");
        return;
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    const deleteBtn = document.getElementById("delete-record");
    if (!deleteBtn) return;

    // Remove previous listener to avoid multiple bindings
    deleteBtn.replaceWith(deleteBtn.cloneNode(true));
    const newDeleteBtn = document.getElementById("delete-record");

    newDeleteBtn.addEventListener("click", async function () {
        newDeleteBtn.disabled = true;
        newDeleteBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

        try {
            const res = await axios.delete(`/schoolclass/${id}`);
            Swal.fire("Success!", res.data.message || "Deleted!", "success");
            modal.hide();
            location.reload();
        } catch (err) {
            console.error("Delete failed:", err.response?.data || err);
            Swal.fire("Error!", err.response?.data?.message || "Delete failed", "error");
        } finally {
            newDeleteBtn.disabled = false;
            newDeleteBtn.innerHTML = "Delete";
        }
    });
}

// === EDIT HANDLER ===
function handleEditClick(e) {
    e.preventDefault();
    e.stopPropagation();

    const row = e.target.closest("tr");
    const id = row?.querySelector(".id")?.dataset.id;
    if (!id) {
        console.error("No ID for edit");
        return;
    }

    console.log("Edit clicked for ID:", id);

    const schoolclass = row.querySelector(".schoolclass")?.dataset.schoolclass || row.querySelector(".schoolclass")?.textContent.trim() || '';
    const armId = row.querySelector(".arm")?.dataset.armId || '';
    const catIdsStr = row.querySelector(".classcategory")?.dataset.categoryIds || '';

    document.getElementById("edit-id-field").value = id;
    document.getElementById("edit-schoolclass").value = schoolclass;

    // Set arm radio
    document.querySelectorAll('#edit-arm-radios input[type="radio"]').forEach(radio => {
        radio.checked = (radio.value === armId);
    });

    // Reset and set categories
    document.querySelectorAll('#edit-category-checkboxes input[type="checkbox"]').forEach(cb => {
        cb.checked = false;
    });

    if (catIdsStr) {
        catIdsStr.split(',').forEach(id => {
            const clean = id.trim();
            const cb = document.querySelector(`#edit-category-${clean}`);
            if (cb) cb.checked = true;
        });
    }

    document.getElementById("edit-alert-error-msg")?.classList.add("d-none");

    const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById("editModal"));
    modal.show();
}

// === FORM SUBMISSIONS (already in your code - keep or replace) ===
document.getElementById("add-schoolclass-form")?.addEventListener("submit", async (e) => {
    // your existing add logic...
});

document.getElementById("edit-schoolclass-form")?.addEventListener("submit", async (e) => {
    // your existing edit logic...
});

// === BULK DELETE ===
function deleteMultiple() {
    const checked = document.querySelectorAll('tbody input[name="chk_child"]:checked');
    if (checked.length === 0) return Swal.fire("No selection", "Select at least one row", "warning");

    const ids = Array.from(checked).map(cb => cb.closest("tr").querySelector(".id")?.dataset.id).filter(Boolean);

    Swal.fire({
        title: "Delete Selected?",
        text: `This will delete ${ids.length} record(s).`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete",
        cancelButtonText: "Cancel"
    }).then(async (result) => {
        if (!result.isConfirmed) return;

        try {
            await Promise.all(ids.map(id => axios.delete(`/schoolclass/${id}`)));
            Swal.fire("Deleted!", "Selected records removed.", "success");
            location.reload();
        } catch (err) {
            Swal.fire("Error!", err.response?.data?.message || "Failed", "error");
        }
    });
}

// === INITIALIZE ===
document.addEventListener("DOMContentLoaded", () => {
    console.log("DOM ready - initializing callbacks");
    refreshCallbacks();
    ischeckboxcheck();
});
