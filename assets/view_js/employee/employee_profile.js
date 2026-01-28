$(document).ready(function () {
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