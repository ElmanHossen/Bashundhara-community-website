document.addEventListener('DOMContentLoaded', function () {
    const recentList = document.getElementById('recentPostsList');
    if (!recentList) return;

    // Event delegation - this also works for posts added to the page
    // dynamically after Create Post, since we're not attaching to each
    // button individually.
    recentList.addEventListener('click', function (e) {
        const btn = e.target.closest('.delete-post-btn');
        if (!btn) return;

        const postId = btn.dataset.postId;
        const card = btn.closest('.post-card');

        const confirmed = confirm('Are you sure you want to delete this post? This cannot be undone.');
        if (!confirmed) return;

        btn.disabled = true;
        btn.textContent = 'Deleting...';

        const formData = new FormData();
        formData.append('post_id', postId);

        fetch('/WebTech/community/ajax/delete_post.php', {
            method: 'POST',
            body: formData
        })
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (data.success) {
                    card.remove();

                    // if that was the last post, bring back the placeholder message
                    if (recentList.children.length === 0) {
                        recentList.innerHTML =
                            '<p class="muted" id="noPostsMsg">You haven\'t posted anything yet — try the form above.</p>';
                    }
                } else {
                    alert(data.message || 'Failed to delete post.');
                    btn.disabled = false;
                    btn.textContent = 'Delete';
                }
            })
            .catch(function (err) {
                alert('Something went wrong. Please try again.');
                console.error(err);
                btn.disabled = false;
                btn.textContent = 'Delete';
            });
    });
});
