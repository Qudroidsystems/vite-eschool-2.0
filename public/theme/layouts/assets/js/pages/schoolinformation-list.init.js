var perPage = 5,
    editlist = false,
    checkAll = document.getElementById("checkAll"),
    options = {
        valueNames: [
            "id",
            "name",
            "email",
            "status",
            "no_of_times_school_opened",
            "date_school_opened",
            "date_next_term_begins",
            "created_at"
        ],
    },
    schoolList = new List("schoolList", options);

console.log("Initial schoolList items:", schoolList.items.length);

schoolList.on("updated", function (e) {
    console.log("List.js updated, matching items:", e.matchingItems.length, "total items:", schoolList.items.length);
    document.getElementsByClassName("noresult")[0].style.display = e.matchingItems.length === 0 ? "block" : "none";
    setTimeout(() => {
        refreshCallbacks();
        ischeckboxcheck();
    }, 100);
});

document.addEventListener("DOMContentLoaded", function () {
    console.log("DOM loaded, initializing List.js...");
    console.log("Initial schoolList items:", schoolList.items.length);
    refreshCallbacks();
    ischeckboxcheck();

    if (typeof Choices !== 'undefined') {
        var statusFilterVal = new Choices(document.getElementById("idStatus"), { searchEnabled: true });
        var emailFilterVal = new Choices(document.getElementById("idEmail"), { searchEnabled: true });
    } else {
        console.warn("Choices.js not available, falling back to native select");
    }
});

if (checkAll) {
    checkAll.onclick = function () {
        console.log("checkAll clicked");
        var checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
        console.log("checkAll clicked, checkboxes found:", checkboxes.length);
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
        document.getElementById("remove-actions").classList.toggle("d-none", checkedCount === 0);
    };
}

var addIdField = document.getElementById("add-id-field"),
    addNameField = document.getElementById("school_name"),
    addAddressField = document.getElementById("school_address"),
    addPhoneField = document.getElementById("school_phone"),
    addEmailField = document.getElementById("school_email"),
    addLogoField = document.getElementById("school_logo"),
    addMottoField = document.getElementById("school_motto"),
    addWebsiteField = document.getElementById("school_website"),
    addTimesOpenedField = document.getElementById("no_of_times_school_opened"),
    addDateOpenedField = document.getElementById("date_school_opened"),
    addNextTermField = document.getElementById("date_next_term_begins"),
    addStatusField = document.getElementById("is_active"),
    editIdField = document.getElementById("edit-id-field"),
    editNameField = document.getElementById("edit_school_name"),
    editAddressField = document.getElementById("edit_school_address"),
    editPhoneField = document.getElementById("edit_school_phone"),
    editEmailField = document.getElementById("edit_school_email"),
    editLogoField = document.getElementById("edit_school_logo"),
    editMottoField = document.getElementById("edit_school_motto"),
    editWebsiteField = document.getElementById("edit_school_website"),
    editTimesOpenedField = document.getElementById("edit_no_of_times_school_opened"),
    editDateOpenedField = document.getElementById("edit_date_school_opened"),
    editNextTermField = document.getElementById("edit_date_next_term_begins"),
    editStatusField = document.getElementById("edit_is_active");

function ensureAxios() {
    if (typeof axios === 'undefined') {
        console.error("Axios is not defined. Please include Axios library.");
        Swal.fire({
            position: "center",
            icon: "error",
            title: "Configuration error",
            text: "Axios library is missing",
            showConfirmButton: true
        });
        return false;
    }
    return true;
}

function ischeckboxcheck() {
    const checkboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    checkboxes.forEach((checkbox) => {
        checkbox.removeEventListener("change", handleCheckboxChange);
        checkbox.addEventListener("change", handleCheckboxChange);
    });
}

