console.log("classcategory.init.js is loaded and executing at", new Date().toISOString());

// Dependency check with retry
function checkDependencies(attempt = 1, maxAttempts = 5) {
    try {
        const dependencies = {
            axios: typeof axios !== 'undefined',
            Swal: typeof Swal !== 'undefined',
            bootstrap: typeof bootstrap !== 'undefined',
            List: typeof List !== 'undefined'
        };

        const missingDeps = Object.keys(dependencies).filter(key => !dependencies[key]);
        if (missingDeps.length > 0) {
            console.warn(`Missing dependencies (attempt ${attempt}):`, missingDeps);
            if (attempt < maxAttempts) {
                console.log(`Retrying dependency check in 1000ms (attempt ${attempt + 1}/${maxAttempts})`);
                setTimeout(() => checkDependencies(attempt + 1, maxAttempts), 1000);
                return false;
            } else {
                console.error(`Failed to load dependencies after ${maxAttempts} attempts:`, missingDeps);
                Swal.fire({
                    icon: "error",
                    title: "Dependency Error",
                    text: `Failed to load required scripts: ${missingDeps.join(", ")}. Please refresh the page.`,
                    confirmButtonClass: "btn btn-primary"
                });
                return false;
            }
        }

        console.log("All dependencies loaded successfully:", dependencies);
        return true;
    } catch (error) {
        console.error("Error checking dependencies:", error);
        return false;
    }
}

// Set Axios CSRF token
function setCsrfToken() {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        if (!csrfToken) throw new Error("CSRF token meta tag not found");
        axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
        console.log("CSRF token set successfully");
    } catch (error) {
        console.error("CSRF token setup failed:", error);
        Swal.fire({
            icon: "error",
            title: "CSRF Token Error",
            text: "Failed to set CSRF token. Please refresh the page.",
            confirmButtonClass: "btn btn-primary"
        });
    }
}

// Debounce function for search input
function debounceInput(func, wait) {
    let timeout;
    return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}

// Form fields
let addIdField, addCategoryField, addSubmitButton;
let editIdField, editCategoryField, editSubmitButton;

// Initialize form fields with retry
function initializeFormFields(attempt = 1, maxAttempts = 5) {
    try {
        addIdField = document.getElementById("add-id-field");
        addCategoryField = document.getElementById("category");
        addSubmitButton = document.getElementById("add-btn");
        editIdField = document.getElementById("edit-id-field");
        editCategoryField = document.getElementById("edit-category");
        editSubmitButton = document.getElementById("update-btn");

        const fieldsFound = {
            addIdField: !!addIdField,
            addCategoryField: !!addCategoryField,
            addSubmitButton: !!addSubmitButton,
            editIdField: !!editIdField,
            editCategoryField: !!editCategoryField,
            editSubmitButton: !!editSubmitButton
        };

        const missingFields = Object.keys(fieldsFound).filter(key => !fieldsFound[key]);
        if (missingFields.length > 0) {
            console.warn(`Missing fields (attempt ${attempt}):`, missingFields);
            if (attempt < maxAttempts) {
                console.log(`Retrying field initialization in 1000ms (attempt ${attempt + 1}/${maxAttempts})`);
                setTimeout(() => initializeFormFields(attempt + 1, maxAttempts), 1000);
                return;
            } else {
                console.error(`Failed to initialize fields after ${maxAttempts} attempts:`, missingFields);
                Swal.fire({
                    icon: "error",
                    title: "Initialization Error",
                    text: `Failed to load form fields: ${missingFields.join(", ")}. Please refresh the page.`,
                    confirmButtonClass: "btn btn-primary"
                });
                return;
            }
        }

        console.log(`All form fields initialized successfully (attempt ${attempt})`);
        initializeEventListeners();
    } catch (error) {
        console.error("Error initializing form fields:", error);
        Swal.fire({
            icon: "error",
            title: "Initialization Error",
            text: "An error occurred while initializing form fields. Please refresh the page.",
            confirmButtonClass: "btn btn-primary"
        });
    }
}

