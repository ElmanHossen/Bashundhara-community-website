window.addEventListener("load", loadPosts);

function loadPosts() {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);
            showPosts(jsObj.posts);
        }
    };

    xhr.open("POST", "../../controllers/community/postControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const data = "action=list" +
        "&search=" + encodeURIComponent(document.getElementById("searchBox").value) +
        "&categoryId=" + encodeURIComponent(document.getElementById("categoryFilter").value);

    xhr.send(data);
}

function showPosts(posts) {
    const postList = document.getElementById("postList");

    if (posts.length == 0) {
        postList.innerHTML = "<div class='card'><p class='msg'>No posts found. Be the first to write one.</p></div>";
        return;
    }

    let html = "";
    for (let i = 0; i < posts.length; i++) {
        const p = posts[i];
        html += "<div class='card post'>";
        html += "<span class='tag'>" + (p.categoryName == null ? "General" : p.categoryName) + "</span>";
        html += "<h2>" + p.title + "</h2>";
        html += "<p class='meta'>by " + p.authorName + " on " + p.createdAt + "</p>";
        html += "<p>" + p.content + "</p>";
        html += "<div class='post-actions'>";
        html += "<button class='btn-small' onclick='likePost(" + p.postId + ")'>Like (<span id='likeCount" + p.postId + "'>" + p.likeCount + "</span>)</button> ";
        html += "<a class='btn-small link-btn' href='postDetails.php?postId=" + p.postId + "'>Comments (" + p.commentCount + ")</a> ";
        html += "<button class='btn-small btn-danger' onclick='openReport(" + p.postId + ")'>Report</button>";
        html += "</div>";
        html += "<div class='report-box' id='reportBox" + p.postId + "'>";
        html += "<input type='text' id='reportReason" + p.postId + "' placeholder='Why are you reporting this?'>";
        html += "<button class='btn-small' onclick='sendReport(" + p.postId + ")'>Send report</button>";
        html += "<span class='msg' id='reportMsg" + p.postId + "'></span>";
        html += "</div>";
        html += "</div>";
    }

    postList.innerHTML = html;
}

function likePost(postId) {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                document.getElementById("likeCount" + jsObj.postId).innerHTML = jsObj.likeCount;
            } else {
                alert(jsObj.message);
            }
        }
    };

    xhr.open("POST", "../../controllers/community/likeControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("postId=" + encodeURIComponent(postId));
}

function openReport(postId) {
    const box = document.getElementById("reportBox" + postId);
    if (box.style.display == "block") {
        box.style.display = "none";
    } else {
        box.style.display = "block";
    }
}

function sendReport(postId) {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);
            document.getElementById("reportMsg" + postId).innerHTML = jsObj.message;
        }
    };

    xhr.open("POST", "../../controllers/community/reportControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const data = "postId=" + encodeURIComponent(postId) +
        "&reason=" + encodeURIComponent(document.getElementById("reportReason" + postId).value);

    xhr.send(data);
}