function handleCheckboxChange(e) {
    const row = e.target.closest("tr");
    if (e.target.checked) {
        row.classList.add("table-active");
    } else {
        row.classList.remove("table-active");
    }
    const checkedCount = document.querySelectorAll('tbody input[name="chk_child"]:checked').length;
    document.getElementById("remove-actions").classList.toggle("d-none", checkedCount === 0);
    const allCheckboxes = document.querySelectorAll('tbody input[name="chk_child"]');
    document.getElementById("checkAll").checked = allCheckboxes.length > 0 && allCheckboxes.length === checkedCount;
}

function refreshCallbacks() {
    console.log("refreshCallbacks executed at", new Date().toISOString());
    var removeButtons = document.getElementsByClassName("remove-item-btn");
    var editButtons = document.getElementsByClassName("edit-item-btn");
    console.log("Attaching event listeners to", removeButtons.length, "remove buttons and", editButtons.length, "edit buttons");

    Array.from(removeButtons).forEach(function (btn) {
        btn.removeEventListener("click", handleRemoveClick);
        btn.addEventListener("click", handleRemoveClick);
    });

    Array.from(editButtons).forEach(function (btn) {
        btn.removeEventListener("click", handleEditClick);
        btn.addEventListener("click", handleEditClick);
    });
}

function handleRemoveClick(e) {
    e.preventDefault();
    try {
        var itemId = e.target.closest("tr").querySelector(".id").getAttribute("data-id");
        console.log("Remove button clicked for ID:", itemId);
        document.getElementById("delete-record").addEventListener("click", function () {
            if (!ensureAxios()) return;
            axios.delete(`/school-information/${itemId}`, {
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
            }).then(function () {
                console.log("Deleted school ID:", itemId);
                window.location.reload();
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "School deleted successfully!",
                    showConfirmButton: false,
                    timer: 2000,
                    showCloseButton: true
                });
            }).catch(function (error) {
                console.error("Error deleting school:", error);
                Swal.fire({
                    position: "center",
                    icon: "error",
                    title: "Error deleting school",
                    text: error.response?.data?.message || "An error occurred",
                    showConfirmButton: true
                });
            });
        }, { once: true });
        var modal = new bootstrap.Modal(document.getElementById("deleteRecordModal"));
        modal.show();
    } catch (error) {
        console.error("Error in remove-item-btn click:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Failed to initiate delete",
            showConfirmButton: true
        });
    }
}

function handleEditClick(e) {
    e.preventDefault();
    try {
        var itemId = e.target.closest("tr").querySelector(".id").getAttribute("data-id");
        var tr = e.target.closest("tr");
        console.log("Edit button clicked for ID:", itemId);
        editlist = true;
        editIdField.value = itemId;
        editNameField.value = tr.querySelector(".name a").innerText;
        editAddressField.value = tr.querySelector(".name").getAttribute("data-address") || "";
        editPhoneField.value = tr.querySelector(".phone")?.innerText || "";
        editEmailField.value = tr.querySelector(".email").innerText;
        editMottoField.value = tr.querySelector(".name").getAttribute("data-motto") || "";
        editWebsiteField.value = tr.querySelector(".name").getAttribute("data-website") || "";
        editTimesOpenedField.value = tr.querySelector(".no_of_times_school_opened").getAttribute("data-no_of_times_school_opened") || "";
        editDateOpenedField.value = tr.querySelector(".date_school_opened").getAttribute("data-date_school_opened") || "";
        editNextTermField.value = tr.querySelector(".date_next_term_begins").getAttribute("data-date_next_term_begins") || "";
        var status = tr.querySelector(".status").getAttribute("data-status") === "Active";
        editStatusField.checked = status;
        var modal = new bootstrap.Modal(document.getElementById("editModal"));
        modal.show();
    } catch (error) {
        console.error("Error in edit-item-btn click:", error);
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Failed to populate edit modal",
            showConfirmButton: true
        });
    }
}

