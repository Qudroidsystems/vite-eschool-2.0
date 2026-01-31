// schoolclass-list.init.js - FIXED VERSION

console.log("schoolclass-list.init.js loaded", new Date().toISOString());

const routes = window.schoolClassRoutes || {};
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

// Axios CSRF setup
axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Modals
let addModal, editModal, deleteModal;
let currentDeleteId = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log("DOM ready - initializing school class management");

    // Initialize Bootstrap modals
    const addModalEl = document.getElementById('addSchoolClassModal');
    const editModalEl = document.getElementById('editSchoolClassModal');
    const deleteModalEl = document.getElementById('deleteConfirmModal');

    if (addModalEl) addModal = new bootstrap.Modal(addModalEl);
    if (editModalEl) editModal = new bootstrap.Modal(editModalEl);
    if (deleteModalEl) deleteModal = new bootstrap.Modal(deleteModalEl);

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
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding...';

        // Prepare form data - IMPORTANT: use FormData
        const formData = new FormData(form);

        // For debugging
        console.log('Form data for add:', Object.fromEntries(formData.entries()));
        console.log('Route:', routes.store);

        try {
            const response = await axios.post(routes.store, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });

            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: response.data.message || 'School class created successfully',
                timer: 2000
            });

            addModal.hide();
            form.reset();

            // Reload after a short delay
            setTimeout(() => {
                location.reload();
            }, 1500);

        } catch (err) {
            console.error('Add error:', err);
            handleAxiosError(err, 'add-error-msg');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
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
        const originalText = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

        const id = document.getElementById('edit-id').value;

        // Prepare form data
        const formData = new FormData(form);
        formData.append('_method', 'PUT');

        // Get the update route
        const updateRoute = routes.update.replace(':id', id);
        console.log('Update route:', updateRoute);
        console.log('Form data for update:', Object.fromEntries(formData.entries()));

        try {
            const response = await axios.post(updateRoute, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });

            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: response.data.message || 'Updated successfully',
                timer: 2000
            });

            editModal.hide();
            form.reset();

            // Reload after a short delay
            setTimeout(() => {
                location.reload();
            }, 1500);

        } catch (err) {
            console.error('Update error:', err);
            handleAxiosError(err, 'edit-error-msg');
        } finally {
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });

    // Delegate edit button clicks
    document.addEventListener('click', e => {
        const editBtn = e.target.closest('.edit-btn');
        if (!editBtn) return;

        const row = editBtn.closest('tr');
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

        // Arm (single selection - radio)
        const armId = row.querySelector('.arm-name')?.dataset.armId || '';
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
    // Set up delete button click handler
    document.addEventListener('click', e => {
        const deleteBtn = e.target.closest('.delete-btn');
        if (!deleteBtn) return;

        const row = deleteBtn.closest('tr');
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
        confirmBtn.addEventListener('click', async () => {
            if (!currentDeleteId) return;

            const originalText = confirmBtn.innerHTML;
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

            const deleteRoute = routes.destroy.replace(':id', currentDeleteId);
            console.log('Delete route:', deleteRoute);

            try {
                const response = await axios.delete(deleteRoute);

                Swal.fire({
                    icon: 'success',
                    title: 'Deleted',
                    text: response.data.message || 'Record removed successfully',
                    timer: 2000
                });

                deleteModal.hide();

                // Reload after a short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);

            } catch (err) {
                console.error('Delete error:', err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: err.response?.data?.message || 'Delete failed. Please try again.'
                });
            } finally {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = originalText;
            }
        });
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

    const ids = checked.map(cb => {
        const row = cb.closest('tr');
        return row ? row.dataset.id : null;
    }).filter(Boolean);

    if (ids.length === 0) {
        Swal.fire('Error', 'No valid records selected', 'error');
        return;
    }

    Swal.fire({
        title: `Delete ${ids.length} record(s)?`,
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete',
        cancelButtonText: 'Cancel'
    }).then(async result => {
        if (!result.isConfirmed) return;

        try {
            const deletePromises = ids.map(id =>
                axios.delete(routes.destroy.replace(':id', id))
            );

            await Promise.all(deletePromises);

            Swal.fire({
                icon: 'success',
                title: 'Deleted',
                text: `${ids.length} record(s) removed successfully`,
                timer: 2000
            });

            // Reload after a short delay
            setTimeout(() => {
                location.reload();
            }, 1500);

        } catch (err) {
            console.error('Bulk delete error:', err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Some deletions failed. Please try again.'
            });
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
    console.error('Axios Error:', err);
    let message = 'An error occurred';

    if (err.response) {
        if (err.response.status === 422) {
            const errors = err.response.data.errors;
            if (errors) {
                message = Object.values(errors).flat().join('<br>');
            } else {
                message = err.response.data.message || message;
            }
        } else {
            message = err.response.data.message || message;
        }
    } else if (err.request) {
        message = 'No response received from server. Please check your connection.';
    } else {
        message = err.message || message;
    }

    if (msgId) {
        const el = document.getElementById(msgId);
        if (el) {
            el.innerHTML = message;
            el.classList.remove('d-none');
        }
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message
        });
    }
}
