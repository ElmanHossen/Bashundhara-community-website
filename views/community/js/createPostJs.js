// MEMBER 2 (amit) - create post AJAX
const postForm = document.getElementById("postForm");
postForm.addEventListener("submit", callPostAjax);

function callPostAjax(e) {
    e.preventDefault();

    document.getElementById("titleErr").innerHTML = "";
    document.getElementById("categoryErr").innerHTML = "";
    document.getElementById("contentErr").innerHTML = "";
    document.getElementById("postMsg").innerHTML = "";

    const xhr = new XMLHttpRequest();
    xhr.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            const jsObj = JSON.parse(this.responseText);

            if (jsObj.success == true) {
                document.getElementById("postMsg").innerHTML = jsObj.message;
                postForm.reset();
            } else {
                if (jsObj.titleErr) document.getElementById("titleErr").innerHTML = jsObj.titleErr;
                if (jsObj.categoryErr) document.getElementById("categoryErr").innerHTML = jsObj.categoryErr;
                if (jsObj.contentErr) document.getElementById("contentErr").innerHTML = jsObj.contentErr;
                if (jsObj.message) document.getElementById("postMsg").innerHTML = jsObj.message;
            }
        }
    };

    xhr.open("POST", "../../controllers/community/postControl.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    const data = "action=add" +
        "&title=" + encodeURIComponent(document.getElementById("title").value) +
        "&categoryId=" + encodeURIComponent(document.getElementById("categoryId").value) +
        "&content=" + encodeURIComponent(document.getElementById("content").value);

    xhr.send(data);
}