// Add new assessment field for Add/Edit Modal
function addAssessmentField(containerId, name = '', score = '') {
    const container = document.getElementById(containerId);
    if (!container) {
        console.error(`Container ${containerId} not found`);
        return;
    }

    const index = container.querySelectorAll('.assessment-row').length;
    const row = document.createElement('div');
    row.className = 'assessment-row d-flex gap-2 mb-2';
    row.innerHTML = `
        <div class="flex-grow-1">
            <input type="text" name="assessments[${index}][name]" class="form-control assessment-name" placeholder="Assessment Name (e.g., CA1, Exam)" value="${name}" required>
        </div>
        <div class="flex-shrink-0" style="width: 100px;">
            <input type="number" name="assessments[${index}][max_score]" class="form-control assessment-score" placeholder="Max Score" value="${score}" min="0" step="0.01" required>
        </div>
        <div class="flex-shrink-0">
            <button type="button" class="btn btn-sm btn-outline-danger remove-assessment-btn"><i class="ph-trash"></i></button>
        </div>
    `;
    container.appendChild(row);

    // Add event listeners for new fields
    const removeBtn = row.querySelector('.remove-assessment-btn');
    removeBtn.addEventListener('click', () => {
        row.remove();
        // Reindex assessments
        reindexAssessments(containerId);
        // Update submit button state
        updateSubmitButtonState(containerId);
    });

    updateSubmitButtonState(containerId);
}

// Reindex assessment fields to maintain correct array indices
function reindexAssessments(containerId) {
    const container = document.getElementById(containerId);
    const rows = container.querySelectorAll('.assessment-row');
    rows.forEach((row, index) => {
        const nameInput = row.querySelector('.assessment-name');
        const scoreInput = row.querySelector('.assessment-score');
        nameInput.name = `assessments[${index}][name]`;
        scoreInput.name = `assessments[${index}][max_score]`;
    });
}

// Update submit button state
function updateSubmitButtonState(containerId) {
    const container = document.getElementById(containerId);
    const submitButton = containerId === 'add-assessments-container' ? addSubmitButton : editSubmitButton;
    const assessments = container.querySelectorAll('.assessment-row');
    if (submitButton) {
        submitButton.disabled = assessments.length === 0;
    }
}

// Check all checkbox
function initializeCheckAll() {
    const checkAll = document.getElementById("checkAll");
    if (checkAll) {
        checkAll.onclick = function () {
            console.log("CheckAll clicked");
            const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
            checkboxes.forEach((checkbox) => {
                checkbox.checked = this.checked;
                const row = checkbox.closest("tr");
                if (checkbox.checked) {
                    row.classList.add("table-active");
                } else {
                    row.classList.remove("table-active");
                }
            });
            const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
            const removeActions = document.getElementById("remove-actions");
            if (removeActions) {
                removeActions.classList.toggle("d-none", checkedCount === 0);
            }
        };
        console.log("CheckAll initialized");
    } else {
        console.warn("CheckAll element not found");
    }
}

// Checkbox handling
function ischeckboxcheck() {
    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    checkboxes.forEach((checkbox) => {
        checkbox.removeEventListener("change", handleCheckboxChange);
        checkbox.addEventListener("change", handleCheckboxChange);
    });
    console.log("Checkbox listeners initialized:", checkboxes.length);
}

function handleCheckboxChange(e) {
    const row = e.target.closest("tr");
    if (e.target.checked) {
        row.classList.add("table-active");
    } else {
        row.classList.remove("table-active");
    }
    const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
    const removeActions = document.getElementById("remove-actions");
    if (removeActions) {
        removeActions.classList.toggle("d-none", checkedCount === 0);
    }
    const allCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    const checkAll = document.getElementById("checkAll");
    if (checkAll) {
        checkAll.checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCount;
    }
}

// Delete single category
function handleRemoveClick(e) {
    e.preventDefault();
    const itemId = e.target.closest("tr").querySelector(".id")?.getAttribute("data-id");
    const deleteButton = document.getElementById("delete-record");
    if (deleteButton) {
        deleteButton.addEventListener("click", function () {
            axios.delete(`/classcategories/${itemId}`).then(function () {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Class category deleted successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
                window.location.reload();
            }).catch(function (error) {
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error deleting class category",
                    text: error.response?.data?.message || "An error occurred",
                    showConfirmButton: true
                });
            });
        }, { once: true });
    }
    try {
        const modal = new bootstrap.Modal(document.getElementById("deleteRecordModal"));
        modal.show();
    } catch (error) {
        console.error("Error opening delete modal:", error);
    }
}

