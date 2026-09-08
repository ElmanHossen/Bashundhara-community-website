document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('recentPostsList');
    if (!container) return;

    // Comment content comes from users, so always escape it before
    // inserting into the page - never trust it as raw HTML.
    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function renderComment(c) {
        return '<div class="comment-item">' +
                    '<strong>' + escapeHtml(c.commenter_name) + '</strong>' +
                    '<span class="comment-date">' + escapeHtml(c.created_at) + '</span>' +
                    '<p>' + escapeHtml(c.content).replace(/\n/g, '<br>') + '</p>' +
                    '<button type="button" class="btn btn-outline btn-sm toggle-report-btn" ' +
                            'data-target-type="comment" data-target-id="' + c.comment_id + '">Report</button>' +
                    '<div class="report-form" id="report-comment-' + c.comment_id + '" style="display:none;">' +
                        '<select class="report-reason-select">' +
                            '<option value="Spam">Spam</option>' +
                            '<option value="Harassment">Harassment or Bullying</option>' +
                            '<option value="Inappropriate Content">Inappropriate Content</option>' +
                            '<option value="False Information">False Information</option>' +
                            '<option value="Other">Other</option>' +
                        '</select>' +
                        '<button type="button" class="btn btn-outline btn-sm submit-report-btn" ' +
                                'data-target-type="comment" data-target-id="' + c.comment_id + '">Submit Report</button>' +
                        '<div class="report-msg"></div>' +
                    '</div>' +
               '</div>';
    }

    // ---------- Toggle comments open/closed, load them the first time ----------
    container.addEventListener('click', function (e) {
        const toggleBtn = e.target.closest('.toggle-comments-btn');
        if (!toggleBtn) return;

        const postId  = toggleBtn.dataset.postId;
        const listDiv = document.getElementById('comments-' + postId);

        const isHidden = listDiv.style.display === 'none' || listDiv.style.display === '';

        if (isHidden) {
            listDiv.style.display = 'block';

            if (!listDiv.dataset.loaded) {
                listDiv.innerHTML = '<p class="muted">Loading comments...</p>';

                fetch('/WebTech/community/ajax/get_comments.php?post_id=' + encodeURIComponent(postId))
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (data.success) {
                            if (data.comments.length === 0) {
                                listDiv.innerHTML = '<p class="muted">No comments yet. Be the first to comment!</p>';
                            } else {
                                listDiv.innerHTML = data.comments.map(renderComment).join('');
                            }
                            listDiv.dataset.loaded = '1';
                        } else {
                            listDiv.innerHTML = '<p class="alert alert-error">Could not load comments.</p>';
                        }
                    })
                    .catch(function () {
                        listDiv.innerHTML = '<p class="alert alert-error">Could not load comments.</p>';
                    });
            }
        } else {
            listDiv.style.display = 'none';
        }
    });

    // ---------- Handle "add comment" form submissions ----------
    container.addEventListener('submit', function (e) {
        const form = e.target.closest('.add-comment-form');
        if (!form) return;
        e.preventDefault();

        const postId    = form.dataset.postId;
        const input      = form.querySelector('input[name="content"]');
        const listDiv    = document.getElementById('comments-' + postId);
        const toggleBtn  = container.querySelector('.toggle-comments-btn[data-post-id="' + postId + '"]');

        const formData = new FormData(form);

        fetch('/WebTech/community/ajax/add_comment.php', {
            method: 'POST',
            body: formData
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    input.value = '';
                    listDiv.style.display = 'block';

                    // remove "no comments yet" / loading placeholder if present
                    const placeholder = listDiv.querySelector('.muted');
                    if (placeholder) {
                        listDiv.innerHTML = '';
                    }

                    listDiv.insertAdjacentHTML('beforeend', renderComment(data.comment));
                    listDiv.dataset.loaded = '1';

                    if (toggleBtn) {
                        toggleBtn.textContent = 'Comments (' + data.comment_count + ')';
                    }
                } else {
                    alert((data.errors && data.errors[0]) || data.message || 'Could not post comment.');
                }
            })
            .catch(function () {
                alert('Something went wrong. Please try again.');
            });
    });
});
