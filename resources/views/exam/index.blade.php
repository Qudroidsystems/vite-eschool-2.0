<!-- Include SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    console.log('CSRF Token:', csrfToken); // Debug

    // Initialize modal filtering
    initModalFiltering();

    // Edit button click handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.edit-exam-btn')) {
            e.preventDefault();
            const examId = e.target.closest('.edit-exam-btn').dataset.id;
            if (examId) {
                console.log('Editing exam:', examId); // Debug
                loadExamForEdit(examId);
            }
        }

        if (e.target.closest('.delete-exam-btn')) {
            e.preventDefault();
            const examId = e.target.closest('.delete-exam-btn').dataset.id;
            if (examId) {
                console.log('Deleting exam:', examId); // Debug
                deleteExam(examId);
            }
        }

        // Check all functionality
        if (e.target.id === 'checkAll') {
            const checkboxes = document.querySelectorAll('input[name="chk_child"]');
            checkboxes.forEach(cb => cb.checked = e.target.checked);
            toggleRemoveActions();
        }

        // Individual checkbox change
        if (e.target.name === 'chk_child') {
            toggleRemoveActions();
        }
    });

    // Form submissions
    const addForm = document.getElementById('add-exam-form');
    if (addForm) {
        addForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Add form submitted'); // Debug
            submitAddForm();
        });
    }

    const editForm = document.getElementById('edit-exam-form');
    if (editForm) {
        editForm.addEventListener('submit', function(e) {
            e.preventDefault();
            console.log('Edit form submitted'); // Debug
            submitEditForm();
        });
    }

    // Search functionality
    const searchInput = document.querySelector('.search');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                window.location.href = `{{ route('exams.index') }}?search=${encodeURIComponent(this.value)}`;
            }, 500);
        });
    }
});

function initModalFiltering() {
    // Add modal filtering
    const addTerm = document.getElementById('addTerm');
    const addSession = document.getElementById('addSession');
    const addSubject = document.getElementById('addSubject');

    if (addTerm && addSession && addSubject) {
        addTerm.addEventListener('change', function() {
            filterSubjects(this.value, addSession.value, addSubject);
        });

        addSession.addEventListener('change', function() {
            filterSubjects(addTerm.value, this.value, addSubject);
        });

        // Initialize filtering
        filterSubjects('', '', addSubject);
    }

    // Subject change listener for add modal
    if (addSubject) {
        addSubject.addEventListener('change', function() {
            if (this.value) {
                loadClassesForSubject(this.value, 'add');
            } else {
                document.getElementById('addClassContainer').innerHTML =
                    '<p class="text-muted text-center mb-0">Select a subject first...</p>';
            }
        });
    }
}

function filterSubjects(termId, sessionId, subjectSelect) {
    const allOptions = subjectSelect.querySelectorAll('option');

    allOptions.forEach(option => {
        if (option.value === '') {
            option.style.display = '';
            return;
        }

        const optionTermId = option.getAttribute('data-termid');
        const optionSessionId = option.getAttribute('data-sessionid');

        // Show option if it matches both selected term and session (if provided)
        const showOption = (!termId || optionTermId == termId) &&
                          (!sessionId || optionSessionId == sessionId);

        option.style.display = showOption ? '' : 'none';
        option.disabled = !showOption;
    });

    // Reset selection if current selection is hidden
    if (subjectSelect.value && subjectSelect.selectedOptions[0].style.display === 'none') {
        subjectSelect.value = '';
        const containerId = subjectSelect.id === 'addSubject' ? 'addClassContainer' : 'editClassContainer';
        document.getElementById(containerId).innerHTML =
            '<p class="text-muted text-center mb-0">Select a subject first...</p>';
    }
}

function loadClassesForSubject(subjectTeacherId, mode = 'add') {
    const containerId = mode === 'add' ? 'addClassContainer' : 'editClassContainer';
    const container = document.getElementById(containerId);

    container.innerHTML = '<p class="text-muted text-center mb-0"><i class="ri-loader-2-line spin me-1"></i> Loading classes...</p>';

    fetch(`/exams/subject-classes/${subjectTeacherId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        console.log('Classes response:', data); // Debug
        if (data.success && data.classes && data.classes.length > 0) {
            let html = '<div class="row">';

            data.classes.forEach(cls => {
                const isChecked = mode === 'edit' && data.selectedClasses &&
                                 data.selectedClasses.includes(parseInt(cls.id));
                html += `
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="schoolclass_ids[]"
                                   value="${cls.id}"
                                   id="class_${mode}_${cls.id}"
                                   ${isChecked ? 'checked' : ''}>
                            <label class="form-check-label" for="class_${mode}_${cls.id}">
                                ${cls.schoolclass} ${cls.arm ? '(' + cls.arm + ')' : ''}
                            </label>
                        </div>
                    </div>`;
            });

            html += '</div>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p class="text-muted text-center mb-0">No classes assigned to this subject.</p>';
        }
    })
    .catch(error => {
        console.error('Error loading classes:', error);
        container.innerHTML = '<p class="text-danger text-center mb-0">Error loading classes. Please try again.</p>';
    });
}

function loadExamForEdit(examId) {
    Swal.fire({
        title: 'Loading...',
        allowOutsideClick: false,
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    fetch(`/exams/${examId}/edit`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        console.log('Edit response status:', response.status); // Debug
        if (!response.ok) {
            throw new Error(`HTTP error! Status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Edit response data:', data); // Debug
        if (data.success && data.exam) {
            populateEditForm(data);
            const editModal = new bootstrap.Modal(document.getElementById('editModal'));
            editModal.show();
            Swal.close();
        } else {
            throw new Error(data.message || 'Invalid response format');
        }
    })
    .catch(error => {
        console.error('Error loading exam:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Failed to load exam data. Please try again.',
            timer: 3000
        });
    });
}

