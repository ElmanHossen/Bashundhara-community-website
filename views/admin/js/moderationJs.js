
window.addEventListener("load", loadAllPosts);

function loadAllPosts() {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                showAllPosts(jsObj.posts);
            } else {
                document.getElementById("postList").innerHTML = "<div class='card'><p class='error'>" + jsObj.message + "</p></div>";
            }
        }
    };

    xhr.open("POST", "../../controllers/admin/moderationControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=listPosts&search=" + encodeURIComponent(document.getElementById("searchBox").value));
}

function showAllPosts(posts) {
    const list = document.getElementById("postList");

    if (posts.length == 0) {
        list.innerHTML = "<div class='card'><p class='msg'>No posts found.</p></div>";
        return;
    }

    let html = "";
    for (let i = 0; i < posts.length; i++) {
        const p = posts[i];
        html += "<div class='card'>";
        html += "<h2>" + p.title + "</h2>";
        html += "<p class='meta'>" + p.authorName + " (" + p.userId + ") - " + p.createdAt + " - <span class='status status-" + p.status + "'>" + p.status + "</span></p>";
        html += "<p>" + p.content + "</p>";

        if (p.status == "active") {
            html += "<button class='btn-small btn-danger' onclick='setPostStatus(" + p.postId + ", \"hidden\")'>Hide post</button> ";
        } else {
            html += "<button class='btn-small' onclick='setPostStatus(" + p.postId + ", \"active\")'>Restore post</button> ";
        }

        html += "<button class='btn-small' onclick='toggleComments(" + p.postId + ")'>Comments (" + p.commentCount + ")</button>";
        html += "<div class='comment-box' id='commentBox" + p.postId + "'></div>";
        html += "</div>";
    }

    list.innerHTML = html;
}

function setPostStatus(postId, status) {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);
            document.getElementById("modMsg").innerHTML = jsObj.message;

            if (jsObj.success == true) {
                loadAllPosts();
            }
        }
    };

    xhr.open("POST", "../../controllers/admin/moderationControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=setPostStatus&postId=" + encodeURIComponent(postId) + "&status=" + encodeURIComponent(status));
}

function toggleComments(postId) {
    const box = document.getElementById("commentBox" + postId);

    if (box.style.display == "block") {
        box.style.display = "none";
        return;
    }

    box.style.display = "block";

    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);
            showAdminComments(postId, jsObj.comments);
        }
    };

    xhr.open("POST", "../../controllers/admin/moderationControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=listComments&postId=" + encodeURIComponent(postId));
}

function showAdminComments(postId, comments) {
    const box = document.getElementById("commentBox" + postId);

    if (comments.length == 0) {
        box.innerHTML = "<p class='msg'>No comments on this post.</p>";
        return;
    }

    let html = "";
    for (let i = 0; i < comments.length; i++) {
        const c = comments[i];
        html += "<div class='comment'>";
        html += "<p class='meta'>" + c.authorName + " - " + c.createdAt + " - <span class='status status-" + c.status + "'>" + c.status + "</span></p>";
        html += "<p>" + c.comment + "</p>";

        if (c.status == "active") {
            html += "<button class='btn-small btn-danger' onclick='setCommentStatus(" + c.commentId + ", \"deleted\", " + postId + ")'>Delete</button>";
        } else {
            html += "<button class='btn-small' onclick='setCommentStatus(" + c.commentId + ", \"active\", " + postId + ")'>Restore</button>";
        }

        html += "</div>";
    }

    box.innerHTML = html;
}

function setCommentStatus(commentId, status, postId) {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);
            document.getElementById("modMsg").innerHTML = jsObj.message;

            if (jsObj.success == true) {
                document.getElementById("commentBox" + postId).style.display = "none";
                toggleComments(postId);
            }
        }
    };

    xhr.open("POST", "../../controllers/admin/moderationControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=setCommentStatus&commentId=" + encodeURIComponent(commentId) + "&status=" + encodeURIComponent(status));
}