// Edit category
function handleEditClick(e) {
    e.preventDefault();
    const tr = e.target.closest("tr");
    const itemId = tr.querySelector(".id")?.getAttribute("data-id");
    
    if (!itemId) {
        console.error("Item ID not found in table row");
        Swal.fire({
            icon: "error",
            title: "Edit Error",
            text: "Unable to find category ID. Please try again.",
            confirmButtonClass: "btn btn-primary"
        });
        return;
    }

    console.log("Populating edit modal for item:", itemId);

    if (editIdField) editIdField.value = itemId;
    if (editCategoryField) editCategoryField.value = tr.querySelector(".category")?.innerText || "";

    // Set is_senior radio buttons
    const isSeniorAttr = tr.querySelector(".gradetype")?.getAttribute("data-issenior");
    const isSenior = isSeniorAttr ? parseInt(isSeniorAttr) : 0;
    console.log("is_senior value from table:", isSenior);

    const editCategoryForm = document.getElementById("edit-category-form");
    if (editCategoryForm) {
        const juniorRadio = editCategoryForm.querySelector("#edit-junior");
        const seniorRadio = editCategoryForm.querySelector("#edit-senior");
        if (juniorRadio && seniorRadio) {
            juniorRadio.checked = !isSenior;
            seniorRadio.checked = !!isSenior;
            console.log("Set radio buttons:", { junior: !isSenior, senior: !!isSenior });
        } else {
            console.warn("Edit modal radio buttons not found");
        }
    }

    // Populate assessments
    const assessments = tr.querySelector(".assessments")?.getAttribute("data-assessments");
    const assessmentsContainer = document.getElementById("edit-assessments-container");
    if (assessmentsContainer && assessments) {
        assessmentsContainer.innerHTML = ''; // Clear existing fields
        JSON.parse(assessments).forEach(assessment => {
            addAssessmentField('edit-assessments-container', assessment.name, assessment.max_score);
        });
    }

    updateSubmitButtonState('edit-assessments-container');

    try {
        const modal = new bootstrap.Modal(document.getElementById("editModal"));
        console.log("Edit modal opened for item:", itemId);
        modal.show();
    } catch (error) {
        console.error("Error opening edit modal:", error);
        Swal.fire({
            icon: "error",
            title: "Modal Error",
            text: "Failed to open edit modal. Please refresh the page.",
            confirmButtonClass: "btn btn-primary"
        });
    }
}

// Clear form fields
function clearAddFields() {
    if (addIdField) addIdField.value = "";
    if (addCategoryField) addCategoryField.value = "";
    if (addSubmitButton) addSubmitButton.disabled = true;
    const addAssessmentsContainer = document.getElementById("add-assessments-container");
    if (addAssessmentsContainer) addAssessmentsContainer.innerHTML = "";
    // Add one default assessment field
    addAssessmentField('add-assessments-container');
}

function clearEditFields() {
    if (editIdField) editIdField.value = "";
    if (editCategoryField) editCategoryField.value = "";
    if (editSubmitButton) editSubmitButton.disabled = true;
    const editAssessmentsContainer = document.getElementById("edit-assessments-container");
    if (editAssessmentsContainer) editAssessmentsContainer.innerHTML = "";
    const editCategoryForm = document.getElementById("edit-category-form");
    if (editCategoryForm) {
        const juniorRadio = editCategoryForm.querySelector("#edit-junior");
        const seniorRadio = editCategoryForm.querySelector("#edit-senior");
        if (juniorRadio) juniorRadio.checked = true; // Default to Junior
        if (seniorRadio) seniorRadio.checked = false;
    }
    // Add one default assessment field
    addAssessmentField('edit-assessments-container');
}

// Delete multiple categories
function deleteMultiple() {
    const ids_array = [];
    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    checkboxes.forEach((checkbox) => {
        if (checkbox.checked) {
            const id = checkbox.closest("tr").querySelector(".id").getAttribute("data-id");
            ids_array.push(id);
        }
    });
    if (ids_array.length > 0) {
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
            if (result.value) {
                Promise.all(ids_array.map((id) => {
                    return axios.delete(`/classcategories/${id}`);
                })).then(() => {
                    Swal.fire({
                        title: "Deleted!",
                        text: "Your class categories have been deleted.",
                        icon: "success",
                        confirmButtonClass: "btn btn-info w-xs mt-2",
                        buttonsStyling: false
                    });
                    window.location.reload();
                }).catch((error) => {
                    Swal.fire({
                        title: "Error!",
                        text: error.response?.data?.message || "Failed to delete class categories",
                        icon: "error",
                        confirmButtonClass: "btn btn-info w-xs mt-2",
                        buttonsStyling: false
                    });
                });
            }
        });
    } else {
        Swal.fire({
            title: "Please select at least one checkbox",
            confirmButtonClass: "btn btn-info",
            buttonsStyling: false,
            showCloseButton: true
        });
    }
}

