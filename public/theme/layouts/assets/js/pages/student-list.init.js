function ensureAxios() {
    if (typeof axios === 'undefined') {
        console.error('Error: Axios is not defined');
        Swal.fire({
            title: "Error!",
            text: "Axios library is missing",
            icon: "error",
            confirmButtonClass: "btn btn-primary",
            buttonsStyling: true
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
            confirmButtonClass: "btn btn-primary",
            buttonsStyling: true
        });
        return false;
    }
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    return true;
}

function populateStates(stateSelectId, lgaSelectId) {
    const stateSelect = document.getElementById(stateSelectId);
    stateSelect.innerHTML = '<option value="">Select State</option>';

    axios.get('/states_lgas.json').then((response) => {
        console.log("States data received:", response.data);
        let states = response.data;

        if (Array.isArray(states)) {
            states = states.map(item => ({
                name: item.state,
                lgas: item.lgas
            }));
        } else if (states && Array.isArray(states.states)) {
            states = states.states;
        } else {
            throw new Error("Invalid or empty states data format");
        }

        if (!states.length) {
            throw new Error("No states data available");
        }

        states.forEach(state => {
            if (!state.name) {
                console.warn("Skipping state with missing name:", state);
                return;
            }
            const option = document.createElement('option');
            option.value = state.name;
            option.textContent = state.name;
            stateSelect.appendChild(option);
        });

        stateSelect.addEventListener('change', function () {
            populateLGAs(this.value, lgaSelectId);
        });
    }).catch((error) => {
        console.error('Error loading states:', error.message, error.response?.status);
        Swal.fire({
            title: "Error!",
            text: error.response?.status === 404 
                ? "States data file not found at /states_lgas.json"
                : error.message || "Failed to load states",
            icon: "error",
            confirmButtonClass: "btn btn-primary",
            buttonsStyling: true
        });
        const option = document.createElement('option');
        option.value = "Other";
        option.textContent = "Other (Enter manually)";
        stateSelect.appendChild(option);
    });
}

function populateLGAs(state, lgaSelectId) {
    const lgaSelect = document.getElementById(lgaSelectId);
    lgaSelect.innerHTML = '<option value="">Select Local Government</option>';

    if (!state || state === 'Other') {
        const option = document.createElement('option');
        option.value = "Other";
        option.textContent = "Other (Enter manually)";
        lgaSelect.appendChild(option);
        return;
    }

    axios.get('/states_lgas.json').then((response) => {
        console.log("LGAs data received for state:", state, response.data);
        let states = response.data;

        if (Array.isArray(states)) {
            states = states.map(item => ({
                name: item.state,
                lgas: item.lgas
            }));
        } else if (states && Array.isArray(states.states)) {
            states = states.states;
        } else {
            throw new Error("Invalid states data format");
        }

        const selectedState = states.find(s => s.name === state);
        if (selectedState && Array.isArray(selectedState.lgas)) {
            selectedState.lgas.forEach(lga => {
                const option = document.createElement('option');
                option.value = lga;
                option.textContent = lga;
                lgaSelect.appendChild(option);
            });
        } else {
            console.warn("No LGAs found for state:", state);
            const option = document.createElement('option');
            option.value = "Other";
            option.textContent = "Other (Enter manually)";
            lgaSelect.appendChild(option);
        }
    }).catch((error) => {
        console.error('Error loading LGAs:', error.message, error.response?.status);
        Swal.fire({
            title: "Error!",
            text: error.response?.status === 404 
                ? "States data file not found at /states_lgas.json"
                : error.message || "Failed to load LGAs",
            icon: "error",
            confirmButtonClass: "btn btn-primary",
            buttonsStyling: true
        });
        const option = document.createElement('option');
        option.value = "Other";
        option.textContent = "Other (Enter manually)";
        lgaSelect.appendChild(option);
    });
}

let studentList;
let allStudents = [];
const itemsPerPage = 10;

