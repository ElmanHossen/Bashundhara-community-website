document.addEventListener('DOMContentLoaded', function () {
    const typeSelect  = document.getElementById('searchType');
    const queryInput  = document.getElementById('searchQuery');
    const searchBtn   = document.getElementById('searchBtn');
    const resultsDiv  = document.getElementById('searchResults');
    if (!typeSelect || !queryInput || !searchBtn || !resultsDiv) return;

    function escapeHtml(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    function renderResult(r) {
        return '<div class="post-card">' +
                    '<div class="post-meta">' +
                        '<span class="badge badge-general">' + escapeHtml(r.type) + '</span>' +
                    '</div>' +
                    '<h3>' + escapeHtml(r.title) + '</h3>' +
                    '<p>' + escapeHtml(r.snippet) + '</p>' +
                    '<p class="muted" style="margin-top:6px;">' + escapeHtml(r.meta) + '</p>' +
               '</div>';
    }

    function runSearch() {
        const type = typeSelect.value;
        const q = queryInput.value.trim();

        if (q.length < 2) {
            resultsDiv.innerHTML = '<p class="muted">Type at least 2 characters to search.</p>';
            return;
        }

        resultsDiv.innerHTML = '<p class="muted">Searching...</p>';

        fetch('/WebTech/community/ajax/search.php?type=' + encodeURIComponent(type) +
              '&q=' + encodeURIComponent(q))
            .then(function (res) {
                return res.json();
            })
            .then(function (data) {
                if (data.success) {
                    if (data.results.length === 0) {
                        resultsDiv.innerHTML = '<p class="muted">No results found.</p>';
                    } else {
                        resultsDiv.innerHTML = data.results.map(renderResult).join('');
                    }
                } else {
                    resultsDiv.innerHTML = '<p class="alert alert-error">' +
                        (data.message || 'Search failed.') + '</p>';
                }
            })
            .catch(function () {
                resultsDiv.innerHTML = '<p class="alert alert-error">Something went wrong. Please try again.</p>';
            });
    }

    searchBtn.addEventListener('click', runSearch);

    // Allow pressing Enter in the search box too
    queryInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            runSearch();
        }
    });
});
