document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('editPostForm');
    if (!form) return; // page is in "not found" state, no form to attach to

    const alertBox  = document.getElementById('postAlert');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function (e) {
        e.preventDefault(); // no full-page reload

        submitBtn.disabled = true;
        submitBtn.textContent = 'Saving...';
        alertBox.innerHTML = '';

        const formData = new FormData(form);

        fetch('/WebTech/community/ajax/edit_post.php', {
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
                        ' <a href="/WebTech/community/create_post.php">Back to Community</a></p>';
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
            .catch(function (err) {
                alertBox.innerHTML =
                    '<p class="alert alert-error">Something went wrong. Please try again.</p>';
                console.error(err);
            })
            .finally(function () {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save Changes';
            });
    });
});
