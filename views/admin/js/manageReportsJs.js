
window.addEventListener("load", loadReports);

function loadReports() {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                showReports(jsObj.reports);
            } else {
                document.getElementById("reportList").innerHTML = "<div class='card'><p class='error'>" + jsObj.message + "</p></div>";
            }
        }
    };

    xhr.open("POST", "../../controllers/admin/reportControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=list&status=" + encodeURIComponent(document.getElementById("statusFilter").value));
}

function showReports(reports) {
    const list = document.getElementById("reportList");

    if (reports.length == 0) {
        list.innerHTML = "<div class='card'><p class='msg'>No reports here.</p></div>";
        return;
    }

    let html = "";
    for (let i = 0; i < reports.length; i++) {
        const r = reports[i];
        html += "<div class='card'>";
        html += "<h2>" + (r.postTitle == null ? "Deleted post" : r.postTitle) + "</h2>";
        html += "<p class='meta'>Reported by " + r.reporterName + " on " + r.createdAt + " - <span class='status status-" + r.status + "'>" + r.status + "</span></p>";
        html += "<p>Reason: " + r.reason + "</p>";

        if (r.status == "pending") {
            html += "<button class='btn-small btn-danger' onclick='hideReportedPost(" + r.postId + ", " + r.reportId + ")'>Hide post</button> ";
            html += "<button class='btn-small' onclick='resolveReport(" + r.reportId + ", \"dismissed\")'>Dismiss</button>";
        } else {
            html += "<button class='btn-small' onclick='resolveReport(" + r.reportId + ", \"pending\")'>Reopen</button>";
        }

        html += "</div>";
    }

    list.innerHTML = html;
}

function resolveReport(reportId, status) {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);
            document.getElementById("reportMsg").innerHTML = jsObj.message;
            loadReports();
        }
    };

    xhr.open("POST", "../../controllers/admin/reportControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=resolve&reportId=" + encodeURIComponent(reportId) + "&status=" + encodeURIComponent(status));
}

function hideReportedPost(postId, reportId) {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);
            document.getElementById("reportMsg").innerHTML = jsObj.message;
            loadReports();
        }
    };

    xhr.open("POST", "../../controllers/admin/reportControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=hidePost&postId=" + encodeURIComponent(postId) + "&reportId=" + encodeURIComponent(reportId));
}