// Initialize List.js for client-side filtering
let categoryList;
function initializeListJs() {
    const categoryListContainer = document.getElementById('categoryList');
    if (categoryListContainer && document.querySelectorAll('#categoryList tbody tr').length > 0) {
        try {
            categoryList = new List('categoryList', {
                valueNames: ['categoryid', 'category', 'assessments', 'datereg'],
                page: 1000,
                pagination: false,
                listClass: 'list'
            });
            console.log("List.js initialized");
        } catch (error) {
            console.error("List.js initialization failed:", error);
        }
    } else {
        console.warn("No class categories available for List.js initialization");
    }

    if (categoryList) {
        categoryList.on('searchComplete', function () {
            const noResultRow = document.querySelector('.noresult');
            if (categoryList.visibleItems.length === 0) {
                noResultRow.style.display = 'block';
            } else {
                noResultRow.style.display = 'none';
            }
        });
    }
}

// Filter data (client-side)
function filterData() {
    const searchInput = document.querySelector(".search-box input.search");
    const searchValue = searchInput ? searchInput.value : "";
    console.log("Filtering with search:", searchValue);
    if (categoryList) {
        categoryList.search(searchValue, ['category', 'assessments']);
    }
}

// Add category
function initializeAddCategoryForm() {
    const addCategoryForm = document.getElementById("add-category-form");
    if (addCategoryForm) {
        addCategoryForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const errorMsg = document.getElementById("alert-error-msg");
            if (errorMsg) errorMsg.classList.add("d-none");

            const formData = new FormData(addCategoryForm);
            const category = formData.get('category');
            const is_senior = parseInt(formData.get('is_senior')) || 0;
            const assessments = [];
            const assessmentRows = document.querySelectorAll('#add-assessments-container .assessment-row');

            assessmentRows.forEach((row, index) => {
                const name = row.querySelector(`input[name="assessments[${index}][name]"]`).value;
                const max_score = parseFloat(row.querySelector(`input[name="assessments[${index}][max_score]"]`).value) || 0;
                assessments.push({ name, max_score });
            });

            if (!category || assessments.length === 0 || isNaN(is_senior)) {
                if (errorMsg) {
                    errorMsg.innerHTML = "Please fill in all required fields, including Grade Type and at least one assessment";
                    errorMsg.classList.remove("d-none");
                }
                return;
            }

            console.log("Submitting Add Category:", { category, is_senior, assessments });
            axios.post('/classcategories', {
                category,
                is_senior,
                assessments
            }, {
                headers: { 'Content-Type': 'application/json' }
            }).then(function (response) {
                console.log("Add Category Success:", response.data);
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Class category added successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
                window.location.reload();
            }).catch(function (error) {
                console.error("Add Category Error:", {
                    status: error.response?.status,
                    data: error.response?.data,
                    message: error.message
                });
                if (errorMsg) {
                    errorMsg.innerHTML = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join(", ") || "Error adding class category";
                    errorMsg.classList.remove("d-none");
                }
            });
        });
        console.log("Add category form initialized");
    } else {
        console.warn("Add category form not found");
    }
}

// Edit category
function initializeEditCategoryForm() {
    const editCategoryForm = document.getElementById("edit-category-form");
    if (editCategoryForm) {
        editCategoryForm.addEventListener("submit", function (e) {
            e.preventDefault();
            const errorMsg = document.getElementById("edit-alert-error-msg");
            if (errorMsg) errorMsg.classList.add("d-none");

            const id = editIdField?.value;
            if (!id) {
                console.error("Edit Category: Missing ID");
                if (errorMsg) {
                    errorMsg.innerHTML = "Category ID is missing";
                    errorMsg.classList.remove("d-none");
                }
                return;
            }

            const formData = new FormData(editCategoryForm);
            const category = formData.get('category');
            const is_senior = parseInt(formData.get('is_senior'));
            const assessments = [];
            const assessmentRows = document.querySelectorAll('#edit-assessments-container .assessment-row');

            assessmentRows.forEach((row, index) => {
                const name = row.querySelector(`input[name="assessments[${index}][name]"]`).value;
                const max_score = parseFloat(row.querySelector(`input[name="assessments[${index}][max_score]"]`).value) || 0;
                assessments.push({ name, max_score });
            });

            if (!category || assessments.length === 0 || isNaN(is_senior)) {
                console.warn("Edit Category: Invalid form data");
                if (errorMsg) {
                    errorMsg.innerHTML = "Please fill in all required fields, including Grade Type and at least one assessment";
                    errorMsg.classList.remove("d-none");
                }
                return;
            }

            console.log("Submitting Edit Category:", { id, category, is_senior, assessments });
            axios.put(`/classcategories/${id}`, {
                category,
                is_senior,
                assessments
            }, {
                headers: { 'Content-Type': 'application/json' }
            }).then(function (response) {
                console.log("Edit Category Success:", response.data);
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Class category updated successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
                window.location.reload();
            }).catch(function (error) {
                console.error("Edit Category Error:", {
                    status: error.response?.status,
                    data: error.response?.data,
                    message: error.message
                });
                if (errorMsg) {
                    errorMsg.innerHTML = error.response?.data?.message || Object.values(error.response?.data?.errors || {}).flat().join(", ") || "Error updating class category";
                    errorMsg.classList.remove("d-none");
                }
            });
        });
        console.log("Edit category form initialized");
    } else {
        console.warn("Edit category form not found");
    }
}

