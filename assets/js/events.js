document.addEventListener('DOMContentLoaded', function () {
    const filter = document.getElementById('eventFilter');
    const list = document.getElementById('eventsList');
    if (!filter || !list) return;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function renderEvent(ev) {
        return '<div class="post-card">' +
                    '<div class="post-meta">' +
                        '<span class="badge badge-general">Event</span>' +
                        '<span class="post-date">' + escapeHtml(ev.date_display) + '</span>' +
                    '</div>' +
                    '<h3>' + escapeHtml(ev.title) + '</h3>' +
                    '<p>' + escapeHtml(ev.description).replace(/\n/g, '<br>') + '</p>' +
                    '<p class="muted" style="margin-top:6px;">📍 ' + escapeHtml(ev.location) + '</p>' +
               '</div>';
    }

    filter.addEventListener('change', function () {
        const filterValue = filter.value;
        list.innerHTML = '<p class="muted">Loading...</p>';

        fetch('/WebTech/community/ajax/get_events.php?filter=' + encodeURIComponent(filterValue))
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (data.success) {
                    if (data.events.length === 0) {
                        list.innerHTML = '<p class="muted">No events found.</p>';
                    } else {
                        list.innerHTML = data.events.map(renderEvent).join('');
                    }
                } else {
                    list.innerHTML = '<p class="alert alert-error">Could not load events.</p>';
                }
            })
            .catch(function () {
                list.innerHTML = '<p class="alert alert-error">Could not load events.</p>';
            });
    });
});
