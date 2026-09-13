// MEMBER 4 (elman) - homepage content management AJAX
window.addEventListener("load", loadHomepageContent);

function loadHomepageContent() {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                showHomepageContent(jsObj.content);
            } else {
                document.getElementById("contentList").innerHTML = "<div class='card'><p class='error'>" + jsObj.message + "</p></div>";
            }
        }
    };

    xhr.open("POST", "../../controllers/admin/homepageControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    xhr.send("action=list");
}

function showHomepageContent(sections) {
    const list = document.getElementById("contentList");

    let html = "";
    for (let i = 0; i < sections.length; i++) {
        const s = sections[i];
        html += "<div class='card'>";
        html += "<h2>Section: " + s.section + "</h2>";
        html += "<label>Title:</label>";
        html += "<input type='text' id='title" + s.contentId + "' value=\"" + s.title.replace(/"/g, "&quot;") + "\">";
        html += "<label>Content:</label>";
        html += "<textarea id='content" + s.contentId + "'>" + s.content + "</textarea>";
        html += "<br><button class='btn-small' onclick='saveSection(" + s.contentId + ")'>Save section</button>";
        html += "<p class='meta'>Last updated: " + s.updatedAt + "</p>";
        html += "</div>";
    }

    list.innerHTML = html;
}

function saveSection(contentId) {
    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);
            document.getElementById("homeMsg").innerHTML = jsObj.message;

            if (jsObj.success == true) {
                loadHomepageContent();
            }
        }
    };

    xhr.open("POST", "../../controllers/admin/homepageControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const data = "action=update&contentId=" + encodeURIComponent(contentId) +
        "&title=" + encodeURIComponent(document.getElementById("title" + contentId).value) +
        "&content=" + encodeURIComponent(document.getElementById("content" + contentId).value);

    xhr.send(data);
}
