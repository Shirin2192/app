$('#loginForm').on('submit', function (e) {
    e.preventDefault();

    // Clear previous errors
    $('.text-danger').html('');

    $.ajax({
        url: base_url + 'common/login_process', // adjust controller path
        type: 'POST',
        dataType: 'json',
        data: $(this).serialize(),
        success: function (res) {

            if (res.status === 'error') {

                // Form validation errors
                if (res.errors) {
                    if (res.errors.email) {
                        $('.error-email').html(res.errors.email);
                    }
                    if (res.errors.password) {
                        $('.error-password').html(res.errors.password);
                    }
                }

                // Invalid email / inactive account
                if (res.email_error) {
                    $('.error-email').html(res.email_error);
                }

                // Invalid password
                if (res.password_error) {
                    $('.error-password').html(res.password_error);
                }

            } else if (res.status === 'success') {

                // Redirect on success
                window.location.href = res.redirect;
            }
        },
        error: function () {
            alert('Something went wrong. Please try again.');
        }
    });
});
