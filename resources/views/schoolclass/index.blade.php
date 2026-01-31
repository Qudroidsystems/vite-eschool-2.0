@extends('layouts.master')
@section('content')

<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Start page title -->
            <div class="row">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">School Class Management</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item"><a href="javascript:void(0);">School Class Management</a></li>
                                <li class="breadcrumb-item active">School Classes</li>
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

            <!-- Debug Panel -->
            <div class="card mb-3 bg-light">
                <div class="card-body">
                    <h6 class="mb-2">Debug Info:</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted">Total Classes:</small>
                            <div class="fw-bold">{{ $all_classes->total() }}</div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Total Arms:</small>
                            <div class="fw-bold">{{ $arms->count() }}</div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Total Categories:</small>
                            <div class="fw-bold">{{ $classcategories->count() }}</div>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Current Page:</small>
                            <div class="fw-bold">{{ $all_classes->currentPage() }}</div>
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-info" onclick="testFormCapture()">
                            <i class="bi bi-bug me-1"></i> Test Form Capture
                        </button>
                        <button type="button" class="btn btn-sm btn-warning" onclick="testSubmission()">
                            <i class="bi bi-send-check me-1"></i> Test Submission
                        </button>
                    </div>
                </div>
            </div>

            <div id="schoolClassList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-3">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" placeholder="Search school classes" value="{{ request()->query('search') }}">
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
                                    <h5 class="card-title mb-0">School Classes <span class="badge bg-dark-subtle text-dark ms-1">{{ $all_classes->total() }}</span></h5>
                                </div>
                                <div class="flex-shrink-0">
                                    <div class="d-flex flex-wrap align-items-start gap-2">
                                        <button class="btn btn-subtle-danger d-none" id="remove-actions" onclick="deleteMultiple()"><i class="ri-delete-bin-2-line"></i></button>
                                        @can('Create school-class')
                                            <button type="button" class="btn btn-primary add-btn" data-bs-toggle="modal" data-bs-target="#addSchoolClassModal"><i class="bi bi-plus-circle align-baseline me-1"></i> Create School Class</button>
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
                                                    <div class="form-check form-check-sm form-check-solid me-3">
                                                        <input class="form-check-input" type="checkbox" id="checkAll" />
                                                    </div>
                                                </th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="schoolclassid">SN</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="schoolclass">School Class</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="arm">Arm</th>
                                                <th class="min-w-125px sort cursor-pointer" data-sort="classcategory">Category</th>
                                                <th class="min-w-100px">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody class="fw-semibold text-gray-600 list form-check-all">
                                            @php $i = ($all_classes->currentPage() - 1) * $all_classes->perPage() @endphp
                                            @forelse ($all_classes as $class)
                                                <tr>
                                                    <td class="id" data-id="{{ $class->id }}">
                                                        <div class="form-check form-check-sm form-check-solid">
                                                            <input class="form-check-input" type="checkbox" name="chk_child" />
                                                        </div>
                                                    </td>
                                                    <td class="schoolclassid">{{ ++$i }}</td>
                                                    <td class="schoolclass" data-schoolclass="{{ $class->schoolclass }}">
                                                        {{ $class->schoolclass }}
                                                        <small class="text-muted d-block">ID: {{ $class->id }}</small>
                                                    </td>
                                                    <td class="arm" data-arm-id="{{ $class->arm_id }}" data-arm="{{ $class->arm_name }}">
                                                        {{ $class->arm_name }}
                                                        <small class="text-muted d-block">Arm ID: {{ $class->arm_id }}</small>
                                                    </td>
                                                    <td class="classcategory" data-category-ids="{{ $class->classcategoryids }}" data-classcategory="{{ $class->classcategory }}">
                                                        {{ $class->classcategory }}
                                                        <small class="text-muted d-block">Category IDs: {{ $class->classcategoryids }}</small>
                                                    </td>
                                                    <td>
                                                        <ul class="d-flex gap-2 list-unstyled mb-0">
                                                            @can('Update school-class')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn" title="Edit">
                                                                        <i class="ph-pencil"></i>
                                                                    </a>
                                                                </li>
                                                            @endcan
                                                            @can('Delete school-class')
                                                                <li>
                                                                    <a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn" title="Delete">
                                                                        <i class="ph-trash"></i>
                                                                    </a>
                                                                </li>
                                                            @endcan>
                                                        </ul>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center py-4">
                                                        <div class="alert alert-warning">
                                                            <i class="ph-warning-circle me-2"></i> No school classes found
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                                <div class="row mt-3 align-items-center" id="pagination-element">
                                    <div class="col-sm">
                                        <div class="text-muted text-center text-sm-start">
                                            Showing <span class="fw-semibold">{{ $all_classes->count() }}</span> of <span class="fw-semibold">{{ $all_classes->total() }}</span> Results
                                        </div>
                                    </div>
                                    <div class="col-sm-auto mt-3 mt-sm-0">
                                        {{ $all_classes->links() }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Add School Class Modal -->
            <div id="addSchoolClassModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="exampleModalLabel" class="modal-title">Add School Class</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="add-schoolclass-form">
                            @csrf
                            <div class="modal-body">
                                <input type="hidden" id="add-id-field" name="id">

                                <div class="mb-3">
                                    <label for="add-schoolclass" class="form-label">School Class <span class="text-danger">*</span></label>
                                    <input type="text" id="add-schoolclass" name="schoolclass" class="form-control" placeholder="Enter school class" required>
                                    <div class="form-text">Enter the class name (e.g., JSS 1, SS 1)</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Select Arm(s) <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap gap-3" id="add-arm-checkboxes">
                                        @if($arms->count() > 0)
                                            @foreach ($arms as $arm)
                                                <div class="form-check form-check-outline form-check-primary">
                                                    <input class="form-check-input add-arm-checkbox" type="checkbox"
                                                           value="{{ $arm->id }}"
                                                           name="arm_id[]"
                                                           id="add-arm-{{ $arm->id }}"
                                                           data-arm="{{ $arm->arm }}">
                                                    <label class="form-check-label" for="add-arm-{{ $arm->id }}">
                                                        {{ $arm->arm }}
                                                        <small class="text-muted">(ID: {{ $arm->id }})</small>
                                                    </label>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="alert alert-danger">
                                                No arms found! Please add arms first.
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-text">Select one or more arms for this class</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Select Category(s) <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap gap-3" id="add-category-checkboxes">
                                        @if($classcategories->count() > 0)
                                            @foreach ($classcategories as $category)
                                                <div class="form-check form-check-outline form-check-primary">
                                                    <input class="form-check-input add-category-checkbox" type="checkbox"
                                                           value="{{ $category->id }}"
                                                           name="classcategoryid[]"
                                                           id="add-category-{{ $category->id }}"
                                                           data-category="{{ $category->category }}">
                                                    <label class="form-check-label" for="add-category-{{ $category->id }}">
                                                        {{ $category->category }}
                                                        <small class="text-muted">(ID: {{ $category->id }})</small>
                                                    </label>
                                                </div>
                                            @endforeach
                                        @else
                                            <div class="alert alert-danger">
                                                No categories found! Please add categories first.
                                            </div>
                                        @endif
                                    </div>
                                    <div class="form-text">Select one or more categories for this class</div>
                                </div>

                                <div class="alert alert-warning d-none" id="add-validation-errors">
                                    <h6 class="alert-heading">Validation Errors:</h6>
                                    <ul id="add-error-list"></ul>
                                </div>

                                <div class="alert alert-danger d-none" id="add-alert-error-msg"></div>

                                <!-- Debug Info -->
                                <div class="alert alert-secondary d-none" id="add-debug-info">
                                    <h6 class="alert-heading">Debug Information:</h6>
                                    <pre id="add-debug-content" class="mb-0" style="font-size: 11px;"></pre>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="button" class="btn btn-warning" onclick="debugFormData('add')">Debug Form</button>
                                <button type="submit" class="btn btn-primary" id="add-btn">Add Class</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Edit School Class Modal -->
            <div id="editModal" class="modal fade" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="editModalLabel" class="modal-title">Edit School Class</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form class="tablelist-form" autocomplete="off" id="edit-schoolclass-form">
                            @csrf
                            @method('PUT')
                            <div class="modal-body">
                                <input type="hidden" id="edit-id-field" name="id">

                                <div class="mb-3">
                                    <label for="edit-schoolclass" class="form-label">School Class <span class="text-danger">*</span></label>
                                    <input type="text" id="edit-schoolclass" name="schoolclass" class="form-control" placeholder="Enter school class" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Select Arm <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap gap-3" id="edit-arm-radios">
                                        @foreach ($arms as $arm)
                                            <div class="form-check form-check-outline form-check-primary">
                                                <input class="form-check-input edit-arm-radio" type="radio"
                                                       value="{{ $arm->id }}"
                                                       name="arm_id"
                                                       id="edit-arm-{{ $arm->id }}">
                                                <label class="form-check-label" for="edit-arm-{{ $arm->id }}">
                                                    {{ $arm->arm }}
                                                    <small class="text-muted">(ID: {{ $arm->id }})</small>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Select Category(s) <span class="text-danger">*</span></label>
                                    <div class="d-flex flex-wrap gap-3" id="edit-category-checkboxes">
                                        @foreach ($classcategories as $category)
                                            <div class="form-check form-check-outline form-check-primary">
                                                <input class="form-check-input edit-category-checkbox" type="checkbox"
                                                       value="{{ $category->id }}"
                                                       name="classcategoryid[]"
                                                       id="edit-category-{{ $category->id }}">
                                                <label class="form-check-label" for="edit-category-{{ $category->id }}">
                                                    {{ $category->category }}
                                                    <small class="text-muted">(ID: {{ $category->id }})</small>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="alert alert-warning d-none" id="edit-validation-errors">
                                    <h6 class="alert-heading">Validation Errors:</h6>
                                    <ul id="edit-error-list"></ul>
                                </div>

                                <div class="alert alert-danger d-none" id="edit-alert-error-msg"></div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary" id="update-btn">Update</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Delete Confirmation Modal -->
            <div id="deleteRecordModal" class="modal fade" tabindex="-1" aria-labelledby="deleteRecordModalLabel" aria-hidden="true" data-bs-backdrop="static">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 id="deleteRecordModalLabel" class="modal-title">Confirm Deletion</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body text-center">
                            <h4 id="delete-class-name">Are you sure?</h4>
                            <p id="delete-class-desc">You won't be able to revert this!</p>
                            <div class="alert alert-info mt-2">
                                <small>Class ID: <span id="delete-class-id">-</span></small>
                            </div>
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

<style>
    .form-check-input {
        width: 1.5em;
        height: 1.5em;
        margin-top: 0.15em;
    }
    .form-check-label {
        font-size: 1.1em;
        line-height: 1.5em;
        margin-left: 0.5em;
    }
    #deleteRecordModal {
        z-index: 1055;
    }
