
window.addEventListener("load", loadStats);

function loadStats() {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                showStats(jsObj.stats);
            } else {
                document.getElementById("statsBox").innerHTML = "<p class='error'>" + jsObj.message + "</p>";
            }
        }
    };

    xhr.open("POST", "../../controllers/admin/statsControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=stats");
}

function showStats(s) {
    const labels = [
        ["Total users", s.totalUsers],
        ["Active users", s.activeUsers],
        ["Blocked users", s.blockedUsers],
        ["Posts", s.totalPosts],
        ["Comments", s.totalComments],
        ["Approved businesses", s.totalBusinesses],
        ["Pending businesses", s.pendingBusinesses],
        ["Products", s.totalProducts],
        ["Orders", s.totalOrders],
        ["Pending reports", s.pendingReports],
        ["Total sales (Tk)", s.totalSales]
    ];

    let html = "";
    for (let i = 0; i < labels.length; i++) {
        html += "<div class='stat-box'>";
        html += "<h2>" + labels[i][1] + "</h2>";
        html += "<p class='msg'>" + labels[i][0] + "</p>";
        html += "</div>";
    }

    document.getElementById("statsBox").innerHTML = html;
}
