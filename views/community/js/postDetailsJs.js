const postId = document.getElementById("postId").value;
const currentUser = document.getElementById("currentUser").value;

window.addEventListener("load", loadComments);

const commentForm = document.getElementById("commentForm");
if (commentForm) {
    commentForm.addEventListener("submit", callCommentAjax);
}

function loadComments() {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);
            showComments(jsObj.comments);
        }
    };

    xhr.open("POST", "../../controllers/community/commentControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=list&postId=" + encodeURIComponent(postId));
}

function showComments(comments) {
    const commentList = document.getElementById("commentList");

    if (comments.length == 0) {
        commentList.innerHTML = "<p class='msg'>No comments yet.</p>";
        return;
    }

    let html = "";
    for (let i = 0; i < comments.length; i++) {
        const c = comments[i];
        html += "<div class='comment'>";
        html += "<p class='meta'>" + c.authorName + " - " + c.createdAt + "</p>";
        html += "<p>" + c.comment + "</p>";

        if (c.userId == currentUser) {
            html += "<button class='btn-small btn-danger' onclick='removeComment(" + c.commentId + ")'>Delete</button>";
        }

        html += "</div>";
    }

    commentList.innerHTML = html;
}

function callCommentAjax(e) {
    e.preventDefault();
    document.getElementById("commentMsg").innerHTML = "";

    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                showComments(jsObj.comments);
                commentForm.reset();
            } else {
                document.getElementById("commentMsg").innerHTML = jsObj.message;
            }
        }
    };

    xhr.open("POST", "../../controllers/community/commentControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const data = "action=add&postId=" + encodeURIComponent(postId) +
        "&comment=" + encodeURIComponent(document.getElementById("comment").value);

    xhr.send(data);
}

function removeComment(commentId) {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                showComments(jsObj.comments);
            } else {
                alert(jsObj.message);
            }
        }
    };

    xhr.open("POST", "../../controllers/community/commentControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=delete&commentId=" + encodeURIComponent(commentId) + "&postId=" + encodeURIComponent(postId));
}

function likeThisPost() {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                document.getElementById("likeCount").innerHTML = jsObj.likeCount;
            } else {
                alert(jsObj.message);
            }
        }
    };

    xhr.open("POST", "../../controllers/community/likeControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("postId=" + encodeURIComponent(postId));
}