</style>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// Global variables
let currentEditId = null;
let currentDeleteId = null;
let addModal = null;
let editModal = null;
let deleteModal = null;

// Debug functions
function showDebugInfo(type, data) {
    const debugDiv = document.getElementById(`${type}-debug-info`);
    const debugContent = document.getElementById(`${type}-debug-content`);

    debugContent.textContent = JSON.stringify(data, null, 2);
    debugDiv.classList.remove('d-none');
}

function hideDebugInfo(type) {
    document.getElementById(`${type}-debug-info`).classList.add('d-none');
}

function debugFormData(type) {
    const form = document.getElementById(`${type}-schoolclass-form`);
    const formData = new FormData(form);
    const data = {};

    console.log(`=== ${type.toUpperCase()} FORM DEBUG ===`);
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
        data[key] = value;
    }

    // Also log checkbox states
    if (type === 'add') {
        const selectedArms = document.querySelectorAll('.add-arm-checkbox:checked');
        const selectedCategories = document.querySelectorAll('.add-category-checkbox:checked');
        console.log('Selected Arms:', Array.from(selectedArms).map(cb => ({id: cb.value, name: cb.dataset.arm})));
        console.log('Selected Categories:', Array.from(selectedCategories).map(cb => ({id: cb.value, name: cb.dataset.category})));

        data.selectedArms = Array.from(selectedArms).map(cb => ({id: cb.value, name: cb.dataset.arm}));
        data.selectedCategories = Array.from(selectedCategories).map(cb => ({id: cb.value, name: cb.dataset.category}));
    }

    showDebugInfo(type, data);
}

