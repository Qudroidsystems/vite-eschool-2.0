console.log("subjectvetting.init.js is loaded and executing!");

// Debug counter for chart initialization
let chartInitCount = 0;

// Verify dependencies
try {
    if (typeof axios === 'undefined') throw new Error("Axios is not loaded");
    if (typeof Swal === 'undefined') throw new Error("SweetAlert2 is not loaded");
    if (typeof bootstrap === 'undefined') throw new Error("Bootstrap is not loaded");
    if (typeof List === 'undefined') throw new Error("List.js is not loaded");
    if (typeof Chart === 'undefined') throw new Error("Chart.js is not loaded");
    console.log("All dependencies loaded successfully");
} catch (error) {
    console.error("Dependency check failed:", error);
}

// Set Axios CSRF token globally
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
if (!csrfToken) console.warn("CSRF token not found");

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Check all checkbox
const checkAll = document.getElementById("checkAll");
if (checkAll) {
    checkAll.addEventListener("click", function () {
        console.log("CheckAll clicked");
        const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
        checkboxes.forEach((checkbox) => {
            checkbox.checked = this.checked;
            const row = checkbox.closest("tr");
            row.classList.toggle("table-active", this.checked);
        });
        const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
        const removeActions = document.getElementById("remove-actions");
        if (removeActions) {
            removeActions.classList.toggle("d-none", checkedCount === 0);
        }
    });
}

// Form fields
const addIdField = document.getElementById("add-id-field");
const addUserIdField = document.getElementById("userid");
const addSessionIdField = document.getElementById("sessionid");
const editIdField = document.getElementById("edit-id-field");
const editUserIdField = document.getElementById("edit-userid");
const editTermIdField = document.getElementById("edit-termid");
const editSessionIdField = document.getElementById("edit-sessionid");
const editSubjectClassIdField = document.getElementById("edit-subjectclassid");
const editStatusField = document.getElementById("edit-status");

// Initialize Chart.js bar chart
let vettingStatusChart;

