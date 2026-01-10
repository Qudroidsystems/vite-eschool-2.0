<script>
// Initialize admission number on page load
updateAdmissionNumber();
updateAdmissionNumber('edit');

// Update admission number based on year selection
function updateAdmissionNumber(prefix = '') {
    const yearSelect = document.getElementById(`${prefix}admissionYear`);
    const admissionNoInput = document.getElementById(`${prefix}admissionNo`);
    const admissionMode = document.querySelector(`input[name="admissionMode"]:checked${prefix ? `[id^="${prefix}"]` : ''}`);

    if (!yearSelect || !admissionNoInput) return;

    const year = yearSelect.value;
    const baseFormat = `CSSK/STD/${year}/`;

    if (admissionMode && admissionMode.value === 'auto') {
        admissionNoInput.readOnly = true;
        fetch(`/students/last-admission-number?year=${year}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                admissionNoInput.value = data.admissionNo;
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Failed to generate admission number',
                    icon: 'error',
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false
                });
                admissionNoInput.value = `${baseFormat}0871`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Failed to generate admission number',
                icon: 'error',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            });
            admissionNoInput.value = `${baseFormat}0871`;
        });
    } else {
        admissionNoInput.readOnly = false;
        if (!admissionNoInput.value || admissionNoInput.value === `${baseFormat}AUTO`) {
            admissionNoInput.value = `${baseFormat}0871`;
        } else if (!admissionNoInput.value.startsWith(baseFormat)) {
            const numericPart = admissionNoInput.value.split('/').pop() || '0871';
            const numericValue = Math.max(871, parseInt(numericPart) || 871);
            admissionNoInput.value = `${baseFormat}${numericValue.toString().padStart(4, '0')}`;
        }
    }
}

// Toggle admission input based on mode
window.toggleAdmissionInput = function(prefix = '') {
    const admissionMode = document.querySelector(`input[name="admissionMode"]:checked${prefix ? `[id^="${prefix}"]` : ''}`);
    const admissionNoInput = document.getElementById(`${prefix}admissionNo`);
    const yearSelect = document.getElementById(`${prefix}admissionYear`);

    if (!admissionMode || !admissionNoInput || !yearSelect) return;

    const year = yearSelect.value;
    const baseFormat = `CSSK/STD/${year}/`;

    if (admissionMode.value === 'auto') {
        admissionNoInput.readOnly = true;
        fetch(`/students/last-admission-number?year=${year}`, {
            method: 'GET',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                admissionNoInput.value = data.admissionNo;
            } else {
                Swal.fire({
                    title: 'Error!',
                    text: data.message || 'Failed to generate admission number',
                    icon: 'error',
                    customClass: { confirmButton: 'btn btn-primary' },
                    buttonsStyling: false
                });
                admissionNoInput.value = `${baseFormat}0871`;
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Failed to generate admission number',
                icon: 'error',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            });
            admissionNoInput.value = `${baseFormat}0871`;
        });
    } else {
        admissionNoInput.readOnly = false;
        if (!admissionNoInput.value || admissionNoInput.value === `${baseFormat}AUTO`) {
            admissionNoInput.value = `${baseFormat}0871`;
        } else if (!admissionNoInput.value.startsWith(baseFormat)) {
            const numericPart = admissionNoInput.value.split('/').pop() || '0871';
            const numericValue = Math.max(871, parseInt(numericPart) || 871);
            admissionNoInput.value = `${baseFormat}${numericValue.toString().padStart(4, '0')}`;
        }
    }
};

// Add event listeners for year selection
document.getElementById('admissionYear')?.addEventListener('change', () => updateAdmissionNumber());
document.getElementById('editAdmissionYear')?.addEventListener('change', () => updateAdmissionNumber('edit'));

// Ensure Axios and CSRF token
function ensureAxios() {
    if (typeof axios === 'undefined') {
        console.error('Error: Axios is not defined');
        Swal.fire({
            title: "Error!",
            text: "Axios library is missing",
            icon: "error",
            customClass: { confirmButton: "btn btn-primary" },
            buttonsStyling: false
        });
        return false;
    }
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    if (!csrfToken) {
        console.error('Error: CSRF token not found');
        Swal.fire({
            title: "Error!",
            text: "CSRF token is missing",
            icon: "error",
            customClass: { confirmButton: "btn btn-primary" },
            buttonsStyling: false
        });
        return false;
    }
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    return true;
}

let allStudents = [];
const itemsPerPage = 100;
const defaultAvatar = '{{ asset("storage/images/student_avatars/unnamed.jpg") }}';

// View toggle function
function toggleView(viewType) {
    const tableView = document.getElementById('tableView');
    const cardView = document.getElementById('cardView');
    const tableViewBtn = document.getElementById('tableViewBtn');
    const cardViewBtn = document.getElementById('cardViewBtn');

    if (viewType === 'table') {
        tableView.classList.remove('d-none');
        cardView.classList.add('d-none');
        tableViewBtn.classList.add('active');
        cardViewBtn.classList.remove('active');

        // Update checkboxes
        document.getElementById('checkAll').checked = false;
        document.getElementById('remove-actions').classList.add('d-none');
    } else {
        tableView.classList.add('d-none');
        cardView.classList.remove('d-none');
        tableViewBtn.classList.remove('active');
        cardViewBtn.classList.add('active');

        // Render cards if not already rendered
        if (document.getElementById('studentsCardsContainer').children.length === 0 && allStudents.length > 0) {
            renderStudentsCards(allStudents);
        }

        // Update checkboxes
        document.getElementById('checkAll').checked = false;
        document.getElementById('remove-actions').classList.add('d-none');
    }
}

// Render students as cards - SIMPLIFIED VERSION
function renderStudentsCards(students) {
    console.log('Rendering students as cards:', students);
    const container = document.getElementById('studentsCardsContainer');
    if (!container) {
        console.error('studentsCardsContainer element not found');
        Swal.fire({
            title: "Error!",
            text: "Students container element not found",
            icon: "error",
            customClass: { confirmButton: "btn btn-primary" },
            buttonsStyling: false
        });
        return;
    }

    container.innerHTML = '';

    if (students.length === 0) {
        container.innerHTML = `
            <div class="col-12">
                <div class="empty-state">
                    <i class="fas fa-users-slash"></i>
                    <h5>No students found</h5>
                    <p>Try adjusting your filters or add a new student</p>
                </div>
            </div>
        `;
        updateCounts(0);
        return;
    }

    students.forEach(student => {
        console.log('Processing student for card:', student);

        // Get initials for avatar
        const firstName = student.firstname || '';
        const lastName = student.lastname || '';
        const initials = (firstName.charAt(0) + lastName.charAt(0)).toUpperCase() || '??';

        // Get avatar URL - handle different possible field names
        let avatarUrl = defaultAvatar;
        if (student.picture) {
            avatarUrl = `/storage/images/student_avatars/${student.picture}`;
        } else if (student.avatar) {
            avatarUrl = `/storage/images/student_avatars/${student.avatar}`;
        }

        // Determine status
        const isActive = student.student_status === 'Active';
        const statusText = isActive ? 'Active' : 'Inactive';
        const statusClass = isActive ? 'status-active' : 'status-inactive';

        // Get student type
        const studentType = student.statusId == 1 ? 'Old Student' : student.statusId == 2 ? 'New Student' : 'N/A';

        // Format registration date
        const regDate = student.created_at ? new Date(student.created_at).toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric'
        }) : 'N/A';

        // Create card HTML
        const cardHtml = `
            <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                <div class="student-card" data-id="${student.id}"
                     data-name="${student.lastname} ${student.firstname} ${student.othername || ''}"
                     data-admission="${student.admissionNo || ''}"
                     data-class="${student.schoolclassid || ''}"
                     data-status="${student.statusId || ''}"
                     data-gender="${student.gender || ''}"
                     data-student-status="${student.student_status || ''}">

                    <!-- Checkbox for multiple selection -->
                    <div class="checkbox-container">
                        <div class="form-check">
                            <input class="form-check-input student-checkbox" type="checkbox" name="chk_child" value="${student.id}">
                        </div>
                    </div>

                    <!-- Status badge -->
                    <span class="status-badge ${statusClass}">${statusText}</span>

                    <!-- Action buttons -->
                    <div class="action-buttons">
                        <button class="action-btn view-btn" title="View Details" onclick="viewStudent(${student.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="action-btn edit-btn" title="Edit" onclick="editStudent(${student.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="action-btn delete-btn" title="Delete" onclick="deleteStudent(${student.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>

                    <!-- Avatar -->
                    <div class="avatar-container">
                        <img src="${avatarUrl}" alt="${student.firstname} ${student.lastname}"
                             class="avatar" onerror="this.style.display='none';
                             this.parentElement.innerHTML='<div class=\"avatar-initials\">${initials}</div>'">
                    </div>

                    <!-- Student name -->
                    <h6 class="student-name">${student.lastname} ${student.firstname}</h6>

                    <!-- Admission number -->
                    <p class="student-admission">${student.admissionNo || 'No Admission No'}</p>

                    <!-- Student details -->
                    <div class="student-details">
                        <div><strong>Class:</strong> ${student.schoolclass || 'N/A'} ${student.arm || ''}</div>
                        <div><strong>Type:</strong> ${studentType}</div>
                        <div><strong>Gender:</strong> ${student.gender || 'N/A'}</div>
                        <div><strong>Registered:</strong> ${regDate}</div>
                    </div>
                </div>
            </div>
        `;

        container.innerHTML += cardHtml;
    });

    initializeStudentCheckboxes();
    updateCounts(students.length);
}

// Update counts display
function updateCounts(count) {
    const totalStudents = document.getElementById('totalStudents');
    const totalCount = document.getElementById('totalCount');
    const showingCount = document.getElementById('showingCount');

    if (totalStudents) totalStudents.textContent = count;
    if (totalCount) totalCount.textContent = count;
    if (showingCount) showingCount.textContent = count;
}

// Initialize student checkboxes for card view
function initializeStudentCheckboxes() {
    const checkAll = document.getElementById('checkAll');
    const studentCheckboxes = document.querySelectorAll('.student-checkbox');

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            studentCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
                const card = checkbox.closest('.student-card');
                if (card) {
                    card.classList.toggle('selected', this.checked);
                }
            });
            document.getElementById('remove-actions').classList.toggle('d-none', !this.checked);
        });
    }

    studentCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const card = this.closest('.student-card');
            if (card) {
                card.classList.toggle('selected', this.checked);
            }

            // Update checkAll state
            const allChecked = document.querySelectorAll('.student-checkbox').length ===
                             document.querySelectorAll('.student-checkbox:checked').length;
            const someChecked = document.querySelectorAll('.student-checkbox:checked').length > 0;

            if (checkAll) {
                checkAll.checked = allChecked;
                checkAll.indeterminate = someChecked && !allChecked;
            }

            document.getElementById('remove-actions').classList.toggle('d-none', !someChecked);
        });
    });
}

// View student details - SIMPLIFIED
function viewStudent(id) {
    console.log('View student:', id);
    if (!ensureAxios()) return;

    // Try your working endpoint first
    axios.get(`/student/${id}/edit`)
        .then((response) => {
            console.log('Student data received for view:', response.data);
            let student = response.data.student || response.data;

            if (!student) {
                throw new Error('Student data is empty');
            }

            // Populate the view modal
            populateViewModal(student);

            // Show the view modal
            const viewModalElement = document.getElementById('viewStudentModal');
            if (viewModalElement) {
                const viewModal = new bootstrap.Modal(viewModalElement);
                viewModal.show();
            } else {
                console.error('View modal element not found');
            }
        })
        .catch((error) => {
            console.error('Error fetching student for view:', error);

            // Fallback: try the show endpoint
            axios.get(`/student/${id}`)
                .then((response) => {
                    console.log('Student data received (fallback):', response.data);
                    let student = response.data.student || response.data.data || response.data;

                    if (!student) {
                        throw new Error('Student data is empty');
                    }

                    populateViewModal(student);

                    const viewModalElement = document.getElementById('viewStudentModal');
                    if (viewModalElement) {
                        const viewModal = new bootstrap.Modal(viewModalElement);
                        viewModal.show();
                    }
                })
                .catch((fallbackError) => {
                    console.error('Fallback also failed:', fallbackError);
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to load student data. Please try again.',
                        icon: 'error',
                        customClass: { confirmButton: 'btn btn-primary' },
                        buttonsStyling: false
                    });
                });
        });
}

// Function to populate view modal
function populateViewModal(student) {
    console.log('Populating view modal with student:', student);

    // Student Photo
    const photoElement = document.getElementById('viewStudentPhoto');
    if (photoElement) {
        if (student.picture) {
            photoElement.src = `/storage/images/student_avatars/${student.picture}`;
        } else if (student.avatar) {
            photoElement.src = `/storage/images/student_avatars/${student.avatar}`;
        }
    }

    // Academic Details
    document.getElementById('viewAcademicYear').textContent = student.admissionYear || student.admission_year || '-';
    document.getElementById('viewRegistrationNo').textContent = student.admissionNo || student.admission_no || '-';

    if (student.admissionDate) {
        document.getElementById('viewAdmissionDate').textContent = new Date(student.admissionDate).toLocaleDateString();
    } else {
        document.getElementById('viewAdmissionDate').textContent = '-';
    }

    // Class information
    const classElement = document.getElementById('viewClass');
    if (classElement) {
        if (student.schoolclass && student.arm) {
            classElement.textContent = `${student.schoolclass} - ${student.arm}`;
        } else if (student.class_name) {
            classElement.textContent = student.class_name;
        } else {
            classElement.textContent = '-';
        }
    }

    document.getElementById('viewTerm').textContent = student.term_name || student.term || '-';

    // Category badges
    const dayBadge = document.getElementById('dayBadge');
    const boardingBadge = document.getElementById('boardingBadge');
    if (dayBadge && boardingBadge) {
        if (student.student_category === 'Day') {
            dayBadge.classList.add('active');
            boardingBadge.classList.remove('active');
        } else if (student.student_category === 'Boarding') {
            boardingBadge.classList.add('active');
            dayBadge.classList.remove('active');
        } else {
            dayBadge.classList.remove('active');
            boardingBadge.classList.remove('active');
        }
    }

    // Personal Details
    document.getElementById('viewSurname').textContent = student.lastname || student.last_name || '-';
    document.getElementById('viewFirstName').textContent = student.firstname || student.first_name || '-';
    document.getElementById('viewMiddleName').textContent = student.othername || student.other_name || student.middle_name || '-';

    const genderElement = document.getElementById('viewGender');
    if (genderElement) {
        const gender = student.gender || '-';
        genderElement.innerHTML = gender === 'Male' ?
            '<i class="fas fa-male"></i> Male' :
            gender === 'Female' ? '<i class="fas fa-female"></i> Female' :
            '<i class="fas fa-user"></i> -';
    }

    if (student.dateofbirth) {
        document.getElementById('viewDateOfBirth').textContent = new Date(student.dateofbirth).toLocaleDateString();
    } else {
        document.getElementById('viewDateOfBirth').textContent = '-';
    }

    document.getElementById('viewBloodGroup').textContent = student.blood_group || '-';
    document.getElementById('viewMotherTongue').textContent = student.mother_tongue || '-';
    document.getElementById('viewReligion').textContent = student.religion || '-';
    document.getElementById('viewSportHouse').textContent = student.school_house || student.sport_house || '-';

    const mobileElement = document.getElementById('viewMobileNumber');
    if (mobileElement) {
        const phone = student.phone_number || '-';
        mobileElement.innerHTML = phone !== '-' ?
            `<i class="fas fa-phone"></i> ${phone}` : '-';
    }

    const emailElement = document.getElementById('viewEmail');
    if (emailElement) {
        const email = student.email || '-';
        emailElement.innerHTML = email !== '-' ?
            `<i class="fas fa-envelope"></i> ${email}` : '-';
    }

    document.getElementById('viewNIN').textContent = student.nin_number || '-';
    document.getElementById('viewCity').textContent = student.city || '-';
    document.getElementById('viewState').textContent = student.state || '-';
    document.getElementById('viewPermanentAddress').textContent = student.permanent_address || '-';
    document.getElementById('viewFutureAmbition').textContent = student.future_ambition || '-';

    // Guardian Details
    document.getElementById('viewFatherName').textContent = student.father_name || '-';
    document.getElementById('viewMotherName').textContent = student.mother_name || '-';
    document.getElementById('viewOccupation').textContent = student.father_occupation || '-';
    document.getElementById('viewParentCity').textContent = student.father_city || '-';

    const parentMobileElement = document.getElementById('viewParentMobile');
    if (parentMobileElement) {
        const parentPhone = student.father_phone || student.mother_phone || '-';
        parentMobileElement.innerHTML = parentPhone !== '-' ?
            `<i class="fas fa-phone"></i> ${parentPhone}` : '-';
    }

    const parentEmailElement = document.getElementById('viewParentEmail');
    if (parentEmailElement) {
        const parentEmail = student.parent_email || '-';
        parentEmailElement.innerHTML = parentEmail !== '-' ?
            `<i class="fas fa-envelope"></i> ${parentEmail}` : '-';
    }

    document.getElementById('viewParentAddress').textContent = student.parent_address || '-';

    // Previous School Details
    const schoolElement = document.getElementById('viewSchoolName');
    if (schoolElement) {
        const schoolName = student.last_school || '-';
        schoolElement.innerHTML = schoolName !== '-' ?
            `<i class="fas fa-school"></i> ${schoolName}` : '-';
    }

    document.getElementById('viewPreviousClass').textContent = student.last_class || '-';
    document.getElementById('viewReasonLeaving').textContent = student.reason_for_leaving || '-';
}

function editStudent(id) {
    console.log('Edit student:', id);
    if (!ensureAxios()) return;

    axios.get(`/student/${id}/edit`)
        .then((response) => {
            console.log('Student data received for edit:', response.data);
            let student = response.data.student || response.data;

            if (!student) {
                throw new Error('Student data is empty');
            }

            // Populate the edit form
            populateEditForm(student);

            // Show the edit modal
            const editModalElement = document.getElementById('editStudentModal');
            if (editModalElement) {
                const editModal = new bootstrap.Modal(editModalElement);
                editModal.show();
            }
        })
        .catch((error) => {
            console.error('Error editing student:', error);
            Swal.fire({
                title: 'Error!',
                text: error.response?.data?.message || 'Failed to load student data',
                icon: 'error',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            });
        });
}

function deleteStudent(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        customClass: { confirmButton: 'btn btn-primary', cancelButton: 'btn btn-light' },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed && ensureAxios()) {
            axios.delete(`/student/${id}/destroy`)
                .then(() => {
                    // Remove the card
                    const card = document.querySelector(`.student-card[data-id="${id}"]`);
                    if (card) {
                        card.closest('.col-xl-3').remove();
                    }
                    // Remove the table row
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    if (row) {
                        row.remove();
                    }
                    // Refresh the list
                    fetchStudents();
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Student has been deleted',
                        icon: 'success',
                        customClass: { confirmButton: 'btn btn-primary' },
                        buttonsStyling: false
                    });
                })
                .catch((error) => {
                    console.error('Error deleting student:', error);
                    Swal.fire({
                        title: 'Error!',
                        text: error.response?.data?.message || 'Failed to delete student',
                        icon: 'error',
                        customClass: { confirmButton: 'btn btn-primary' },
                        buttonsStyling: false
                    });
                });
        }
    });
}

// Fetch students from the server - SIMPLIFIED
function fetchStudents() {
    if (!ensureAxios()) return;
    console.log('Fetching students from /students/data');

    axios.get('/students/data')
        .then((response) => {
            console.log('Full API response:', response.data);

            let studentsArray = [];

            // Handle different response formats
            if (Array.isArray(response.data)) {
                studentsArray = response.data;
            } else if (response.data.students && Array.isArray(response.data.students)) {
                studentsArray = response.data.students;
            } else if (response.data.data && Array.isArray(response.data.data)) {
                studentsArray = response.data.data;
            } else if (response.data.success && Array.isArray(response.data.data)) {
                studentsArray = response.data.data;
            } else {
                console.log('Unexpected response format, trying to extract students:', response.data);
                // Try to extract students from the response
                studentsArray = Object.values(response.data).filter(item =>
                    item && (item.id || item.student_id)
                );
            }

            console.log('Students array:', studentsArray);

            if (studentsArray.length > 0) {
                console.log('First student data:', studentsArray[0]);
            }

            allStudents = studentsArray.map(student => ({
                id: student.id || student.student_id || '',
                admissionNo: student.admissionNo || student.admission_no || student.admission_number || '',
                firstname: student.firstname || student.first_name || '',
                lastname: student.lastname || student.last_name || '',
                othername: student.othername || student.other_name || student.middle_name || '',
                gender: student.gender || '',
                statusId: student.statusId || student.status_id || student.student_status_id || '',
                student_status: student.student_status || student.status || '',
                created_at: student.created_at || student.created_date || student.registration_date || '',
                picture: student.picture || student.avatar || student.profile_picture || '',
                schoolclass: student.schoolclass || student.class || student.class_name || '',
                arm: student.arm || student.section || '',
                schoolclassid: student.schoolclassid || student.class_id || ''
            }));

            console.log('Processed students:', allStudents);
            console.log('Processed students count:', allStudents.length);

            // Update counts
            updateCounts(allStudents.length);

            // Check which view is active and render accordingly
            const tableView = document.getElementById('tableView');
            const isTableView = !tableView.classList.contains('d-none');

            if (isTableView) {
                renderStudents(allStudents);
            } else {
                renderStudentsCards(allStudents);
            }
        })
        .catch((error) => {
            console.error('Error fetching students:', error);
            Swal.fire({
                title: "Error!",
                text: "Failed to load students. Please try again.",
                icon: "error",
                customClass: { confirmButton: "btn btn-primary" },
                buttonsStyling: false
            });
            renderStudents([]);
            renderStudentsCards([]);
        });
}

// Render students in the table
function renderStudents(students) {
    console.log('Rendering students in table:', students);
    const tbody = document.getElementById('studentTableBody');
    if (!tbody) {
        console.error('studentTableBody element not found');
        return;
    }

    tbody.innerHTML = '';

    if (students.length === 0) {
        const row = document.createElement('tr');
        row.innerHTML = `<td colspan="8" class="text-center">No students found</td>`;
        tbody.appendChild(row);
        updatePagination();
        return;
    }

    students.forEach(student => {
        const studentImage = student.picture ? `/storage/images/student_avatars/${student.picture}` : defaultAvatar;

        const row = document.createElement('tr');
        row.setAttribute('data-id', student.id);
        row.innerHTML = `
            <td class="id" data-id="${student.id}">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="chk_child">
                </div>
            </td>
            <td class="name" data-name="${student.lastname} ${student.firstname} ${student.othername || ''}">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-50px me-3">
                        <img src="${studentImage}" alt="" class="rounded-circle avatar-sm student-image" style="object-fit:cover; width: 50px; height: 50px;"/>
                    </div>
                    <div>
                        <h6 class="mb-0">
                            <b>${student.lastname}</b> ${student.firstname} ${student.othername || ''}
                        </h6>
                    </div>
                </div>
            </td>
            <td class="admissionNo" data-admissionno="${student.admissionNo}">${student.admissionNo}</td>
            <td class="class" data-class="${student.schoolclassid}">${student.schoolclass || ''} ${student.arm ? ' - ' + student.arm : ''}</td>
            <td class="status" data-status="${student.statusId}">${student.statusId == 1 ? 'Old Student' : student.statusId == 2 ? 'New Student' : ''}</td>
            <td class="gender" data-gender="${student.gender}">${student.gender}</td>
            <td class="datereg">${student.created_at ? new Date(student.created_at).toISOString().split('T')[0] : ''}</td>
            <td>
                <ul class="d-flex gap-2 list-unstyled mb-0">
                    <li><a href="javascript:void(0);" class="btn btn-subtle-info btn-icon btn-sm view-item-btn" data-id="${student.id}" onclick="viewStudent(${student.id})" title="View Details"><i class="ph-eye"></i></a></li>
                    <li><a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn" data-id="${student.id}" onclick="editStudent(${student.id})" title="Edit"><i class="ph-pencil"></i></a></li>
                    <li><a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn" data-id="${student.id}" onclick="deleteStudent(${student.id})" title="Delete"><i class="ph-trash"></i></a></li>
                </ul>
            </td>
        `;
        tbody.appendChild(row);
    });

    updatePagination();
    initializeCheckboxes();
}

// Update pagination controls
function updatePagination() {
    const totalItems = allStudents.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const currentPage = 1;
    const paginationLinks = document.getElementById('paginationLinks');

    if (!paginationLinks) return;

    paginationLinks.innerHTML = '';

    // Show only a few page links
    const maxPagesToShow = 5;
    let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
    let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);

    // Adjust start page if we're at the end
    if (endPage - startPage + 1 < maxPagesToShow) {
        startPage = Math.max(1, endPage - maxPagesToShow + 1);
    }

    for (let i = startPage; i <= endPage; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === currentPage ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="javascript:void(0);">${i}</a>`;
        li.addEventListener('click', () => {
            // Simple pagination logic
            const startIndex = (i - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const pageStudents = allStudents.slice(startIndex, endIndex);

            // Check which view is active
            const tableView = document.getElementById('tableView');
            const isTableView = !tableView.classList.contains('d-none');

            if (isTableView) {
                renderStudents(pageStudents);
            } else {
                renderStudentsCards(pageStudents);
            }

            document.getElementById('showingCount').textContent = pageStudents.length;
        });
        paginationLinks.appendChild(li);
    }

    const prevPage = document.getElementById('prevPage');
    const nextPage = document.getElementById('nextPage');

    if (prevPage) {
        prevPage.classList.toggle('disabled', currentPage === 1);
        prevPage.onclick = currentPage > 1 ? () => {
            // Previous page logic
            const newPage = currentPage - 1;
            const startIndex = (newPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const pageStudents = allStudents.slice(startIndex, endIndex);

            const tableView = document.getElementById('tableView');
            const isTableView = !tableView.classList.contains('d-none');

            if (isTableView) {
                renderStudents(pageStudents);
            } else {
                renderStudentsCards(pageStudents);
            }

            updatePaginationForPage(newPage);
        } : null;
    }

    if (nextPage) {
        nextPage.classList.toggle('disabled', currentPage === totalPages);
        nextPage.onclick = currentPage < totalPages ? () => {
            // Next page logic
            const newPage = currentPage + 1;
            const startIndex = (newPage - 1) * itemsPerPage;
            const endIndex = startIndex + itemsPerPage;
            const pageStudents = allStudents.slice(startIndex, endIndex);

            const tableView = document.getElementById('tableView');
            const isTableView = !tableView.classList.contains('d-none');

            if (isTableView) {
                renderStudents(pageStudents);
            } else {
                renderStudentsCards(pageStudents);
            }

            updatePaginationForPage(newPage);
        } : null;
    }
}

function updatePaginationForPage(page) {
    const paginationLinks = document.querySelectorAll('#paginationLinks .page-item');
    paginationLinks.forEach((li, index) => {
        li.classList.remove('active');
        const pageNum = parseInt(li.querySelector('.page-link').textContent);
        if (pageNum === page) {
            li.classList.add('active');
        }
    });
}

// Filter function for both views
function filterData() {
    const search = document.querySelector('#search-input')?.value.toLowerCase() || '';
    const classId = document.getElementById('schoolclass-filter')?.value || 'all';
    const statusId = document.getElementById('status-filter')?.value || 'all';
    const gender = document.getElementById('gender-filter')?.value || 'all';

    console.log('Filtering with:', { search, classId, statusId, gender });

    // Filter the allStudents array
    const filteredStudents = allStudents.filter(student => {
        const name = `${student.lastname} ${student.firstname} ${student.othername || ''}`.toLowerCase();
        const admissionNo = (student.admissionNo || '').toLowerCase();

        const matchesSearch = name.includes(search) || admissionNo.includes(search);
        const matchesClass = classId === 'all' || student.schoolclassid == classId;
        const matchesStatus = statusId === 'all' || student.statusId == statusId;
        const matchesGender = gender === 'all' || student.gender === gender;

        return matchesSearch && matchesClass && matchesStatus && matchesGender;
    });

    // Check which view is active
    const tableView = document.getElementById('tableView');
    const isTableView = !tableView.classList.contains('d-none');

    if (isTableView) {
        renderStudents(filteredStudents);
    } else {
        renderStudentsCards(filteredStudents);
    }

    document.getElementById('showingCount').textContent = filteredStudents.length;
}

// Delete multiple students (works for both views)
function deleteMultiple() {
    // Check which view is active
    const tableView = document.getElementById('tableView');
    const isTableView = !tableView.classList.contains('d-none');

    let ids = [];

    if (isTableView) {
        // Table view selection
        ids = Array.from(document.querySelectorAll('input[name="chk_child"]:checked'))
            .map(checkbox => {
                const row = checkbox.closest('tr');
                return row ? row.getAttribute('data-id') : null;
            })
            .filter(id => id !== null);
    } else {
        // Card view selection
        ids = Array.from(document.querySelectorAll('.student-checkbox:checked'))
            .map(checkbox => checkbox.value)
            .filter(id => id !== null);
    }

    if (ids.length === 0) {
        Swal.fire({
            title: "Error!",
            text: "Please select at least one student",
            icon: "error",
            customClass: { confirmButton: "btn btn-primary" },
            buttonsStyling: false
        });
        return;
    }

    Swal.fire({
        title: "Are you sure?",
        text: `You are about to delete ${ids.length} student(s). This action cannot be undone!`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete them!",
        cancelButtonText: "Cancel",
        customClass: {
            confirmButton: "btn btn-danger",
            cancelButton: "btn btn-secondary"
        },
        buttonsStyling: false
    }).then((result) => {
        if (result.isConfirmed && ensureAxios()) {
            // Delete each student individually
            const deletePromises = ids.map(id =>
                axios.delete(`/student/${id}/destroy`)
            );

            Promise.all(deletePromises)
                .then(() => {
                    // Refresh the list
                    fetchStudents();

                    Swal.fire({
                        title: "Deleted!",
                        text: `Successfully deleted ${ids.length} student(s)`,
                        icon: "success",
                        customClass: { confirmButton: "btn btn-primary" },
                        buttonsStyling: false
                    });
                })
                .catch((error) => {
                    console.error('Error deleting students:', error);
                    Swal.fire({
                        title: "Error!",
                        text: error.response?.data?.message || "Failed to delete students",
                        icon: "error",
                        customClass: { confirmButton: "btn btn-primary" },
                        buttonsStyling: false
                    });
                });
        }
    });
}

// Initialize checkboxes for multiple selection (table view)
function initializeCheckboxes() {
    const checkAll = document.getElementById('checkAll');
    if (!checkAll) return;

    checkAll.addEventListener('change', function () {
        document.querySelectorAll('input[name="chk_child"]').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        document.getElementById('remove-actions').classList.toggle('d-none', !this.checked);
    });

    document.querySelectorAll('input[name="chk_child"]').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const allChecked = document.querySelectorAll('input[name="chk_child"]').length ===
                document.querySelectorAll('input[name="chk_child"]:checked').length;
            checkAll.checked = allChecked;
            document.getElementById('remove-actions').classList.toggle('d-none',
                document.querySelectorAll('input[name="chk_child"]:checked').length === 0);
        });
    });
}

// Populate edit form with student data
function populateEditForm(student) {
    console.log('Populating edit form with student:', student);

    // Updated fields array
    const fields = [
        { id: 'editStudentId', value: student.id },
        { id: 'editAdmissionNo', value: student.admissionNo || student.admission_no || '' },
        { id: 'editAdmissionYear', value: student.admissionYear || '' },
        { id: 'editAdmissionDate', value: student.admissionDate ? student.admissionDate.split('T')[0] : '' },
        { id: 'editTitle', value: student.title || '' },
        { id: 'editFirstname', value: student.firstname || student.first_name || '' },
        { id: 'editLastname', value: student.lastname || student.last_name || '' },
        { id: 'editOthername', value: student.othername || student.other_name || student.middle_name || '' },
        { id: 'editPermanentAddress', value: student.permanent_address || '' },
        { id: 'editDOB', value: student.dateofbirth ? student.dateofbirth.split('T')[0] : '' },
        { id: 'editPlaceofbirth', value: student.placeofbirth || '' },
        { id: 'editNationality', value: student.nationality || '' },
        { id: 'editReligion', value: student.religion || '' },
        { id: 'editLastSchool', value: student.last_school || '' },
        { id: 'editLastClass', value: student.last_class || '' },
        { id: 'editSchoolclassid', value: student.schoolclassid || student.class_id || '' },
        { id: 'editTermid', value: student.termid || student.term_id || '' },
        { id: 'editSessionid', value: student.sessionid || student.session_id || '' },
        { id: 'editPhoneNumber', value: student.phone_number || student.phone || '' },
        { id: 'editEmail', value: student.email || '' },
        { id: 'editFutureAmbition', value: student.future_ambition || '' },
        { id: 'editCity', value: student.city || '' },
        { id: 'editState', value: student.state || '' },
        { id: 'editLocal', value: student.local || '' },
        { id: 'editNinNumber', value: student.nin_number || student.nin || '' },
        { id: 'editBloodGroup', value: student.blood_group || '' },
        { id: 'editMotherTongue', value: student.mother_tongue || '' },
        { id: 'editFatherName', value: student.father_name || '' },
        { id: 'editFatherPhone', value: student.father_phone || '' },
        { id: 'editFatherOccupation', value: student.father_occupation || '' },
        { id: 'editFatherCity', value: student.father_city || '' },
        { id: 'editMotherName', value: student.mother_name || '' },
        { id: 'editMotherPhone', value: student.mother_phone || '' },
        { id: 'editParentEmail', value: student.parent_email || '' },
        { id: 'editParentAddress', value: student.parent_address || '' },
        { id: 'editStudentCategory', value: student.student_category || '' },
        { id: 'editSchoolHouse', value: student.school_house || student.sport_house || '' }
    ];

    fields.forEach(({ id, value }) => {
        const element = document.getElementById(id);
        if (element) {
            element.value = value || '';
            console.log(`Set ${id} to:`, value);
        } else {
            console.warn(`Element with ID '${id}' not found`);
        }
    });

    // Set gender
    const genderRadios = document.querySelectorAll('input[name="gender"]');
    if (genderRadios.length > 0) {
        const studentGender = student.gender || '';
        genderRadios.forEach(radio => {
            radio.checked = (radio.value === studentGender);
        });
        console.log('Set gender to:', studentGender);
    }

    // Set status
    const statusRadios = document.querySelectorAll('input[name="statusId"]');
    if (statusRadios.length > 0) {
        const studentStatusId = student.statusId || student.status_id || '';
        statusRadios.forEach(radio => {
            radio.checked = (parseInt(radio.value) === parseInt(studentStatusId));
        });
        console.log('Set statusId to:', studentStatusId);
    }

    // Set student activity status
    const studentStatusRadios = document.querySelectorAll('input[name="student_status"]');
    if (studentStatusRadios.length > 0) {
        const studentActivityStatus = student.student_status || student.status || '';
        studentStatusRadios.forEach(radio => {
            radio.checked = (radio.value === studentActivityStatus);
        });
        console.log('Set student_status to:', studentActivityStatus);
    }

    // Set avatar
    const avatarElement = document.getElementById('editStudentAvatar');
    if (avatarElement) {
        const avatarUrl = student.picture ? `/storage/images/student_avatars/${student.picture}` : defaultAvatar;
        avatarElement.src = avatarUrl;
        avatarElement.setAttribute('data-original-src', avatarUrl);
        console.log('Set avatar to:', avatarUrl);
    }

    // Calculate age if date of birth exists
    if (student.dateofbirth) {
        showage(student.dateofbirth, 'editAge');
    }

    // Update form action
    const form = document.getElementById('editStudentForm');
    if (form && student.id) {
        form.action = `/student/${student.id}`;
        console.log('Updated form action to:', form.action);
    }
}

// Age calculation function
window.showage = function (date, displayId = 'addAge') {
    if (date) {
        const dateString = date.includes('T') ? date.split('T')[0] : date;
        const dob = new Date(dateString);
        const today = new Date();
        let age = today.getFullYear() - dob.getFullYear();
        const monthDiff = today.getMonth() - dob.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < dob.getDate())) {
            age--;
        }
        const ageInputId = displayId === 'addAge' ? 'addAgeInput' : 'editAgeInput';
        const ageInput = document.getElementById(ageInputId);
        if (ageInput) {
            ageInput.value = age;
        }
    } else {
        const ageInputId = displayId === 'addAge' ? 'addAgeInput' : 'editAgeInput';
        const ageInput = document.getElementById(ageInputId);
        if (ageInput) {
            ageInput.value = '';
        }
    }
};

// Initialize the student list
function initializeStudentList() {
    console.log('Initializing student list...');

    // Initial fetch of students
    fetchStudents();

    // Initialize view toggle
    const tableViewBtn = document.getElementById('tableViewBtn');
    const cardViewBtn = document.getElementById('cardViewBtn');

    if (tableViewBtn) {
        tableViewBtn.addEventListener('click', () => toggleView('table'));
    }

    if (cardViewBtn) {
        cardViewBtn.addEventListener('click', () => toggleView('card'));
    }

    // Filter event listeners
    const searchInput = document.querySelector('#search-input');
    const schoolClassFilter = document.getElementById('schoolclass-filter');
    const statusFilter = document.getElementById('status-filter');
    const genderFilter = document.getElementById('gender-filter');

    if (searchInput) {
        searchInput.addEventListener('input', filterData);
    }

    if (schoolClassFilter) {
        schoolClassFilter.addEventListener('change', filterData);
    }

    if (statusFilter) {
        statusFilter.addEventListener('change', filterData);
    }

    if (genderFilter) {
        genderFilter.addEventListener('change', filterData);
    }
}

// Call initializeStudentList on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing student list...');
    initializeStudentList();
});
</script>
