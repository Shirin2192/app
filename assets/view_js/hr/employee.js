$(document).ready(function () {

    fetchEmployees();

    // ===============================
    // ADD EMPLOYEE
    // ===============================
    $('#addEmployeeForm').submit(function (e) {
        e.preventDefault();
        clearErrors();

        $.ajax({
            url: base_url + 'hr/add_employee',
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            success: function (res) {
                if (res.status === 'error') {
                    showErrors(res.errors);
                } else {
                    alert(res.message);
                    $('#addEmployeeForm')[0].reset();
                    fetchEmployees();
                }
            }
        });
    });

    // ===============================
    // UPDATE EMPLOYEE
    // ===============================
    $('#updateEmployeeForm').submit(function (e) {
        e.preventDefault();
        clearErrors();

        $.ajax({
            url: base_url + 'hr/update_employee',
            type: 'POST',
            dataType: 'json',
            data: $(this).serialize(),
            success: function (res) {
                if (res.status === 'error') {
                    showErrors(res.errors);
                } else {
                    alert(res.message);
                    $('#editModal').modal('hide');
                    fetchEmployees();
                }
            }
        });
    });

});

// ===============================
// FETCH EMPLOYEE LIST
// ===============================
function fetchEmployees() {
    $.ajax({
        url: base_url + 'hr/fetch_employees_list',
        type: 'GET',
        dataType: 'json',
        success: function (res) {
            let html = '';

            if (res.status === 'success' && res.data.length > 0) {
                $.each(res.data, function (i, emp) {
                    html += `
                        <tr>
                            <td>${emp.employee_code}</td>
                            <td>${emp.first_name} ${emp.last_name}</td>
                            <td>${emp.mobile}</td>
                            <td>${emp.department}</td>
                            <td>${emp.designation}</td>
                            <td>
                                <button class="btn btn-sm btn-primary" onclick="editEmployee(${emp.id})">Edit</button>
                                <button class="btn btn-sm btn-danger" onclick="deleteEmployee(${emp.id})">Delete</button>
                            </td>
                        </tr>`;
                });
            } else {
                html = `<tr><td colspan="6" class="text-center">No data found</td></tr>`;
            }

            $('#employeeTable tbody').html(html);
        }
    });
}

// ===============================
// GET EMPLOYEE DETAIL (EDIT)
// ===============================
function editEmployee(id) {
    $.ajax({
        url: base_url + 'hr/get_employees_detail_on_id',
        type: 'POST',
        dataType: 'json',
        data: { id: id },
        success: function (res) {
            let emp = res.data[0];

            $('#employee_id').val(emp.id);
            $('[name="employee_code"]').val(emp.employee_code);
            $('[name="first_name"]').val(emp.first_name);
            $('[name="last_name"]').val(emp.last_name);
            $('[name="mobile"]').val(emp.mobile);
            $('[name="department"]').val(emp.department);
            $('[name="designation"]').val(emp.designation);
            $('[name="date_of_joining"]').val(emp.date_of_joining);
            $('[name="employment_type"]').val(emp.employment_type);
            $('[name="basic_salary"]').val(emp.basic_salary);
            $('[name="monthly_salary"]').val(emp.monthly_salary);
            $('[name="bank_name"]').val(emp.bank_name);
            $('[name="bank_account_no"]').val(emp.bank_account_no);
            $('[name="ifsc_code"]').val(emp.ifsc_code);

            $('#editModal').modal('show');
        }
    });
}

// ===============================
// DELETE EMPLOYEE (SOFT)
// ===============================
function deleteEmployee(id) {
    if (!confirm('Are you sure you want to delete this employee?')) return;

    $.ajax({
        url: base_url + 'hr/delete_employee',
        type: 'POST',
        dataType: 'json',
        data: { employee_id: id },
        success: function (res) {
            alert(res.message);
            if (res.status === 'success') {
                fetchEmployees();
            }
        }
    });
}

// ===============================
// ERROR HANDLING
// ===============================
function showErrors(errors) {
    $.each(errors, function (key, value) {
        $('.error-' + key).html(value);
    });
}

function clearErrors() {
    $('.text-danger').html('');
}
