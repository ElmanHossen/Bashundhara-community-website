document.addEventListener('DOMContentLoaded', function () {
    const container = document.getElementById('recentPostsList');
    if (!container) return;

    container.addEventListener('click', function (e) {
        // ---------- Toggle a report form open/closed ----------
        const toggleBtn = e.target.closest('.toggle-report-btn');
        if (toggleBtn) {
            const targetType = toggleBtn.dataset.targetType;
            const targetId   = toggleBtn.dataset.targetId;
            const formDiv    = document.getElementById('report-' + targetType + '-' + targetId);
            if (!formDiv) return;

            formDiv.style.display =
                (formDiv.style.display === 'none' || formDiv.style.display === '') ? 'flex' : 'none';
            return;
        }

        // ---------- Submit a report ----------
        const submitBtn = e.target.closest('.submit-report-btn');
        if (submitBtn) {
            const targetType = submitBtn.dataset.targetType;
            const targetId   = submitBtn.dataset.targetId;
            const formDiv    = document.getElementById('report-' + targetType + '-' + targetId);
            const select     = formDiv.querySelector('.report-reason-select');
            const msgDiv     = formDiv.querySelector('.report-msg');

            submitBtn.disabled = true;
            submitBtn.textContent = 'Submitting...';

            const formData = new FormData();
            formData.append('target_type', targetType);
            formData.append('target_id', targetId);
            formData.append('reason', select.value);

            fetch('/WebTech/community/ajax/report_content.php', {
                method: 'POST',
                body: formData
            })
                .then(function (res) {
                    return res.json();
                })
                .then(function (data) {
                    if (data.success) {
                        msgDiv.innerHTML =
                            '<p class="alert alert-success report-inline-msg">' + data.message + '</p>';
                        select.disabled = true;
                        submitBtn.style.display = 'none';
                    } else {
                        msgDiv.innerHTML =
                            '<p class="alert alert-error report-inline-msg">' +
                            (data.message || 'Could not submit report.') + '</p>';
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Submit Report';
                    }
                })
                .catch(function () {
                    msgDiv.innerHTML =
                        '<p class="alert alert-error report-inline-msg">Something went wrong. Please try again.</p>';
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Submit Report';
                });
        }
    });
});