function clearAddFields() {
    addIdField.value = "";
    addNameField.value = "";
    addAddressField.value = "";
    addPhoneField.value = "";
    addEmailField.value = "";
    addLogoField.value = "";
    addMottoField.value = "";
    addWebsiteField.value = "";
    addTimesOpenedField.value = "";
    addDateOpenedField.value = "";
    addNextTermField.value = "";
    addStatusField.checked = false;
}

function clearEditFields() {
    editIdField.value = "";
    editNameField.value = "";
    editAddressField.value = "";
    editPhoneField.value = "";
    editEmailField.value = "";
    editLogoField.value = "";
    editMottoField.value = "";
    editWebsiteField.value = "";
    editTimesOpenedField.value = "";
    editDateOpenedField.value = "";
    editNextTermField.value = "";
    editStatusField.checked = false;
}

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
                if (!ensureAxios()) return;
                Promise.all(ids_array.map((id) => {
                    return axios.delete(`/school-information/${id}`, {
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                })).then(() => {
                    window.location.reload();
                    Swal.fire({
                        title: "Deleted!",
                        text: "Your data has been deleted.",
                        icon: "success",
                        confirmButtonClass: "btn btn-info w-xs mt-2",
                        buttonsStyling: false
                    });
                }).catch((error) => {
                    console.error("Error deleting schools:", error);
                    Swal.fire({
                        title: "Error!",
                        text: error.response?.data?.message || "Failed to delete schools",
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

function filterData() {
    var searchInput = document.querySelector(".search-box input.search").value.toLowerCase();
    var statusSelect = document.getElementById("idStatus");
    var emailSelect = document.getElementById("idEmail");
    var selectedStatus = typeof Choices !== 'undefined' && statusFilterVal ? statusFilterVal.getValue(true) : statusSelect.value;
    var selectedEmail = typeof Choices !== 'undefined' && emailFilterVal ? emailFilterVal.getValue(true) : emailSelect.value;

    console.log("Filtering with:", { search: searchInput, status: selectedStatus, email: selectedEmail });

    schoolList.filter(function (item) {
        var nameMatch = item.values().name.toLowerCase().includes(searchInput);
        var emailMatch = item.values().email.toLowerCase().includes(searchInput);
        var statusMatch = selectedStatus === "all" || item.values().status === selectedStatus;
        var emailSelectMatch = selectedEmail === "all" || item.values().email === selectedEmail;

        return (nameMatch || emailMatch) && statusMatch && emailSelectMatch;
    });
}

document.getElementById("add-school-form").addEventListener("submit", function (e) {
    e.preventDefault();
    var errorMsg = document.getElementById("alert-error-msg");
    errorMsg.classList.remove("d-none");
    setTimeout(() => errorMsg.classList.add("d-none"), 2000);

    if (addNameField.value === "") {
        errorMsg.innerHTML = "Please enter a school name";
        return false;
    }
    if (addAddressField.value === "") {
        errorMsg.innerHTML = "Please enter an address";
        return false;
    }
    if (addPhoneField.value === "") {
        errorMsg.innerHTML = "Please enter a phone number";
        return false;
    }
    if (addEmailField.value === "") {
        errorMsg.innerHTML = "Please enter an email";
        return false;
    }
    if (addTimesOpenedField.value === "") {
        errorMsg.innerHTML = "Please enter the number of times school opened";
        return false;
    }

    if (!ensureAxios()) return;

    var formData = new FormData();
    formData.append('school_name', addNameField.value);
    formData.append('school_address', addAddressField.value);
    formData.append('school_phone', addPhoneField.value);
    formData.append('school_email', addEmailField.value);
    if (addLogoField.files.length > 0) {
        formData.append('school_logo', addLogoField.files[0]);
    }
    formData.append('school_motto', addMottoField.value);
    formData.append('school_website', addWebsiteField.value);
    formData.append('no_of_times_school_opened', addTimesOpenedField.value);
    formData.append('date_school_opened', addDateOpenedField.value);
    formData.append('date_next_term_begins', addNextTermField.value);
    formData.append('is_active', addStatusField.checked ? 1 : 0);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    axios.post('/school-information', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    }).then(function (response) {
        window.location.reload();
        Swal.fire({
            position: "center",
            icon: "success",
            title: "School added successfully!",
            showConfirmButton: false,
            timer: 2000,
            showCloseButton: true
        });
    }).catch(function (error) {
        console.error("Error adding school:", error);
        var message = error.response?.data?.message || "Error adding school";
        if (error.response?.status === 422) {
            message = Object.values(error.response.data.errors || {}).flat().join(", ");
        }
        errorMsg.innerHTML = message;
    });
});

document.getElementById("edit-school-form").addEventListener("submit", function (e) {
    e.preventDefault();
    var errorMsg = document.getElementById("alert-error-msg");
    errorMsg.classList.remove("d-none");
    setTimeout(() => errorMsg.classList.add("d-none"), 2000);

    if (editNameField.value === "") {
        errorMsg.innerHTML = "Please enter a school name";
        return false;
    }
    if (editAddressField.value === "") {
        errorMsg.innerHTML = "Please enter an address";
        return false;
    }
    if (editPhoneField.value === "") {
        errorMsg.innerHTML = "Please enter a phone number";
        return false;
    }
    if (editEmailField.value === "") {
        errorMsg.innerHTML = "Please enter an email";
        return false;
    }
    if (editTimesOpenedField.value === "") {
        errorMsg.innerHTML = "Please enter the number of times school opened";
        return false;
    }

    if (!ensureAxios()) return;

    var formData = new FormData();
    formData.append('school_name', editNameField.value);
    formData.append('school_address', editAddressField.value);
    formData.append('school_phone', editPhoneField.value);
    formData.append('school_email', editEmailField.value);
    if (editLogoField.files.length > 0) {
        formData.append('school_logo', editLogoField.files[0]);
    }
    formData.append('school_motto', editMottoField.value);
    formData.append('school_website', editWebsiteField.value);
    formData.append('no_of_times_school_opened', editTimesOpenedField.value);
    formData.append('date_school_opened', editDateOpenedField.value);
    formData.append('date_next_term_begins', editNextTermField.value);
    formData.append('is_active', editStatusField.checked ? 1 : 0);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('_method', 'PUT');

    console.log("Submitting edit form with data:", [...formData.entries()]);

    axios.post(`/school-information/${editIdField.value}`, formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    }).then(function (response) {
        window.location.reload();
        Swal.fire({
            position: "center",
            icon: "success",
            title: "School updated successfully!",
            showConfirmButton: false,
            timer: 2000,
            showCloseButton: true
        });
    }).catch(function (error) {
        console.error("Error updating school:", error.response || error);
        var message = error.response?.data?.message || "Error updating school";
        if (error.response?.status === 422) {
            console.log("Validation errors:", error.response.data.errors);
            message = Object.values(error.response.data.errors || {}).flat().join(", ");
        }
        errorMsg.innerHTML = message;
    });
});

document.getElementById("showModal").addEventListener("show.bs.modal", function (e) {
    if (e.relatedTarget.classList.contains("add-btn")) {
        console.log("Opening showModal for adding school...");
        document.getElementById("addModalLabel").innerHTML = "Add School";
        document.getElementById("add-btn").innerHTML = "Add School";
    }
});

document.getElementById("editModal").addEventListener("show.bs.modal", function () {
    console.log("Opening editModal...");
    document.getElementById("editModalLabel").innerHTML = "Edit School";
    document.getElementById("update-btn").innerHTML = "Update";
});

document.getElementById("showModal").addEventListener("hidden.bs.modal", function () {
    console.log("showModal closed, clearing fields...");
    clearAddFields();
});

document.getElementById("editModal").addEventListener("hidden.bs.modal", function () {
    console.log("editModal closed, clearing fields...");
    clearEditFields();
});