// Modal events
function initializeModals() {
    const addModal = document.getElementById("addCategoryModal");
    if (addModal) {
        addModal.addEventListener("show.bs.modal", function (e) {
            console.log("Add modal opening");
            clearAddFields();
            if (e.relatedTarget.classList.contains("add-btn")) {
                const modalLabel = document.getElementById("exampleModalLabel");
                const addBtn = document.getElementById("add-btn");
                if (modalLabel) modalLabel.innerHTML = "Add Class Category";
                if (addBtn) addBtn.innerHTML = "Add Category";
            }
            updateSubmitButtonState('add-assessments-container');
        });
        addModal.addEventListener("hidden.bs.modal", function () {
            console.log("Add modal closed");
            clearAddFields();
        });
        console.log("Add modal initialized");
    }

    const editModal = document.getElementById("editModal");
    if (editModal) {
        editModal.addEventListener("show.bs.modal", function () {
            console.log("Edit modal opening");
            const modalLabel = document.getElementById("editModalLabel");
            const updateBtn = document.getElementById("update-btn");
            if (modalLabel) modalLabel.innerHTML = "Edit Class Category";
            if (updateBtn) updateBtn.innerHTML = "Update";
            updateSubmitButtonState('edit-assessments-container');
        });
        editModal.addEventListener("hidden.bs.modal", function () {
            console.log("Edit modal closed");
            clearEditFields();
        });
        console.log("Edit modal initialized");
    }
}

// Initialize event listeners
function initializeEventListeners() {
    try {
        // Add assessment buttons
        const addAssessmentBtn = document.getElementById("add-assessment-btn");
        if (addAssessmentBtn) {
            addAssessmentBtn.addEventListener("click", () => addAssessmentField('add-assessments-container'));
        }
        const editAssessmentBtn = document.getElementById("edit-assessment-btn");
        if (editAssessmentBtn) {
            editAssessmentBtn.addEventListener("click", () => addAssessmentField('edit-assessments-container'));
        }

        // Event delegation for edit and remove buttons
        document.addEventListener('click', function (e) {
            if (e.target.closest('.edit-item-btn')) {
                handleEditClick(e);
            } else if (e.target.closest('.remove-item-btn')) {
                handleRemoveClick(e);
            }
        });

        const searchInput = document.querySelector(".search-box input.search");
        if (searchInput) {
            searchInput.addEventListener("input", debounceInput(function () {
                console.log("Search input changed:", searchInput.value);
                filterData();
            }, 300));
        } else {
            console.error("Search input not found!");
        }

        console.log("Event listeners initialized successfully");
    } catch (error) {
        console.error("Error initializing event listeners:", error);
        Swal.fire({
            icon: "error",
            title: "Event Listener Initialization Error",
            text: "An error occurred while setting up event listeners. Please refresh the page.",
            confirmButtonClass: "btn btn-primary"
        });
    }
}

// Initialize everything
function initializeApp() {
    if (!checkDependencies()) return;
    setCsrfToken();
    initializeFormFields();
    initializeCheckAll();
    ischeckboxcheck();
    initializeListJs();
    initializeAddCategoryForm();
    initializeEditCategoryForm();
    initializeModals();
    // Add initial assessment field
    addAssessmentField('add-assessments-container');
}

// Use window.onload to ensure all assets are loaded
window.onload = function () {
    console.log("window.onload fired at", new Date().toISOString());
    initializeApp();
};

// Fallback to DOMContentLoaded for faster initialization
document.addEventListener("DOMContentLoaded", function () {
    console.log("DOMContentLoaded fired at", new Date().toISOString());
    if (!window.onloadFired) {
        initializeApp();
        window.onloadFired = true;
    }
});

// Expose functions to global scope
window.deleteMultiple = deleteMultiple;