function populateEditForm(data) {
    const exam = data.exam;

    // Basic fields
    document.getElementById('edit-id-field').value = exam.id;
    document.getElementById('edit-title').value = exam.title || '';
    document.getElementById('edit-description').value = exam.description || '';
    document.getElementById('edit-duration').value = exam.duration || '';

    // Date fields - format for datetime-local input
    if (exam.start_time) {
        // Handle different date formats
        let startDate;
        if (exam.start_time.includes('T')) {
            startDate = new Date(exam.start_time);
        } else {
            startDate = new Date(exam.start_time.replace(' ', 'T'));
        }
        document.getElementById('edit-start_time').value = formatDateForInput(startDate);
    }

    if (exam.end_time) {
        // Handle different date formats
        let endDate;
        if (exam.end_time.includes('T')) {
            endDate = new Date(exam.end_time);
        } else {
            endDate = new Date(exam.end_time.replace(' ', 'T'));
        }
        document.getElementById('edit-end_time').value = formatDateForInput(endDate);
    }

    // Select fields
    document.getElementById('edit-termid').value = exam.termid || '';
    document.getElementById('edit-session').value = exam.session || '';
    document.getElementById('edit-publishStatus').checked = exam.is_published == 1;

    // Subject selection with filtering
    const subjectSelect = document.getElementById('edit-subject_id');
    subjectSelect.value = exam.subject_id || '';

    // Apply filtering based on selected term and session
    filterSubjects(exam.termid, exam.session, subjectSelect);

    // Load classes for this subject
    setTimeout(() => {
        if (exam.subject_id) {
            loadClassesForEdit(exam.subject_id, data.schoolclass_ids || []);
        }
    }, 100);
}

function formatDateForInput(date) {
    if (!date || isNaN(date)) return '';

    try {
        // Format to YYYY-MM-DDTHH:MM
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        return `${year}-${month}-${day}T${hours}:${minutes}`;
    } catch (error) {
        console.error('Date formatting error:', error);
        return '';
    }
}

