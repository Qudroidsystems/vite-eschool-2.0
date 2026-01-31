// schoolclass-list.init.js

console.log("schoolclass-list.init.js loaded", new Date().toISOString());

const routes = window.schoolClassRoutes || {};
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

// Axios CSRF setup
axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;

// Modals
let addModal, editModal, deleteModal;
let currentDeleteId = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log("DOM ready - initializing school class management");

    // Initialize Bootstrap modals
    addModal    = new bootstrap.Modal(document.getElementById('addSchoolClassModal'));
    editModal   = new bootstrap.Modal(document.getElementById('editSchoolClassModal'));
    deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmModal'));

    initCheckboxes();
    initSearch();
    initAddForm();
    initEditForm();
    initDeleteButtons();
    initBulkDelete();
});

// ────────────────────────────────────────────────
// Checkbox logic (select all + row highlight)
// ────────────────────────────────────────────────
function initCheckboxes() {
    const checkAll = document.getElementById('checkAll');
    if (!checkAll) return;

    checkAll.addEventListener('change', function() {
        document.querySelectorAll('input[name="chk_child"]').forEach(cb => {
            cb.checked = this.checked;
            toggleRowActive(cb);
        });
        toggleBulkDeleteButton();
    });

    document.querySelectorAll('input[name="chk_child"]').forEach(cb => {
        cb.addEventListener('change', () => {
            toggleRowActive(cb);
            toggleBulkDeleteButton();
            // Update "select all" state
            const all = document.querySelectorAll('input[name="chk_child"]');
            checkAll.checked = all.length > 0 && [...all].every(c => c.checked);
        });
    });
}

function toggleRowActive(checkbox) {
    const row = checkbox.closest('tr');
    if (checkbox.checked) {
        row.classList.add('table-active');
    } else {
        row.classList.remove('table-active');
    }
}

function toggleBulkDeleteButton() {
    const checkedCount = document.querySelectorAll('input[name="chk_child"]:checked').length;
    const btn = document.getElementById('remove-actions');
    if (btn) {
        btn.classList.toggle('d-none', checkedCount === 0);
    }
}

// ────────────────────────────────────────────────
// Search / Filter
// ────────────────────────────────────────────────
function initSearch() {
    const searchInput = document.querySelector('.search-box input.search');
    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        const term = this.value.toLowerCase().trim();
        document.querySelectorAll('#schoolclass-table tbody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(term) ? '' : 'none';
        });
    });
}

