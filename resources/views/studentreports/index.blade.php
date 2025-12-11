@extends('layouts.master')

@section('content')
<div class="main-content">
    <div class="page-content">
        <div class="container-fluid">
            <!-- Dismissible Alert Notification -->
            <div id="selectionAlert" class="alert alert-info alert-dismissible fade show" role="alert" style="display: none; position: fixed; top: 0; left: 0; right: 0; z-index: 1050; margin: 0 auto; max-width: 90%;">
                <span id="selectionAlertText">No selections made.</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>

            <!-- Start page title -->
            <div class="row" style="margin-top: 60px;">
                <div class="col-12">
                    <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                        <h4 class="mb-sm-0">{{ $pagetitle }}</h4>
                        <div class="page-title-right">
                            <ol class="breadcrumb m-0">
                                <li class="breadcrumb-item active">Student Reports</li>
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

            @if (session('status') || session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('status') ?? session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div id="studentList">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-xxl-3 col-sm-6">
                                        <select class="form-control" id="idclass" name="schoolclassid">
                                            <option value="ALL">Select Class</option>
                                            @foreach ($schoolclasses as $class)
                                                <option value="{{ $class->id }}">{{ $class->schoolclass }} {{ $class->arm }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <select class="form-control" id="idsession" name="sessionid">
                                            <option value="ALL">Select Session</option>
                                            @foreach ($schoolsessions as $session)
                                                <option value="{{ $session->id }}">{{ $session->session }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6" id="termSelectContainer" style="display: none;">
                                        <select class="form-control" id="idterm" name="termid">
                                            <option value="ALL">Select Term</option>
                                            <option value="1">First Term</option>
                                            <option value="2">Second Term</option>
                                            <option value="3">Third Term</option>
                                        </select>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6">
                                        <div class="search-box">
                                            <input type="text" class="form-control search" id="searchInput" name="search" placeholder="Search students...">
                                            <i class="ri-search-line search-icon"></i>
                                        </div>
                                    </div>
                                    <div class="col-xxl-3 col-sm-6 d-flex gap-2">
                                        <button type="button" class="btn btn-secondary w-50" id="searchBtn" style="display: none;" onclick="filterData()"><i class="bi bi-search align-baseline me-1"></i> Search</button>
                                        <button type="button" class="btn btn-primary w-50" id="printAllBtn" style="display: none;" onclick="printAllResults()"><i class="bi bi-printer align-baseline me-1"></i> Print Selected Results</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">Students <span class="badge bg-dark-subtle text-dark ms-1" id="studentcount">{{ $allstudents ? $allstudents->total() : 0 }}</span></h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-centered align-middle table-nowrap mb-0" id="studentListTable">
                                        <thead class="table-active">
                                            <tr>
                                                <th><div class="form-check"><input class="form-check-input" type="checkbox" id="checkAll"><label class="form-check-label" for="checkAll"></label></div></th>
                                                <th>Admission No</th>
                                                <th>Picture</th>
                                                <th>Last Name</th>
                                                <th>First Name</th>
                                                <th>Other Name</th>
                                                <th>Gender</th>
                                                <th>Class</th>
                                                <th>Arm</th>
                                                <th>Session</th>
                                            </tr>
                                        </thead>
                                        <tbody id="studentTableBody">
                                            @include('studentreports.partials.student_rows')
                                        </tbody>
                                    </table>
                                    <div class="d-flex justify-content-end mt-3" id="pagination-container">
                                        {{ $allstudents ? $allstudents->links('pagination::bootstrap-5') : '' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Image View Modal -->
                <div id="imageViewModal" class="modal fade" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Student Image</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body text-center">
                                <img id="enlargedImage" src="" alt="Student Image" class="img-fluid" onerror="this.src='{{ asset('storage/student_avatars/unnamed.jpg') }}'; console.log('Enlarged image failed to load');">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column Selection Modal -->
                <div class="modal fade" id="columnSelectionModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Select Columns for PDF Report</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="columnSelectionContent">
                                    <div class="alert alert-info">
                                        <i class="ri-information-line me-2"></i>
                                        Please select the columns you want to include in the PDF report. You must select at least Class, Session, and Term first.
                                    </div>
                                    <div id="columnSelectionLoader" class="text-center py-5">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading columns...</span>
                                        </div>
                                        <p class="mt-2">Loading column options...</p>
                                    </div>
                                    <div id="columnSelectionForm" style="display: none;">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="card mb-3">
                                                    <div class="card-header">
                                                        <h6 class="mb-0">Student Information</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row" id="studentInfoColumns"></div>
                                                    </div>
                                                </div>
                                                
                                                <div class="card mb-3">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <h6 class="mb-0">Assessments</h6>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="selectAllAssessments">
                                                            <label class="form-check-label" for="selectAllAssessments">
                                                                Select All
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row" id="assessmentColumns"></div>
                                                    </div>
                                                </div>
                                                
                                                <div class="card mb-3">
                                                    <div class="card-header">
                                                        <h6 class="mb-0">Scores & Metrics</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row" id="scoreColumns"></div>
                                                    </div>
                                                </div>
                                                
                                                <div class="card mb-3">
                                                    <div class="card-header d-flex justify-content-between align-items-center">
                                                        <h6 class="mb-0">GPA/CGPA Metrics</h6>
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" id="selectAllGPAMetrics">
                                                            <label class="form-check-label" for="selectAllGPAMetrics">
                                                                Select All
                                                            </label>
                                                        </div>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row" id="gpaColumns"></div>
                                                    </div>
                                                </div>
                                                
                                                <div class="card">
                                                    <div class="card-header">
                                                        <h6 class="mb-0">Other Information</h6>
                                                    </div>
                                                    <div class="card-body">
                                                        <div class="row" id="otherColumns"></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="button" class="btn btn-primary" id="saveColumnSelection" disabled>Apply Selection & Generate PDF</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    console.log("Script loaded at", new Date().toISOString());

    function updateSelectionAlert() {
        const classSelect = document.getElementById("idclass");
        const sessionSelect = document.getElementById("idsession");
        const termSelect = document.getElementById("idterm");
        const checkedCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]:checked');
        const selectionAlert = document.getElementById("selectionAlert");
        const selectionAlertText = document.getElementById("selectionAlertText");

        let alertText = [];
        if (classSelect.value !== 'ALL') {
            alertText.push(`Class: ${classSelect.options[classSelect.selectedIndex].text}`);
        }
        if (sessionSelect.value !== 'ALL') {
            alertText.push(`Session: ${sessionSelect.options[sessionSelect.selectedIndex].text}`);
        }
        if (termSelect.value !== 'ALL') {
            alertText.push(`Term: ${termSelect.options[termSelect.selectedIndex].text}`);
        }
        alertText.push(`Students Selected: ${checkedCheckboxes.length}`);

        if (classSelect.value !== 'ALL' && sessionSelect.value !== 'ALL') {
            selectionAlert.style.display = 'block';
            selectionAlertText.innerText = alertText.join(' | ');
        } else {
            selectionAlert.style.display = 'none';
            selectionAlertText.innerText = 'No selections made.';
        }
    }

    function updateSearchButtonVisibility() {
        const classSelect = document.getElementById("idclass");
        const sessionSelect = document.getElementById("idsession");
        const searchBtn = document.getElementById("searchBtn");
        const termSelectContainer = document.getElementById("termSelectContainer");
        const printAllBtn = document.getElementById("printAllBtn");

        searchBtn.style.display = (classSelect.value !== 'ALL' && sessionSelect.value !== 'ALL') ? 'block' : 'none';
        termSelectContainer.style.display = 'none';
        printAllBtn.style.display = 'none';
        updateSelectionAlert();
    }

    function updateTermSelectVisibility() {
        const termSelectContainer = document.getElementById("termSelectContainer");
        const printAllBtn = document.getElementById("printAllBtn");
        const studentCount = parseInt(document.getElementById("studentcount").innerText);

        termSelectContainer.style.display = studentCount > 0 ? 'block' : 'none';
        printAllBtn.style.display = 'none';
        updateSelectionAlert();
    }

    function updatePrintButtonVisibility() {
        const termSelect = document.getElementById("idterm");
        const printAllBtn = document.getElementById("printAllBtn");
        const checkedCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]:checked');

        printAllBtn.style.display = (termSelect.value !== 'ALL' && checkedCheckboxes.length > 0) ? 'block' : 'none';
        updateSelectionAlert();
    }

    function filterData() {
        console.log("filterData called");
        if (typeof axios === 'undefined') {
            console.error("Axios is not defined");
            Swal.fire({
                icon: "error",
                title: "Configuration Error",
                text: "Axios library is missing.",
                showConfirmButton: true
            });
            return;
        }

        const classSelect = document.getElementById("idclass");
        const sessionSelect = document.getElementById("idsession");
        const termSelect = document.getElementById("idterm");
        const searchInput = document.getElementById("searchInput");

        if (!classSelect || !sessionSelect || !termSelect) {
            console.error("Class, session, or term select elements not found");
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "Required filter elements not found.",
                showConfirmButton: true
            });
            return;
        }

        const classValue = classSelect.value;
        const sessionValue = sessionSelect.value;
        const termValue = termSelect.value;
        const searchValue = searchInput ? searchInput.value.trim() : '';

        if (classValue === 'ALL' || sessionValue === 'ALL') {
            document.getElementById('studentTableBody').innerHTML = '<tr><td colspan="11" class="text-center">Select class and session to view students.</td></tr>';
            document.getElementById('pagination-container').innerHTML = '';
            document.getElementById('studentcount').innerText = '0';
            document.getElementById('printAllBtn').style.display = 'none';
            document.getElementById('termSelectContainer').style.display = 'none';
            updateSelectionAlert();
            Swal.fire({
                icon: "warning",
                title: "Missing Selection",
                text: "Please select a valid class and session.",
                showConfirmButton: true
            });
            return;
        }

        console.log("Sending AJAX request with:", { search: searchValue, schoolclassid: classValue, sessionid: sessionValue, termid: termValue });

        const tableBody = document.getElementById('studentTableBody');
        tableBody.innerHTML = '<tr><td colspan="11" class="text-center">Loading...</td></tr>';

        axios.get('{{ route("studentreports.index") }}', {
            params: {
                search: searchValue,
                schoolclassid: classValue,
                sessionid: sessionValue,
                termid: termValue
            },
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            console.log("AJAX response received:", response.data);

            document.getElementById('studentTableBody').innerHTML = response.data.tableBody || '<tr><td colspan="11" class="text-center">No students found.</td></tr>';
            document.getElementById('pagination-container').innerHTML = response.data.pagination || '';
            document.getElementById('studentcount').innerText = response.data.studentCount || '0';

            setupPaginationLinks();
            setupCheckboxListeners();
            updateTermSelectVisibility();
            updatePrintButtonVisibility();

            if (response.data.tableBody.includes('No students found') || response.data.tableBody.includes('Select class and session')) {
                Swal.fire({
                    icon: "info",
                    title: "No Results",
                    text: "No students found for the selected class and session.",
                    showConfirmButton: true
                });
            }
        }).catch(function (error) {
            console.error("AJAX error:", error);
            tableBody.innerHTML = '<tr><td colspan="11" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
            Swal.fire({
                icon: "error",
                title: "Error",
                text: error.response?.data?.message || "Failed to fetch student data.",
                showConfirmButton: true
            });
        });
    }

    function printAllResults() {
        const classSelect = document.getElementById("idclass");
        const sessionSelect = document.getElementById("idsession");
        const termSelect = document.getElementById("idterm");
        const classValue = classSelect.value;
        const sessionValue = sessionSelect.value;
        const termValue = termSelect.value;
        const checkedCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]:checked');
        const selectedStudentIds = Array.from(checkedCheckboxes).map(checkbox => checkbox.value);

        if (classValue === 'ALL' || sessionValue === 'ALL' || termValue === 'ALL') {
            Swal.fire({
                icon: "warning",
                title: "Missing Selection",
                text: "Please select a valid class, session, and term.",
                showConfirmButton: true
            });
            return;
        }

        if (selectedStudentIds.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "No Students Selected",
                text: "Please select at least one student to generate the PDF.",
                showConfirmButton: true
            });
            return;
        }

        // Show column selection modal
        const columnModal = new bootstrap.Modal(document.getElementById('columnSelectionModal'));
        columnModal.show();
        
        // Load column options
        loadColumnOptions(classValue, sessionValue, termValue, selectedStudentIds);
    }

    function loadColumnOptions(classId, sessionId, termId, studentIds) {
        const loader = document.getElementById('columnSelectionLoader');
        const form = document.getElementById('columnSelectionForm');
        const saveBtn = document.getElementById('saveColumnSelection');
        
        loader.style.display = 'block';
        form.style.display = 'none';
        saveBtn.disabled = true;
        
        // Store the parameters for later use
        window.currentPrintParams = {
            classId: classId,
            sessionId: sessionId,
            termId: termId,
            studentIds: studentIds
        };
        
        fetch('{{ route("studentreports.column-options") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                schoolclassid: classId,
                sessionid: sessionId,
                termid: termId
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateColumnOptions(data.columns);
                loader.style.display = 'none';
                form.style.display = 'block';
                saveBtn.disabled = false;
            } else {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: data.message || "Failed to load column options.",
                });
                columnModal.hide();
            }
        })
        .catch(error => {
            console.error('Error loading column options:', error);
            Swal.fire({
                icon: "error",
                title: "Network Error",
                text: "Failed to load column options. Please try again.",
            });
            columnModal.hide();
        });
    }

    function populateColumnOptions(columns) {
        // Clear existing content
        document.getElementById('studentInfoColumns').innerHTML = '';
        document.getElementById('assessmentColumns').innerHTML = '';
        document.getElementById('scoreColumns').innerHTML = '';
        document.getElementById('gpaColumns').innerHTML = '';
        document.getElementById('otherColumns').innerHTML = '';
        
        // Populate Student Info Columns
        if (columns.student_info) {
            Object.entries(columns.student_info).forEach(([key, config]) => {
                const colDiv = document.createElement('div');
                colDiv.className = 'col-md-4 col-sm-6 mb-2';
                colDiv.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input column-checkbox" type="checkbox" 
                            id="col_${key}" data-column="${key}" ${config.default ? 'checked' : ''}>
                        <label class="form-check-label" for="col_${key}">
                            ${config.label}
                        </label>
                    </div>
                `;
                document.getElementById('studentInfoColumns').appendChild(colDiv);
            });
        }
        
        // Populate Assessment Columns
        if (columns.assessments) {
            Object.entries(columns.assessments).forEach(([key, config]) => {
                const colDiv = document.createElement('div');
                colDiv.className = 'col-md-4 col-sm-6 mb-2';
                const subText = config.has_sub_assessments ? 
                    '<small class="text-muted d-block">Has sub-assessments</small>' : '';
                colDiv.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input column-checkbox assessment-checkbox" type="checkbox" 
                            id="col_${key}" data-column="${key}" ${config.default ? 'checked' : ''}>
                        <label class="form-check-label" for="col_${key}">
                            ${config.label}
                            ${subText}
                        </label>
                    </div>
                `;
                document.getElementById('assessmentColumns').appendChild(colDiv);
            });
        }
        
        // Populate Score Columns
        if (columns.scores) {
            Object.entries(columns.scores).forEach(([key, config]) => {
                const colDiv = document.createElement('div');
                colDiv.className = 'col-md-4 col-sm-6 mb-2';
                colDiv.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input column-checkbox" type="checkbox" 
                            id="col_${key}" data-column="${key}" ${config.default ? 'checked' : ''}>
                        <label class="form-check-label" for="col_${key}">
                            ${config.label}
                        </label>
                    </div>
                `;
                document.getElementById('scoreColumns').appendChild(colDiv);
            });
        }
        
        // Populate GPA Columns
        if (columns.gpa_metrics) {
            Object.entries(columns.gpa_metrics).forEach(([key, config]) => {
                const colDiv = document.createElement('div');
                colDiv.className = 'col-md-4 col-sm-6 mb-2';
                colDiv.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input column-checkbox gpa-checkbox" type="checkbox" 
                            id="col_${key}" data-column="${key}" ${config.default ? 'checked' : ''}>
                        <label class="form-check-label" for="col_${key}">
                            ${config.label}
                        </label>
                    </div>
                `;
                document.getElementById('gpaColumns').appendChild(colDiv);
            });
        }
        
        // Populate Other Columns
        if (columns.other) {
            Object.entries(columns.other).forEach(([key, config]) => {
                const colDiv = document.createElement('div');
                colDiv.className = 'col-md-4 col-sm-6 mb-2';
                colDiv.innerHTML = `
                    <div class="form-check">
                        <input class="form-check-input column-checkbox" type="checkbox" 
                            id="col_${key}" data-column="${key}" ${config.default ? 'checked' : ''}>
                        <label class="form-check-label" for="col_${key}">
                            ${config.label}
                        </label>
                    </div>
                `;
                document.getElementById('otherColumns').appendChild(colDiv);
            });
        }
        
        // Set up select all functionality
        document.getElementById('selectAllAssessments').addEventListener('change', function() {
            document.querySelectorAll('.assessment-checkbox').forEach(cb => {
                cb.checked = this.checked;
            });
        });
        
        document.getElementById('selectAllGPAMetrics').addEventListener('change', function() {
            document.querySelectorAll('.gpa-checkbox').forEach(cb => {
                cb.checked = this.checked;
            });
        });
    }

    // Handle save column selection and generate PDF
    document.getElementById('saveColumnSelection').addEventListener('click', function() {
        const selectedColumns = [];
        document.querySelectorAll('.column-checkbox:checked').forEach(cb => {
            selectedColumns.push(cb.dataset.column);
        });
        
        if (selectedColumns.length === 0) {
            Swal.fire({
                icon: "warning",
                title: "No Columns Selected",
                text: "Please select at least one column to include in the PDF.",
                showConfirmButton: true
            });
            return;
        }
        
        const params = window.currentPrintParams;
        const columnModal = bootstrap.Modal.getInstance(document.getElementById('columnSelectionModal'));
        columnModal.hide();
        
        // Show loading dialog
        Swal.fire({
            title: 'Generating PDF',
            html: `
                <p><strong>Class:</strong> ${document.getElementById('idclass').options[document.getElementById('idclass').selectedIndex].text}</p>
                <p><strong>Session:</strong> ${document.getElementById('idsession').options[document.getElementById('idsession').selectedIndex].text}</p>
                <p><strong>Term:</strong> ${document.getElementById('idterm').options[document.getElementById('idterm').selectedIndex].text}</p>
                <p><strong>Students Selected:</strong> ${params.studentIds.length}</p>
                <p><strong>Columns Selected:</strong> ${selectedColumns.length}</p>
                <p>Generating PDF... Please wait.</p>
            `,
            icon: 'info',
            showCancelButton: false,
            showConfirmButton: false,
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        console.log('Generating PDF with params:', { 
            schoolclassid: params.classId, 
            sessionid: params.sessionId, 
            termid: params.termId, 
            studentIds: params.studentIds,
            selectedColumns: selectedColumns 
        });

        // Create a form to submit the request - This will display PDF in browser
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("studentreports.exportClassResultsPdf") }}';
        form.target = '_blank'; // Open in new tab
        
        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);
        
        // Add other parameters
        const classIdInput = document.createElement('input');
        classIdInput.type = 'hidden';
        classIdInput.name = 'schoolclassid';
        classIdInput.value = params.classId;
        form.appendChild(classIdInput);
        
        const sessionIdInput = document.createElement('input');
        sessionIdInput.type = 'hidden';
        sessionIdInput.name = 'sessionid';
        sessionIdInput.value = params.sessionId;
        form.appendChild(sessionIdInput);
        
        const termIdInput = document.createElement('input');
        termIdInput.type = 'hidden';
        termIdInput.name = 'termid';
        termIdInput.value = params.termId;
        form.appendChild(termIdInput);
        
        const responseMethodInput = document.createElement('input');
        responseMethodInput.type = 'hidden';
        responseMethodInput.name = 'response_method';
        responseMethodInput.value = 'inline'; // Use 'inline' to display in browser
        form.appendChild(responseMethodInput);
        
        // Add student IDs as separate inputs
        params.studentIds.forEach((id, index) => {
            const studentIdInput = document.createElement('input');
            studentIdInput.type = 'hidden';
            studentIdInput.name = `studentIds[${index}]`;
            studentIdInput.value = id;
            form.appendChild(studentIdInput);
        });
        
        // Add selected columns as separate inputs
        selectedColumns.forEach((col, index) => {
            const colInput = document.createElement('input');
            colInput.type = 'hidden';
            colInput.name = `selectedColumns[${index}]`;
            colInput.value = col;
            form.appendChild(colInput);
        });
        
        // Add form to document and submit
        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
        
        // Close the loading dialog after a short delay
        setTimeout(() => {
            Swal.close();
        }, 2000);
    });

    function setupPaginationLinks() {
        const paginationLinks = document.querySelectorAll('#pagination-container a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.href;
                if (url && !this.classList.contains('disabled')) {
                    loadPage(url);
                }
            });
        });
    }

    function loadPage(url) {
        console.log("Loading page:", url);
        const tableBody = document.getElementById('studentTableBody');
        tableBody.innerHTML = '<tr><td colspan="11" class="text-center">Loading...</td></tr>';

        axios.get(url, {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        }).then(function (response) {
            console.log("Page load response:", response.data);
            document.getElementById('studentTableBody').innerHTML = response.data.tableBody || '<tr><td colspan="11" class="text-center">No students found.</td></tr>';
            document.getElementById('pagination-container').innerHTML = response.data.pagination || '';
            document.getElementById('studentcount').innerText = response.data.studentCount || '0';
            setupPaginationLinks();
            setupCheckboxListeners();
            updateTermSelectVisibility();
            updatePrintButtonVisibility();
        }).catch(function (error) {
            console.error("Page load error:", error);
            tableBody.innerHTML = '<tr><td colspan="11" class="text-center text-danger">Error loading data. Please try again.</td></tr>';
            Swal.fire({
                icon: "error",
                title: "Error",
                text: error.response?.data?.message || "Failed to fetch student data.",
                showConfirmButton: true
            });
        });
    }

    function setupCheckboxListeners() {
        const checkAll = document.getElementById("checkAll");
        const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');

        if (checkAll) {
            checkAll.addEventListener("change", function () {
                checkboxes.forEach((checkbox) => {
                    checkbox.checked = this.checked;
                    const row = checkbox.closest("tr");
                    row.classList.toggle("table-active", this.checked);
                });
                updatePrintButtonVisibility();
            });
        }

        checkboxes.forEach(checkbox => {
            checkbox.addEventListener("change", function () {
                const row = this.closest("tr");
                row.classList.toggle("table-active", this.checked);
                const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
                const allCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]').length;
                document.getElementById("checkAll").checked = checkedCount === allCheckboxes && allCheckboxes > 0;
                updatePrintButtonVisibility();
            });
        });
    }

    document.addEventListener("DOMContentLoaded", function () {
        console.log("DOM loaded");
        setupCheckboxListeners();

        const classSelect = document.getElementById("idclass");
        const sessionSelect = document.getElementById("idsession");
        const termSelect = document.getElementById("idterm");

        classSelect.addEventListener("change", function () {
            updateSearchButtonVisibility();
            termSelect.value = 'ALL';
            document.getElementById('studentTableBody').innerHTML = '<tr><td colspan="11" class="text-center">Select class and session to view students.</td></tr>';
            document.getElementById('pagination-container').innerHTML = '';
            document.getElementById('studentcount').innerText = '0';
        });

        sessionSelect.addEventListener("change", function () {
            updateSearchButtonVisibility();
            termSelect.value = 'ALL';
            document.getElementById('studentTableBody').innerHTML = '<tr><td colspan="11" class="text-center">Select class and session to view students.</td></tr>';
            document.getElementById('pagination-container').innerHTML = '';
            document.getElementById('studentcount').innerText = '0';
        });

        termSelect.addEventListener("change", function () {
            updatePrintButtonVisibility();
            if (this.value !== 'ALL') {
                filterData();
            }
        });

        const modal = document.getElementById('imageViewModal');
        if (modal) {
            modal.addEventListener('show.bs.modal', function (event) {
                const button = event.relatedTarget;
                const imageSrc = button.getAttribute('data-image');
                const modalImage = modal.querySelector('#enlargedImage');
                modalImage.src = imageSrc || '{{ asset('storage/student_avatars/unnamed.jpg') }}';
            });
        }
    });
</script>
@endsection