function loadClassesForEdit(subjectTeacherId, selectedClassIds = []) {
    const container = document.getElementById('editClassContainer');

    container.innerHTML = '<p class="text-muted text-center mb-0"><i class="ri-loader-2-line spin me-1"></i> Loading classes...</p>';

    fetch(`/exams/subject-classes/${subjectTeacherId}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        console.log('Edit classes response:', data); // Debug
        if (data.success && data.classes && data.classes.length > 0) {
            let html = '<div class="row">';

            data.classes.forEach(cls => {
                const isChecked = selectedClassIds.includes(parseInt(cls.id));
                html += `
                    <div class="col-md-6 mb-2">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="schoolclass_ids[]"
                                   value="${cls.id}"
                                   id="class_edit_${cls.id}"
                                   ${isChecked ? 'checked' : ''}>
                            <label class="form-check-label" for="class_edit_${cls.id}">
                                ${cls.schoolclass} ${cls.arm ? '(' + cls.arm + ')' : ''}
                            </label>
                        </div>
                    </div>`;
            });

            html += '</div>';
            container.innerHTML = html;
        } else {
            container.innerHTML = '<p class="text-muted text-center mb-0">No classes assigned to this subject.</p>';
        }
    })
    .catch(error => {
        console.error('Error loading classes:', error);
        container.innerHTML = '<p class="text-danger text-center mb-0">Error loading classes. Please try again.</p>';
    });
}

function submitAddForm() {
    const form = document.getElementById('add-exam-form');
    const submitBtn = document.getElementById('add-btn');
    const originalText = submitBtn.textContent;

    // Validate class selection
    const classCheckboxes = form.querySelectorAll('input[name="schoolclass_ids[]"]:checked');
    if (classCheckboxes.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Please select at least one class.',
            timer: 3000
        });
        return;
    }

    const formData = new FormData(form);

    // Log form data for debugging
    console.log('Add form data:');
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }

    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating...';
    submitBtn.disabled = true;

    fetch('{{ route('exams.store') }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => {
        console.log('Add response status:', response.status); // Debug
        return response.json();
    })
    .then(data => {
        console.log('Add response data:', data); // Debug
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message || 'Exam created successfully!',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('addExamModal'));
                if (modal) modal.hide();
                form.reset();

                // Reset class container
                document.getElementById('addClassContainer').innerHTML =
                    '<p class="text-muted text-center mb-0">Select a subject first...</p>';

                // Reload page
                window.location.reload();
            });
        } else {
            let errorMsg = 'An error occurred.';
            if (data.errors) {
                errorMsg = Object.values(data.errors).flat().join('<br>');
            } else if (data.message) {
                errorMsg = data.message;
            }
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: errorMsg,
                timer: 5000
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred. Please try again.',
            timer: 3000
        });
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

function submitEditForm() {
    const form = document.getElementById('edit-exam-form');
    const examId = document.getElementById('edit-id-field').value;
    const submitBtn = document.getElementById('update-btn');
    const originalText = submitBtn.textContent;

    if (!examId) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Invalid exam ID.',
            timer: 3000
        });
        return;
    }

    // Validate class selection
    const classCheckboxes = form.querySelectorAll('input[name="schoolclass_ids[]"]:checked');
    if (classCheckboxes.length === 0) {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Please select at least one class.',
            timer: 3000
        });
        return;
    }

    const formData = new FormData(form);

    // Log form data for debugging
    console.log('Edit form data for exam', examId, ':');
    for (let [key, value] of formData.entries()) {
        console.log(key + ': ' + value);
    }

    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';
    submitBtn.disabled = true;

    // Use the proper PUT route
    fetch(`/exams/${examId}`, {
        method: 'POST', // Use POST with _method override
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Update response status:', response.status); // Debug
        return response.json();
    })
    .then(data => {
        console.log('Update response data:', data); // Debug
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: data.message || 'Exam updated successfully!',
                timer: 2000,
                showConfirmButton: false
            }).then(() => {
                const modal = bootstrap.Modal.getInstance(document.getElementById('editModal'));
                if (modal) modal.hide();

                // Reload page
                window.location.reload();
            });
        } else {
            let errorMsg = 'An error occurred.';
            if (data.errors) {
                errorMsg = Object.values(data.errors).flat().join('<br>');
            } else if (data.message) {
                errorMsg = data.message;
            }
            Swal.fire({
                icon: 'error',
                title: 'Error',
                html: errorMsg,
                timer: 5000
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'An error occurred. Please try again.',
            timer: 3000
        });
    })
    .finally(() => {
        submitBtn.textContent = originalText;
        submitBtn.disabled = false;
    });
}

function deleteExam(examId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Deleting...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/exams/${examId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => {
                console.log('Delete response status:', response.status); // Debug
                return response.json();
            })
            .then(data => {
                console.log('Delete response data:', data); // Debug
                Swal.close();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: data.message || 'Exam deleted successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to delete exam.',
                        timer: 3000
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to delete exam. Please try again.',
                    timer: 3000
                });
            });
        }
    });
}

function deleteMultiple() {
    const checkedBoxes = document.querySelectorAll('input[name="chk_child"]:checked');
    if (checkedBoxes.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'No Selection',
            text: 'Please select at least one exam to delete.',
            timer: 3000
        });
        return;
    }

    const ids = Array.from(checkedBoxes)
        .map(cb => cb.closest('td').dataset.id)
        .filter(id => id);

    Swal.fire({
        title: `Delete ${ids.length} exam(s)?`,
        text: "This action cannot be undone!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete them!',
        cancelButtonText: 'Cancel',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Deleting...',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch(`/exams/bulk-destroy`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ ids: ids })
            })
            .then(response => {
                console.log('Bulk delete response status:', response.status); // Debug
                return response.json();
            })
            .then(data => {
                console.log('Bulk delete response data:', data); // Debug
                Swal.close();
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Deleted!',
                        text: data.message || 'Exams deleted successfully!',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message || 'Failed to delete exams.',
                        timer: 3000
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Failed to delete exams. Please try again.',
                    timer: 3000
                });
            });
        }
    });
}

function toggleRemoveActions() {
    const removeActions = document.getElementById('remove-actions');
    if (removeActions) {
        const checkedBoxes = document.querySelectorAll('input[name="chk_child"]:checked');
        removeActions.classList.toggle('d-none', checkedBoxes.length === 0);
    }
}
</script>

<style>
.spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

option[style*="display: none"] {
    display: none !important;
}

.spinner-border {
    display: inline-block;
    width: 1rem;
    height: 1rem;
    vertical-align: text-bottom;
    border: 0.2em solid currentColor;
    border-right-color: transparent;
    border-radius: 50%;
    animation: spinner-border .75s linear infinite;
}

@keyframes spinner-border {
    to { transform: rotate(360deg); }
}
</style>
