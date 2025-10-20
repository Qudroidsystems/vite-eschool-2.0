@extends('layouts.master')
@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">Class Category Management</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">Class Category Management</a></li>
                                <li class="breadcrumb-item active">Categories</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End page title -->

            @if ($errors->any())
                <div class="alert alert-danger">
                    <strong>Whoops!</strong> There were some problems with your input.<br><br>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if (session('danger'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('danger') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div id="categoryList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-3">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Search categories or assessments">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header d-flex align-items-center">
                                <div class="flex-grow-1">
                                    <h5 class="card-title mb-0">Class Categories <span class="badge bg-dark-subtle text-dark ms-1">{{ $classcategories->total() }}</span></h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex flex-wrap align-items-start gap-2">
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
                                        @can('Create class-category')
                                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addCategoryModal"><i class="bi bi-plus-circle align-baseline me-1"></i> Create Category</button>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table align-middle table-row-dashed fs-6 gy-5 mb-0" id="kt_roles_view_table">
                                        <thead>
                                            <tr class="text-start text-muted fw-bold fs-7 text-uppercase gs-0">
                                                <th class="w-10px pe-2">
                                                    <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" id="checkAll" />
                                                    </div>
                                                </th>
                                                <th class="min-w-50px sort cursor-pointer" data-sort="categoryid">SN</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="category">Class Category</th>
                                                <th class="min-w-200px sort cursor-pointer" data-sort="assessments">Assessments</th>
                                                <th class="min-w-100px sort cursor-pointer" data-sort="gradetype">Grade Type</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="datereg">Date Updated</th>
                                                <th class="min-w-100px">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600 list form-check-all">
                                            @php $i = (($classcategories->currentPage() - 1) * $classcategories->perPage()) + 1; @endphp
                                            @forelse ($classcategories as $sc)
                                                @php
                                                    $assessment = $sc->assessments->first();
                                                    $subAssessments = $assessment ? $assessment->subAssessments : collect();
                                                    $subDisplay = $subAssessments->map(function ($sub) {
                                                        return $sub->name ? $sub->name . ' (' . number_format($sub->max_score, 2) . ')' : number_format($sub->max_score, 2);
                                                    })->implode(', ');
                                                    $assessmentData = $assessment ? [
                                                        'name' => $assessment->name,
                                                        'subAssessments' => $assessment->subAssessments->toArray()
                                                    ] : ['name' => '', 'subAssessments' => []];
                                                @endphp
                                                <tr data-url="{{ route('classcategories.deleteclasscategory', ['classcategoryid' => $sc->id]) }}" data-edit-data="{{ json_encode(['id' => $sc->id, 'category' => $sc->category, 'is_senior' => $sc->is_senior, 'assessment' => $assessmentData]) }}">
                                                    <td class="id" data-id="{{ $sc->id }}">
                                                        <div class="form-check form-check-sm form-check-custom form-check-solid">
                                                            <input class="form-check-input" type="checkbox" name="chk_child" />
                                                        </div>
                                                    </td>
                                                    <td class="categoryid">{{ $i++ }}</td>
                                                    <td class="category" data-category="{{ $sc->category }}">{{ $sc->category }}</td>
                                                    <td class="assessments">
                                                        @if($assessment)
                                                            <div>
                                                                <strong>{{ $assessment->name }}</strong> ({{ number_format($assessment->max_score, 2) }})
                                                            </div>
                                                            @if($subAssessments->count() > 0)
                                                                <div class="mt-1">
                                                                    <small class="text-muted">
                                                                        Subs: {{ $subDisplay }}
                                                                    </small>
                                                                </div>
                                                            @endif
                                                        @else
                                                            No Assessment
                                                        @endif
                                                    </td>
                                                    <td class="gradetype" data-issenior="{{ $sc->is_senior ? 1 : 0 }}">
                                                        <span class="badge bg-{{ $sc->is_senior ? 'success' : 'primary' }}-subtle text-{{ $sc->is_senior ? 'success' : 'primary' }}">
                                                            {{ $sc->is_senior ? 'Senior' : 'Junior' }}
                                                        </span>
                                                    </td>
                                                    <td class="datereg">{{ $sc->updated_at->format('Y-m-d') }}</td>
                                                    <td>
                                                        <ul class="d-flex gap-2 list-unstyled mb-0">
                                                            @can('Update class-category')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn"><i class="ph-pencil"></i></a>
                                                                </li>
                                                            @endcan
                                                            @can('Delete class-category')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn"><i class="ph-trash"></i></a>
                                                                </li>
                                                            @endcan
                                                        </ul>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="noresult" style="display: block;">No results found</td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="d-flex justify-content-end">
                                    {{ $classcategories->links() }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add Category Modal -->
            <div id="addCategoryModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Add Class Category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="add-category-form">
                            <div class="modal-body">
                                <input type="hidden" id="add-id-field" name="id">
                                <div class="mb-3">
                                    <label for="category" class="form-label">Class Category Name</label>
                                    <input type="text" name="category" id="category" class="form-control" placeholder="Enter category name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Grade Type</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_senior" id="junior" value="0" checked>
                                            <label class="form-check-label" for="junior">Junior (A, B, C, D, F)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_senior" id="senior" value="1">
                                            <label class="form-check-label" for="senior">Senior (A1, B2, B3, C4, C5, C6, D7, E8, F9)</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="assessment_name" class="form-label">Assessment Name</label>
                                    <input type="text" name="assessments[0][name]" id="assessment_name" class="form-control" placeholder="Enter assessment name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Sub Assessments</label>
                                    <div class="sub-assessments-container mb-2" id="add-sub-container">
                                        <!-- Dynamic sub-assessment fields added here -->
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="add-sub-btn">
                                        <i class="bi bi-plus-circle me-1"></i>Add Sub Assessment
                                    </button>
                                </div>
                                <div class="alert alert-danger d-none" id="alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="add-btn" disabled>Add Category</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit Category Modal -->
            <div id="editModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Edit Class Category</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="edit-category-form">
                            <div class="modal-body">
                                <input type="hidden" name="id" id="edit-id-field">
                                <div class="mb-3">
                                    <label for="edit-category" class="form-label">Class Category Name</label>
                                    <input type="text" name="category" id="edit-category" class="form-control" placeholder="Enter category name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Grade Type</label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_senior" id="edit-junior" value="0">
                                            <label class="form-check-label" for="edit-junior">Junior (A, B, C, D, F)</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_senior" id="edit-senior" value="1">
                                            <label class="form-check-label" for="edit-senior">Senior (A1, B2, B3, C4, C5, C6, D7, E8, F9)</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="edit-assessment_name" class="form-label">Assessment Name</label>
                                    <input type="text" name="assessments[0][name]" id="edit-assessment_name" class="form-control" placeholder="Enter assessment name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Sub Assessments</label>
                                    <div class="sub-assessments-container mb-2" id="edit-sub-container">
                                        <!-- Dynamic sub-assessment fields added here -->
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="edit-sub-btn">
                                        <i class="bi bi-plus-circle me-1"></i>Add Sub Assessment
                                    </button>
                                </div>
                                <div class="alert alert-danger d-none" id="edit-alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="update-btn" disabled>Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div id="deleteRecordModal" class="modal fade" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-body text-center">
                            <h4>Are you sure?</h4>
                            <p>You won't be able to revert this!</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-danger" id="delete-record">Delete</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let addSubIndex = 0;
    let editSubIndex = 0;
    let deleteUrl = null; // For delete modal

    const addForm = document.getElementById('add-category-form');
    const addBtn = document.getElementById('add-btn');
    const addAlert = document.getElementById('alert-error-msg');

    const editForm = document.getElementById('edit-category-form');
    const editBtn = document.getElementById('update-btn');
    const editAlert = document.getElementById('edit-alert-error-msg');

    const deleteModal = new bootstrap.Modal(document.getElementById('deleteRecordModal'));
    const deleteRecordBtn = document.getElementById('delete-record');

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const jsonHeaders = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    };
    const postHeaders = {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': csrfToken
    };

    // Function to validate add form and toggle button
    function validateAddForm() {
        const category = document.getElementById('category').value.trim();
        const assessmentName = document.getElementById('assessment_name').value.trim();
        const isSenior = document.querySelector('input[name="is_senior"]:checked');
        const subRows = document.querySelectorAll('#add-sub-container .sub-assessment-row');
        let validSubs = 0;

        subRows.forEach(row => {
            const maxScoreInput = row.querySelector('input[name$="[max_score]"]');
            const maxScore = parseFloat(maxScoreInput.value);
            if (!isNaN(maxScore) && maxScore >= 0) {
                validSubs++;
            }
        });

        const isValid = category && assessmentName && isSenior && validSubs > 0;
        addBtn.disabled = !isValid;
        addBtn.classList.toggle('disabled', !isValid);
    }

    // Function to validate edit form and toggle button
    function validateEditForm() {
        const category = document.getElementById('edit-category').value.trim();
        const assessmentName = document.getElementById('edit-assessment_name').value.trim();
        const isSenior = document.querySelector('input[name="is_senior"]:checked');
        const subRows = document.querySelectorAll('#edit-sub-container .sub-assessment-row');
        let validSubs = 0;

        subRows.forEach(row => {
            const maxScoreInput = row.querySelector('input[name$="[max_score]"]');
            const maxScore = parseFloat(maxScoreInput.value);
            if (!isNaN(maxScore) && maxScore >= 0) {
                validSubs++;
            }
        });

        const isValid = category && assessmentName && isSenior && validSubs > 0;
        editBtn.disabled = !isValid;
        editBtn.classList.toggle('disabled', !isValid);
    }

    // Function to add sub-assessment field
    function addSubAssessment(containerId, subData = null, isEdit = false) {
        const container = document.getElementById(containerId);
        const currentIndex = isEdit ? editSubIndex++ : addSubIndex++;
        const subHtml = `
            <div class="sub-assessment-row row mb-2" data-index="${currentIndex}">
                <div class="col-md-5">
                    <input type="text" name="assessments[0][sub_assessments][${currentIndex}][name]" class="form-control" placeholder="Sub Assessment Name" ${subData && subData.name ? `value="${subData.name}"` : ''}>
                </div>
                <div class="col-md-4">
                    <input type="number" name="assessments[0][sub_assessments][${currentIndex}][max_score]" class="form-control" placeholder="Max Score" min="0" step="0.01" ${subData && subData.max_score ? `value="${subData.max_score}"` : ''} required>
                </div>
                <div class="col-md-3">
                    <button type="button" class="btn btn-outline-danger w-100" onclick="this.closest('.sub-assessment-row').remove(); validate${isEdit ? 'Edit' : 'Add'}Form();">
                        <i class="bi bi-trash"></i>
                    </button>
                </div>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', subHtml);
        if (isEdit) {
            validateEditForm();
        } else {
            validateAddForm();
        }
    }

    // Event listeners for dynamic validation
    function attachValidationListeners(containerId, isAdd = true) {
        const inputs = document.querySelectorAll(`${containerId} input`);
        inputs.forEach(input => {
            input.addEventListener('input', () => {
                if (isAdd) {
                    validateAddForm();
                } else {
                    validateEditForm();
                }
            });
        });
    }

    // Add sub button for add modal
    document.getElementById('add-sub-btn').addEventListener('click', function() {
        addSubAssessment('add-sub-container', null, false);
        attachValidationListeners('#add-sub-container', true);
    });

    // Add sub button for edit modal
    document.getElementById('edit-sub-btn').addEventListener('click', function() {
        addSubAssessment('edit-sub-container', null, true);
        attachValidationListeners('#edit-sub-container', false);
    });

    // Add initial sub for add modal and attach listeners
    addSubAssessment('add-sub-container', null, false);
    attachValidationListeners('#add-sub-container', true);
    validateAddForm();

    // Form submissions for add
    addForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (addBtn.disabled) return;

        const formData = new FormData(this);
        const data = {
            category: formData.get('category'),
            is_senior: formData.get('is_senior'),
            assessments: [{
                name: formData.get('assessments[0][name]'),
                sub_assessments: []
            }]
        };

        // Collect sub-assessments (only valid ones)
        const subRows = document.querySelectorAll('#add-sub-container .sub-assessment-row');
        subRows.forEach(row => {
            const nameInput = row.querySelector('input[name$="[name]"]');
            const maxScoreInput = row.querySelector('input[name$="[max_score]"]');
            const name = nameInput.value.trim() || null;
            const maxScore = parseFloat(maxScoreInput.value);
            if (!isNaN(maxScore) && maxScore >= 0) {
                data.assessments[0].sub_assessments.push({
                    name: name,
                    max_score: maxScore
                });
            }
        });

        if (data.assessments[0].sub_assessments.length === 0) {
            addAlert.textContent = 'At least one sub-assessment with a valid max score (>= 0) is required.';
            addAlert.classList.remove('d-none');
            return;
        }
        addAlert.classList.add('d-none');

        console.log('Submitting add data:', data);

        // AJAX submit
        fetch('{{ route("classcategories.store") }}', {
            method: 'POST',
            headers: jsonHeaders,
            body: JSON.stringify(data)
        }).then(async response => {
            let result;
            try {
                result = await response.json();
            } catch (parseErr) {
                // If JSON parse fails, log the text response for debugging
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response. Check console for details.');
            }
            if (!response.ok) {
                throw result;
            }
            return result;
        }).then(result => {
            if (result.success) {
                location.reload();
            } else {
                let errorMsg = result.message || 'An unknown error occurred.';
                if (result.errors) {
                    const firstError = Object.values(result.errors).flat().shift();
                    errorMsg = firstError || errorMsg;
                }
                addAlert.textContent = errorMsg;
                addAlert.classList.remove('d-none');
            }
        }).catch(error => {
            console.error('Add error:', error);
            addAlert.textContent = error.message || 'Failed to add category.';
            addAlert.classList.remove('d-none');
        });
    });

    // Edit form submission
    editForm.addEventListener('submit', function(e) {
        e.preventDefault();
        if (editBtn.disabled) return;

        const formData = new FormData(this);
        const data = {
            id: formData.get('id'),
            category: formData.get('category'),
            is_senior: formData.get('is_senior'),
            assessments: [{
                name: formData.get('assessments[0][name]'),
                sub_assessments: []
            }]
        };

        const subRows = document.querySelectorAll('#edit-sub-container .sub-assessment-row');
        subRows.forEach(row => {
            const nameInput = row.querySelector('input[name$="[name]"]');
            const maxScoreInput = row.querySelector('input[name$="[max_score]"]');
            const name = nameInput.value.trim() || null;
            const maxScore = parseFloat(maxScoreInput.value);
            if (!isNaN(maxScore) && maxScore >= 0) {
                data.assessments[0].sub_assessments.push({
                    name: name,
                    max_score: maxScore
                });
            }
        });

        if (data.assessments[0].sub_assessments.length === 0) {
            editAlert.textContent = 'At least one sub-assessment with a valid max score (>= 0) is required.';
            editAlert.classList.remove('d-none');
            return;
        }
        editAlert.classList.add('d-none');

        console.log('Submitting edit data:', data);

        fetch('{{ route("classcategories.updateclasscategory") }}', {
            method: 'POST',
            headers: jsonHeaders,
            body: JSON.stringify(data)
        }).then(async response => {
            let result;
            try {
                result = await response.json();
            } catch (parseErr) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response. Check console for details.');
            }
            if (!response.ok) {
                throw result;
            }
            return result;
        }).then(result => {
            if (result.success) {
                location.reload();
            } else {
                let errorMsg = result.message || 'An unknown error occurred.';
                if (result.errors) {
                    const firstError = Object.values(result.errors).flat().shift();
                    errorMsg = firstError || errorMsg;
                }
                editAlert.textContent = errorMsg;
                editAlert.classList.remove('d-none');
            }
        }).catch(error => {
            console.error('Edit error:', error);
            editAlert.textContent = error.message || 'Failed to update category.';
            editAlert.classList.remove('d-none');
        });
    });

    // Edit button click
    document.querySelectorAll('.edit-item-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            const editDataStr = row.getAttribute('data-edit-data') || '{}';
            const editData = JSON.parse(editDataStr);
            console.log('Edit data:', editData); // Debug: Check if data is loaded

            document.getElementById('edit-id-field').value = editData.id || '';
            document.getElementById('edit-category').value = editData.category || '';
            const seniorValue = editData.is_senior ? 1 : 0;
            const seniorRadio = document.querySelector(`input[name="is_senior"][value="${seniorValue}"]`);
            if (seniorRadio) seniorRadio.checked = true;
            document.getElementById('edit-assessment_name').value = editData.assessment ? editData.assessment.name : '';

            // Clear existing subs and reset index
            document.getElementById('edit-sub-container').innerHTML = '';
            editSubIndex = 0;

            const subAssessments = editData.assessment && editData.assessment.subAssessments ? editData.assessment.subAssessments : [];
            if (subAssessments.length > 0) {
                subAssessments.forEach(subData => {
                    addSubAssessment('edit-sub-container', subData, true);
                });
            } else {
                addSubAssessment('edit-sub-container', null, true);
            }

            // Attach listeners after populating
            attachValidationListeners('#edit-sub-container', false);
            // Re-attach main edit fields listeners
            ['#edit-category', '#edit-assessment_name'].forEach(selector => {
                document.querySelectorAll(selector).forEach(input => {
                    input.addEventListener('input', validateEditForm);
                });
            });
            document.querySelectorAll('input[name="is_senior"]').forEach(radio => {
                radio.addEventListener('change', validateEditForm);
            });
            validateEditForm();

            new bootstrap.Modal(document.getElementById('editModal')).show();
        });
    });

    // Delete button click - show modal
    document.querySelectorAll('.remove-item-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const row = this.closest('tr');
            deleteUrl = row.getAttribute('data-url');
            if (deleteUrl) {
                deleteModal.show();
            } else {
                console.error('Delete URL not found');
            }
        });
    });

    // Delete record button in modal
    deleteRecordBtn.addEventListener('click', function() {
        if (!deleteUrl) return;

        fetch(deleteUrl, { 
            method: 'POST', 
            headers: postHeaders
        }).then(async response => {
            let result;
            try {
                result = await response.json();
            } catch (parseErr) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server returned non-JSON response. Check console for details.');
            }
            if (!response.ok) {
                throw result;
            }
            return result;
        }).then(result => {
            if (result.success) {
                location.reload();
            } else {
                alert(result.message || 'Failed to delete category.');
            }
            deleteModal.hide();
            deleteUrl = null;
        }).catch(error => {
            console.error('Delete error:', error);
            alert(error.message || 'Failed to delete category.');
            deleteModal.hide();
            deleteUrl = null;
        });
    });

    // Close delete modal reset
    document.getElementById('deleteRecordModal').addEventListener('hidden.bs.modal', function() {
        deleteUrl = null;
    });

    // Attach listeners to main form fields for add
    ['#category', '#assessment_name'].forEach(selector => {
        document.querySelectorAll(selector).forEach(input => {
            input.addEventListener('input', validateAddForm);
        });
    });
    document.querySelectorAll('input[name="is_senior"]').forEach(radio => {
        radio.addEventListener('change', () => {
            validateAddForm();
            validateEditForm(); // For both modals
        });
    });
});
</script>
@endsection