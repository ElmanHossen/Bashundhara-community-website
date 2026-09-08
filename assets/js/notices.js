document.addEventListener('DOMContentLoaded', function () {
    const filter = document.getElementById('priorityFilter');
    const list = document.getElementById('noticesList');
    if (!filter || !list) return;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function renderNotice(n) {
        return '<div class="post-card">' +
                    '<div class="post-meta">' +
                        '<span class="badge badge-' + n.priority.toLowerCase() + '">' +
                            escapeHtml(n.priority) +
                        '</span>' +
                        '<span class="post-date">' + escapeHtml(n.created_at) + '</span>' +
                    '</div>' +
                    '<h3>' + escapeHtml(n.title) + '</h3>' +
                    '<p>' + escapeHtml(n.content).replace(/\n/g, '<br>') + '</p>' +
                    '<p class="muted" style="margin-top:6px;">📍 ' + escapeHtml(n.location) + '</p>' +
               '</div>';
    }

    filter.addEventListener('change', function () {
        const priority = filter.value;
        list.innerHTML = '<p class="muted">Loading...</p>';

        fetch('/WebTech/community/ajax/get_notices.php?priority=' + encodeURIComponent(priority))
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (data.success) {
                    if (data.notices.length === 0) {
                        list.innerHTML = '<p class="muted">No notices match this filter.</p>';
                    } else {
                        list.innerHTML = data.notices.map(renderNotice).join('');
                    }
                } else {
                    list.innerHTML = '<p class="alert alert-error">Could not load notices.</p>';
                }
            })
            .catch(function () {
                list.innerHTML = '<p class="alert alert-error">Could not load notices.</p>';
            });
    });
});
