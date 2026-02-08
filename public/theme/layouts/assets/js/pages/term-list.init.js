console.log("term.init.js is loaded and executing!");

// Verify dependencies
try {
    if (typeof axios === 'undefined') throw new Error("Axios is not loaded");
    if (typeof Swal === 'undefined') throw new Error("SweetAlert2 is not loaded");
    if (typeof bootstrap === 'undefined') throw new Error("Bootstrap is not loaded");
    if (typeof List === 'undefined') throw new Error("List.js is not loaded");
} catch (error) {
    console.error("Dependency check failed:", error);
}

// CSRF token
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.content;

// Debounce
function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Check all
var checkAll = document.getElementById("checkAll");
if (checkAll) {
    checkAll.onclick = function () {
        var checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
        checkboxes.forEach((checkbox) => {
            checkbox.checked = this.checked;
            checkbox.closest("tr").classList.toggle("table-active", this.checked);
        });
        updateRemoveButton();
    };
}

function updateRemoveButton() {
    var checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
    var removeActions = document.getElementById("remove-actions");
    if (removeActions) removeActions.classList.toggle("d-none", checkedCount === 0);
}

// Individual checkbox
function ischeckboxcheck() {
    document.querySelectorAll('tbody input[name="chk_child"]').forEach(checkbox => {
        checkbox.addEventListener("change", function () {
            this.closest("tr").classList.toggle("table-active", this.checked);
            updateRemoveButton();

            var all = document.querySelectorAll('tbody input[name="chk_child"]');
            var checked = document.querySelectorAll('tbody input[name="chk_child"]:checked');
            if (checkAll) checkAll.checked = all.length > 0 && all.length === checked.length;
        });
    });
}

// Edit handler
function handleEditClick(e) {
    e.preventDefault();
    var tr = e.target.closest("tr");
    var id = tr.querySelector(".id").getAttribute("data-id");
    var term = tr.querySelector(".term").innerText.trim();
    var status = tr.querySelector(".status").getAttribute("data-status") === "1";

    document.getElementById("edit-id-field").value = id;
    document.getElementById("edit-term").value = term;
    document.getElementById("editStatus").checked = status;

    new bootstrap.Modal(document.getElementById("editModal")).show();
}

// Delete single
function handleRemoveClick(e) {
    e.preventDefault();
    var itemId = e.target.closest("tr").querySelector(".id").getAttribute("data-id");
    var deleteButton = document.getElementById("delete-record");

    // Clone to avoid multiple listeners
    var newDeleteButton = deleteButton.cloneNode(true);
    deleteButton.parentNode.replaceChild(newDeleteButton, deleteButton);

    newDeleteButton.addEventListener("click", function () {
        axios.delete(`/term/${itemId}`)
            .then(() => {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Term deleted successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
                window.location.reload();
            })
            .catch(error => {
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error deleting term",
                    text: error.response?.data?.message || "An error occurred",
                    showConfirmButton: true
                });
            });
    }, { once: true });

    new bootstrap.Modal(document.getElementById("deleteRecordModal")).show();
}

// Bulk delete
function deleteMultiple() {
    const ids = [];
    document.querySelectorAll('tbody input[name="chk_child"]:checked').forEach(chk => {
        ids.push(chk.closest("tr").querySelector(".id").getAttribute("data-id"));
    });

    if (ids.length === 0) {
        Swal.fire("Please select at least one term", "", "info");
        return;
    }

    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#dc3545",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel"
    }).then((result) => {
        if (!result.isConfirmed) return;

        Promise.all(ids.map(id => axios.delete(`/term/${id}`)))
            .then(() => {
                Swal.fire("Deleted!", "Selected terms have been deleted.", "success");
                window.location.reload();
            })
            .catch(error => {
                Swal.fire("Error!", error.response?.data?.message || "Failed to delete terms", "error");
            });
    });
}

// List.js
var termList;
if (document.getElementById('termList') && document.querySelectorAll('#termList tbody tr').length > 0) {
    termList = new List('termList', {
        valueNames: ['term', 'datereg', 'status'],
        page: 1000,
        pagination: false
    });

    termList.on('searchComplete', function () {
        var noResultRow = document.querySelector('.noresult');
        if (noResultRow) {
            noResultRow.style.display = termList.visibleItems.length === 0 ? 'block' : 'none';
        }
    });
}

// Search
var searchInput = document.querySelector(".search-box input.search");
if (searchInput) {
    searchInput.addEventListener("input", debounce(function () {
        if (termList) termList.search(searchInput.value);
    }, 300));
}

// Add form
document.getElementById("add-term-form")?.addEventListener("submit", function (e) {
    e.preventDefault();
    var errorMsg = document.getElementById("alert-error-msg");
    errorMsg?.classList.add("d-none");

    var formData = new FormData(this);
    var payload = {
        term: formData.get('term')?.trim(),
        status: formData.get('status') === 'on'
    };

    if (!payload.term) {
        errorMsg.innerHTML = "Please enter a term name";
        errorMsg?.classList.remove("d-none");
        return;
    }

    axios.post('/term', payload)
        .then(() => {
            Swal.fire({
                position: "center",
                icon: "success",
                title: "Term added successfully!",
                showConfirmButton: false,
                timer: 2000,
                showCloseButton: true
            });
            location.reload();
        })
        .catch(error => {
            errorMsg.innerHTML = error.response?.data?.message || "Error adding term";
            errorMsg?.classList.remove("d-none");
        });
});

// Edit form
document.getElementById("edit-term-form")?.addEventListener("submit", function (e) {
    e.preventDefault();
    var errorMsg = document.getElementById("edit-alert-error-msg");
    errorMsg?.classList.add("d-none");

    var formData = new FormData(this);
    var id = formData.get('id');
    var payload = {
        term: formData.get('term')?.trim(),
        status: formData.get('status') === 'on'
    };

    if (!payload.term) {
        errorMsg.innerHTML = "Please enter a term name";
        errorMsg?.classList.remove("d-none");
        return;
    }

    axios.put(`/term/${id}`, payload)
        .then(() => {
            Swal.fire({
                position: "center",
                icon: "success",
                title: "Term updated successfully!",
                showConfirmButton: false,
                timer: 2000,
                showCloseButton: true
            });
            location.reload();
        })
        .catch(error => {
            errorMsg.innerHTML = error.response?.data?.message || "Error updating term";
            errorMsg?.classList.remove("d-none");
        });
});

// Event delegation
document.addEventListener('click', function (e) {
    if (e.target.closest('.edit-item-btn')) handleEditClick(e);
    if (e.target.closest('.remove-item-btn')) handleRemoveClick(e);
});

// Modal cleanup
document.getElementById("addTermModal")?.addEventListener("hidden.bs.modal", function () {
    document.getElementById("add-term-form")?.reset();
    document.getElementById("addStatus")?.checked = true;
});

document.getElementById("editModal")?.addEventListener("hidden.bs.modal", function () {
    document.getElementById("edit-term-form")?.reset();
});

// Init
document.addEventListener("DOMContentLoaded", function () {
    ischeckboxcheck();
    updateRemoveButton();
});

window.deleteMultiple = deleteMultiple;
