document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('resetPasswordForm');
    if (!form) return;

    const alertBox  = document.getElementById('resetAlert');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        submitBtn.disabled = true;
        submitBtn.textContent = 'Resetting...';
        alertBox.innerHTML = '';

        const formData = new FormData(form);

        fetch('/WebTech/auth/ajax/reset_password.php', {
            method: 'POST',
            body: formData
        })
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (data.success) {
                    alertBox.innerHTML =
                        '<p class="alert alert-success">' + data.message +
                        ' <a href="/WebTech/auth/login.php">Go to Login</a></p>';
                    form.style.display = 'none';
                } else {
                    let errorHtml = '<div class="alert alert-error"><ul>';
                    if (data.errors && data.errors.length > 0) {
                        data.errors.forEach(function (err) {
                            errorHtml += '<li>' + err + '</li>';
                        });
                    } else {
                        errorHtml += '<li>' + data.message + '</li>';
                    }
                    errorHtml += '</ul></div>';
                    alertBox.innerHTML = errorHtml;
                }
            })
            .catch(function () {
                alertBox.innerHTML = '<p class="alert alert-error">Something went wrong. Please try again.</p>';
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Reset Password';
            });
    });
});
