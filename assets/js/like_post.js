document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('recentPostsList');
    if (!container) return;

    // Event delegation - works for posts added dynamically too
    container.addEventListener('click', function (e) {
        const btn = e.target.closest('.like-btn');
        if (!btn) return;

        const postId = btn.dataset.postId;
        btn.disabled = true;

        const formData = new FormData();
        formData.append('post_id', postId);

        fetch('/WebTech/community/ajax/toggle_like.php', {
            method: 'POST',
            body: formData
        })
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (data.success) {
                    const countSpan = btn.querySelector('.like-count');
                    const labelSpan = btn.querySelector('.like-label');

                    countSpan.textContent = data.like_count;

                    if (data.liked) {
                        btn.classList.add('liked');
                        labelSpan.textContent = 'Liked';
                    } else {
                        btn.classList.remove('liked');
                        labelSpan.textContent = 'Like';
                    }
                } else {
                    alert(data.message || 'Something went wrong.');
                }
            })
            .catch(function (err) {
                alert('Something went wrong. Please try again.');
                console.error(err);
            })
            .finally(function () {
                btn.disabled = false;
            });
    });
});