function fetchStudents() {
    if (!ensureAxios()) return;
    console.log('Fetching students from /students/data');
    axios.get('/students/data')
        .then((response) => {
            console.log('Students data received:', response.data);
            if (!response.data.success) {
                throw new Error(response.data.message || 'Failed to fetch students');
            }
            allStudents = response.data.students || [];
            document.querySelector('#totalStudents').textContent = allStudents.length;
            document.querySelector('#totalCount').textContent = allStudents.length;
            renderStudents(allStudents);
            initializeList();
            filterData();
        })
        .catch((error) => {
            console.error('Error fetching students:', {
                message: error.message,
                status: error.response?.status,
                data: error.response?.data,
                url: '/students/data'
            });
            Swal.fire({
                title: "Error!",
                text: error.response?.data?.message || error.message || "Failed to load students. Check console for details.",
                icon: "error",
                confirmButtonClass: "btn btn-primary",
                buttonsStyling: true
            });
            renderStudents([]);
        });
}

function renderStudents(students) {
    const tbody = document.getElementById('studentTableBody');
    tbody.innerHTML = '';
    students.forEach(student => {
        const studentImage = student.picture 
            ? '/storage/' + student.picture 
            : '/storage/student_avatars/unnamed.jpg';
        const row = document.createElement('tr');
        const actionButtons = [];
        if (window.appPermissions.canShowStudent) {
            actionButtons.push(`<li><a href="/student/${student.id||''}" class="btn btn-subtle-primary btn-icon btn-sm"><i class="ph-eye"></i></a></li>`);
        }
        if (window.appPermissions.canUpdateStudent) {
            actionButtons.push(`<li><a href="javascript:void(0);" class="btn btn-subtle-secondary btn-icon btn-sm edit-item-btn" data-bs-toggle="modal" data-bs-target="#editStudentModal" data-id="${student.id||''}"><i class="ph-pencil"></i></a></li>`);
        }
        if (window.appPermissions.canDeleteStudent) {
            actionButtons.push(`<li><a href="javascript:void(0);" class="btn btn-subtle-danger btn-icon btn-sm remove-item-btn" data-id="${student.id||''}"><i class="ph-trash"></i></a></li>`);
        }
        row.innerHTML = `
            <td class="id" data-id="${student.id||''}">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="chk_child">
                </div>
            </td>
            <td class="name" data-name="${student.lastname||''} ${student.firstname||''} ${student.othername||''}">
                <div class="d-flex align-items-center">
                    <div class="symbol symbol-50px me-3">
                        <img src="${studentImage}" alt="" class="rounded-circle avatar-sm student-image" style="object-fit:cover;" data-bs-toggle="modal" data-bs-target="#imageViewModal" data-image="${studentImage}"/>
                    </div>
                    <div>
                        <h6 class="mb-0">
                            <a href="/student/${student.id||''}" class="text-reset products">
                                <b>${student.lastname||''}</b> ${student.firstname||''} ${student.othername||''}
                            </a>
                        </h6>
                    </div>
                </div>
            </td>
            <td class="admissionNo" data-admissionNo="${student.admissionNo||''}">${student.admissionNo||''}</td>
            <td class="class" data-class="${student.schoolclassid||''}">${student.schoolclass||''} - ${student.arm||''}</td>
            <td class="status" data-status="${student.statusId||''}">${student.statusId==1?'Old Student':student.statusId==2?'New Student':''}</td>
            <td class="gender" data-gender="${student.gender||''}">${student.gender||''}</td>
            <td class="datereg">${student.created_at ? new Date(student.created_at).toISOString().split('T')[0] : ''}</td>
            <td>
                <ul class="d-flex gap-2 list-unstyled mb-0">
                    ${actionButtons.join('')}
                </ul>
            </td>
        `;
        tbody.appendChild(row);
    });
    initializeCheckboxes();
}