// ────────────────────────────────────────────────
// Add Form
// ────────────────────────────────────────────────
function initAddForm() {
    const form = document.getElementById('add-schoolclass-form');
    if (!form) return;

    form.addEventListener('submit', async e => {
        e.preventDefault();
        clearErrors('add');

        const btn = document.getElementById('add-submit-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding...';

        const formData = new FormData(form);

        try {
            const response = await axios.post(routes.store, formData);
            Swal.fire('Success', response.data.message || 'School class created', 'success');
            addModal.hide();
            form.reset();
            location.reload();
        } catch (err) {
            handleAxiosError(err, 'add-error-msg');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Add Class';
        }
    });
}

// ────────────────────────────────────────────────
// Edit Form + Edit Buttons
// ────────────────────────────────────────────────
function initEditForm() {
    const form = document.getElementById('edit-schoolclass-form');
    if (!form) return;

    form.addEventListener('submit', async e => {
        e.preventDefault();
        clearErrors('edit');

        const btn = document.getElementById('edit-submit-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

        const id = document.getElementById('edit-id').value;
        const formData = new FormData(form);
        formData.append('_method', 'PUT');

        try {
            const response = await axios.post(routes.update.replace(':id', id), formData);
            Swal.fire('Success', response.data.message || 'Updated', 'success');
            editModal.hide();
            form.reset();
            location.reload();
        } catch (err) {
            handleAxiosError(err, 'edit-error-msg');
        } finally {
            btn.disabled = false;
            btn.innerHTML = 'Update';
        }
    });

    // Delegate edit button clicks
    document.addEventListener('click', e => {
        if (!e.target.closest('.edit-btn')) return;
        const row = e.target.closest('tr');
        if (!row) return;

        const id = row.dataset.id;
        if (!id) {
            Swal.fire('Error', 'Cannot find record ID', 'error');
            return;
        }

        // Fill form
        document.getElementById('edit-id').value = id;
        document.getElementById('edit-schoolclass').value = row.querySelector('.schoolclass-name')?.textContent.trim() || '';
        document.getElementById('edit-description').value = row.querySelector('.description')?.textContent.trim() || '';

        // Arm (assuming single arm - radio)
        const armId = row.querySelector('.arm-name')?.dataset.armId;
        document.querySelectorAll('.edit-arm-radio').forEach(radio => {
            radio.checked = (radio.value === armId);
        });

        // Categories (checkboxes)
        const catIds = (row.querySelector('.categories')?.dataset.categoryIds || '').split(',').map(v => v.trim());
        document.querySelectorAll('.edit-category').forEach(cb => {
            cb.checked = catIds.includes(cb.value);
        });

        clearErrors('edit');
        editModal.show();
    });
}

// ────────────────────────────────────────────────
// Delete (single)
// ────────────────────────────────────────────────
function initDeleteButtons() {
    document.addEventListener('click', e => {
        if (!e.target.closest('.delete-btn')) return;
        const row = e.target.closest('tr');
        if (!row) return;

        currentDeleteId = row.dataset.id;
        if (!currentDeleteId) {
            Swal.fire('Error', 'Cannot find record ID', 'error');
            return;
        }

        deleteModal.show();
    });

    const confirmBtn = document.getElementById('confirm-delete-btn');
    if (confirmBtn) {
        confirmBtn.onclick = async () => {
            if (!currentDeleteId) return;

            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

            try {
                const response = await axios.delete(routes.destroy.replace(':id', currentDeleteId));
                Swal.fire('Deleted', response.data.message || 'Record removed', 'success');
                deleteModal.hide();
                location.reload();
            } catch (err) {
                handleAxiosError(err);
                Swal.fire('Error', err.response?.data?.message || 'Delete failed', 'error');
            } finally {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = 'Delete';
            }
        };
    }
}

// ────────────────────────────────────────────────
// Bulk Delete
// ────────────────────────────────────────────────
function deleteMultiple() {
    const checked = [...document.querySelectorAll('input[name="chk_child"]:checked')];
    if (checked.length === 0) {
        Swal.fire('No selection', 'Please select at least one record', 'warning');
        return;
    }

    const ids = checked.map(cb => cb.closest('tr')?.dataset.id).filter(Boolean);

    Swal.fire({
        title: `Delete ${ids.length} record(s)?`,
        text: "This cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete'
    }).then(async result => {
        if (!result.isConfirmed) return;

        try {
            await Promise.all(ids.map(id =>
                axios.delete(routes.destroy.replace(':id', id))
            ));
            Swal.fire('Deleted', `${ids.length} record(s) removed`, 'success');
            location.reload();
        } catch (err) {
            Swal.fire('Error', 'Some deletions failed', 'error');
            console.error(err);
        }
    });
}

// ────────────────────────────────────────────────
// Helpers
// ────────────────────────────────────────────────
function clearErrors(prefix = '') {
    const msgEl = document.getElementById(prefix ? `${prefix}-error-msg` : 'add-error-msg');
    if (msgEl) {
        msgEl.classList.add('d-none');
        msgEl.innerHTML = '';
    }
}

function handleAxiosError(err, msgId = null) {
    console.error(err);
    let message = 'An error occurred';

    if (err.response) {
        if (err.response.status === 422) {
            const errors = err.response.data.errors;
            message = Object.values(errors).flat().join('<br>');
        } else {
            message = err.response.data.message || message;
        }
    }

    if (msgId) {
        const el = document.getElementById(msgId);
        if (el) {
            el.innerHTML = message;
            el.classList.remove('d-none');
        }
    }
}
