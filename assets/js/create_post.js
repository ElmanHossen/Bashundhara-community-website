document.addEventListener('DOMContentLoaded', function () {
    const form        = document.getElementById('createPostForm');
    const alertBox     = document.getElementById('postAlert');
    const submitBtn     = document.getElementById('submitBtn');
    const recentList    = document.getElementById('recentPostsList');
    const noPostsMsg    = document.getElementById('noPostsMsg');

    form.addEventListener('submit', function (e) {
        e.preventDefault(); // stop the normal full-page form submit

        submitBtn.disabled = true;
        submitBtn.textContent = 'Publishing...';
        alertBox.innerHTML = '';

        // FormData automatically includes the uploaded file too
        const formData = new FormData(form);

        fetch('/WebTech/community/ajax/create_post.php', {
            method: 'POST',
            body: formData
        })
            .then(function (res) {
                return res.json(); // parse the JSON the PHP endpoint sent back
            })
            .then(function (data) {
                if (data.success) {
                    alertBox.innerHTML =
                        '<p class="alert alert-success">' + data.message + '</p>';

                    // remove the "you haven't posted yet" placeholder if present
                    if (noPostsMsg) {
                        noPostsMsg.remove();
                    }

                    // add the new post to the top of the list, no reload needed
                    recentList.insertAdjacentHTML('afterbegin', data.post.html);

                    form.reset();
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
                submitBtn.textContent = 'Publish Post';
            });
    });
});