// Test function to verify data capture
function testFormCapture() {
    console.log('=== TEST FORM CAPTURE ===');

    // Get all form elements
    const form = document.getElementById('add-schoolclass-form');
    const elements = form.elements;

    console.log('Total form elements:', elements.length);

    // Check each element
    console.log('=== FORM ELEMENTS ===');
    for (let element of elements) {
        if (element.name) {
            console.log(`Element: name="${element.name}", type="${element.type}", value="${element.value}", checked="${element.checked}"`);
        }
    }

    // Check checkboxes specifically
    const armCheckboxes = document.querySelectorAll('.add-arm-checkbox');
    const categoryCheckboxes = document.querySelectorAll('.add-category-checkbox');

    console.log('Arm checkboxes found:', armCheckboxes.length);
    console.log('Category checkboxes found:', categoryCheckboxes.length);

    // Test what FormData captures
    const testFormData = new FormData(form);
    console.log('=== FormData Capture Test ===');
    const capturedData = {};
    for (let [key, value] of testFormData.entries()) {
        console.log(`${key}: ${value}`);
        if (capturedData[key]) {
            if (Array.isArray(capturedData[key])) {
                capturedData[key].push(value);
            } else {
                capturedData[key] = [capturedData[key], value];
            }
        } else {
            capturedData[key] = value;
        }
    }
    console.log('Captured Data Object:', capturedData);

    // Check which checkboxes are checked
    console.log('=== CHECKED CHECKBOXES ===');
    armCheckboxes.forEach((cb, index) => {
        console.log(`Arm ${index}: ID=${cb.value}, Checked=${cb.checked}, In FormData=${capturedData['arm_id[]'] ? 'YES' : 'NO'}`);
    });

    categoryCheckboxes.forEach((cb, index) => {
        console.log(`Category ${index}: ID=${cb.value}, Checked=${cb.checked}, In FormData=${capturedData['classcategoryid[]'] ? 'YES' : 'NO'}`);
    });

    alert('Check browser console for form capture test results');
}