function initializeList() {
    if (typeof List === 'undefined') {
        console.error('List.js is not loaded');
        Swal.fire({
            title: "Error!",
            text: "List.js library is missing",
            icon: "error",
            confirmButtonClass: "btn btn-primary",
            buttonsStyling: true
        });
        return;
    }
    const options = {
        valueNames: ['name', 'admissionNo', 'class', 'status', 'gender', 'datereg'],
        page: itemsPerPage,
        pagination: true
    };
    studentList = new List('studentList', options);
    studentList.on('updated', function () {
        updatePagination();
        document.getElementById('showingCount').textContent = studentList.visibleItems.length;
        document.getElementById('totalCount').textContent = studentList.items.length;
        document.getElementById('totalStudents').textContent = studentList.items.length;
    });
}

function updatePagination() {
    if (!studentList) return;
    const totalItems = studentList.items.length;
    const totalPages = Math.ceil(totalItems / itemsPerPage);
    const currentPage = studentList.page ? Math.ceil(studentList.i / itemsPerPage) : 1;
    const paginationLinks = document.getElementById('paginationLinks');
    paginationLinks.innerHTML = '';

    for (let i = 1; i <= totalPages; i++) {
        const li = document.createElement('li');
        li.className = `page-item ${i === currentPage ? 'active' : ''}`;
        li.innerHTML = `<a class="page-link" href="javascript:void(0);">${i}</a>`;
        li.addEventListener('click', () => {
            studentList.show((i - 1) * itemsPerPage + 1, itemsPerPage);
        });
        paginationLinks.appendChild(li);
    }

    document.getElementById('prevPage').classList.toggle('disabled', currentPage === 1);
    document.getElementById('nextPage').classList.toggle('disabled', currentPage === totalPages);
    document.getElementById('prevPage').onclick = currentPage > 1 ? () => studentList.show((currentPage - 2) * itemsPerPage + 1, itemsPerPage) : null;
    document.getElementById('nextPage').onclick = currentPage < totalPages ? () => studentList.show(currentPage * itemsPerPage + 1, itemsPerPage) : null;
}

function filterData() {
    if (!studentList) return;
    const search = document.querySelector('.search').value.toLowerCase();
    const classId = document.getElementById('idClass').value;
    const statusId = document.getElementById('idStatus').value;
    const gender = document.getElementById('idGender').value;

    studentList.filter(item => {
        const name = item.values().name.toLowerCase();
        const admissionNo = item.values().admissionNo.toLowerCase();
        const classValue = item.elm.querySelector('.class').dataset.class;
        const statusValue = item.elm.querySelector('.status').dataset.status;
        const genderValue = item.elm.querySelector('.gender').dataset.gender;

        const matchesSearch = name.includes(search) || admissionNo.includes(search);
        const matchesClass = classId === 'all' || classValue === classId;
        const matchesStatus = statusId === 'all' || statusValue === statusId;
        const matchesGender = gender === 'all' || genderValue === gender;

        return matchesSearch && matchesClass && matchesStatus && matchesGender;
    });
}

function deleteMultiple() {
    const ids = Array.from(document.querySelectorAll('input[name="chk_child"]:checked'))
        .map(checkbox => checkbox.closest('tr').querySelector('.id').dataset.id);

    if (ids.length === 0) {
        Swal.fire({
            title: "Error!",
            text: "Please select at least one student",
            icon: "error",
            confirmButtonClass: "btn btn-primary",
            buttonsStyling: true
        });
        return;
    }

    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn btn-primary",
        cancelButtonClass: "btn btn-light",
        buttonsStyling: true
    }).then((result) => {
        if (result.isConfirmed && ensureAxios()) {
            axios.post('/students/destroy-multiple', { ids }).then(() => {
                ids.forEach(id => {
                    const row = document.querySelector(`tr[data-id="${id}"]`);
                    if (row) row.remove();
                });
                studentList.reIndex();
                Swal.fire({
                    title: "Deleted!",
                    text: "Students have been deleted",
                    icon: "success",
                    confirmButtonClass: "btn btn-primary",
                    buttonsStyling: true
                });
                document.getElementById('checkAll').checked = false;
                document.getElementById('remove-actions').classList.add('d-none');
            }).catch((error) => {
                console.error('Error deleting students:', error);
                Swal.fire({
                    title: "Error!",
                    text: error.response?.data?.message || "Failed to delete students",
                    icon: "error",
                    confirmButtonClass: "btn btn-primary",
                    buttonsStyling: true
                });
            });
        }
    });
}

