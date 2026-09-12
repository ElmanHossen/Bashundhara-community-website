document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('forgotPasswordForm');
    if (!form) return;

    const alertBox  = document.getElementById('resetAlert');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        submitBtn.disabled = true;
        submitBtn.textContent = 'Sending...';
        alertBox.innerHTML = '';

        const formData = new FormData(form);

        fetch('/WebTech/auth/ajax/request_reset.php', {
            method: 'POST',
            body: formData
        })
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (data.success) {
                    let html = '<p class="alert alert-success">' + data.message + '</p>';

                    if (data.reset_link) {
                       
                        html += '<div class="alert alert-success" style="word-break:break-all;">' +
                                    '<strong>Demo mode - reset link:</strong><br>' +
                                    '<a href="' + data.reset_link + '">' + data.reset_link + '</a>' +
                                '</div>';
                    }

                    alertBox.innerHTML = html;
                    form.reset();
                } else {
                    alertBox.innerHTML = '<p class="alert alert-error">' +
                        (data.message || 'Something went wrong.') + '</p>';
                }
            })
            .catch(function () {
                alertBox.innerHTML = '<p class="alert alert-error">Something went wrong. Please try again.</p>';
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send Reset Link';
            });
    });
});