// Test submission with hardcoded data
async function testSubmission() {
    console.log('=== TEST SUBMISSION ===');

    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Create test data
    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('schoolclass', 'TEST CLASS');
    formData.append('arm_id[]', '1');
    formData.append('arm_id[]', '2');
    formData.append('classcategoryid[]', '1');
    formData.append('classcategoryid[]', '2');

    console.log('Test Data to Send:');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}: ${value}`);
    }

    try {
        const response = await axios.post('{{ route("schoolclass.store") }}', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        console.log('Test Success:', response.data);
        Swal.fire('Success!', 'Test submission successful!', 'success');
    } catch (error) {
        console.log('Test Error:', error.response);
        Swal.fire('Error!', 'Test submission failed. Check console.', 'error');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // Initialize modals
    addModal = new bootstrap.Modal(document.getElementById('addSchoolClassModal'));
    editModal = new bootstrap.Modal(document.getElementById('editModal'));
    deleteModal = new bootstrap.Modal(document.getElementById('deleteRecordModal'));

    // Get CSRF token
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

    console.log('=== PAGE LOADED ===');
    console.log('CSRF Token:', csrfToken);
    console.log('Route URLs:', {
        store: '{{ route("schoolclass.store") }}',
        update: '{{ route("schoolclass.update", ":id") }}',
        destroy: '{{ route("schoolclass.destroy", ":id") }}'
    });

    // Handle Add Form Submission - FIXED VERSION
    const addForm = document.getElementById('add-schoolclass-form');
    if (addForm) {
        addForm.addEventListener('submit', async function (e) {
            e.preventDefault();

            console.log('=== ADD FORM SUBMISSION STARTED ===');

            // Validate checkboxes
            const selectedArms = document.querySelectorAll('.add-arm-checkbox:checked');
            const selectedCategories = document.querySelectorAll('.add-category-checkbox:checked');

            console.log('Selected Arms Count:', selectedArms.length);
            console.log('Selected Categories Count:', selectedCategories.length);

            // Log each selected arm
            selectedArms.forEach((arm, index) => {
                console.log(`Arm ${index + 1}: ID=${arm.value}, Name=${arm.dataset.arm}`);
            });

            // Log each selected category
            selectedCategories.forEach((category, index) => {
                console.log(`Category ${index + 1}: ID=${category.value}, Name=${category.dataset.category}`);
            });

            if (selectedArms.length === 0) {
                Swal.fire('Error!', 'Please select at least one arm.', 'error');
                return;
            }

            if (selectedCategories.length === 0) {
                Swal.fire('Error!', 'Please select at least one category.', 'error');
                return;
            }

            // Create form data PROPERLY
            const properFormData = new FormData();

            // Add CSRF token
            properFormData.append('_token', csrfToken);

            // Add schoolclass
            const schoolclassInput = document.getElementById('add-schoolclass');
            properFormData.append('schoolclass', schoolclassInput.value);
            console.log('Added schoolclass:', schoolclassInput.value);

            // Add selected arms - CRITICAL: Use the correct format
            selectedArms.forEach((arm, index) => {
                properFormData.append('arm_id[]', arm.value);
                console.log(`Added arm_id[${index}]:`, arm.value);
            });

            // Add selected categories - CRITICAL: Use the correct format
            selectedCategories.forEach((category, index) => {
                properFormData.append('classcategoryid[]', category.value);
                console.log(`Added classcategoryid[${index}]:`, category.value);
            });

            // Log the final form data
            console.log('=== FINAL FORM DATA TO SEND ===');
            const finalData = {};
            for (let [key, value] of properFormData.entries()) {
                console.log(`${key}: ${value}`);
                if (finalData[key]) {
                    if (Array.isArray(finalData[key])) {
                        finalData[key].push(value);
                    } else {
                        finalData[key] = [finalData[key], value];
                    }
                } else {
                    finalData[key] = value;
                }
            }
            console.log('Final Data Object:', finalData);

            // Show debug info
            showDebugInfo('add', {
                finalData: finalData,
                selectedArms: Array.from(selectedArms).map(a => ({id: a.value, name: a.dataset.arm})),
                selectedCategories: Array.from(selectedCategories).map(c => ({id: c.value, name: c.dataset.category}))
            });

            const submitBtn = document.getElementById('add-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Adding...';

            try {
                console.log('Sending request to:', '{{ route("schoolclass.store") }}');
                console.log('Request payload:', finalData);

                // Add debug header
                properFormData.append('X-Debug-JS', JSON.stringify(finalData));

                const response = await axios.post('{{ route("schoolclass.store") }}', properFormData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });

                console.log('=== ADD SUCCESS ===');
                console.log('Response:', response.data);

                Swal.fire({
                    title: 'Success!',
                    text: response.data.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        addModal.hide();
                        addForm.reset();
                        location.reload();
                    }
                });

            } catch (error) {
                console.log('=== ADD ERROR ===');
                console.log('Full Error:', error);
                console.log('Error Response:', error.response);

                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    console.log('Validation Errors:', errors);

                    let errorMsg = '';
                    let errorList = '';

                    for (let key in errors) {
                        if (errors[key]) {
                            const fieldErrors = errors[key].join('<br>');
                            errorMsg += `<strong>${key}:</strong> ${fieldErrors}<br>`;
                            errorList += `<li><strong>${key}:</strong> ${errors[key].join(', ')}</li>`;
                        }
                    }

                    document.getElementById('add-alert-error-msg').innerHTML = errorMsg;
                    document.getElementById('add-alert-error-msg').classList.remove('d-none');

                    if (errorList) {
                        document.getElementById('add-error-list').innerHTML = errorList;
                        document.getElementById('add-validation-errors').classList.remove('d-none');
                    }

                    document.getElementById('add-alert-error-msg').scrollIntoView({ behavior: 'smooth' });

                } else if (error.response && error.response.status === 500) {
                    const errorDetails = error.response.data;
                    console.error('Server Error Details:', errorDetails);

                    Swal.fire({
                        title: 'Server Error!',
                        html: `
                            <div class="text-start">
                                <p><strong>Error:</strong> ${errorDetails.message || 'Internal Server Error'}</p>
                                ${errorDetails.error ? `<p><strong>SQL Error:</strong> ${errorDetails.error}</p>` : ''}
                                <p><strong>Data Sent:</strong></p>
                                <pre style="background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 12px;">${JSON.stringify(finalData, null, 2)}</pre>
                            </div>
                        `,
                        icon: 'error',
                        width: 600
                    });

                } else {
                    Swal.fire('Error!', error.response?.data?.message || 'Something went wrong', 'error');
                }
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }

    // Handle Edit Button Click
    document.querySelectorAll('.edit-item-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const row = this.closest('tr');
            currentEditId = row.querySelector('.id').dataset.id;
            const schoolclass = row.querySelector('.schoolclass').dataset.schoolclass;
            const armId = row.querySelector('.arm').dataset.armId;
            const categoryIdsStr = row.querySelector('.classcategory').dataset.categoryIds;

            console.log('Editing Class:', {
                id: currentEditId,
                schoolclass: schoolclass,
                armId: armId,
                categoryIds: categoryIdsStr
            });

            document.getElementById('edit-id-field').value = currentEditId;
            document.getElementById('edit-schoolclass').value = schoolclass;

            // Set arm radio
            document.querySelectorAll('#edit-arm-radios input[type="radio"]').forEach(radio => {
                radio.checked = radio.value == armId;
            });

            // Reset category checkboxes
            document.querySelectorAll('#edit-category-checkboxes input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });

            // Check the original categories
            if (categoryIdsStr) {
                const categoryIds = categoryIdsStr.split(',');
                categoryIds.forEach(catId => {
                    const checkbox = document.querySelector(`#edit-category-${catId.trim()}`);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }

            // Hide previous errors
            document.getElementById('edit-alert-error-msg').classList.add('d-none');
            document.getElementById('edit-validation-errors').classList.add('d-none');

            editModal.show();
        });
    });

    // Handle Edit Form Submission
    const editForm = document.getElementById('edit-schoolclass-form');
    if (editForm) {
        editForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            if (!currentEditId) {
                console.error('No edit ID set!');
                return;
            }

            console.log('=== EDIT FORM SUBMISSION STARTED ===');
            console.log('Editing ID:', currentEditId);

            // Validate checkboxes
            const selectedCategories = document.querySelectorAll('.edit-category-checkbox:checked');
            if (selectedCategories.length === 0) {
                Swal.fire('Error!', 'Please select at least one category.', 'error');
                return;
            }

            const formData = new FormData(editForm);

            console.log('Edit Form Data:');
            const formDataObj = {};
            for (let [key, value] of formData.entries()) {
                console.log(`${key}: ${value}`);
                if (formDataObj[key]) {
                    if (Array.isArray(formDataObj[key])) {
                        formDataObj[key].push(value);
                    } else {
                        formDataObj[key] = [formDataObj[key], value];
                    }
                } else {
                    formDataObj[key] = value;
                }
            }
            console.log('Form Data Object:', formDataObj);

            const submitBtn = document.getElementById('update-btn');
            const originalText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Updating...';

            try {
                const updateUrl = '{{ route("schoolclass.update", ":id") }}'.replace(':id', currentEditId);
                console.log('Update URL:', updateUrl);

                const response = await axios.post(updateUrl, formData, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });

                console.log('=== EDIT SUCCESS ===');
                console.log('Response:', response.data);

                Swal.fire({
                    title: 'Success!',
                    text: response.data.message,
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        editModal.hide();
                        editForm.reset();
                        location.reload();
                    }
                });

            } catch (error) {
                console.log('=== EDIT ERROR ===');
                console.log('Error:', error);
                console.log('Error Response:', error.response);

                if (error.response && error.response.status === 422) {
                    const errors = error.response.data.errors;
                    let errorMsg = '';
                    let errorList = '';

                    for (let key in errors) {
                        if (errors[key]) {
                            const fieldErrors = errors[key].join('<br>');
                            errorMsg += `<strong>${key}:</strong> ${fieldErrors}<br>`;
                            errorList += `<li><strong>${key}:</strong> ${errors[key].join(', ')}</li>`;
                        }
                    }

                    document.getElementById('edit-alert-error-msg').innerHTML = errorMsg;
                    document.getElementById('edit-alert-error-msg').classList.remove('d-none');

                    if (errorList) {
                        document.getElementById('edit-error-list').innerHTML = errorList;
                        document.getElementById('edit-validation-errors').classList.remove('d-none');
                    }

                    document.getElementById('edit-alert-error-msg').scrollIntoView({ behavior: 'smooth' });

                } else if (error.response && error.response.status === 500) {
                    const errorDetails = error.response.data;
                    console.error('Server Error Details:', errorDetails);

                    Swal.fire({
                        title: 'Server Error!',
                        html: `
                            <div class="text-start">
                                <p><strong>Error:</strong> ${errorDetails.message || 'Internal Server Error'}</p>
                                ${errorDetails.error ? `<p><strong>Details:</strong> ${errorDetails.error}</p>` : ''}
                            </div>
                        `,
                        icon: 'error'
                    });

                } else {
                    Swal.fire('Error!', error.response?.data?.message || 'Something went wrong', 'error');
                }
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
    }

    // Handle Delete Button Click
    document.querySelectorAll('.remove-item-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const row = this.closest('tr');
            currentDeleteId = row.querySelector('.id').dataset.id;
            const schoolClassName = row.querySelector('.schoolclass').dataset.schoolclass;
            const schoolClassText = row.querySelector('.schoolclass').textContent.split('\n')[0].trim();

            // Update modal text
            document.getElementById('delete-class-name').textContent = `Delete "${schoolClassText}"?`;
            document.getElementById('delete-class-desc').textContent = `Are you sure you want to delete class "${schoolClassText}"? This action cannot be undone.`;
            document.getElementById('delete-class-id').textContent = currentDeleteId;

            deleteModal.show();
        });
    });

    // Handle Delete Confirmation
    document.getElementById('delete-record').addEventListener('click', async function () {
        if (!currentDeleteId) {
            console.error('No delete ID set!');
            return;
        }

        console.log('=== DELETE CONFIRMED ===');
        console.log('Deleting ID:', currentDeleteId);

        const submitBtn = this;
        const originalText = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Deleting...';

        try {
            const destroyUrl = '{{ route("schoolclass.destroy", ":id") }}'.replace(':id', currentDeleteId);
            console.log('Delete URL:', destroyUrl);

            const response = await axios.delete(destroyUrl);

            console.log('=== DELETE SUCCESS ===');
            console.log('Response:', response.data);

            Swal.fire({
                title: 'Deleted!',
                text: response.data.message,
                icon: 'success',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    deleteModal.hide();
                    location.reload();
                }
            });

        } catch (error) {
            console.log('=== DELETE ERROR ===');
            console.log('Error:', error);
            console.log('Error Response:', error.response);

            Swal.fire({
                title: 'Error!',
                text: error.response?.data?.message || 'Something went wrong while deleting',
                icon: 'error'
            });
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    });

    // Handle Add Modal Close - Reset form
    document.getElementById('addSchoolClassModal').addEventListener('hidden.bs.modal', function () {
        if (addForm) {
            addForm.reset();
            document.getElementById('add-alert-error-msg').classList.add('d-none');
            document.getElementById('add-alert-error-msg').innerHTML = '';
            document.getElementById('add-validation-errors').classList.add('d-none');
            document.getElementById('add-error-list').innerHTML = '';
            hideDebugInfo('add');
        }
    });

    // Handle Edit Modal Close - Reset form
    document.getElementById('editModal').addEventListener('hidden.bs.modal', function () {
        if (editForm) {
            editForm.reset();
            document.getElementById('edit-alert-error-msg').classList.add('d-none');
            document.getElementById('edit-alert-error-msg').innerHTML = '';
            document.getElementById('edit-validation-errors').classList.add('d-none');
            document.getElementById('edit-error-list').innerHTML = '';
            currentEditId = null;
        }
    });

    // Handle Delete Modal Close
    document.getElementById('deleteRecordModal').addEventListener('hidden.bs.modal', function () {
        currentDeleteId = null;
    });
});
</script>
@endsection
