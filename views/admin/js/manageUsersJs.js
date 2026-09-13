// MEMBER 4 (elman) - user management AJAX
window.addEventListener("load", loadUsers);

function loadUsers() {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                showUsers(jsObj.users);
            } else {
                document.getElementById("userList").innerHTML = "<p class='error'>" + jsObj.message + "</p>";
            }
        }
    };

    xhr.open("POST", "../../controllers/admin/userManageControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const data = "action=list" +
        "&search=" + encodeURIComponent(document.getElementById("searchBox").value) +
        "&role=" + encodeURIComponent(document.getElementById("roleFilter").value);

    xhr.send(data);
}

function showUsers(users) {
    const list = document.getElementById("userList");

    if (users.length == 0) {
        list.innerHTML = "<p class='msg'>No users matched.</p>";
        return;
    }

    let html = "<table><tr><th>User Id</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Action</th></tr>";

    for (let i = 0; i < users.length; i++) {
        const u = users[i];
        html += "<tr>";
        html += "<td>" + u.userId + "</td>";
        html += "<td>" + u.name + "</td>";
        html += "<td>" + u.email + "</td>";
        html += "<td>" + u.role + "</td>";
        html += "<td><span class='status status-" + u.status + "'>" + u.status + "</span></td>";
        html += "<td>";

        if (u.role != "admin") {
            if (u.status == "active") {
                html += "<button class='btn-small btn-danger' onclick=\"setUserStatus('" + u.userId + "', 'blocked')\">Block</button>";
            } else {
                html += "<button class='btn-small' onclick=\"setUserStatus('" + u.userId + "', 'active')\">Unblock</button>";
            }
        } else {
            html += "-";
        }

        html += "</td>";
        html += "</tr>";
    }

    html += "</table>";
    list.innerHTML = html;
}

function setUserStatus(userId, status) {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);
            document.getElementById("userMsg").innerHTML = jsObj.message;

            if (jsObj.success == true) {
                loadUsers();
            }
        }
    };

    xhr.open("POST", "../../controllers/admin/userManageControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=setStatus&userId=" + encodeURIComponent(userId) + "&status=" + encodeURIComponent(status));
}
