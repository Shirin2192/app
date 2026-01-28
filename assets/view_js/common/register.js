$('#registrationForm').on('submit', function (e) {
    e.preventDefault();

    // Clear previous errors
    $('.text-danger').html('');

    $.ajax({
        url: base_url + 'common/registration', // controller/method
        type: 'POST',
        dataType: 'json',
        data: $(this).serialize(),

        beforeSend: function () {
            $('button[type="submit"]').prop('disabled', true);
        },

        success: function (res) {

            if (res.status === 'error') {

                // Validation errors
                if (res.errors) {
                    $.each(res.errors, function (key, value) {
                        $('.error-' + key).html(value);
                    });
                }

                // Custom failure messages
                if (res.message) {
                    alert(res.message);
                }

            } else if (res.status === 'success') {

                alert(res.message);
                $('#registrationForm')[0].reset();
            }
        },

        complete: function () {
            $('button[type="submit"]').prop('disabled', false);
        },

        error: function () {
            alert('Something went wrong. Please try again.');
        }
    });
});
