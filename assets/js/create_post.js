document.addEventListener('DOMContentLoaded', function () {
    const form        = document.getElementById('createPostForm');
    const alertBox     = document.getElementById('postAlert');
    const submitBtn     = document.getElementById('submitBtn');
    const recentList    = document.getElementById('recentPostsList');
    const noPostsMsg    = document.getElementById('noPostsMsg');

    form.addEventListener('submit', function (e) {
        e.preventDefault(); 

        submitBtn.disabled = true;
        submitBtn.textContent = 'Publishing...';
        alertBox.innerHTML = '';

        const formData = new FormData(form);

        fetch('/WebTech/community/ajax/create_post.php', {
            method: 'POST',
            body: formData
        })
            .then(function (res) {
                return res.json(); 
            })
            .then(function (data) {
                if (data.success) {
                    alertBox.innerHTML =
                        '<p class="alert alert-success">' + data.message + '</p>';

                    if (noPostsMsg) {
                        noPostsMsg.remove();
                    }

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