function initializeVettingStatusChart() {
    chartInitCount++;
    console.log(`Attempting to initialize chart (attempt #${chartInitCount}) with data:`, window.vettingStatusCounts);
    
    const ctx = document.getElementById('vettingStatusChart')?.getContext('2d');
    if (!ctx) {
        console.error("Chart canvas not found");
        return;
    }

    if (!window.vettingStatusCounts) {
        console.warn("vettingStatusCounts is undefined, using default data");
        window.vettingStatusCounts = { pending: 0, completed: 0, rejected: 0 };
    }

    if (vettingStatusChart) {
        console.log("Destroying existing chart instance");
        vettingStatusChart.destroy();
    }

    try {
        vettingStatusChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Pending', 'Completed', 'Rejected'],
                datasets: [{
                    label: 'Vetting Assignments',
                    data: [
                        window.vettingStatusCounts.pending || 0,
                        window.vettingStatusCounts.completed || 0,
                        window.vettingStatusCounts.rejected || 0
                    ],
                    backgroundColor: ['#dc3545', '#28a745', '#ffc107'], // Red for Pending, Green for Completed, Yellow for Rejected
                    borderColor: ['#c82333', '#218838', '#e0a800'], // Darker shades for borders
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: Math.max(10, (window.vettingStatusCounts.pending || 0) + 2),
                        ticks: { stepSize: 1 },
                        title: { display: true, text: 'Number of Assignments' }
                    },
                    x: { title: { display: true, text: 'Status' } }
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.dataset.label}: ${context.raw}`;
                            }
                        }
                    }
                }
            }
        });
        console.log("Vetting status chart initialized successfully");
    } catch (error) {
        console.error("Failed to initialize chart:", error);
    }
}

// Checkbox handling
function initializeCheckboxes() {
    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    checkboxes.forEach((checkbox) => {
        checkbox.removeEventListener("change", handleCheckboxChange);
        checkbox.addEventListener("change", handleCheckboxChange);
    });
}

function handleCheckboxChange(e) {
    console.log("Checkbox changed:", e.target.checked);
    const row = e.target.closest("tr");
    row.classList.toggle("table-active", e.target.checked);
    const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
    const removeActions = document.getElementById("remove-actions");
    if (removeActions) {
        removeActions.classList.toggle("d-none", checkedCount === 0);
    }
    const allCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    if (checkAll) {
        checkAll.checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCount;
    }
}

// Create button
const createButton = document.querySelector('.add-btn');
if (createButton) {
    createButton.addEventListener('click', function (e) {
        e.preventDefault();
        console.log("Create Subject Vetting button clicked");
        try {
            const modal = new bootstrap.Modal(document.getElementById("addSubjectVettingModal"));
            modal.show();
            console.log("Add modal opened");
        } catch (error) {
            console.error("Error opening add modal:", error);
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error opening modal",
                text: "Please ensure Bootstrap is loaded and try again.",
                showConfirmButton: true
            });
        }
    });
}

// Event delegation
document.addEventListener('click', function (e) {
    const editBtn = e.target.closest('.edit-item-btn');
    const removeBtn = e.target.closest('.remove-item-btn');
    const image = e.target.closest('.staff-image');
    
    if (editBtn) {
        handleEditClick(e, editBtn);
    } else if (removeBtn) {
        handleRemoveClick(e, removeBtn);
    } else if (image) {
        handleImageClick(e, image);
    }
});

// Image preview
function handleImageClick(e, image) {
    e.preventDefault();
    console.log("Image clicked, attributes:", {
        imageUrl: image.getAttribute('data-image'),
        teacherName: image.getAttribute('data-teachername'),
        fileExists: image.getAttribute('data-file-exists'),
        defaultExists: image.getAttribute('data-default-exists'),
        picture: image.getAttribute('data-picture')
    });
    
    const imageUrl = image.getAttribute('data-image');
    const teacherName = image.getAttribute('data-teachername');
    const fileExists = image.getAttribute('data-file-exists') === 'true';
    const defaultExists = image.getAttribute('data-default-exists') === 'true';
    
    const previewImage = document.getElementById('preview-image');
    const previewTeacherName = document.getElementById('preview-teachername');
    
    if (previewImage && previewTeacherName) {
        if (fileExists || (image.getAttribute('data-picture') === 'none' && defaultExists)) {
            previewImage.src = imageUrl;
            previewTeacherName.textContent = teacherName || 'Unknown Staff';
        } else {
            previewImage.src = '/storage/staff_avatars/unnamed.jpg';
            previewTeacherName.textContent = teacherName || 'Unknown Staff';
            console.warn('Image not found, using default');
        }
        
        previewImage.onerror = function() {
            this.src = '/storage/staff_avatars/unnamed.jpg';
            console.error('Preview image failed to load, falling back to default');
        };
        
        try {
            const modal = new bootstrap.Modal(document.getElementById('imageViewModal'));
            modal.show();
            console.log("Image preview modal opened");
        } catch (error) {
            console.error("Error opening image preview modal:", error);
            Swal.fire({
                position: "center",
                icon: "error",
                title: "Error opening image preview",
                text: "Failed to open modal. Please ensure Bootstrap is loaded.",
                showConfirmButton: true
            });
        }
    } else {
        console.error("Preview elements not found");
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Error opening image preview",
            text: "Preview elements not found.",
            showConfirmButton: true
        });
    }
}

// Delete single assignment
function handleRemoveClick(e, button) {
    e.preventDefault();
    console.log("Remove button clicked");
    
    const itemId = button.closest("tr").querySelector(".id")?.getAttribute("data-id");
    const deleteUrl = button.closest("tr").getAttribute("data-url");
    
    if (!itemId || !deleteUrl) {
        console.error("Item ID or delete URL not found");
        return;
    }
    
    const modal = new bootstrap.Modal(document.getElementById("deleteRecordModal"));
    modal.show();
    console.log("Delete modal opened");

    const deleteButton = document.getElementById("delete-record");
    if (deleteButton) {
        deleteButton.onclick = null;
        deleteButton.onclick = function () {
            console.log("Deleting subject vetting assignment:", itemId);
            
            axios.delete(deleteUrl)
                .then(function (response) {
                    console.log("Delete success:", response.data);
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: response.data.message || "Subject Vetting assignment deleted successfully!",
                        showConfirmButton: false,
                        timer: 2000,
                        showCloseButton: true
                    });
                    modal.hide();
                    setTimeout(() => refreshTable(), 500);
                })
                .catch(function (error) {
                    console.error("Delete error:", error.response?.data || error);
                    Swal.fire({
                        position: "center",
                        icon: "error",
                        title: "Error deleting subject vetting assignment",
                        text: error.response?.data?.message || "An error occurred",
                        showConfirmButton: true
                    });
                    modal.hide();
                });
        };
    }
}

// Edit assignment
function handleEditClick(e, button) {
    e.preventDefault();
    console.log("Edit button clicked");
    
    const itemId = button.closest("tr").querySelector(".id")?.getAttribute("data-id");
    const tr = button.closest("tr");
    
    if (!itemId) {
        console.error("Item ID not found");
        return;
    }
    
    const vettingUserId = tr.querySelector(".vetting_username")?.getAttribute("data-vetting_userid") || "";
    const subjectClassId = tr.querySelector(".sclass")?.getAttribute("data-schoolclassid") || "";
    const termId = tr.querySelector(".termname")?.getAttribute("data-termid") || "";
    const sessionId = tr.querySelector(".sessionname")?.getAttribute("data-sessionid") || "";
    const status = tr.querySelector(".status")?.textContent || "pending";
    
    console.log("Edit data:", { itemId, vettingUserId, subjectClassId, termId, sessionId, status });
    
    if (editIdField) editIdField.value = itemId;
    if (editUserIdField) editUserIdField.value = vettingUserId;
    if (editSubjectClassIdField) editSubjectClassIdField.value = subjectClassId;
    if (editTermIdField) editTermIdField.value = termId;
    if (editSessionIdField) editSessionIdField.value = sessionId;
    if (editStatusField) editStatusField.value = status;

    try {
        const modal = new bootstrap.Modal(document.getElementById("editModal"));
        modal.show();
        console.log("Edit modal opened with data populated");
    } catch (error) {
        console.error("Error opening edit modal:", error);
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Error opening edit modal",
            text: "Please try again or contact support.",
            showConfirmButton: true
        });
    }
}

// Clear form fields
function clearAddFields() {
    if (addIdField) addIdField.value = "";
    if (addUserIdField) addUserIdField.value = "";
    if (addSessionIdField) addSessionIdField.value = "";
    document.querySelectorAll('#addSubjectVettingModal input[name="termid[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
    document.querySelectorAll('#addSubjectVettingModal input[name="subjectclassid[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
}

function clearEditFields() {
    if (editIdField) editIdField.value = "";
    if (editUserIdField) editUserIdField.value = "";
    if (editTermIdField) editTermIdField.value = "";
    if (editSessionIdField) editSessionIdField.value = "";
    if (editSubjectClassIdField) editSubjectClassIdField.value = "";
    if (editStatusField) editStatusField.value = "pending";
}

// Delete multiple assignments
function deleteMultiple() {
    console.log("Delete multiple triggered");
    
    const ids_array = [];
    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]:checked');
    
    checkboxes.forEach((checkbox) => {
        const id = checkbox.closest("tr").querySelector(".id")?.getAttribute("data-id");
        if (id) ids_array.push(id);
    });
    
    if (ids_array.length === 0) {
        Swal.fire({
            title: "Please select at least one checkbox",
            confirmButtonClass: "btn btn-info",
            buttonsStyling: false,
            showCloseButton: true
        });
        return;
    }
    
    Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonClass: "btn btn-primary w-xs me-2 mt-2",
        cancelButtonClass: "btn btn-danger w-xs mt-2",
        confirmButtonText: "Yes, delete it!",
        buttonsStyling: false,
        showCloseButton: true
    }).then((result) => {
        if (result.isConfirmed) {
            Promise.all(ids_array.map((id) => axios.delete(`/subjectvetting/${id}`)))
                .then(() => {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Your subject vetting assignments have been deleted.",
                        icon: "success",
                        confirmButtonClass: "btn btn-info w-xs mt-2",
                        buttonsStyling: false
                    });
                    setTimeout(() => refreshTable(), 1000);
                })
                .catch((error) => {
                    console.error("Bulk delete error:", error);
                    Swal.fire({
                        title: "Error!",
                        text: error.response?.data?.message || "Failed to delete subject vetting assignments",
                        icon: "error",
                        confirmButtonClass: "btn btn-info w-xs mt-2",
                        buttonsStyling: false
                    });
                });
        }
    });
}

// Initialize List.js
let subjectVettingList;

function initializeListJS() {
    const subjectVettingListContainer = document.getElementById('kt_subject_vetting_table');
    const hasRows = document.querySelectorAll('#kt_subject_vetting_table tbody tr:not(.noresult)').length > 0;
    
    if (subjectVettingListContainer && hasRows) {
        try {
            if (subjectVettingList) {
                subjectVettingList.clear();
            }
            
            subjectVettingList = new List('subjectVettingList', {
                valueNames: ['sn', 'vetting_username', 'subjectname', 'sclass', 'schoolarm', 'teachername', 'termname', 'sessionname', 'status', 'datereg'],
                page: 10,
                pagination: {
                    innerWindow: 2,
                    outerWindow: 1,
                    left: 0,
                    right: 0,
                    paginationClass: "listjs-pagination"
                },
                listClass: 'list'
            });
            
            console.log("List.js initialized with pagination");
            
            subjectVettingList.on('updated', function () {
                const totalRecords = subjectVettingList.items.length;
                const visibleRecords = subjectVettingList.visibleItems.length;
                const showingRecords = Math.min(visibleRecords, subjectVettingList.page);
                const currentPage = Math.ceil((subjectVettingList.i - 1) / subjectVettingList.page) + 1;
                
                document.getElementById('showing-records').textContent = showingRecords;
                document.getElementById('total-records').textContent = totalRecords;
                document.getElementById('total-records-footer').textContent = totalRecords;
                
                const pagination = document.querySelector('.listjs-pagination');
                if (pagination) {
                    pagination.querySelectorAll('.page').forEach(page => {
                        page.classList.remove('active');
                        if (parseInt(page.textContent) === currentPage) {
                            page.classList.add('active');
                        }
                    });
                }
                
                const noResultRow = document.querySelector('.noresult');
                if (noResultRow) {
                    noResultRow.style.display = visibleRecords === 0 ? 'block' : 'none';
                }
                
                initializeCheckboxes();
            });
            
            subjectVettingList.update();
            
        } catch (error) {
            console.error("List.js initialization failed:", error);
        }
    } else {
        console.warn("No subject vetting assignments available for List.js initialization");
        document.getElementById('showing-records').textContent = 0;
        document.getElementById('total-records').textContent = 0;
        document.getElementById('total-records-footer').textContent = 0;
    }
}

// Filter data
function filterData() {
    const searchInput = document.querySelector(".search-box input.search");
    const searchValue = searchInput?.value || "";
    
    console.log("Filtering with search:", searchValue);
    
    if (subjectVettingList) {
        subjectVettingList.search(searchValue, ['sn', 'vetting_username', 'subjectname', 'sclass', 'schoolarm', 'teachername', 'termname', 'sessionname', 'status']);
        subjectVettingList.update();
    }
}

// Refresh table and chart
let isRefreshing = false;

function refreshTable() {
    if (isRefreshing) {
        console.log("Refresh table skipped: Previous request still in progress");
        return;
    }

    isRefreshing = true;
    console.log("Refreshing table and chart via AJAX...");
    
    axios.get('/subjectvetting', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.data || !response.data.subjectvettings) {
            throw new Error('Invalid response data structure');
        }

        console.log("Refresh response:", response.data);

        // Clear existing items
        if (subjectVettingList) {
            subjectVettingList.clear();
            
            // Add new items
            response.data.subjectvettings.forEach((item, index) => {
                subjectVettingList.add({
                    sn: index + 1,
                    vetting_username: item.vetting_username,
                    subjectname: `${item.subjectname} ${item.subjectcode ? `(${item.subjectcode})` : ''}`,
                    sclass: item.sclass,
                    schoolarm: item.schoolarm || '',
                    teachername: item.teachername || '',
                    termname: item.termname,
                    sessionname: item.sessionname,
                    status: item.status,
                    datereg: item.updated_at.split(' ')[0],
                    className: item.status === 'completed' ? 'table-success' :
                               item.status === 'pending' ? 'table-danger' :
                               item.status === 'rejected' ? 'table-warning' : ''
                });
            });
            
            // Update the list
            subjectVettingList.update();
            
            // Update chart data
            window.vettingStatusCounts = response.data.statusCounts || { 
                pending: 0, 
                completed: 0, 
                rejected: 0 
            };
            
            // Reinitialize chart
            initializeVettingStatusChart();
            
            // Reinitialize checkboxes
            initializeCheckboxes();
        }
    })
    .catch(error => {
        console.error("Error refreshing table and chart:", error);
        
        let errorMessage = "An error occurred while refreshing the data.";
        if (error.response) {
            errorMessage = error.response.data.message || errorMessage;
        }
        
        Swal.fire({
            icon: "error",
            title: "Error refreshing data",
            text: errorMessage,
            showConfirmButton: true
        });
    })
    .finally(() => {
        isRefreshing = false;
    });
}

// Add assignment
const addSubjectVettingForm = document.getElementById("add-subjectvetting-form");
if (addSubjectVettingForm) {
    addSubjectVettingForm.addEventListener("submit", function (e) {
        e.preventDefault();
        console.log("Add form submitted at", new Date().toISOString());

        const errorMsg = document.getElementById("alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");

        const formData = new FormData(addSubjectVettingForm);
        const userId = formData.get('userid');
        const termIds = formData.getAll('termid[]');
        const sessionId = formData.get('sessionid');
        const subjectClassIds = [...new Set(formData.getAll('subjectclassid[]'))]; // Remove duplicates

        console.log("Form data (before sending):", { userId, termIds, sessionId, subjectClassIds });

        if (!userId || userId === "") {
            if (errorMsg) {
                errorMsg.innerHTML = "Please select a staff member";
                errorMsg.classList.remove("d-none");
            }
            return;
        }

        if (termIds.length === 0) {
            if (errorMsg) {
                errorMsg.innerHTML = "Please select at least one term";
                errorMsg.classList.remove("d-none");
            }
            return;
        }

        if (!sessionId || sessionId === "") {
            if (errorMsg) {
                errorMsg.innerHTML = "Please select a session";
                errorMsg.classList.remove("d-none");
            }
            return;
        }
        
        if (subjectClassIds.length === 0) {
            if (errorMsg) {
                errorMsg.innerHTML = "Please select at least one subject-class";
                errorMsg.classList.remove("d-none");
            }
            return;
        }

        const submitBtn = document.getElementById("add-btn");
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = "Adding...";
        }

        console.log("Sending add request:", { userId, termIds, sessionId, subjectClassIds });

        axios.post('/subjectvetting', {
            userid: userId,
            termid: termIds,
            sessionid: sessionId,
            subjectclassid: subjectClassIds
        }, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
        })
        .then(function (response) {
            console.log("Add success:", response.data);
            
            const modalElement = document.getElementById("addSubjectVettingModal");
            const modal = bootstrap.Modal.getInstance(modalElement);
            
            if (modal) {
                modal.hide();
            }
            
            setTimeout(() => {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: response.data.message || "Subject Vetting assignment(s) added successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
                
                setTimeout(() => refreshTable(), 500);
            }, 300);
        })
        .catch(function (error) {
            console.error("Add error:", error.response?.data || error);
            
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = "Add Subject Vetting Assignment";
            }
            
            if (errorMsg) {
                errorMsg.innerHTML = error.response?.data?.message ||
                    Object.values(error.response?.data?.errors || {}).flat().join(", ") ||
                    "Error adding subject vetting assignment";
                errorMsg.classList.remove("d-none");
            }
        });
    });
}

// Edit assignment
const editSubjectVettingForm = document.getElementById("edit-subjectvetting-form");
if (editSubjectVettingForm) {
    editSubjectVettingForm.addEventListener("submit", function (e) {
        e.preventDefault();
        console.log("Edit form submitted");
        
        const errorMsg = document.getElementById("edit-alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");
        
        const formData = new FormData(editSubjectVettingForm);
        const userId = formData.get('userid');
        const termId = formData.get('termid');
        const sessionId = formData.get('sessionid');
        const subjectClassId = formData.get('subjectclassid');
        const status = formData.get('status');
        const id = editIdField?.value;
        
        if (!id || !userId || !termId || !sessionId || !subjectClassId || !status) {
            if (errorMsg) {
                errorMsg.innerHTML = "Please fill all required fields";
                errorMsg.classList.remove("d-none");
                console.warn("Form validation failed: Invalid fields");
            }
            return;
        }
        
        const submitBtn = document.getElementById("update-btn");
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = "Updating...";
        }
        
        console.log("Sending edit request:", { id, userId, termId, sessionId, subjectClassId, status });
        
        axios.put(`/subjectvetting/${id}`, { userid: userId, termid: termId, sessionid: sessionId, subjectclassid: subjectClassId, status })
            .then(function (response) {
                console.log("Edit success:", response.data);
                
                const modalElement = document.getElementById("editModal");
                const modal = bootstrap.Modal.getInstance(modalElement);
                
                if (modal) {
                    modal.hide();
                }
                
                setTimeout(() => {
                    Swal.fire({
                        position: "center",
                        icon: "success",
                        title: response.data.message || "Subject Vetting assignment updated successfully!",
                        showConfirmButton: false,
                        timer: 2000,
                        showCloseButton: true
                    });
                    
                    setTimeout(() => refreshTable(), 500);
                }, 300);
            })
            .catch(function (error) {
                console.error("Edit error:", error.response?.data || error);
                
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = "Update";
                }
                
                if (errorMsg) {
                    errorMsg.innerHTML = error.response?.data?.message ||
                        Object.values(error.response?.data?.errors || {}).flat().join(", ") ||
                        "Error updating subject vetting assignment";
                    errorMsg.classList.remove("d-none");
                }
            });
    });
}

// Modal events
const addModal = document.getElementById("addSubjectVettingModal");
if (addModal) {
    addModal.addEventListener("show.bs.modal", function (e) {
        console.log("Add modal show event");

        const modalLabel = document.getElementById("addModalLabel");
        const addBtn = document.getElementById("add-btn");

        if (modalLabel) modalLabel.innerHTML = "Add Subject Vetting Assignment";
        if (addBtn) {
            addBtn.disabled = true;
            addBtn.innerHTML = "Add Subject Vetting Assignment";
        }

        // Function to update the subject-class checkboxes based on selected terms
        const updateSubjectClassCheckboxes = () => {
            const selectedTermIds = Array.from(
                document.querySelectorAll('#addSubjectVettingModal input[name="termid[]"]:checked')
            ).map(checkbox => checkbox.value);

            const subjectClassCheckboxes = document.querySelectorAll(
                '#addSubjectVettingModal input[name="subjectclassid[]"]'
            );

            subjectClassCheckboxes.forEach(checkbox => {
                const termId = checkbox.getAttribute('data-termid');
                // Enable checkbox if no terms are selected or if its termId matches any selected term
                const isEnabled = selectedTermIds.length === 0 || selectedTermIds.includes(termId);
                checkbox.disabled = !isEnabled;
                checkbox.closest('.form-check').style.opacity = isEnabled ? '1' : '0.5';
                if (!isEnabled) checkbox.checked = false; // Uncheck disabled checkboxes
            });

            updateSubmitButton();
        };

        // Function to update the submit button state
        const updateSubmitButton = () => {
            const userId = document.getElementById("userid")?.value;
            const sessionId = document.getElementById("sessionid")?.value;
            const checkedTerms = document.querySelectorAll('#addSubjectVettingModal input[name="termid[]"]:checked').length;
            const checkedClasses = document.querySelectorAll('#addSubjectVettingModal input[name="subjectclassid[]"]:checked').length;
            if (addBtn) addBtn.disabled = !userId || !sessionId || checkedTerms === 0 || checkedClasses === 0;
        };

        // Remove any existing event listeners to prevent duplicates
        const userIdSelect = document.getElementById("userid");
        const sessionIdSelect = document.getElementById("sessionid");
        const termCheckboxes = document.querySelectorAll('#addSubjectVettingModal input[name="termid[]"]');
        const subjectClassCheckboxes = document.querySelectorAll('#addSubjectVettingModal input[name="subjectclassid[]"]');

        // Clone elements to remove existing listeners
        if (userIdSelect) {
            const newUserIdSelect = userIdSelect.cloneNode(true);
            userIdSelect.parentNode.replaceChild(newUserIdSelect, userIdSelect);
            newUserIdSelect.addEventListener("change", updateSubmitButton);
        }

        if (sessionIdSelect) {
            const newSessionIdSelect = sessionIdSelect.cloneNode(true);
            sessionIdSelect.parentNode.replaceChild(newSessionIdSelect, sessionIdSelect);
            newSessionIdSelect.addEventListener("change", updateSubmitButton);
        }

        termCheckboxes.forEach(cb => {
            const newCb = cb.cloneNode(true);
            cb.parentNode.replaceChild(newCb, cb);
            newCb.addEventListener("change", () => {
                updateSubjectClassCheckboxes();
                updateSubmitButton();
            });
        });

        subjectClassCheckboxes.forEach(cb => {
            const newCb = cb.cloneNode(true);
            cb.parentNode.replaceChild(newCb, cb);
            newCb.addEventListener("change", updateSubmitButton);
        });

        // Initialize subject-class checkboxes state
        updateSubjectClassCheckboxes();
    });

    addModal.addEventListener("hidden.bs.modal", function () {
        console.log("Add modal hidden - cleaning up");
        clearAddFields();
        const errorMsg = document.getElementById("alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");

        const addBtn = document.getElementById("add-btn");
        if (addBtn) {
            addBtn.disabled = true;
            addBtn.innerHTML = "Add Subject Vetting Assignment";
        }

        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }

        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';

        // Reset subject-class checkboxes to enabled state
        const subjectClassCheckboxes = document.querySelectorAll(
            '#addSubjectVettingModal input[name="subjectclassid[]"]'
        );
        subjectClassCheckboxes.forEach(checkbox => {
            checkbox.disabled = false;
            checkbox.closest('.form-check').style.opacity = '1';
        });
    });
}

const editModal = document.getElementById("editModal");
if (editModal) {
    editModal.addEventListener("show.bs.modal", function (e) {
        console.log("Edit modal show event");

        const modalLabel = document.getElementById("editModalLabel");
        const updateBtn = document.getElementById("update-btn");

        if (modalLabel) modalLabel.innerHTML = "Edit Subject Vetting Assignment";
        if (updateBtn) {
            updateBtn.disabled = false;
            updateBtn.innerHTML = "Update";
        }
    });

    editModal.addEventListener("hidden.bs.modal", function () {
        console.log("Edit modal hidden - cleaning up");
        clearEditFields();
        const errorMsg = document.getElementById("edit-alert-error-msg");
        if (errorMsg) errorMsg.classList.add("d-none");

        const updateBtn = document.getElementById("update-btn");
        if (updateBtn) {
            updateBtn.disabled = false;
            updateBtn.innerHTML = "Update";
        }

        const backdrop = document.querySelector('.modal-backdrop');
        if (backdrop) {
            backdrop.remove();
        }

        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function () {
    console.log("DOM fully loaded, initializing components");
    initializeListJS();
    initializeVettingStatusChart();
    initializeCheckboxes();

    const searchInput = document.querySelector(".search-box input.search");
    if (searchInput) {
        searchInput.addEventListener("input", debounce(filterData, 300));
    }
});