function initializeCheckboxes() {
    document.getElementById('checkAll').addEventListener('change', function () {
        document.querySelectorAll('input[name="chk_child"]').forEach(checkbox => {
            checkbox.checked = this.checked;
        });
        document.getElementById('remove-actions').classList.toggle('d-none', !this.checked);
    });

    document.querySelectorAll('input[name="chk_child"]').forEach(checkbox => {
        checkbox.addEventListener('change', function () {
            const allChecked = document.querySelectorAll('input[name="chk_child"]').length ===
                document.querySelectorAll('input[name="chk_child"]:checked').length;
            document.getElementById('checkAll').checked = allChecked;
            document.getElementById('remove-actions').classList.toggle('d-none',
                document.querySelectorAll('input[name="chk_child"]:checked').length === 0);
        });
    });
}

function showage(dob, targetId = 'addAge') {
    const ageInputId = targetId === 'addAge' ? 'addAgeInput' : 'editAgeInput';
    const ageDisplay = document.getElementById(targetId);
    const ageInput = document.getElementById(ageInputId);
    
    if (!dob || isNaN(Date.parse(dob))) {
        if (ageDisplay) ageDisplay.textContent = '';
        if (ageInput) ageInput.value = '';
        return;
    }
    
    const birthDate = new Date(dob);
    const today = new Date();
    let age = today.getFullYear() - birthDate.getFullYear();
    const monthDiff = today.getMonth() - birthDate.getMonth();
    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
        age--;
    }
    if (age < 0) {
        age = 0;
    }
    
    if (ageDisplay) ageDisplay.textContent = `Age: ${age} years`;
    if (ageInput) ageInput.value = age;
}

