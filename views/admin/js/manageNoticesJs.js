
window.addEventListener("load", loadNotices);

const noticeForm = document.getElementById("noticeForm");
noticeForm.addEventListener("submit", saveNotice);

function loadNotices() {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                showNotices(jsObj.notices);
            } else {
                document.getElementById("noticeList").innerHTML = "<p class='error'>" + jsObj.message + "</p>";
            }
        }
    };

    xhr.open("POST", "../../controllers/admin/noticeControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=list&type=all");
}

function showNotices(notices) {
    const list = document.getElementById("noticeList");

    if (notices.length == 0) {
        list.innerHTML = "<p class='msg'>Nothing published yet.</p>";
        return;
    }

    let html = "";
    for (let i = 0; i < notices.length; i++) {
        const n = notices[i];
        html += "<div class='notice'>";
        html += "<span class='tag'>" + n.type + "</span>";
        html += "<h2>" + n.title + "</h2>";
        html += "<p class='meta'>" + n.createdAt + (n.eventDate == null ? "" : " | Event date: " + n.eventDate) + "</p>";
        html += "<p>" + n.content + "</p>";
        html += "<button class='btn-small' onclick=\"editNotice(" + n.noticeId + ", '" + escapeText(n.title) + "', '" + escapeText(n.content) + "', '" + n.type + "', '" + (n.eventDate == null ? "" : n.eventDate) + "')\">Edit</button> ";
        html += "<button class='btn-small btn-danger' onclick='removeNotice(" + n.noticeId + ")'>Delete</button>";
        html += "</div>";
    }

    list.innerHTML = html;
}

function escapeText(text) {
    return text.replace(/\\/g, "\\\\").replace(/'/g, "\\'").replace(/\r?\n/g, " ");
}

function editNotice(noticeId, title, content, type, eventDate) {
    document.getElementById("noticeId").value = noticeId;
    document.getElementById("title").value = title;
    document.getElementById("content").value = content;
    document.getElementById("type").value = type;
    document.getElementById("eventDate").value = eventDate;
    document.getElementById("submitBtn").value = "Save changes";
    window.scrollTo(0, 0);
}

function resetNoticeForm() {
    noticeForm.reset();
    document.getElementById("noticeId").value = "0";
    document.getElementById("submitBtn").value = "Publish";
    document.getElementById("noticeMsg").innerHTML = "";
}

function saveNotice(e) {
    e.preventDefault();

    const ids = ["titleErr", "contentErr", "dateErr", "noticeMsg"];
    for (let i = 0; i < ids.length; i++) {
        document.getElementById(ids[i]).innerHTML = "";
    }

    const noticeId = document.getElementById("noticeId").value;
    const action = (noticeId == "0") ? "add" : "update";

    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                document.getElementById("noticeMsg").innerHTML = jsObj.message;
                resetNoticeForm();
                loadNotices();
            } else {
                if (jsObj.titleErr) document.getElementById("titleErr").innerHTML = jsObj.titleErr;
                if (jsObj.contentErr) document.getElementById("contentErr").innerHTML = jsObj.contentErr;
                if (jsObj.dateErr) document.getElementById("dateErr").innerHTML = jsObj.dateErr;
                if (jsObj.message) document.getElementById("noticeMsg").innerHTML = jsObj.message;
            }
        }
    };

    xhr.open("POST", "../../controllers/admin/noticeControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const data = "action=" + action +
        "&noticeId=" + encodeURIComponent(noticeId) +
        "&title=" + encodeURIComponent(document.getElementById("title").value) +
        "&type=" + encodeURIComponent(document.getElementById("type").value) +
        "&eventDate=" + encodeURIComponent(document.getElementById("eventDate").value) +
        "&content=" + encodeURIComponent(document.getElementById("content").value);

    xhr.send(data);
}

function removeNotice(noticeId) {
    if (!confirm("Delete this notice?")) {
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);
            document.getElementById("noticeMsg").innerHTML = jsObj.message;
            loadNotices();
        }
    };

    xhr.open("POST", "../../controllers/admin/noticeControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=delete&noticeId=" + encodeURIComponent(noticeId));
}
