// MEMBER 2 (amit) - my posts: edit and delete via AJAX
window.addEventListener("load", loadMyPosts);

const editForm = document.getElementById("editForm");
editForm.addEventListener("submit", saveEdit);

function loadMyPosts() {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                showMyPosts(jsObj.posts);
            } else {
                document.getElementById("myPostList").innerHTML = "<div class='card'><p class='error'>" + jsObj.message + "</p></div>";
            }
        }
    };

    xhr.open("POST", "../../controllers/community/postControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=myPosts");
}

function showMyPosts(posts) {
    const list = document.getElementById("myPostList");

    if (posts.length == 0) {
        list.innerHTML = "<div class='card'><p class='msg'>You have not posted anything yet. <a href='createPost.php'>Write your first post</a>.</p></div>";
        return;
    }

    let html = "";
    for (let i = 0; i < posts.length; i++) {
        const p = posts[i];
        html += "<div class='card post'>";
        html += "<span class='tag'>" + (p.categoryName == null ? "General" : p.categoryName) + "</span>";
        html += "<h2>" + p.title + "</h2>";
        html += "<p class='meta'>" + p.createdAt + " - " + p.likeCount + " likes</p>";
        html += "<p>" + p.content + "</p>";
        html += "<div class='post-actions'>";
        html += "<button class='btn-small' onclick=\"openEdit(" + p.postId + ", '" + escapeText(p.title) + "', '" + escapeText(p.content) + "', " + p.categoryId + ")\">Edit</button> ";
        html += "<button class='btn-small btn-danger' onclick='removePost(" + p.postId + ")'>Delete</button>";
        html += "</div>";
        html += "</div>";
    }

    list.innerHTML = html;
}

function escapeText(text) {
    return text.replace(/\\/g, "\\\\").replace(/'/g, "\\'").replace(/\r?\n/g, " ");
}

function openEdit(postId, title, content, categoryId) {
    document.getElementById("editPostId").value = postId;
    document.getElementById("editTitle").value = title;
    document.getElementById("editContent").value = content;
    document.getElementById("editCategory").value = categoryId;
    document.getElementById("editBox").style.display = "block";
    document.getElementById("editMsg").innerHTML = "";
}

function closeEdit() {
    document.getElementById("editBox").style.display = "none";
}

function saveEdit(e) {
    e.preventDefault();

    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);
            document.getElementById("editMsg").innerHTML = jsObj.message;

            if (jsObj.success == true) {
                loadMyPosts();
                closeEdit();
            }
        }
    };

    xhr.open("POST", "../../controllers/community/postControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const data = "action=update" +
        "&postId=" + encodeURIComponent(document.getElementById("editPostId").value) +
        "&title=" + encodeURIComponent(document.getElementById("editTitle").value) +
        "&categoryId=" + encodeURIComponent(document.getElementById("editCategory").value) +
        "&content=" + encodeURIComponent(document.getElementById("editContent").value);

    xhr.send(data);
}

function removePost(postId) {
    if (!confirm("Delete this post?")) {
        return;
    }

    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                loadMyPosts();
            } else {
                alert(jsObj.message);
            }
        }
    };

    xhr.open("POST", "../../controllers/community/postControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=delete&postId=" + encodeURIComponent(postId));
}