function initializeStudentList() {
    populateStates('addState', 'addLocal');
    populateStates('editState', 'editLocal');
    fetchStudents();

    document.querySelector('.search').addEventListener('input', filterData);
    document.getElementById('idClass').addEventListener('change', filterData);
    document.getElementById('idStatus').addEventListener('change', filterData);
    document.getElementById('idGender').addEventListener('change', filterData);

    document.getElementById('avatar').addEventListener('change', function(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('addStudentAvatar');
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire({
                    title: "Error!",
                    text: "File size exceeds 2MB limit.",
                    icon: "error",
                    confirmButtonClass: "btn btn-info",
                    buttonsStyling: false
                });
                event.target.value = '';
                preview.style.display = 'none';
                return;
            }
            const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    title: "Error!",
                    text: "Only PNG, JPG, and JPEG files are allowed.",
                    icon: "error",
                    confirmButtonClass: "btn btn-info",
                    buttonsStyling: false
                });
                event.target.value = '';
                preview.style.display = 'none';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
            };
            reader.readAsDataURL(file);
        } else {
            preview.src = '/theme/layouts/assets/media/avatars/blank.png';
            preview.style.display = 'none';
        }
    });

    document.getElementById('editAvatar').addEventListener('change', function(event) {
        const file = event.target.files[0];
        const preview = document.getElementById('editStudentAvatar');
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                Swal.fire({
                    title: "Error!",
                    text: "File size exceeds 2MB limit.",
                    icon: "error",
                    confirmButtonClass: "btn btn-info",
                    buttonsStyling: false
                });
                event.target.value = '';
                preview.src = preview.getAttribute('data-original-src') || '/theme/layouts/assets/media/avatars/blank.png';
                return;
            }
            const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
            if (!allowedTypes.includes(file.type)) {
                Swal.fire({
                    title: "Error!",
                    text: "Only PNG, JPG, and JPEG files are allowed.",
                    icon: "error",
                    confirmButtonClass: "btn btn-info",
                    buttonsStyling: false
                });
                event.target.value = '';
                preview.src = preview.getAttribute('data-original-src') || '/theme/layouts/assets/media/avatars/blank.png';
                return;
            }
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
            };
            reader.readAsDataURL(file);
        } else {
            preview.src = preview.getAttribute('data-original-src') || '/theme/layouts/assets/media/avatars/blank.png';
        }
    });

    document.getElementById('studentTableBody').addEventListener('click', function(e) {
        if (e.target.closest('.edit-item-btn')) {
            const button = e.target.closest('.edit-item-btn');
            const avatarImg = document.getElementById('editStudentAvatar');
            avatarImg.setAttribute('data-original-src', avatarImg.src);
            const id = button.getAttribute("data-id");
            console.log("Edit button clicked for student ID:", id);
            if (!ensureAxios()) return;

            axios.get(`/student/${id}/edit`).then((response) => {
                console.log("Student data received:", response.data);
                const student = response.data.student;
                if (!student) {
                    throw new Error("Student data is empty");
                }

                const fields = [
                    { id: "editStudentId", value: student.id },
                    { id: "editAdmissionNo", value: student.admissionNo },
                    { id: "editTittle", value: student.title || '' },
                    { id: "editFirstname", value: student.firstname },
                    { id: "editLastname", value: student.lastname },
                    { id: "editOthername", value: student.othername || '' },
                    { id: "editHomeAddress", value: student.home_address },
                    { id: "editHomeAddress2", value: student.home_address2 },
                    { id: "editDOB", value: student.dateofbirth },
                    { id: "editPlaceofbirth", value: student.placeofbirth },
                    { id: "editNationality", value: student.nationality || '' },
                    { id: "editReligion", value: student.religion || '' },
                    { id: "editLastSchool", value: student.last_school },
                    { id: "editLastClass", value: student.last_class },
                    { id: "editSchoolclassid", value: student.schoolclassid || '' },
                    { id: "editTermid", value: student.termid || '' },
                    { id: "editSessionid", value: student.sessionid || '' }
                ];

                fields.forEach(({ id, value }) => {
                    const element = document.getElementById(id);
                    if (element) {
                        element.value = value || '';
                    } else {
                        console.warn(`Element with ID '${id}' not found`);
                    }
                });

                const genderRadios = document.querySelectorAll('input[name="gender"]');
                genderRadios.forEach(radio => {
                    radio.checked = (radio.value === student.gender);
                });
                console.log(`Set gender to: ${student.gender}`);

                const statusRadios = document.querySelectorAll('input[name="statusId"]');
                statusRadios.forEach(radio => {
                    radio.checked = (parseInt(radio.value) === parseInt(student.statusId));
                });
                console.log(`Set status to: ${student.statusId}`);

                const avatarElement = document.getElementById("editStudentAvatar");
                if (avatarElement) {
                    avatarElement.src = student.picture ? `/storage/${student.picture}` : '/storage/student_avatars/unnamed.jpg';
                    avatarElement.setAttribute('data-original-src', student.picture ? `/storage/${student.picture}` : '/storage/student_avatars/unnamed.jpg');
                }

                const stateSelect = document.getElementById("editState");
                const lgaSelect = document.getElementById("editLocal");
                if (student.state && stateSelect) {
                    stateSelect.value = student.state;
                    setTimeout(() => {
                        populateLGAs(student.state, 'editLocal');
                        setTimeout(() => {
                            if (lgaSelect) {
                                lgaSelect.value = student.local || '';
                            }
                        }, 200);
                    }, 100);
                } else if (lgaSelect) {
                    lgaSelect.innerHTML = '<option value="">Select Local Government</option>';
                }

                if (student.dateofbirth) {
                    showage(student.dateofbirth, 'editAge');
                }

                const form = document.getElementById('editStudentForm');
                if (form) {
                    form.action = `/student/${id}`;
                }
            }).catch((error) => {
                console.error("Error fetching student:", {
                    message: error.message,
                    status: error.response?.status,
                    data: error.response?.data
                });
                Swal.fire({
                    title: "Error!",
                    text: error.response?.data?.message || "Failed to load student data. Check console for details.",
                    icon: "error",
                    confirmButtonClass: "btn btn-primary",
                    buttonsStyling: false
                });
            });
        }
    });

    document.getElementById('addStudentForm').addEventListener('submit', function (e) {
        e.preventDefault();
        if (!ensureAxios()) return;

        const formData = new FormData(this);
        for (let pair of formData.entries()) {
            console.log(`${pair[0]}: ${pair[1]}`);
        }

        axios.post(this.action, formData).then((response) => {
            Swal.fire({
                title: "Success!",
                text: "Student added successfully",
                icon: "success",
                confirmButtonClass: "btn btn-primary",
                buttonsStyling: true
            }).then(() => {
                fetchStudents();
                document.getElementById('addStudentModal').querySelector('.btn-close').click();
            });
        }).catch((error) => {
            console.error('Error adding student:', error.response?.data);
            Swal.fire({
                title: "Error!",
                text: error.response?.data?.message || "Failed to add student",
                icon: "error",
                confirmButtonClass: "btn btn-primary",
                buttonsStyling: true
            });
        });
    });

    document.getElementById('editStudentForm').addEventListener('submit', function (e) {
        e.preventDefault();
        if (!ensureAxios()) return;

        const id = document.getElementById('editStudentId').value;
        const formData = new FormData(this);
        for (let pair of formData.entries()) {
            console.log(`${pair[0]}: ${pair[1]}`);
        }

        axios.post(this.action, formData, {
            headers: { 'X-HTTP-Method-Override': 'PATCH' }
        }).then((response) => {
            Swal.fire({
                title: "Success!",
                text: "Student updated successfully",
                icon: "success",
                confirmButtonClass: "btn btn-primary",
                buttonsStyling: true
            }).then(() => {
                fetchStudents();
                document.getElementById('editStudentModal').querySelector('.btn-close').click();
            });
        }).catch((error) => {
            console.error('Error updating student:', error.response?.data);
            Swal.fire({
                title: "Error!",
                text: error.response?.data?.message || "Failed to update student",
                icon: "error",
                confirmButtonClass: "btn btn-primary",
                buttonsStyling: true
            });
        });
    });

    document.getElementById('studentTableBody').addEventListener('click', function(e) {
        if (e.target.closest('.remove-item-btn')) {
            const button = e.target.closest('.remove-item-btn');
            const id = button.getAttribute('data-id');
            Swal.fire({
                title: "Are you sure?",
                text: "You won't be able to revert this!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonClass: "btn btn-primary",
                cancelButtonClass: "btn btn-light",
                buttonsStyling: true
            }).then((result) => {
                if (result.isConfirmed && ensureAxios()) {
                    axios.delete(`/student/${id}/destroy`).then(() => {
                        const row = button.closest('tr');
                        if (row) row.remove();
                        studentList.reIndex();
                        Swal.fire({
                            title: "Deleted!",
                            text: "Student has been deleted",
                            icon: "success",
                            confirmButtonClass: "btn btn-primary",
                            buttonsStyling: true
                        });
                    }).catch((error) => {
                        console.error('Error deleting student:', error);
                        Swal.fire({
                            title: "Error!",
                            text: error.response?.data?.message || "Failed to delete student",
                            icon: "error",
                            confirmButtonClass: "btn btn-primary",
                            buttonsStyling: true
                        });
                    });
                }
            });
        }
    });

    // Add event listener for image view modal
    document.getElementById('imageViewModal').addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;
        const imageSrc = button.getAttribute('data-image');
        const modalImage = this.querySelector('#enlargedImage');
        modalImage.src = imageSrc;
    });
}