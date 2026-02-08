// public/js/term.js
// ────────────────────────────────────────────────────────────────
// Term Management - Full CRUD + Status + Live Search
// Last updated: February 2026 style
// ────────────────────────────────────────────────────────────────

document.addEventListener('DOMContentLoaded', function () {

    // ─── CSRF Token Setup ────────────────────────────────────────
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (csrfToken) {
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    } else {
        console.error('CSRF token not found');
    }

    // ─── Elements ─────────────────────────────────────────────────
    const addModalEl     = document.getElementById('addTermModal');
    const editModalEl    = document.getElementById('editTermModal');
    const deleteModalEl  = document.getElementById('deleteConfirmModal');

    const addForm        = document.getElementById('addTermForm');
    const editForm       = document.getElementById('editTermForm');

    const addError       = document.getElementById('addError');
    const editError      = document.getElementById('editError');

    const searchInput    = document.querySelector('.search-box .search, .search');

    let currentDeleteId  = null;

    // ─── Utility: Simple debounce ────────────────────────────────
    function debounce(fn, delay = 300) {
        let timer;
        return function (...args) {
            clearTimeout(timer);
            timer = setTimeout(() => fn.apply(this, args), delay);
        };
    }

    // ─── Utility: Show/hide error message ────────────────────────
    function showError(element, message) {
        if (element) {
            element.textContent = message;
            element.classList.remove('d-none');
        }
    }

    function clearError(element) {
        if (element) {
            element.textContent = '';
            element.classList.add('d-none');
        }
    }

    // ─── Live Search (client-side filtering) ─────────────────────
    if (searchInput) {
        searchInput.addEventListener('input', debounce(() => {
            const term = searchInput.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#termTable tbody tr');

            let visibleCount = 0;

            rows.forEach(row => {
                if (row.classList.contains('no-results-row')) return;

                const text = row.textContent.toLowerCase();
                const matches = text.includes(term);

                row.style.display = matches ? '' : 'none';
                if (matches) visibleCount++;
            });

            // Show/hide "no results" message
            const noResultsRow = document.querySelector('.no-results-row') ||
                                 document.querySelector('tr td[colspan="5"]')?.parentElement;

            if (noResultsRow) {
                noResultsRow.style.display = visibleCount === 0 ? '' : 'none';
            }
        }));
    }

    // ─── ADD TERM ─────────────────────────────────────────────────
    if (addForm) {
        addForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            clearError(addError);

            try {
                const formData = new FormData(addForm);
                const payload = {
                    term:   formData.get('term')?.trim(),
                    status: formData.get('status') === 'on'   // checkbox sends 'on' when checked
                };

                if (!payload.term) {
                    throw new Error('Term name is required');
                }

                const response = await axios.post('/term', payload);

                if (response.data.success) {
                    bootstrap.Modal.getInstance(addModalEl)?.hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Created!',
                        text: response.data.message || 'Term added successfully',
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            } catch (error) {
                const msg = error.response?.data?.message
                         || error.message
                         || 'Failed to create term';
                showError(addError, msg);
                console.error('Add term error:', error);
            }
        });
    }

    // ─── EDIT TERM ────────────────────────────────────────────────
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function () {
            const row = this.closest('tr');
            if (!row) return;

            const id     = row.dataset.id;
            const term   = row.querySelector('.term')?.textContent.trim();
            const status = row.querySelector('.badge')?.textContent.trim() === 'Active';

            if (!id || !term) return;

            document.getElementById('editId').value    = id;
            document.getElementById('editTerm').value  = term;
            document.getElementById('editStatus').checked = status;

            clearError(editError);
            bootstrap.Modal.getOrCreateInstance(editModalEl).show();
        });
    });

    if (editForm) {
        editForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            clearError(editError);

            try {
                const formData = new FormData(editForm);
                const id = formData.get('id');
                const payload = {
                    term:   formData.get('term')?.trim(),
                    status: formData.get('status') === 'on'
                };

                if (!id || !payload.term) {
                    throw new Error('Invalid data');
                }

                const response = await axios.put(`/term/${id}`, payload);

                if (response.data.success) {
                    bootstrap.Modal.getInstance(editModalEl)?.hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Updated!',
                        text: response.data.message || 'Term updated successfully',
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            } catch (error) {
                const msg = error.response?.data?.message
                         || error.message
                         || 'Failed to update term';
                showError(editError, msg);
                console.error('Update term error:', error);
            }
        });
    }

    // ─── DELETE TERM ──────────────────────────────────────────────
    document.querySelectorAll('.remove-btn').forEach(button => {
        button.addEventListener('click', function () {
            const row = this.closest('tr');
            currentDeleteId = row?.dataset.id;

            if (!currentDeleteId) {
                Swal.fire('Error', 'Cannot identify term to delete', 'error');
                return;
            }

            bootstrap.Modal.getOrCreateInstance(deleteModalEl).show();
        });
    });

    const confirmDeleteBtn = document.getElementById('confirmDelete');
    if (confirmDeleteBtn) {
        confirmDeleteBtn.addEventListener('click', async function () {
            if (!currentDeleteId) return;

            try {
                const response = await axios.delete(`/term/${currentDeleteId}`);

                if (response.data.success) {
                    bootstrap.Modal.getInstance(deleteModalEl)?.hide();
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: response.data.message || 'Term removed successfully',
                        timer: 1800,
                        showConfirmButton: false
                    }).then(() => location.reload());
                }
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Delete failed',
                    text: error.response?.data?.message || 'Could not delete term'
                });
                console.error('Delete error:', error);
            }
        });
    }

    // ─── Modal cleanup (optional) ─────────────────────────────────
    [addModalEl, editModalEl].forEach(modal => {
        if (modal) {
            modal.addEventListener('hidden.bs.modal', function () {
                // Reset forms
                if (this.id === 'addTermModal') {
                    addForm?.reset();
                    document.getElementById('addStatus')?.checked = true;
                    clearError(addError);
                } else if (this.id === 'editTermModal') {
                    editForm?.reset();
                    clearError(editError);
                }
            });
        }
    });

    console.log('Term management JS initialized');
});
